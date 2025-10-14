<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit();
}
# streber - a php5 based project management system  (c) 2005-2007  / www.streber-pm.org
# Distributed under the terms and conditions of the GPL as stated in lang/license.html

/**\file
 * classes related to rendering blocks which can be collapsed
 *
 * included by: @render_page
 * @author     Thomas Mann
 */

require_once(confGet('DIR_STREBER') . 'render/render_page.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_block.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_list_column.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_list_column_special.inc.php');

require_once(confGet('DIR_STREBER') . 'pages/task_view.inc.php');   // for build request feedback form

/**
* provide front-end for rendering and manipulating lists
*
* usage:
*
*    $block= new ListCommentsBlock($comments);
*    $block->render()
*
* @uses     ListFunction
* @usedby   most pages
*/
class CommentsOnItemBlock extends PageBlock
{
    public $item_with_comments;

    public function __construct($item)
    {
        parent::__construct(null);
        $this->item_with_comments = $item;
    }

    public function __toString()
    {
        global $PH;
        global $auth;

        #--- news -----------------------------------------------------------

        $comments = $this->item_with_comments->getComments(['order_by' => 'created']);

        $block = new PageBlock([
            'title' => sprintf(__('%s Comments'), count($comments)
                                                   ? count($comments)
                                                   : __('No', 'As in... >No< Comments')),
            'id' => 'news',
        ]);
        $block->render_blockStart();

        $count = 0;
        foreach ($comments as $c) {
            ### own comment
            $is_comment_editable = ($auth->cur_user->user_rights & RIGHT_EDITALL) || ($c->created_by == $auth->cur_user->id);

            if (!$creator = Person::getVisibleById($c->created_by)) {
                continue;
            }

            echo "<div class='post_list_entry'>";
            echo '<h3>';
            if ($c->created_by == $auth->cur_user->id) {
                echo $creator->nickname;
            } else {
                echo $creator->getLink();
            }

            echo '<span class=separator>:</span>';
            echo asHtml($c->name);

            if ($new = $c->isChangedForUser()) {
                if ($new == 1) {
                    echo '<span class=new> (' . __('New') . ') </span>';
                } else {
                    echo '<span class=new>  (' . __('Updated') . ') </span>';
                }
            }

            echo '</h3>';
            echo '<p class= details>';
            echo renderTimeAgo($c->created);

            ### get version ###
            {
                require_once(confGet('DIR_STREBER') . 'db/db_itemchange.inc.php');
                $versions = ItemVersion::getFromItem($c);
                if (count($versions) > 1) {
                    echo ' (' . $PH->getLink(
                        'itemViewDiff',
                        sprintf(__('%s. update', 'like in... Nth update'), count($versions)),
                        ['item' => $c->id]
                    );
                    echo ' ' . renderTimeAgo($c->modified);
                    echo ') ';
                }
            }

            if ($c->pub_level != PUB_LEVEL_OPEN) {
                echo ' - ' . sprintf(__('visible as %s'), renderPubLevelName($c->pub_level));

                ### publish ###
                if (
                    ($parent_task = Task::getEditableById($c->task))
                    && ($c->pub_level < PUB_LEVEL_OPEN)
                ) {
                    echo ' - ' . $PH->getLink('itemsSetPubLevel', __('Publish'), ['item' => $c->id, 'item_pub_level' => PUB_LEVEL_OPEN]);
                }
            }

            ### delete
            if ($is_comment_editable) {
                echo ' - ' . $PH->getLink('commentsDelete', __('Delete'), ['comment' => $c->id]);
            }

            echo '</p>';

            if ($is_comment_editable) {
                echo wikifieldAsHtml($c, 'description');
            } else {
                echo wikifieldAsHtml($c, 'description', ['editable' => false]);
            }

            echo '</div>';
            $c->nowViewedByUser();
        }
        $this->render_blockEnd();
        return '';
    }

    public function render_blockFooter()
    {
        global $auth;
        if (!$auth->isAnonymousUser()) {
            $this->_render_commentField();
        }
    }

    protected function _render_commentField()
    {
        global $PH;
        echo '<div class=footer_form>';

        require_once(confGet('DIR_STREBER') . 'render/render_form.inc.php');
        $project = new Project($this->item_with_comments->project);

        $form = new PageForm();
        $form->button_cancel = false;

        ### add comment ###
        {
            $form->add(new Form_CustomHTML('<h3>' . __('Add Comment') . '</h3>'));

            ### Comment ###
            $comment_name = '';
            $comment = new Comment([
                'id' => 0,
                'name' => $comment_name,
            ]);

            $e = $comment->fields['description']->getFormElement($comment, __('Comment'));
            $e->rows = 8;
            $form->add($e);
            $form->add($comment->fields['name']->getFormElement($comment, __('Summary')));

            ### request feedback
            $form->add(buildRequestFeedbackInput($project));
        }

        /**
        * to reduce spam, enforce captcha test for guests
        */
        global $auth;
        if ($auth->cur_user->id == confGet('ANONYMOUS_USER')) {
            $form->addCaptcha();
        }

        ### some required hidden fields for correct data passing ###
        $form->add(new Form_HiddenField('comment_task', '', $this->item_with_comments->id));
        $form->add(new Form_HiddenField('comment', '', 0));

        if ($return = get('return')) {
            $form->add(new Form_HiddenField('return', '', asHtml($return)));
        }

        $PH->go_submit = 'commentEditSubmit';
        echo $form;
        echo '</div>';
    }
}

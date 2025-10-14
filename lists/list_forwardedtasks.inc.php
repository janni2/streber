<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit;
}
# streber - a php based project management system
# Copyright (c) 2005 Thomas Mann - thomas@pixtur.de
# Distributed under the terms and conditions of the GPL as stated in docs/license.txt

/**
 * derived ListBlock-class for listing forwarded tasks
 *
 * @includedby:     pages/*
 *
 * @author:         Esther Burger
 * @uses:           ListBlock
 * @usedby:
 *
 */

require_once('db/class_taskperson.inc.php');
require_once('db/class_task.inc.php');
require_once('lists/list_tasks.inc.php');

class ListBlock_forwarded_tasks extends ListBlock
{
    public function __construct($args = null)
    {
        parent::__construct($args);

        global $PH;
        global $auth;

        $this->id = 'forwarded_tasks';
        $this->title = __('Your demand notes');
        $this->no_items_html = __('You have no demand notes');
        $this->show_icons = true;

        $this->add_col(new ListBlockCol_ForwardedTaskStatus());
        $this->add_col(new ListBlockCol_ForwardedTaskName());
        $this->add_col(new ListBlockCol_ForwardedTaskComment());
        $this->add_col(new ListBlockCol_ForwardedTaskModified());
    }

    public function print_automatic()
    {
        global $PH;
        global $auth;

        $this->active_block_function = 'list';
        $this->query_options['person'] = $auth->cur_user->id;
        $this->query_options['forward'] = 1;
        $this->query_options['state'] = 1;

        $task_people = TaskPerson::getTaskPeople($this->query_options);

        $this->render_list($task_people);
    }
}

/**
* returns the state of the forwarded task
*
*/
class ListBlockCol_ForwardedTaskStatus extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Status');
        $this->width = '10%';
    }

    public function render_tr(&$obj, $style = '')
    {
        global $g_status_names;

        $status_name = '';
        $extra = '';

        #if($task = Task::getVisibleById($obj->task)) {
        if ($task = Task::getById($obj->task)) {
            $status_name = asHtml($g_status_names[$task->status]);

            if ($task->status <= STATUS_NEW) {
                $extra = 'new';
            }

            if ($task->status >= STATUS_COMPLETED) {
                $extra = 'done';
            }

            if ($task->status == STATUS_OPEN) {
                $extra = 'open';
            }

            if ($task->status >= STATUS_COMPLETED) {
                $extra = 'isDone';
            }
        }

        if ($status_name) {
            echo "<td class='status $extra'>$status_name</td>";
        } else {
            echo '<td>-</td>';
        }
    }
}

/**
* returns the name of the forwarded task
*
*/
class ListBlockCol_ForwardedTaskName extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Name');
        $this->width = '30%';
    }

    public function render_tr(&$obj, $style = '')
    {
        global $PH;

        $str_url = '';
        $str_name = '';
        $isDone = '';
        $html_details = '';
        $link = '';

        #if($task = Task::getVisibleById($obj->task)) {
        if ($task = Task::getById($obj->task)) {
            $str_name = asHtml($task->name);
            $str_url = $PH->getUrl('taskView', ['tsk' => $task->id]);
            if ($task->status >= STATUS_COMPLETED) {
                $isDone = 'class=isDone';
            }
            #if($prj = Project::getVisibleById($task->project)) {
            if ($prj = Project::getById($task->project)) {
                $link = $PH->getLink('projView', $prj->getShort(), ['prj' => $prj->id]);
                $html_details .= __('in', 'very short for IN folder...') . ' ' . $link;
                if ($tmp = $task->getFolderLinks()) {
                    $html_details .= ' > ' . $tmp;
                }
            }
        }

        if ($str_name) {
            echo "<td class='nowrap'><span $isDone><a href='$str_url'>$str_name</a></span>";
            if ($html_details) {
                echo "<br><span class='sub who'>$html_details</span>";
            }
            echo '</td>';
        } else {
            $PH->abortWarning('Could not get type of the element.', ERROR_BUG);
            echo '<td>&nbsp;</td>';
        }
    }
}

/**
* returns the comment of the forwarded task
*
*/
class ListBlockCol_ForwardedTaskComment extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Comment');
        $this->width = '35%';
    }

    public function render_tr(&$obj, $style = '')
    {
        global $PH;

        if ($obj->forward_comment) {
            echo '<td><span>' . $obj->forward_comment . '</span></td>';
        } else {
            echo '<td>-</td>';
        }
    }
}

/**
* returns the modification date of the forwarded task
*
*/
class ListBlockCol_ForwardedTaskModified extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Modified');
        $this->width = '10%';
    }

    public function render_tr(&$obj, $style = '')
    {
        global $PH;
        $str_date = '';
        $str_name = '';
        $str_url = '';

        if ($i = DbProjectItem::getById($obj->id)) {
            if ($i->modified) {
                $mod_date = $i->modified;
                $str_date = renderDateHtml($mod_date);
                if ($i->modified_by) {
                    #if($person = Person::getVisibleById($i->modified_by)){
                    if ($person = Person::getById($i->modified_by)) {
                        $str_name = asHtml($person->name);
                        $str_url = $person->getLink();
                    }
                }
                echo '<td><span class=date>' . $str_date . '</span><br><span class="sub who">' . __('by') . ' ' . $str_url . '</span></td>';
            } else {
                echo "<td class='nowrap'>&nbsp;</td>";
            }
        } else {
            $PH->abortWarning('Could not get modification date of the element.', ERROR_BUG);
            echo "<td class='nowrap'>-</td>";
        }
    }
}

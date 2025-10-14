<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit();
}
# streber - a php based project management system
# Copyright (c) 2005 Thomas Mann - thomas@pixtur.de
# Distributed under the terms and conditions of the GPL as stated in docs/license.txt

require_once(confGet('DIR_STREBER') . 'db/class_file.inc.php');

class ListGroupingMimetype extends ListGrouping
{
    public function __construct($args = null)
    {
        $this->id = 'mimetype';
        $this->name = __('Type');
        parent::__construct($args);
    }

    /**
    * render separating row
    */
    public function render(&$item)
    {
        if (!($item instanceof DbProjectItem)) {
            trigger_error("can't group for mimetype", E_USER_NOTICE);
            return '---';
        } else {
            return $item->mimetype;
        }
    }
}

class ListGroupingParentItem extends ListGrouping
{
    public function __construct($args = null)
    {
        $this->id = 'parent_item';
        $this->name = __('Parent item');
        parent::__construct($args);
    }

    /**
    * render separating row
    */
    public function render(&$item)
    {
        if (!($item instanceof DbProjectItem)) {
            trigger_error("can't group for parent_item", E_USER_NOTICE);
            return '---';
        } else {
            if ($task = Task::getVisibleById($item->parent_item)) {
                return $task->getFolderLinks(false) . '&gt;' . $task->getLink(false);
            } else {
                return '/';
            }
        }
    }
}

/**
 * derived ListBlock-class for listing files
 *
 * @includedby:     pages/*
 *
 * @author         Thomas Mann
 * @uses:           ListBlock
 * @usedby:
 *
 */
class ListBlock_files extends ListBlock
{
    public function __construct($args = null)
    {
        parent::__construct($args);

        global $PH;
        $this->id = 'files';
        $this->title = 'Files';
        $this->query_options['order_by'] = 'created';

        $this->query_options['latest_only'] = true;

        ### columns ###
        $this->add_col(new ListBlockColSelect());
        $this->add_col(new ListBlockCol_FileThumbnail());
        $this->add_col(new ListBlockCol_FileName());
        $this->add_col(new ListBlockColFormat([
            'key' => 'version',
            'name' => __('Version'),
            'sort' => 0,
            'format' => '{?version}',
        ]));
        $this->add_col(new ListBlockCol_CreatedBy());
        $this->add_col(new ListBlockColDate());
        $this->add_col(new ListBlockCol_FileSummary());

        ### functions ###
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('fileEdit')->id,
            'name' => __('Edit file'),
            'id' => 'fileEdit',
            'icon' => 'edit',
            'context_menu' => 'submit',
        ]));
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('filesDelete')->id,
            'name' => __('Delete'),
            'id' => 'filesDelete',
            'icon' => 'delete',
            'context_menu' => 'submit',
        ]));

        $this->add_function(new ListFunction([
            'target' => $PH->getPage('filesMoveToFolder')->id,
            'name' => __('Move files'),
            'id' => 'filesMoveToFolder',
            'context_menu' => 'submit',
        ]));
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('itemsAsBookmark')->id,
            'name' => __('Mark as bookmark'),
            'id' => 'itemsAsBookmark',
            'context_menu' => 'submit',
        ]));
        /*
        $this->add_function(new ListFunction(array(
            'target'=>$PH->getPage('fileNew')->id,
            'name'  =>__('New file'),
            'id'    =>'fileNew',
            'icon'  =>'new',
            'context_menu'=>'submit',
        )));
        */

        ### block style functions ###
        $this->add_blockFunction(new BlockFunction([
            'target' => 'changeBlockStyle',
            'key' => 'list',
            'name' => __('List', 'List sort mode'),
            'params' => [
                'style' => 'list',
                'block_id' => $this->id,
                'page_id' => $PH->cur_page->id,
#                'use_collapsed'=>true,
             ],
            'default' => true,
        ]));
        $this->groupings = new BlockFunction_grouping([
            'target' => 'changeBlockStyle',
            'key' => 'grouped',
            'name' => __('Grouped', 'List sort mode'),
            'params' => [
                'style' => 'grouped',
                'block_id' => $this->id,
                'page_id' => $PH->cur_page->id,
            ],
        ]);

        $this->add_blockFunction($this->groupings);

        ### list groupings ###
        $this->groupings->groupings = [
            new ListGroupingStatus(),
            new ListGroupingMimetype(),
            new ListGroupingCreatedBy(),
            new ListGroupingParentItem(),
        ];
    }

    /**
    * print a complete list as html
    * - use filters
    * - use check list style (tree, list, grouped)
    * - check customization-values
    * - check sorting
    * - get objects from database
    *
    */
    public function print_automatic(&$project = null)
    {
        global $PH;

        if (!$this->active_block_function = $this->getBlockStyleFromCookie()) {
            $this->active_block_function = 'list';
        }

        if ($project) {
            $this->query_options['project'] = $project->id;
        }

        $this->group_by = get("blockstyle_{$PH->cur_page->id}_{$this->id}_grouping");

        $s_cookie = "sort_{$PH->cur_page->id}_{$this->id}_{$this->active_block_function}";
        if ($sort = get($s_cookie)) {
            $this->query_options['order_by'] = $sort;
        }

        ### add filter options ###
        if (!$this->no_items_html) {
            $this->no_items_html = __('No files uploaded');
        }

        ### grouped view ###
        if ($this->active_block_function == 'grouped') {
            ### prepend key to sorting ###
            if (isset($this->query_options['order_by'])) {
                $this->query_options['order_by'] = $this->groupings->getActiveFromCookie() . ',' . $this->query_options['order_by'];
            } else {
                $this->query_options['order_by'] = $this->groupings->getActiveFromCookie();
            }

            /**
            * @@@ later use only once...
            *
            *   $this->columns= filterOptions($this->columns,"CURPAGE.BLOCKS[{$this->id}].STYLE[{$this->active_block_function}].COLUMNS");
            */
            if (isset($this->columns[$this->group_by])) {
                unset($this->columns[$this->group_by]);
            }
        }
        ### list view ###
        else {
            $foo = true;
        }
        $files = File::getAll($this->query_options);
        $this->render_list($files);
    }
}

class ListBlockCol_FileDownload extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Download', 'Column header');
        $this->id = 'download';
        $this->width = '90';
    }

    public function render_tr(&$file, $style = '')
    {
        global $PH;
        if (!isset($file) || !$file instanceof File) {
            return;
        }
        $buffer = '';

        if ($file->mimetype == 'image/png'
          ||
          $file->mimetype == 'image/x-png'
          ||
          $file->mimetype == 'image/jpeg'
          ||
          $file->mimetype == 'image/pjpeg'
          ||
          $file->mimetype == 'image/jpg'
          ||
          $file->mimetype == 'image/gif'
        ) {
            $buffer = "<a target='blank' href='" . $PH->getUrl('fileDownloadAsImage', ['file' => $file->id]) . "'><img class='left' title='" . asHtml($file->name) . "' alt='" . asHtml($file->name) . "' src='" . $PH->getUrl('fileDownloadAsImage', ['file' => $file->id, 'max_size' => 100]) . "'></a>";
        } else {
            $buffer = $PH->getLink('fileDownload', $file->name, ['file' => $file->id]);
        }
        echo "<td>$buffer</td>";
    }
}

class ListBlockCol_FileName extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('File', 'Column header');
        $this->id = 'name';
        $this->width = '80%';
        $this->key = 'name';
    }

    public function render_tr(&$file, $style = '')
    {
        global $PH;
        if (!isset($file) || !$file instanceof File) {
            return;
        }

        require_once(confGet('DIR_STREBER') . 'render/render_wiki.inc.php');

        $buffer = '<b>';

        $buffer .= $PH->getLink('fileDownload', $file->name, ['file' => $file->id]);
        $buffer .= '</b>'
                  . '<br />';

        ### name ###
        #$buffer = '<b>'
        #        . $PH->getLink('fileView',$file->name, array('file'=>$file->id),'item file')
        #        .'</b>'
        #        . '<br>';

        ### parent task ###
        $diz_buffer = '';

        if ($file->parent_item && $item = DbProjectItem::getVisibleById($file->parent_item)) {
            if ($item->type == ITEM_TASK) {
                if ($task = Task::getVisibleById($file->parent_item)) {
                    $tmp = $task->getFolderLinks();
                    if ($tmp) {
                        $tmp = $tmp . '&gt;';
                    }
                    $diz_buffer .= __('in', '... folder') . ': ' . $tmp . $task->getLink(false) . '<br/>';
                }
            }
        }

        ### details ###
        $diz_buffer .= $file->filesize . ' bytes'
                    . ' / '
                    . asHtml($file->mimetype)
                    . ' / '
                    . sprintf(__('ID %s'), $file->id)
                    . ' / '
                    . '<span class=sub>' . $PH->getLink('fileView', __('Show Details'), ['file' => $file->id]) . '</span>'
                    . '<br/>';

        ### description ###
        $diz_buffer .= wikifieldAsHtml($file, 'description');

        if ($diz_buffer) {
            $buffer .= '<span class=sub>' . $diz_buffer . '</span>';
        }

        echo '<td>' . $buffer . '</td>';

        #$str= $PH->getLink('fileView',$obj->name, array('file'=>$obj->id),'item file');
        #print "<td>$str</td>";
    }
}

class ListBlockCol_ParentTask extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Attached to', 'Column header');
        $this->id = 'parent_task';
    }

    public function render_tr(&$obj, $style = '')
    {
        global $PH;
        if (!isset($obj) || !$obj instanceof File) {
            return;
        }

        if ($task = Task::getVisibleById($obj->parent_item)) {
            echo '<td>' . $task->getLink(false) . '</td>';
        } else {
            echo '<td></td>';
        }
    }
}

/**
* short single column for taskView
*/
class ListBlockCol_FileSummary extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Summary', 'Column header');
        $this->id = 'summary';
    }

    public function render_tr(&$file, $style = '')
    {
        global $PH;
        if (!isset($file) || !$file instanceof File) {
            return;
        }

        #$buffer = $PH->getLink('fileView',__("Details"), array('file'=>$file->id),'item file');

        $buffer = '';

        if ($file->mimetype == 'image/png'
          ||
          $file->mimetype == 'image/x-png'
          ||
          $file->mimetype == 'image/jpeg'
          ||
          $file->mimetype == 'image/pjpeg'
          ||
          $file->mimetype == 'image/jpg'
          ||
          $file->mimetype == 'image/gif'
        ) {
            if ($author = Person::getVisibleById($file->created_by)) {
                $author_name = $author->nickname;
            } else {
                $author_name = '???';
            }

            $buffer .= '<span class=sub><a '
                   . "title='" . sprintf(__('creatd on %s', 'date a file was created'), renderDate($file->created)) . "' "
                   . "target='blank' "
                   . "href='" . $PH->getUrl('fileDownloadAsImage', ['file' => $file->id]) . "' "
                   . "><img class='left' title='" . asHtml($file->name) . "' alt='" . asHtml($file->name) . "' "
                   #. "src='".$PH->getUrl('fileDownloadAsImage',array('file'=>$file->id,'max_size'=>100))."'"
                   . "src='" . $file->getCachedUrl(100) . "'"
                   . '><br>'
                   . ''
                   . asHtml($file->name)
                   . '</a>'
                   . '</span>'
                   . '<br>'
                   . "<span class=sub title='" . __('click to show details') . "'>"
                   . $PH->getLink('fileView', '#' . $file->id, ['file' => $file->id])
                   . ' '
                   . sprintf(__('by %s', 'person who uploaded a file'), $author_name)
                   . ', '
                   . renderFilesize($file->filesize)
                   . '<br>'
                   . '</span>';
        } else {
            $buffer .= '<b>' . $PH->getLink('fileDownload', $file->name, ['file' => $file->id]) . '</b>'
               . '<br>'
               . '<span class=sub>' . $file->filesize . ' bytes' . ' / ' . sprintf(__('ID %s'), $file->id) . ' / ' . renderDateHtml($file->created) . '</span>'
               . '<br>'
               . '<span class=sub>' . $PH->getLink('fileView', __('Show Details'), ['file' => $file->id]) . '</span>'
            ;
        }

        echo "<td class='attachment_summary'>$buffer</td>";
    }
}

class ListBlockCol_FileThumbnail extends ListBlockCol
{
    public function __construct($args = null)
    {
        parent::__construct($args);
        $this->name = __('Thumbnail', 'Column header');
        $this->id = 'thumbnail';
        $this->width = '10';
    }

    public function render_tr(&$file, $style = '')
    {
        global $PH;
        if (!isset($file) || !$file instanceof File) {
            return;
        }

        #$buffer = $PH->getLink('fileView',__("Details"), array('file'=>$file->id),'item file');

        $buffer = '';

        if ($file->mimetype == 'image/png'
          ||
          $file->mimetype == 'image/x-png'
          ||
          $file->mimetype == 'image/jpeg'
          ||
          $file->mimetype == 'image/pjpeg'
          ||
          $file->mimetype == 'image/jpg'
          ||
          $file->mimetype == 'image/gif'
        ) {
            $buffer .= "<a target='blank' href='" . $PH->getUrl('fileDownloadAsImage', ['file' => $file->id]) . "'>";

            #$buffer.= "<img class='left' title='".asHtml($file->name)."' alt='".asHtml($file->name)."' src='".$PH->getUrl('fileDownloadAsImage',array('file'=>$file->id,'max_size'=>100))."'>"
            $buffer .= "<img class='left' title='"
                    . asHtml($file->name) . "' "
                    . " alt='" . asHtml($file->name) . "'"
                    #. " src='".$PH->getUrl('fileDownloadAsImage',array('file'=>$file->id,'max_size'=>100)) . "'"
                    . " src='" . $file->getCachedUrl(100) . "'"
                    . '>';
            $buffer .= '</a>';
        }

        echo "<td>$buffer</td>";
    }
}

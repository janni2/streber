<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit();
}
require_once('db/class_task.inc.php');
require_once('db/class_project.inc.php');

/**
* contains functions for querying and editing items with ajax
*
* read more at: http://www.streber-pm.org/3695
*/

/**
* get field value of an item for inplace editing
*/
function itemLoadField()
{
    header('Content-type: text/html; charset=utf-8');
    header('Expires: -1');
    header('Cache-Control: post-check=0, pre-check=0');
    header('Pragma: no-cache');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    if (!$item_id = get('item')) {
        echo 'Failure: could not get item_id';
        return null;
    }
    if (!$item = DbProjectItem::getVisibleById($item_id)) {
        echo 'Failure: could not get item #' . intval($item_id);
        return null;
    }
    if (!$object = DbProjectItem::getObjectById($item_id)) {
        echo 'Failure: could not get object #' . intval($item_id);
        return null;
    }

    $field_name = 'description';
    if (get('field')) {
        $field_name = asCleanString(get('field'));
    }

    if (!isset($object->fields[$field_name])) {
        return null;
    }
    require_once(confGet('DIR_STREBER') . 'render/render_wiki.inc.php');

    $chapter = intval(get('chapter'));
    if (is_null($chapter)) {
        echo $object->$field_name;
    } else {
        echo getOneWikiChapter($object->$field_name, $chapter);
    }
}

/**
* save field value of an item which has been edited inplace
* and return formatted html code.
*
* If only a chapter has been edited,  number defined in "chapter"
*/
function itemSaveField()
{
    header('Content-type: text/html; charset=utf-8');

    ### disable page caching ###
    header('Expires: -1');
    header('Cache-Control: post-check=0, pre-check=0');
    header('Pragma: no-cache');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    $value = get('value');
    if (is_null($value)) {
        return;
    }

    $plainmode = get('plain'); // without wiki formatting

    if (!$item_id = get('item')) {
        echo 'Failure';
        return;
    }
    global $g_wiki_project;

    if (!$item = DbProjectItem::getEditableById($item_id)) {
        echo 'Failure';
        return;
    }
    if (!$object = DbProjectItem::getObjectById($item_id)) {
        echo 'Failure';
        return;
    }

    if ($item->type == ITEM_PROJECT) {
        if (!$project = Project::getVisibleById($item->id)) {
            echo 'Failure getting project';
            return;
        }
    } elseif (!$project = Project::getVisibleById($item->project)) {
        echo 'Failure getting project';
        return;
    }
    $g_wiki_project = $project;

    $field_name = 'description';
    if (get('field')) {
        $field_name = asCleanString(get('field'));
    }
    if (!isset($object->fields[$field_name])) {
        return null;
    }
    require_once(confGet('DIR_STREBER') . 'render/render_wiki.inc.php');

    $chapter = intval(get('chapter'));

    ### replace complete field ###
    if (is_null($chapter)) {
        $object->$field_name = $value;
    }
    ### only replace chapter ###
    else {
        require_once(confGet('DIR_STREBER') . 'render/render_wiki.inc.php');

        /**
        * split originial wiki block into chapters
        * start with headline and belonging text
        */
        $org = $object->$field_name;
        if ($object->type == ITEM_TASK) {
            global $g_wiki_task;
            $g_wiki_task = $object;
        }
        $parts = getWikiChapters($org);

        ### replace last line return (added by textarea) ###
        if (!preg_match("/\n$/", $value)) {
            $value .= "\n";
        }
        #$value= str_replace("\\'", "'", $value);
        #$value= str_replace('\\"', "\"", $value);

        $parts[$chapter] = $value;

        $new_wiki_text = implode('', $parts);

        $object->$field_name = $new_wiki_text;
    }

    ### update
    $object->update([$field_name]);

    ### mark parent of comment as changes
    if ($item->type == ITEM_COMMENT) {
        if ($parent_task = Task::getById($object->task)) {
            echo 'calling now changed by user';
            $parent_task->nowChangedByUser();
        }
    }

    if ($plainmode) {
        echo $object->$field_name;
    } else {
        echo wiki2purehtml($object->$field_name);
    }

    $item->nowChangedByUser();
}

/**
* get recent changes for ajax request from home @ingroup pages
*
* @Params
* - prj
* - start
* - count
*
* @NOTE
* This page function was formerly a part of home.inc.php but since it will
* be used in other places as well, item_ajax might be a better place for it.
*/
function AjaxMoreChanges()
{
    require_once(confGet('DIR_STREBER') . 'std/class_changeline.inc.php');
    require_once(confGet('DIR_STREBER') . 'lists/list_recentchanges.inc.php');

    global $auth;
    header('Content-type: text/html; charset=utf-8');

    if (!$project = Project::getVisibleById(get('prj'))) {
        return;
    }
    $start = is_null(get('start'))
          ? 0
          : intval(get('start'));

    $count = is_null(get('count'))
          ? 20
          : intval(get('count'));

    $options = [
        'project' => $project->id,
        'unviewed_only' => false,
        'limit_rowcount' => $count,
        'limit_offset' => $start,
        'type' => [ITEM_TASK, ITEM_FILE],
    ];

    if ($auth->cur_user->settings & USER_SETTING_FILTER_OWN_CHANGES) {
        $options['not_modified_by'] = $auth->cur_user->id;
    }

    /**
    * first query all unviewed changes
    */
    if ($changes = ChangeLine::getChangeLines($options)) {
        $lines = 0;
        foreach ($changes as $c) {
            printChangeLine($c);
        }
    }
}

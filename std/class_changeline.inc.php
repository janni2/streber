<?php if(!function_exists('startedIndexPhp')) { header("location:../index.php"); exit();}
# streber - a php based project management system
# Copyright (c) 2005 Thomas Mann - thomas@pixtur.de
# Distributed under the terms and conditions of the GPL as stated in docs/license.txt

/**
 * changeline class to collect and sort changes to items
 *
 * @includedby:     pages/*
 *
 * @author         Thomas Mann
 * @uses:           ListChanges
 * @usedby:

*/

require_once(confGet('DIR_STREBER') . 'db/class_comment.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_page.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_list.inc.php');
require_once(confGet('DIR_STREBER') . "db/db_itemchange.inc.php");

define('COMMENT_LEN_PREVIEW',   240);
define('ITEM_DELETED', 1);
define('ITEM_MODIFIED', 2);
define('ITEM_NEW', 3);


/**
* class for assembling information in a change-list-line
*
* Some notes:
* - changes are collected for each person since given date by the static function "getChangeLinesForPerson()"
* - this function returns a list of ChangeLines that can be used either to print html-lists or
*   to compose Notification mails
* - this function has not been optimized for performance
*
*/



class ChangeLine extends BaseObject 
{
    
    const UNDEFINED = 0;
    const NEW_TOPIC = 1;
    const NEW_TASK  = 2;
    const NEW_FILE  = 3;
    const UPDATED   = 4;
    const COMMENTED = 5;
    const MOVED     = 6;
    const RENAMED   = 7;
    const EDITED_WIKI= 8;
    const ATTACHED_FILE =9;
    const ASSIGNED  =10;
    const DELETED  =11;

    public $project_id;
    public $timestamp;
    public $person_by;
    public $relavant_to_cur_user= true;
    public $html_what;
    public $txt_what;
    public $type= ChangeLine::UNDEFINED;   #type
    public $item;
    public $task;
    public $item_id;
    public $task_html;
    public $html_details;
    public $html_assignment;




    public function __construct($args)
    {
        parent::__construct($args);
    }



    static function getChangeLinesForPerson(&$person, $project=NULL, $date_compare=NULL)
    {
        global $PH;
        $query_options= [];

        if(!$date_compare) {
            $date_compare= $person->last_logout;
        }

        if($project) {
            $query_options['project']= $project->id;
        }
        fillMissingValues($query_options, [
            'alive_only'     => false,
            'date_min'       => $date_compare,
            'not_modified_by'=> $person->id,
        ]);

        $change_lines= ChangeLine::getChangeLines($query_options);
        return $change_lines;
    }


    static function getChangeLines($query_options)
    {
        global $PH;

        global $auth;
        fillMissingValues($query_options, [
            'alive_only' => false,
        ]);


        $date_compare= isset($query_options['date_min'])
                    ? $query_options['date_min']
                    : "0000-00-00";


        /**
        * get list of items touched by other people
        */       
        $changed_items= DbProjectItem::getAll($query_options);
       
        
        /**
        * go through list
        */

        $changes= [];
        foreach($changed_items as $i) {

            $change_type= NULL;

            if(!isset($query_options['project'])){
                $project= Project::getVisibleById($i->project);
            }
            else {
                $project= NULL;
            }

            /**
            * get item-change-type depeding on dates
            */
            if($i->deleted >= $i->modified) {
                $change_type= ITEM_DELETED;
            }
            else if($i->modified > $i->created) {
                $change_type= ITEM_MODIFIED;
            }
            else {
                $change_type= ITEM_NEW;
            }


            /**
            * build up change-list
            */
            switch($change_type) {
            case ITEM_NEW:

                if($i->type == ITEM_TASK) {

                    if(!$task= Task::getVisibleById($i->id)) {
                        continue;
                    }

                    if($assigned_people= $task->getAssignedPeople()) {
                        $tmp=[];

                        foreach($assigned_people as $ap) {
                            $tmp[]= $ap->getLink();
                        }

                        $html_assignment= __('to','very short for assigned tasks TO...'). ' ' . implode(', ', $tmp);
                    }
                    else {
                        $html_assignment= '';
                    }



                    $html_details= '';
                    if($tmp= $task->getFolderLinks(true, $project)) {
                        $html_details .=__('in', 'very short for IN folder...'). ' '. $tmp;
                    }

                    if($task->prio != PRIO_NORMAL && $task->prio != PRIO_UNDEFINED) {
                        global $g_prio_names;
                        $html_details .= ' / ' . $g_prio_names[$task->prio];
                    }


                    $change= new ChangeLine([
                        'item'      =>      $task,
                        'person_by' =>      $i->created_by,
                        'timestamp' =>      $i->created,
                        'item_id'   =>      $i->id,
                        #'task'      =>      $task,
                        'html_what' =>      '<span class=new>'. __('new') .' '. $task->getLabel() .'</span>',
                        'txt_what'  =>      __('new') .' '. $task->getLabel(),
                        'type'      =>      ChangeLine::NEW_TASK,
                        'html_assignment'=> $html_assignment,
                        'html_details'=>    $html_details,
                    ]);
                    $changes[]= $change;

                }
                else if ($i->type == ITEM_FILE) {
                    require_once(confGet('DIR_STREBER') . 'db/class_file.inc.php');
                    if($file= File::getVisibleById($i->id)) {                    
                        $change= new ChangeLine([
                            'item'      =>      $file,
                            'person_by' =>      $i->created_by,
                            'timestamp' =>      $i->created,
                            'item_id'   =>      $i->id,
                            'html_what' =>      __('New file'),
                            'txt_what'  =>      __('New file'),
                            'type'      =>      ChangeLine::NEW_FILE,
                            'html_details'=>    $file->name,
                        ]);
                        $changes[]= $change;
                    }
                }

                break;

            case ITEM_MODIFIED:

                $timestamp_last_change= $date_compare;                 # make sure we use the last occured change type
                /**
                * modified tasks
                */
                $type = ChangeLine::UPDATED;
                if($i->type == ITEM_TASK) {


                    if(!$task= Task::getVisibleById($i->id)) {
                        continue;
                    }

                    if($assigned_people= $task->getAssignedPeople()) {
                        $tmp=[];

                        foreach($assigned_people as $ap) {
                            $tmp[]= $ap->getLink();
                        }

                        $html_assignment= __('to','very short for assigned tasks TO...'). ' ' . implode(', ', $tmp);
                    }
                    else {
                        $html_assignment= '';
                    }

                    $html_details= '';
                    if($tmp= $task->getFolderLinks(true, $project)) {
                        $html_details .=__('in', 'very short for IN folder...'). ' '. $tmp;
                    }

                    $txt_what= $html_what= __('modified');
                    $type= ChangeLine::UPDATED;

                    ### try to get comments
                    {
                        $html_comment= '';
                        if($comments = Comment::getAll([
                            'person' => $i->modified_by,
                            'task' => $task->id,
                            'date_min'  => $timestamp_last_change,
                            'order_by' => 'created ASC',

                        ])) {
                            $last_comment= $comments[count($comments)-1];
                            $timestamp_last_change= $last_comment->created;


                            if($last_comment->name && $last_comment->name != __('New Comment')) {      # ignore default title
                                $html_comment= strip_tags($last_comment->name). ': ';
                            }
                            $html_comment.= strip_tags($last_comment->description);
                            $html_comment = asHtml($html_comment);
                        }
                    }

                    ### get changed fields ###
                    $changed_fields_hash=[];
                    $html_functions= false; # this is to be added after the details
                    {
                        if($changed_fields_list= ItemChange::getItemChanges([
                            'item'      => $i->id,
                            'person'    => $i->modified_by,
                            'date_min'  => $date_compare,
                        ])) {
                            foreach($changed_fields_list as $cf) {
                                $changed_fields_hash[$cf->field]= $cf;
                            }

                            if(isset($changed_fields_hash['status'])) {
                                $status_old= $changed_fields_hash['status']->value_old;
                                if($task->status == STATUS_COMPLETED && $task->status > $status_old) {
                                    $txt_what= $html_what= __('completed') .' '. $task->getLabel();
                                    $html_functions= $PH->getLink('tasksApproved', __('Approve Task'),['tsk' => $task->id]);
                                    unset($changed_fields_hash['status']);
                                }
                                else if ($task->status == STATUS_APPROVED && $task->status > $status_old) {
                                    $txt_what= $html_what= __('approved');
                                    unset($changed_fields_hash['status']);
                                }
                                else if ($task->status == STATUS_CLOSED && $task->status > $status_old) {
                                    $txt_what= $html_what= __('closed');
                                    unset($changed_fields_hash['status']);
                                }
                                else if ($task->status == STATUS_OPEN && $task->status < $status_old) {
                                    $txt_what= $html_what= __('reopened');
                                    unset($changed_fields_hash['status']);
                                }
                                else if($task->status == STATUS_OPEN) {
                                    unset($changed_fields_hash['status']);
                                }
                                else if ($task->status == STATUS_BLOCKED) {
                                    $txt_what= $html_what= __('is blocked');
                                    unset($changed_fields_hash['status']);
                                }
                            }
                        }
                        
                        if(isset($changed_fields_hash['parent_task'])) {
                            $txt_what= $html_what= __('moved');
                            $type= ChangeLine::MOVED;
                            unset($changed_fields_hash['parent_task']);
                        }
                        else if(count($changed_fields_hash) == 1 && isset($changed_fields_hash['name'])) {
                            $txt_what= $html_what= __('renamed');
                            $type= ChangeLine::RENAMED;
                        }
                        else if(count($changed_fields_hash) == 1 && isset($changed_fields_hash['description'])) {
                            $txt_what= $html_what= __('edit wiki');
                            $type= ChangeLine::EDITED_WIKI;
                        }

                        else if(count($changed_fields_hash)) {                        # status does not count
                            $html_details .= ' / ' . __('changed:'). ' '. implode(', ', array_keys($changed_fields_hash));
                        }

                        /**
                        * task modified, but nothing changed, any comments?
                        */
                        else if($html_comment) {
                            $txt_what= $html_what= __('commented');
                            $type= ChangeLine::COMMENTED;
                        }

                        if($html_comment) {
                            $html_details .= ' / ' . $html_comment;
                        }
                    }

                    /**
                    * any recents assignments ?
                    * - to avoid confusion only list assignmets if it was to last action,
                    *
                    */

                    require_once "db/class_taskperson.inc.php";
                    $count_assignments=0;
                    if($assignments= TaskPerson::getTaskPeople([
                        'task'      => $task->id,
                        'project'   => $task->project,
                        'date_min'  => $task->modified,
                    ])) {
                        $t_timestamp= '';
                        foreach($assignments as $a) {

                            if($a->person != $task->modified_by
                              &&
                              $a->created_by == $task->modified_by
                              &&
                              $a->assigntype != ASSIGNTYPE_INITIAL
                            ) {
                                $t_timestamp = $a->created;
                                $count_assignments++;
                            }
                        }
                        if($count_assignments
                           &&
                           $timestamp_last_change < $t_timestamp
                        ) {
                            $type= ChangeLine::ASSIGNED;
                            $txt_what= $html_what= __('assigned');
                            $timestamp_last_change = $t_timestamp;
                        }

                        if($html_comment) {
                            $html_details .= ' / ' . $html_comment;
                        }
                    }
                    /**
                    * any recents attachments by last editor ?
                    */
                    require_once "db/class_file.inc.php";
                    if($files= File::getAll([
                        'parent_item'      => $task->id,
                        'project'   => $task->project,
                        'date_min'  => $date_compare,
                        'created_by' => $task->modified_by,
                    ])) {
                        $count_attached_files= 0;
                        $html_attached=__("attached").": ";
                        $t_timestamp='';
                        $separator= '';

                        foreach($files as $f) {
                            if($task->modified_by == $f->modified_by) {
                                $t_timestamp= $f->created;
                                $count_attached_files++;
                                $html_attached.= $separator . $PH->getLink('fileView', $f->name, ['file'=>$f->id]);
                                $separator= ', ';
                            }
                        }
                        if($count_attached_files) {
                            $type= ChangeLine::ATTACHED_FILE;
                            $txt_what= $html_what= __('attached file to');
                            if($timestamp_last_change < $t_timestamp) {
                                $html_details.= ' / '. $html_attached;
                                $timestamp_last_change = $t_timestamp;
                            }
                        }
                    }

                    if(count($changed_fields_hash)){
                        $html_details.=" / ". $PH->getLink('itemViewDiff',NULL, ['item'=>$task->id, 'date1' => $date_compare, 'date2' => gmdate("Y-m-d H:i:s")]);
                    }

                    if($html_functions) {
                        $html_details.= " | ". $html_functions;
                    }

                    $change= new ChangeLine([
                        'person_by' =>      $i->modified_by,
                        'timestamp' =>      $i->modified,
                        'item_id'   =>      $i->id,
                        #'task'      =>      $task,
                        'item'      =>      $task,
                        'type'      =>      $type,
                        'txt_what'  =>      $txt_what,
                        'html_what' =>      $html_what,
                        'html_assignment'=> $html_assignment,
                        'html_details'=>    $html_details,
                        #'project_id'=> $i->project,
                    ]);
                    $changes[]= $change;
                }
                
                else if ($i->type == ITEM_FILE)
                {
                    require_once(confGet('DIR_STREBER') . 'db/class_file.inc.php');
                    if($file= File::getVisibleById($i->id)) {                    
                        $change= new ChangeLine([
                            'item'      =>      $file,
                            'person_by' =>      $i->created_by,
                            'timestamp' =>      $i->created,
                            'item_id'   =>      $i->id,
                            'html_what' =>      __('changed File'),
                            'txt_what'  =>      __('changed File'),
                            'type'      =>      ChangeLine::NEW_FILE,
                            'html_details'=>    $file->name,
                        ]);
                        $changes[]= $change;
                    }
                }                
                
                break;


            case ITEM_DELETED:

                /**
                * deleted tasks
                */
                if($i->type == ITEM_TASK) {
                    if(!$task= Task::getVisibleById($i->id)) {
                        continue;
                    }

                    if($assigned_people= $task->getAssignedPeople()) {
                        $tmp=[];

                        foreach($assigned_people as $ap) {
                            $tmp[]= $ap->getLink();
                        }

                        $html_assignment= __('to','very short for assigned tasks TO...'). ' ' . implode(', ', $tmp);
                    }
                    else {
                        $html_assignment= '';
                    }


                    $html_details= '';
                    if($tmp= $task->getFolderLinks(true, $project)) {
                        $html_details .=__('in', 'very short for IN folder...'). ' '. $tmp;
                    }

                    $html_details.= '|' .  $PH->getLink('itemsRestore',__('restore'),['item'=>$task->id]);

                    $txt_what= $html_what= __('deleted');

                    $change= new ChangeLine([
                        'item'      =>      $task,
                        'person_by' =>      $i->deleted_by,
                        'timestamp' =>      $i->deleted,
                        'item_id'   =>      $i->id,
                        #'task'      =>      $task,
                        'type'      =>      ChangeLine::DELETED,
                        'txt_what' =>      $txt_what,
                        'html_what' =>      $html_what,
                        'html_assignment'=> $html_assignment,
                        'html_details'=>    $html_details,
                    ]);
                    $changes[]= $change;
                }

                /**
                * deleted file
                */
                else if($i->type == ITEM_FILE) {

                    require_once(confGet('DIR_STREBER') . 'db/class_file.inc.php');
                    if($file= File::getVisibleById($i->id)) {                    
                        $change= new ChangeLine([
                            'item'      =>      $file,
                            'person_by' =>      $i->created_by,
                            'timestamp' =>      $i->created,
                            'item_id'   =>      $i->id,
                            'html_what' =>      __('deleted File'),
                            'txt_what'  =>      ChangeLine::DELETED,
                            'html_details'=>    $file->name,
                        ]);
                        $changes[]= $change;
                    }
                }

                break;

            default:
                trigger_error("unknown change-type $change_type", E_USER_WARNING);
                break;
            }
        }
        return $changes;
    }
}
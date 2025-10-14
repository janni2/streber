<?php if(!function_exists('startedIndexPhp')) { header("location:../index.php"); exit();}

# streber - a php based project management system
# Copyright (c) 2005 Thomas Mann - thomas@pixtur.de
# Distributed under the terms and conditions of the GPL as stated in docs/license.txt

/**\file
 * pages relating to people
 *
 * @author Thomas Mann
 *
 */

require_once(confGet('DIR_STREBER') . 'db/class_task.inc.php');
require_once(confGet('DIR_STREBER') . 'db/class_project.inc.php');
require_once(confGet('DIR_STREBER') . 'db/class_person.inc.php');
require_once(confGet('DIR_STREBER') . 'db/class_company.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_list.inc.php');
require_once(confGet('DIR_STREBER') . 'lists/list_people.inc.php');
require_once(confGet('DIR_STREBER') . 'lists/list_projects.inc.php');
require_once(confGet('DIR_STREBER') . 'lists/list_tasks.inc.php');
require_once(confGet('DIR_STREBER') . 'lists/list_efforts.inc.php');
require_once(confGet('DIR_STREBER') . 'render/render_wiki.inc.php');



/**
* personList active @ingroup pages
*/
function personList()
{
    global $PH;
    global $auth;


    $has_edit_rights = $auth->cur_user->user_rights & RIGHT_PERSON_EDIT;
    $anonymous_users_enabled = confGet('ANONYMOUS_USER') != false;

    if((!$has_edit_rights && !$anonymous_users_enabled) || $auth->hideOtherPeoplesDetails() ) {
        ### set up page and write header ####

        $page= new Page();
	    $page->type=__("List");
        $page->cur_tab='people';
        $page->title=__('List of people');

        echo(new PageHeader);

        echo (new PageContentOpen);

        echo "<div class=license>";
        echo wiki2purehtml(__("Sorry, but this information is available."));
        echo "</div>";

        echo (new PageContentClose);
        echo (new PageHtmlEnd);        
        exit();        
    }
    
    $presets= [
        ### all ###
        'all_people' => [
            'name'=> __('all'),
            'filters'=> [
                'person_category'=> [
                    'id'        => 'person_category',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => PCATEGORY_UNDEFINED,
                    'max'       => PCATEGORY_PARTNER,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'tree',
                ]
            ]
        ],
        ### without account ###
        'people_without_account' => [
            'name'=> __('without account'),
            'filters'=> [
                'can_login'=> [
                    'id'        => 'can_login',
                    'value'     => '0',
                    'visible'   => true,
                    'active'    => true,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        ### with account ###
        'people_with_account' => [
            'name'=> __('with account'),
            'filters'=> [
                'can_login'=> [
                    'id'        => 'can_login',
                    'value'     => '1', 
                    'visible'   => true,
                    'active'    => true,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        ### employee ###
        'person_employee' => [
            'name'=> __('employees'),
            'filters'=> [
                'person_category'=> [
                    'id'        => 'person_category',
                    'visible'   => false,
                    'active'    => true,
                    'min'       => PCATEGORY_STAFF,
                    'max'       => PCATEGORY_EXEMPLOYEE,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        ### contact people ###
        'person_contact' => [
            'name'=> __('contact people'),
            'filters'=> [
                'person_category'=> [
                    'id'        => 'person_category',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => PCATEGORY_CLIENT,
                    'max'       => PCATEGORY_PARTNER,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        ### deleted people ###
        'deleted_people' => [
            'name'=> __('deleted'),
            'filters'=> [
                'person_is_alive'=> [
                    'id'        => 'person_is_alive',
                    'value'     => false,
                    'visible'   => true,
                    'active'    => true,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
    ];

    ## set preset location ##
    $preset_location = 'personList';

    ### get preset-id ###
    {
        $preset_id= 'people_with_account';                           # default value
        if($tmp_preset_id= get('preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id= $tmp_preset_id;
            }

            ### set cookie
            setcookie(
                'STREBER_personList_preset',
                $preset_id,
                time()+60*60*24*30,
                '',
                '',
                0);
        }
        else if($tmp_preset_id= get('STREBER_personList_preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id= $tmp_preset_id;
            }
        }
    }
    
    ### create from handle ###
    $PH->defineFromHandle(['preset_id'=>$preset_id]);

    ### set up page and write header ####
    {
        $page= new Page();
        $page->cur_tab='people';
        $page->title=__('People','Pagetitle for person list');
        if(!($auth->cur_user->user_rights & RIGHT_VIEWALL)) {
            $page->title_minor= sprintf(__("relating to %s","Page title Person list title add on"), $auth->cur_user->name);
        }
        else {
            $page->title_minor=__("admin view", "Page title add on if admin");
        }
     
        $page->type=__('List','page type');

        $page->options=build_personList_options();


        ### page functions ###
        $page->add_function(new PageFunction([
            'target'    =>'personNew',
            'params'    =>[],
            'icon'      =>'new',
            'tooltip'   =>__('New person'),
        ]));


        ### render title ###
        echo(new PageHeader);
    }
    
    echo (new PageContentOpen);
    
    
    #--- list people --------------------------------------------------------
    if($order_by=get('sort_'.$PH->cur_page->id."_people_list")) {
        $order_by= str_replace(",",", ", $order_by);
    }
    else {
        $order_by='name';
    }

    $list= new ListBlock_people();
    $list->title= $page->title;
    unset($list->columns['profile']);
    unset($list->columns['projects']);
    unset($list->columns['changes']);
    
    $list->filters[] = new ListFilter_people();
    {            
        $preset = $presets[$preset_id];
        foreach($preset['filters'] as $f_name=>$f_settings) {
            switch($f_name) {
                case 'person_category':
                    $list->filters[]= new ListFilter_person_category_min([
                        'value'=>$f_settings['min'],
                    ]);
                    $list->filters[]= new ListFilter_person_category_max([
                        'value'=>$f_settings['max'],
                    ]);
                    break;
                case 'can_login':
                    $list->filters[]= new ListFilter_can_login([
                        'value'=>$f_settings['value'],
                    ]);
                    break;
                case 'person_is_alive':
                    $list->filters[]= new ListFilter_is_alive([
                        'value'=>$f_settings['value'],
                    ]);
                    break;
                default:
                    trigger_error("Unknown filter setting $f_name", E_USER_WARNING);
                    break;
            }
        }

        $filter_empty_folders =  (isset($preset['filter_empty_folders']) && $preset['filter_empty_folders'])
                              ? true
                              : NULL;
        
        
        if($auth->cur_user->user_rights & RIGHT_PERSON_CREATE) {
            $list->no_items_html=$PH->getLink('personNew','');
        }
        else {
            $list->no_items_html=__("no related people");
        }
        
        $page->print_presets([
            'target' => $preset_location,
            'project_id' => '',
            'preset_id' => $preset_id,
            'presets' => $presets,
            'person_id' => '']);
            
        
        $list->query_options['order_by'] = $order_by;
        $list->print_automatic();
        
        ## Link to start cvs export for priviledged users ##
        if(($auth->cur_user->user_rights & RIGHT_PERSON_EDIT) || confGet('ANONYMOUS_USER') == false) {
            $format = get('format');
            if($format == FORMAT_HTML || $format == ''){
                echo $PH->getCSVLink();
            }
        }
    }

    echo(new PageContentClose);
    echo(new PageHtmlEnd);

}


/**
* display projects for person...  @ingroup pages
*/
function personViewProjects()
{
    global $PH;
    
    ### get current project ###
    $id = getOnePassedId('person','people_*');
    
    if(!$person = Person::getVisibleById($id)) {
        $PH->abortWarning("invalid person-id");
        return;
    }
    
    $presets= [
        ### all ###
        'all_related_projects' => [
            'name'=> __('all'),
            'filters'=> [
                'project_status'=> [
                    'id'        => 'project_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_UNDEFINED,
                    'max'       => STATUS_CLOSED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],

        ### open projects ###
        'open_related_projects' => [
            'name'=> __('open'),
            'filters'=> [
                'project_status'=> [
                    'id'        => 'project_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_UNDEFINED,
                    'max'       => STATUS_OPEN,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### closed projects ###
        'closed_related_projects' => [
            'name'=> __('closed'),
            'filters'=> [
                'project_status'=> [
                    'id'        => 'project_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_BLOCKED,
                    'max'       => STATUS_CLOSED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
    ];
    
    ## set preset location ##
    $preset_location = 'personViewProjects';
    
    ### get preset-id ###
    {
        $preset_id = 'all_related_projects';                           # default value
        if($tmp_preset_id = get('preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id = $tmp_preset_id;
            }

            ### set cookie
            setcookie(
                'STREBER_personViewProjects_preset',
                $preset_id,
                time()+60*60*24*30,
                '',
                '',
                0);
        }
        else if($tmp_preset_id = get('STREBER_personViewProjects_preset')) {
            if(isset($presets[$tmp_preset_id])) {

                $preset_id = $tmp_preset_id;
            }
        }
    }
    ### create from handle ###
    $PH->defineFromHandle(['person'=>$person->id, 'preset_id' =>$preset_id]);
    
    ### set up page ####
    {
        $page = new Page();
        $page->cur_tab = 'people';
        $page->title = $person->name;
        $page->title_minor = __('Projects','Page title add on');
        $page->type = __("Person");

        $page->crumbs = build_person_crumbs($person);
        $page->options = build_person_options($person);

        echo(new PageHeader);
    }
    echo (new PageContentOpen);
    
    #--- list projects --------------------------------------------------------------------------
    {
        $order_by = get('sort_'.$PH->cur_page->id."_projects");

        require_once(confGet('DIR_STREBER') . 'db/class_project.inc.php');
        
        $list= new ListBlock_projects();
        unset($list->functions['effortNew']);
        unset($list->functions['projNew']);
        unset($list->functions['projNewFromTemplate']);
        $list->no_items_html= __('no projects yet');
        
        #$list->filters[] = new ListFilter_projects();
        {
            $preset = $presets[$preset_id];
            foreach($preset['filters'] as $f_name=>$f_settings) {
                switch($f_name) {
                    case 'project_status':
                        $list->filters[]= new ListFilter_status_min([
                            'value'=>$f_settings['min'],
                        ]);
                        $list->filters[]= new ListFilter_status_max([
                            'value'=>$f_settings['max'],
                        ]);
                        break;
                    default:
                        trigger_error("Unknown filter setting $f_name", E_USER_WARNING);
                        break;
                }
            }
    
            $filter_empty_folders =  (isset($preset['filter_empty_folders']) && $preset['filter_empty_folders'])
                                  ? true
                                  : NULL;
        }
        
        $page->print_presets([
        'target' => $preset_location,
        'project_id' => '',
        'preset_id' => $preset_id,
        'presets' => $presets,
        'person_id' => $person->id]);
        
        $list->query_options['order_by'] = $order_by;
        $list->query_options['person'] = $person->id;
        $list->print_automatic();
        
        //$list->render_list($efforts);
    }
    
    echo '<input type="hidden" name="person" value="'.$person->id.'">';
    
    echo (new PageContentClose);
    echo (new PageHtmlEnd());
}

function personViewTasks()
{
    global $PH;
    global $auth;
    
    ### get current project ###
    $id = getOnePassedId('person','people_*');
    
    if(!$person = Person::getVisibleById($id)) {
        $PH->abortWarning("invalid person-id");
        return;
    }
    
    $presets= [
        ### all ###
        'all_tasks' => [
            'name'=> __('all'),
            'filters'=> [
                'task_status'=> [
                    'id'        => 'task_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_NEW,
                    'max'       => STATUS_CLOSED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [
                        ''
                    ],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### open tasks ###
        'new_tasks' => [
            'name'=> __('new'),
            'filters'=> [
                'task_status'=> [
                    'id'        => 'task_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_NEW,
                    'max'       => STATUS_NEW,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [
                        ''
                    ],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### open tasks ###
        'open_tasks' => [
            'name'=> __('open'),
            'filters'=> [
                'task_status'=> [
                    'id'        => 'task_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_OPEN,
                    'max'       => STATUS_OPEN,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [
                        ''
                    ],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### blocked tasks ###
        'blocked_tasks' => [
            'name'=> __('blocked'),
            'filter_empty_folders'=>true,
            'filters'=> [
                'task_status'=> [
                    'id'        => 'task_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_BLOCKED,
                    'max'       => STATUS_BLOCKED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [
                        ''
                    ],
                    'style'=> 'list',
                ]
            ]
        ],


        ### to be approved ###
        'approve_tasks' => [
            'name'=> __('needs approval'),
            'filter_empty_folders'=>true,
            'filters'=> [
                'task_status'=> [
                    'id'        => 'task_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => STATUS_COMPLETED,
                    'max'       => STATUS_COMPLETED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [
                        ''
                    ],
                    'style'=> 'list',
                ]
            ]
        ],

        ### closed tasks ###
        'closed_tasks' => [
            'name'=> __('closed'),
            'filter_empty_folders'=>false,
            'filters'=> [
                'task_status'=> [
                    'id'        => 'task_status',
                    'visible'   => true,
                    'active'    => true,
                    'values'    => [ STATUS_APPROVED, STATUS_CLOSED],
                    'min'       => STATUS_APPROVED,
                    'max'       => STATUS_CLOSED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [
                        ''
                    ],
                    'style'=> 'list',
                ]
            ]
        ],
    ];

    ## set preset location ##
    $preset_location = 'personViewTasks';

    ### get preset-id ###
    {
        $preset_id= 'all_tasks';                           # default value
        if($tmp_preset_id= get('preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id= $tmp_preset_id;
            }

            ### set cookie
            setcookie(
                'STREBER_personViewTasks_preset',
                $preset_id,
                time()+60*60*24*30,
                '',
                '',
                0);
        }
        else if($tmp_preset_id= get('STREBER_personViewTasks_preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id= $tmp_preset_id;
            }
        }
    }

    ### create from handle ###
    $PH->defineFromHandle(['person'=>$person->id, 'preset_id' =>$preset_id]);
    
    ### set up page ####
    {
        $page = new Page();
        $page->cur_tab = 'people';
        $page->title = $person->name;
        $page->title_minor = __('Tasks','Page title add on');
        $page->type = __("Person");

        $page->crumbs = build_person_crumbs($person);
        $page->options = build_person_options($person);

        echo(new PageHeader);
    }
    echo (new PageContentOpen);
    
    #--- list projects --------------------------------------------------------------------------
    {
        $order_by = get('sort_'.$PH->cur_page->id."_tasks");

        require_once(confGet('DIR_STREBER') . 'db/class_project.inc.php');
        
        $list= new ListBlock_tasks([
            'active_block_function'=>'list'
        ]);

        unset($list->columns['created_by']);
        unset($list->columns['planned_start']);
        unset($list->columns['assigned_to']);
        //unset($list->columns['efforts_estimated']);
        $list->no_items_html= __('no tasks yet');
        
        $list->filters[] = new ListFilter_tasks();
        {

            $preset= $presets[$preset_id];
            foreach($preset['filters'] as $f_name=>$f_settings) {
                switch($f_name) {
    
                    case 'task_status':
                        $list->filters[]= new ListFilter_status_min([
                            'value'=>$f_settings['min'],
                        ]);
                        $list->filters[]= new ListFilter_status_max([
                            'value'=>$f_settings['max'],
                        ]);
                        break;
    
                    default:
                        trigger_error("Unknown filter setting $f_name", E_USER_WARNING);
                        break;
                }
            }
    
            $filter_empty_folders=  (isset($preset['filter_empty_folders']) && $preset['filter_empty_folders'])
                                 ? true
                                 : NULL;
        }

    
        
        $page->print_presets([
        'target' => $preset_location,
        'project_id' => '',
        'preset_id' => $preset_id,
        'presets' => $presets,
        'person_id' => $person->id]);
        
        $list->query_options['assigned_to_person']= $person->id;
        $list->query_options['person'] = $person->id;
        $list->print_automatic(NULL, NULL, true);
        
    }
    
    echo '<input type="hidden" name="person" value="'.$person->id.'">';
    
    echo (new PageContentClose);
    echo (new PageHtmlEnd());
}


/**
* display efforts for person...  @ingroup pages
*/
function personViewEfforts()
{
    global $PH;
    global $auth;
    
    ### get current project ###
    $id=getOnePassedId('person','people_*');
    
    if(!$person= Person::getVisibleById($id)) {
        $PH->abortWarning("invalid person-id");
        return;
    }
    
    $presets= [
        ### all ###
        'all_efforts' => [
            'name'=> __('all'),
            'filters'=> [
                'effort_status'=> [
                    'id'        => 'effort_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => EFFORT_STATUS_NEW,
                    'max'       => EFFORT_STATUS_BALANCED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],

        ### new efforts ###
        'new_efforts' => [
            'name'=> __('new'),
            'filters'=> [
                'effort_status'=> [
                    'id'        => 'effort_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => EFFORT_STATUS_NEW,
                    'max'       => EFFORT_STATUS_NEW,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### open efforts ###
        'open_efforts' => [
            'name'=> __('open'),
            'filters'=> [
                'effort_status'=> [
                    'id'        => 'effort_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => EFFORT_STATUS_OPEN,
                    'max'       => EFFORT_STATUS_OPEN,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### discounted efforts ###
        'discounted_efforts' => [
            'name'=> __('discounted'),
            'filters'=> [
                'effort_status'=> [
                    'id'        => 'effort_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => EFFORT_STATUS_DISCOUNTED,
                    'max'       => EFFORT_STATUS_DISCOUNTED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### not chargeable efforts ###
        'notchargeable_efforts' => [
            'name'=> __('not chargeable'),
            'filters'=> [
                'effort_status'=> [
                    'id'        => 'effort_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => EFFORT_STATUS_NOTCHARGEABLE,
                    'max'       => EFFORT_STATUS_NOTCHARGEABLE,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ### balanced efforts ###
        'balanced_efforts' => [
            'name'=> __('balanced'),
            'filters'=> [
                'effort_status'=> [
                    'id'        => 'effort_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'       => EFFORT_STATUS_BALANCED,
                    'max'       => EFFORT_STATUS_BALANCED,
                ],
            ],
            'list_settings' => [
                'tasks' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        
        ## last logout ##
        'last_logout' => [
            'name'=> __('last logout'),
            'filters'=> [
                'last_logout'   => [
                    'id'        => 'last_logout',
                    'visible'   => true,
                    'active'    => true,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        
        ## 1 week ##
        'last_week' => [
            'name'=> __('1 week'),
            'filters'=> [
                'last_weeks'    => [
                    'id'        => 'last_weeks',
                    'visible'   => true,
                    'active'    => true,
                    'factor'    => 7,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        
        ## 2 weeks ##
        'last_two_weeks' => [
            'name'=> __('2 weeks'),
            'filters'=> [
                'last_weeks'    => [
                    'id'        => 'last_weeks',
                    'visible'   => true,
                    'active'    => true,
                    'factor'    => 14,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        
        ## 3 weeks ##
        'last_three_weeks' => [
            'name'=> __('3 weeks'),
            'filters'=> [
                'last_weeks'    => [
                    'id'        => 'last_weeks',
                    'visible'   => true,
                    'active'    => true,
                    'factor'    => 21,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        
        ## 1 month ##
        'last_month' => [
            'name'=> __('1 month'),
            'filters'=> [
                'last_weeks'    => [
                    'id'        => 'last_weeks',
                    'visible'   => true,
                    'active'    => true,
                    'factor'    => 28,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        
        ## prior ##
        'prior' => [
            'name'=> __('prior'),
            'filters'=> [
                'prior'    => [
                    'id'        => 'prior',
                    'visible'   => true,
                    'active'    => true,
                    'factor'    => 29,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
    ];

    ## set preset location ##
    $preset_location = 'personViewEfforts';
    
    ### get preset-id ###
    {
        $preset_id= 'all_efforts';                           # default value
        if($tmp_preset_id= get('preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id= $tmp_preset_id;
            }

            ### set cookie
            setcookie(
                'STREBER_personViewEfforts_preset',
                $preset_id,
                time()+60*60*24*30,
                '',
                '',
                0);
        }
        else if($tmp_preset_id= get('STREBER_personViewEfforts_preset')) {
            if(isset($presets[$tmp_preset_id])) {

                $preset_id= $tmp_preset_id;
            }
        }
    }
    ### create from handle ###
    $PH->defineFromHandle(['person'=>$person->id, 'preset_id' =>$preset_id]);

    ### set up page ####
    {
        $page= new Page();
        $page->cur_tab='people';
        $page->title=$person->name;
        $page->title_minor=__('Efforts','Page title add on');
        $page->type=__("Person");

        $page->crumbs = build_person_crumbs($person);
        $page->options= build_person_options($person);

        echo(new PageHeader);
    }
    echo (new PageContentOpen);



    #--- list efforts --------------------------------------------------------------------------
    {
        $order_by=get('sort_'.$PH->cur_page->id."_efforts");

        require_once(confGet('DIR_STREBER') . 'db/class_effort.inc.php');

        $list= new ListBlock_efforts();
        unset($list->functions['effortNew']);
        unset($list->functions['effortNew']);
        $list->no_items_html= __('no efforts yet');
        
        $list->filters[] = new ListFilter_efforts();
        {
            $preset = $presets[$preset_id];
            foreach($preset['filters'] as $f_name=>$f_settings) {
                switch($f_name) {
                    case 'effort_status':
                        $list->filters[]= new ListFilter_effort_status_min([
                            'value'=>$f_settings['min'],
                        ]);
                        $list->filters[]= new ListFilter_effort_status_max([
                            'value'=>$f_settings['max'],
                        ]);
                        break;
                    case 'last_logout':
                        $list->filters[]= new ListFilter_last_logout([
                            'value'=>$f_settings['value'],
                        ]);
                        break;
                    case 'last_weeks':
                        $list->filters[]= new ListFilter_min_week([
                            'value'=>$f_settings['value'], 'factor'=>$f_settings['factor']
                        ]);
                        break;
                    case 'prior':
                        $list->filters[]= new ListFilter_max_week([
                            'value'=>$f_settings['value'], 'factor'=>$f_settings['factor']
                        ]);
                        break;
                    default:
                        trigger_error("Unknown filter setting $f_name", E_USER_WARNING);
                        break;
                }
            }
    
            $filter_empty_folders =  (isset($preset['filter_empty_folders']) && $preset['filter_empty_folders'])
                                  ? true
                                  : NULL;
        }
        
        $page->print_presets([
        'target' => $preset_location,
        'project_id' => '',
        'preset_id' => $preset_id,
        'presets' => $presets,
        'person_id' => $person->id]);
        
        $list->query_options['order_by'] = $order_by;
        $list->query_options['person'] = $person->id;
        $list->print_automatic();
    }
    
    echo '<input type="hidden" name="person" value="'.$person->id.'">';

    echo (new PageContentClose);
    echo (new PageHtmlEnd());
}

function personViewChanges()
{
    global $PH;
    global $auth;
    
    ### get current project ###
    $id = getOnePassedId('person','people_*');
    
    if(!$person = Person::getVisibleById($id)) {
        $PH->abortWarning("invalid person-id");
        return;
    }
    
    ### sets the presets ###
    $presets = [
        ### all ###
        'all_changes' => [
            'name'=> __('all'),
            'filters'=> [
                'task_status'   =>  [
                    'id'        => 'task_status',
                    'visible'   => true,
                    'active'    => true,
                    'min'    =>  STATUS_UNDEFINED,
                    'max'    =>  STATUS_CLOSED,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ]
        ],
        ## last logout ##
        'last_logout' => [
            'name'=> __('last logout'),
            'filters'=> [
                'last_logout'   => [
                    'id'        => 'last_logout',
                    'visible'   => true,
                    'active'    => true,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        ## 1 week ##
        'last_week' => [
            'name'=> __('1 week'),
            'filters'=> [
                'last_week'   => [
                    'id'        => 'last_week',
                    'visible'   => true,
                    'active'    => true,
                    'factor'    => 7,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
        ## 2 week ##
        'last_two_weeks' => [
            'name'=> __('2 weeks'),
            'filters'=> [
                'last_two_weeks'   => [
                    'id'        => 'last_two_weeks',
                    'visible'   => true,
                    'active'    => true,
                    'factor'    => 14,
                    'value'     => $auth->cur_user->id,
                ],
            ],
            'list_settings' => [
                'changes' =>[
                    'hide_columns'  => [''],
                    'style'=> 'list',
                ]
            ],
        ],
    ];

    ## set preset location ##
    $preset_location = 'personViewChanges';

    ### get preset-id ###
    {
        $preset_id= 'last_two_weeks';                           # default value
        if($tmp_preset_id= get('preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id= $tmp_preset_id;
            }

            ### set cookie
            setcookie(
                'STREBER_personViewChanges_preset',
                $preset_id,
                time()+60*60*24*30,
                '',
                '',
                0);
        }
        else if($tmp_preset_id= get('STREBER_personViewChanges_preset')) {
            if(isset($presets[$tmp_preset_id])) {
                $preset_id= $tmp_preset_id;
            }
        }
    }

    ### create from handle ###
    $PH->defineFromHandle(['person'=>$person->id, 'preset_id'=>$preset_id]);
    
    ### set up page ####
    {
        $page= new Page();
        $page->cur_tab='people';
        $page->title=$person->name;
        $page->title_minor=__('Changes','Page title add on');
        $page->type=__("Person");

        $page->crumbs = build_person_crumbs($person);
        $page->options= build_person_options($person);

        echo(new PageHeader);
    }
    echo (new PageContentOpen);
    
    #--- list efforts --------------------------------------------------------------------------
    {
        require_once(confGet('DIR_STREBER') . './lists/list_changes.inc.php');
        

        $list= new ListBlock_changes();
        $list->no_items_html= __('no changes yet');
        
        $list->filters[] = new ListFilter_changes();
        {
            $preset = $presets[$preset_id];
            foreach($preset['filters'] as $f_name=>$f_settings) {
                switch($f_name) {
                    case 'task_status':
                        $list->filters[]= new ListFilter_status_min([
                            'value'=>$f_settings['min'],
                        ]);
                        #$list->filters[]= new ListFilter_status_max(array(
                        #    'value'=>$f_settings['max'],
                        #));
                        break;
                    case 'last_logout':
                        $list->filters[]= new ListFilter_last_logout([
                            'value'=>$f_settings['value'],
                        ]);
                        break;
                    case 'last_week':
                        $list->filters[]= new ListFilter_min_week([
                            'value'=>$f_settings['value'], 'factor'=>$f_settings['factor']
                        ]);
                        #$list->filters[]= new ListFilter_max_week(array(
                        #   'value'=>$f_settings['value'],
                        #));
                        break;
                    case 'last_two_weeks':
                        $list->filters[]= new ListFilter_min_week([
                            'value'=>$f_settings['value'], 'factor'=>$f_settings['factor']
                        ]);
                        #$list->filters[]= new ListFilter_max_week(array(
                        #   'value'=>$f_settings['value'],
                        #));
                        break;
                    default:
                        trigger_error("Unknown filter setting $f_name", E_USER_WARNING);
                        break;
                }
            }
    
            $filter_empty_folders =  (isset($preset['filter_empty_folders']) && $preset['filter_empty_folders'])
                                  ? true
                                  : NULL;
        }
        
        $page->print_presets([
        'target' => $preset_location,
        'project_id' => '',
        'preset_id' => $preset_id,
        'presets' => $presets,
        'person_id' => $person->id]);
        
        $list->query_options['modified_by'] = $person->id;
        $list->print_automatic();
    }
    
    echo '<input type="hidden" name="person" value="'.$person->id.'">';

    echo (new PageContentClose);
    echo (new PageHtmlEnd());
    
}


/**
* Create a new person  @ingroup pages
*/
function personNew() {
    global $PH;
    global $auth;
    global $g_user_profile_names;
    global $g_user_profiles;

    $name=get('new_name')
        ? get('new_name')
        :__("New person");


    $default_profile_num= confGet('PERSON_PROFILE_DEFAULT');
    $default_profile    = $g_user_profiles[$default_profile_num];
    if(! $default_rights= $default_profile['default_user_rights']) {
        trigger_error("Undefined default profile requested. Check conf.inc.php and customize.inc.php.", E_USER_ERROR);
    }

    ### build new object ###
    $person_new= new Person([
        'id'                    => 0,                                           # temporary new
        'name'                  => $name,
        'profile'               => confGet('PERSON_PROFILE_DEFAULT'),
        'user_rights'           => $default_rights,
        'language'              => $auth->cur_user->language,
        'notification_period'   => 3,                                            # in days
        'can_login'             => 1,
        ]
    );

    $PH->show('personEdit',['person' => $person_new->id],$person_new);

}


/**
* Edit a person  @ingroup pages
*/
function personEdit($person=NULL)
{
    global $PH;
    global $auth;


    ### new object not in database ###
    if(!$person) {
        $id= getOnePassedId('person','people_*');   # WARNS if multiple; ABORTS if no id found
        if(!$person= Person::getEditableById($id)) {
            $PH->abortWarning("ERROR: could not get Person");
            return;
        }
    }

    ### validate rights ###

    if(
        (
         $auth->cur_user->id == $person->id
         &&
         $auth->cur_user->user_rights & RIGHT_PERSON_EDIT_SELF
        )
        ||
        ($auth->cur_user->user_rights & RIGHT_PERSON_EDIT)
        ||
        (($auth->cur_user->user_rights & RIGHT_PERSON_CREATE)
         &&
         $person->id == 0

        )
    ) {
        $pass= true;
    }
    else {
        $PH->abortWarning(__("not allowed to edit"),ERROR_RIGHTS);
    }

    ### set up page and write header ####
    {
        $page= new Page(['use_jscalendar'=>true, 'autofocus_field'=>'person_name']);
        $page->cur_tab='people';
        $page->type=__('Edit Person','Page type');
        $page->title=$person->name;
        $page->title_minor='';

        $page->crumbs= build_person_crumbs($person);
        $page->options=[
            new NaviOption([
                'target_id' => 'personEdit',
            ]),
        ];
        echo(new PageHeader);
    }
    echo (new PageContentOpen);

    ### form background ###
    $block=new PageBlock([
        'id'    =>'person_edit',
    ]);
    $block->render_blockStart();

    ### write form #####
    {
        require_once(confGet('DIR_STREBER') . 'render/render_form.inc.php');
        global $g_pcategory_names;

        $form=new PageForm();
        $form->button_cancel=true;

        $form->add($person->fields['name']->getFormElement($person));
        
                
        ### profile and login ###
        if( ($auth->cur_user->user_rights & RIGHT_PERSON_EDIT_RIGHTS)
            ||
            (
                ($auth->cur_user->user_rights & RIGHT_PERSON_CREATE)
                &&
                ($auth->cur_user->user_rights & RIGHT_PROJECT_ASSIGN)
                &&
                $person->id == 0
            )
        ) {
            /**
            * if checkbox not rendered, submit might reset $person->can_login.
            * ...be sure the user_rights match
            */
            $form->add(new Form_checkbox("person_can_login",__('Person with account (can login)','form label'),$person->can_login));

        }

        $form->add($tab_group=new Page_TabGroup());

        ### account ###
        {
            $tab_group->add($tab=new Page_Tab("account",__("Account")));
            $fnick=$person->fields['nickname']->getFormElement($person);
            if($person->can_login) {
                $fnick->required= true;
            }
            $tab->add($fnick);

            $tab->add($person->fields['office_email']->getFormElement($person));

            ### show password-fields if can_login ###
            /**
            * since the password as stored as md5-hash, we can initiate current password,
            * but have have to make sure the it is not changed on submit
            */
            $fpw1=new Form_password('person_password1',__('Password','form label'),"__dont_change__", $person->fields['password']->tooltip);
            if($person->can_login) {
                $fpw1->required= true;
            }
            $tab->add($fpw1);

            $fpw2=new Form_password('person_password2',__('confirm Password','form label'),"__dont_change__",  $person->fields['password']->tooltip);
            if($person->can_login) {
                $fpw2->required= true;
            }
            $tab->add($fpw2);
            
            ### authentication ###
            if(confGet('LDAP')){
                $authentication = ['streber'=>0, 'ldap'=>1];
                $tab->add(new Form_Dropdown('person_auth', __("Authentication with","form label"), $authentication, $person->ldap));
            }


            ### profile and login ###
            if($auth->cur_user->user_rights & RIGHT_PERSON_EDIT_RIGHTS) {
                global $g_user_profile_names;
                global $g_user_profiles;



                ### display "undefined" profile if rights changed ###
                # will be skipped when submitting
                $profile_num= $person->profile;
                $reset="";

                if(! $default_rights= $g_user_profiles[$profile_num]['default_user_rights']) {
                    trigger_error("undefined/invalid profile requested ($profile_num)", E_USER_ERROR);
                }

                $list = $g_user_profile_names;

                if($default_rights != $person->user_rights) {
                    $profile_num='-1';
                    $list['-1']= __('-- reset to...--');
                }

                $tab->add(new Form_Dropdown(
                    'person_profile',
                    __("Profile","form label"),
                    array_flip($list),
                    $profile_num
                ));
            }

            ### notification ###
            {
                $a=[
                    sprintf(__('ASAP'),  -1)        => -1,
                    sprintf(__('daily'),  1)        =>  1,
                    sprintf(__('each 3 days'), 3)   =>  3,
                    sprintf(__('each 7 days'), 7)   =>  7,
                    sprintf(__('each 14 days'), 14) => 14,
                    sprintf(__('each 30 days'), 30) => 30,
                    __('Never')                     =>  0,
                ];
                $p= $person->notification_period;
                if(!$person->settings & USER_SETTING_NOTIFICATIONS) {
                    $p= 0;
                }
                $tab->add(new Form_Dropdown('person_notification_period',  __("Send notifications","form label"), $a, $p));
                #$tab->add(new Form_checkbox("person_html_mail",__('Send mail as html','form label'),$person->settings & USER_SETTING_HTML_MAIL));
            }


            ## assigne to project ##
            {
                if($person->id == 0){
                    $prj_num = '-1';

                    $prj_names = [];
                    $prj_names['-1'] = __('- no -');

                    ## get all projects ##
                    if($projects = Project::getAll()){
                        foreach($projects as $p){
                            $prj_names[$p->id] = $p->name;
                        }

                        ## assigne new person to ptoject ##
                        $tab->add(new Form_Dropdown('assigned_prj', __('Assigne to project','form label'), array_flip($prj_names), $prj_num));
                    }
                }

            }

        }

        ### details ###
        {
            $tab_group->add($tab=new Page_Tab("details",__("Details")));

            ### category ###
            if($p= get('perscat')){
                $perscat = $p;
            }
            else {
                $perscat = $person->category;
            }
            $tab->add(new Form_Dropdown('pcategory',  __('Category','form label'),array_flip($g_pcategory_names), $perscat));
            $tab->add($person->fields['mobile_phone']->getFormElement($person));
            $tab->add($person->fields['office_phone']->getFormElement($person));
            $tab->add($person->fields['office_fax']->getFormElement($person));
            $tab->add($person->fields['office_street']->getFormElement($person));
            $tab->add($person->fields['office_zipcode']->getFormElement($person));
            $tab->add($person->fields['office_homepage']->getFormElement($person));

            $tab->add($person->fields['personal_email']->getFormElement($person));
            $tab->add($person->fields['personal_phone']->getFormElement($person));
            $tab->add($person->fields['personal_fax']->getFormElement($person));
            $tab->add($person->fields['personal_street']->getFormElement($person));
            $tab->add($person->fields['personal_zipcode']->getFormElement($person));
            $tab->add($person->fields['personal_homepage']->getFormElement($person));
            $tab->add($person->fields['birthdate']->getFormElement($person));
        }

        ### description ###
        {
            $tab_group->add($tab=new Page_Tab("description",__("Description")));

            $e= $person->fields['description']->getFormElement($person);
            $e->rows=20;
            $tab->add($e);
        }


        ### options ###
        {
            $tab_group->add($tab=new Page_Tab("options",__("Options")));


            $tab->add(new Form_checkbox("person_enable_efforts",__('Enable efforts'), $person->settings & USER_SETTING_ENABLE_EFFORTS));
            $tab->add(new Form_checkbox("person_enable_bookmarks",__('Enable bookmarks'), $person->settings & USER_SETTING_ENABLE_BOOKMARKS));


            ### theme and language ###
            {
                global $g_theme_names;
                if(count($g_theme_names)> 1) {
                    $tab->add(new Form_Dropdown('person_theme',  __("Theme","form label"), array_flip($g_theme_names), $person->theme));
                }

                global $g_languages;
                $tab->add(new Form_Dropdown('person_language', __("Language","form label"), array_flip($g_languages), $person->language));
            }

            ### time zone ###
            {
                global $g_time_zones;
                $tab->add(new Form_Dropdown('person_time_zone', __("Time zone","form label"), $g_time_zones, $person->time_zone));
            }



            ### effort-style ###
            $effort_styles=[
                __("start times and end times")=> 1,
                __("duration")=> 2,
            ];
            $effort_style= ($person->settings & USER_SETTING_EFFORTS_AS_DURATION)
                         ? 2
                         : 1;

            $tab->add(new Form_Dropdown('person_effort_style',  __("Log Efforts as"), $effort_styles, $effort_style));
            


            $tab->add(new Form_checkbox("person_filter_own_changes",__('Filter own changes from recent changes list'), $person->settings & USER_SETTING_FILTER_OWN_CHANGES));
        }
        
        ## internal area ##
        {
            if((confGet('INTERNAL_COST_FEATURE')) && ($auth->cur_user->user_rights & RIGHT_VIEWALL) && ($auth->cur_user->user_rights & RIGHT_EDITALL)){
                $tab_group->add($tab=new Page_Tab("internal",__("Internal")));
                $tab->add($person->fields['salary_per_hour']->getFormElement($person));
            }
        }

        ### temp uid for account activation ###
        if($tuid = get('tuid')) {
            $form->add(new Form_Hiddenfield('tuid','',$tuid));
        }

        ### create another person ###
        if($auth->cur_user->user_rights & RIGHT_PERSON_CREATE && $person->id == 0) {
            #$form->add(new Form_checkbox("create_another","",));
            $checked= get('create_another')
            ? 'checked'
            : '';

            $form->form_options[]="<span class=option><input id='create_another' name='create_another' class='checker' type=checkbox $checked><label for='create_another'>" . __("Create another person after submit") . "</label></span>";     ;
        }

        #echo "<input type=hidden name='person' value='$person->id'>";
        $form->add(new Form_HiddenField('person','',$person->id));

        echo ($form);

        $PH->go_submit= 'personEditSubmit';

        ### pass company-id? ###
        if($c= get('company')) {
            echo "<input type=hidden name='company' value='$c'>";
        }

    }


    $block->render_blockEnd();

    echo (new PageContentClose);
    echo (new PageHtmlEnd);
}



/**
* Submit changes to a person @ingroup pages
*/
function personEditSubmit()
{
    global $PH;
    global $auth;
    
    global $g_user_profile_names;
    global $g_user_profiles;
    

    ### cancel ? ###
    if(get('form_do_cancel')) {
        if(!$PH->showFromPage()) {
            $PH->show('home',[]);
        }
        exit();
    }

    ### Validate form integrity
    if(!validateFormCrc()) {
        $PH->abortWarning(__('Invalid checksum for hidden form elements'));
    }

    ### get person ####
    $id= getOnePassedId('person');

    ### temporary obj, not in db
    if($id == 0) {
        $person= new Person(['id'=>0]);
    }
    else {
        if(!$person= Person::getEditableById($id)) {
            $PH->abortWarning(__("Could not get person"));
            return;
        }
    }

    ### person category ###
    $pcategory = get('pcategory');
    if($pcategory != NULL)
    {
        if($pcategory == -1)
        {
            $person->category = PCATEGORY_STAFF;
        }
        else if ($pcategory == -2)
        {
            $person->category = PCATEGORY_CONTACT;
        }
        else
        {
            $person->category = $pcategory;
        }
    }

    ### validate rights ###
    if(
        (
         $auth->cur_user->id == $person->id
         &&
         $auth->cur_user->user_rights & RIGHT_PERSON_EDIT_SELF
        )
        ||
        ($auth->cur_user->user_rights & RIGHT_PERSON_EDIT)
        ||
        (($auth->cur_user->user_rights & RIGHT_PERSON_CREATE)
         &&
         $person->id == 0

        )
    ) {
        $pass= true;
    }
    else {
        $PH->abortWarning(__("not allowed to edit"),ERROR_RIGHTS);
    }


    $flag_ok=true;      # update valid?

    # retrieve all possible values from post-data
    # NOTE:
    # - this could be an security-issue.
    # - TODO: as some kind of form-edit-behaviour to field-definition
    foreach($person->fields as $f) {
        $name=$f->name;
        $f->parseForm($person);
    }



    ### rights & theme & profile ###
    if($auth->cur_user->user_rights & RIGHT_PERSON_EDIT_RIGHTS) {

        /**
        * if profile != -1, it will OVERWRITE (or reinit) user_rights
        *
        * therefore persEdit set profil to 0 if rights don't fit profile. It will
        * then be skipped here
        */
        $profile_num= get('person_profile');

        if(!is_null($profile_num )) {
            if($profile_num != -1) {
                $person->profile= $profile_num;
                if(isset($g_user_profiles[$profile_num]['default_user_rights'])) {
                    $rights=$g_user_profiles[$profile_num]['default_user_rights'];
                    

                    /**
                    * add warning on changed profile
                    */
                    if($person->user_rights != $rights && $person->id) {
                        new FeedbackHint(__('The changed profile <b>does not affect existing project roles</b>! Those has to be adjusted inside the projects.'));
                    }
                    $person->user_rights= $rights;
                }
                else {
                    trigger_error("Undefined profile requested ($profile_num)", E_USER_ERROR);
                }
            }
        }
    }
    
    ### can login ###
    if( ($auth->cur_user->user_rights & RIGHT_PERSON_EDIT_RIGHTS)
        ||
        (
            ($auth->cur_user->user_rights & RIGHT_PERSON_CREATE)
            &&
            ($auth->cur_user->user_rights & RIGHT_PROJECT_ASSIGN)
            &&
            $person->id == 0
        )
    ) {
    
        /**
        * NOTE, if checkbox is not rendered in editForm, user-account will be disabled!
        * there seems no way the be sure the checkbox has been rendered, if it is not checked in form
        */
        if($can_login= get('person_can_login')) {
            $person->can_login= 1;
        }
        else {
            $person->can_login= 0;
        }
    }

    ### notifications ###
    {
        $period= get('person_notification_period');

        ### turn off ###
        if($period === 0 || $period === "0") {
            $person->settings &= USER_SETTING_NOTIFICATIONS ^ RIGHT_ALL;
            $person->notification_period= 0;
        }
        else {
            $person->settings |= USER_SETTING_NOTIFICATIONS;

            $person->notification_period= $period;

            if($person->can_login && !$person->personal_email && !$person->office_email) {
                $flag_ok = false;
                $person->fields['office_email']->required=true;
                $person->fields['personal_email']->required=true;
                new FeedbackWarning(__("Sending notifactions requires an email-address."));
            }

        }

        if(get('person_html_mail')) {
            $person->settings |= USER_SETTING_HTML_MAIL;

        }
        else {
            $person->settings &= USER_SETTING_HTML_MAIL ^ RIGHT_ALL;
        }
    }

    ### effort style ###
    if($effort_style= get('person_effort_style')) {
        if($effort_style == EFFORT_STYLE_TIMES) {
            $person->settings &= USER_SETTING_EFFORTS_AS_DURATION ^ RIGHT_ALL;
        }
        else if($effort_style ==EFFORT_STYLE_DURATION) {
            $person->settings |= USER_SETTING_EFFORTS_AS_DURATION;
        }
        else {
            trigger_error("undefined person effort style", E_USER_WARNING);
        }
    }

    ### filter own changes ###
    if(get('person_filter_own_changes')) {
        $person->settings |= USER_SETTING_FILTER_OWN_CHANGES;
    }
    else {
        $person->settings &= USER_SETTING_FILTER_OWN_CHANGES ^ RIGHT_ALL;        
    }

    
    ### enable bookmarks ###
    if(get('person_enable_bookmarks')) {
        $person->settings |= USER_SETTING_ENABLE_BOOKMARKS;
    }
    else {
        $person->settings &= USER_SETTING_ENABLE_BOOKMARKS ^ RIGHT_ALL;        
    }

    if(get('person_enable_efforts')) {
        $person->settings |= USER_SETTING_ENABLE_EFFORTS;
    }
    else {
        $person->settings &= USER_SETTING_ENABLE_EFFORTS ^ RIGHT_ALL;        
    }


    ### time zone ###
    {
        $zone= get('person_time_zone');
        if($zone != NULL && $person->time_zone != (1.0 * $zone)) {
            $person->time_zone = 1.0 * $zone;

            if($zone == TIME_OFFSET_AUTO) {
                new FeedbackMessage(__("Using auto detection of time zone requires this user to relogin."));
            }
            else{
                $person->time_offset= $zone * 60.0 * 60.0;
                if($person->id == $auth->cur_user->id) {
                    $auth->cur_user->time_offset= $zone * 60.0 * 60.0;
                }
            }
        }
    }

    ### theme and lanuage ###
    {
        $theme= get('person_theme');
        if($theme != NULL) {
            $person->theme= $theme;

            ### update immediately / without page-reload ####
            if($person->id == $auth->cur_user->id) {
                $auth->cur_user->theme = $theme;
            }
        }

        $language= get('person_language');
        global $g_languages;
        if(isset($g_languages[$language])) {
            $person->language= $language;

            ### update immediately / without page-reload ####
            if($person->id == $auth->cur_user->id) {
                $auth->cur_user->language =$language;
                setLang($language);
            }
        }
    }

    $t_nickname= get('person_nickname');

    ### check if changed nickname is unique
    if($person->can_login || $person->nickname != "") {

        /**
        * actually this should be mb_strtolower, but this is not installed by default
        */
        if($person->nickname != strtolower($person->nickname)) {
            new FeedbackMessage(__("Nickname has been converted to lowercase"));
            $person->nickname = strtolower($person->nickname);
        }
        
        ### authentication ###
        $p_auth = get('person_auth');
        if($p_auth){
            $person->ldap = 1;
        }
        else{
            $person->ldap = 0;
        }
        
        if($p2= Person::getByNickname($t_nickname)) { # another person with this nick?
            if($p2->id != $person->id) {
                new FeedbackWarning(__("Nickname has to be unique"));
                $person->fields['nickname']->required=true;
                $flag_ok = false;
            }
        }
    }

    ### password entered? ###
    $t_password1= get('person_password1');
    $t_password2= get('person_password2');
    $flag_password_ok=true;
    if(($t_password1 || $t_password2) && $t_password1!="__dont_change__") {

        ### check if password match ###
        if($t_password1 !== $t_password2) {
            new FeedbackWarning(__("Passwords do not match"));
            $person->fields['password']->required=true;
            $flag_ok = false;
            $flag_password_ok = false;

        }

        ### check if password is good enough ###
        if($person->can_login) {
            $password_length= strlen($t_password1);
            $password_count_numbers= strlen(preg_replace('/[\d]/','',$t_password1));
            $password_count_special= strlen(preg_replace('/[\w]/','',$t_password1));

            $password_value= -7 + $password_length + $password_count_numbers*2 + $password_count_special*8;
            if($password_value < confGet('CHECK_PASSWORD_LEVEL')){
                new FeedbackWarning(__("Password is too weak (please add numbers, special chars or length)"));
                $flag_ok= false;
                $flag_password_ok = false;
            }
        }

        if($flag_password_ok) {
            $person->password= md5($t_password1);
        }
    }


    if($flag_ok && $person->can_login) {
        if(!$person->nickname) {
            new FeedbackWarning(__("Login-accounts require a unique nickname"));
            $person->fields['nickname']->required=true;
            $person->fields['nickname']->invalid=true;

            $flag_ok=false;
        }
    }


    ### repeat form if invalid data ###
    if(!$flag_ok) {
        $PH->show('personEdit',NULL,$person);

        exit();
    }

    /**
    * store indentifier-string for login from notification & reminder - mails
    */
    $person->identifier= $person->calcIdentifierString();

    ### insert new object ###
    if($person->id == 0) {

        if(($person->settings & USER_SETTING_NOTIFICATIONS) && $person->can_login) {
            $person->settings |= USER_SETTING_SEND_ACTIVATION;            

            require_once(confGet('DIR_STREBER') . 'std/class_email_welcome.inc.php');

            $email= new EmailWelcome($person);
            
            if($email->information_count) {
                $result= $email->send();
                if($result === true ) {
                    ### reset activation-flag ###
                    $person->settings &= USER_SETTING_SEND_ACTIVATION ^ RIGHT_ALL;
                    $person->notification_last= gmdate("Y-m-d H:i:s");
                }
                else if ($result !== false) {
                    $num_warnings++;
                    new FeedbackWarning(sprintf(__('Failure sending mail: %s'), $result));
                }
            }
        }

        $person->notification_last = getGMTString(time() - $person->notification_period * 60*60*24 - 1);

        $person->cookie_string= $person->calcCookieString();

        if($person->insert()) {

            ### link to a company ###
            if($c_id= get('company')) {
                require_once(confGet('DIR_STREBER') . 'db/class_company.inc.php');

                if($c= Company::getVisibleById($c_id)) {
                    require_once(confGet('DIR_STREBER') . 'db/class_employment.inc.php');
                    $e= new Employment([
                        'id'=>0,
                        'person'=>$person->id,
                        'company'=>$c->id
                    ]);
                    $e->insert();
                }
            }

            ## assigne to project ##
            require_once(confGet('DIR_STREBER') . 'db/class_projectperson.inc.php');
            $prj_num = get('assigned_prj');

            if(isset($prj_num)){
                if($prj_num != -1){
                    if($p= Project::getVisibleById($prj_num)){
                        $prj_person = new ProjectPerson([
                                'person' => $person->id,
                                'project' => $p->id,
                                'name' => $g_user_profile_names[$person->profile],
                                ]);
                        $prj_person->insert();
                    }
                }
            }
            new FeedbackMessage(sprintf(__('Person %s created'), $person->getLink()));
        }
        else {
            new FeedbackError(__("Could not insert object"));
        }
    }

    ### ... or update existing ###
    else {
        new FeedbackMessage(sprintf(__('Updated settings for %s.'), $person->getLink()));
        $person->update();
    }

    if($auth->cur_user->id == $person->id) {
        $auth->cur_user= $person;
    }

    ### notify on change ###
    $person->nowChangedByUser();

    ### store cookie, if accountActivation ###
    if(get('tuid')) {
        $auth->removeUserCookie();
        $auth->storeUserCookie();
        
    }

    ### create another person ###
    if(get('create_another')) {
        if($c_id= get('company')) {
            $PH->show('personNew',['company'=>$c_id]);
        }
        else {
            $PH->show('personNew');
        }
    }
    else {
        ### display fromPage ####
        if(!$PH->showFromPage()) {
            $PH->show('home',[]);
        }
    }
}




/**
* Send activation mail to a person @ingroup pages
*/
function personSendActivation()
{
    global $PH;

    ### get person ####
    $person_id= getOnePassedId('person','people_*');

    if(!$person = Person::getEditableById($person_id)) {
        $PH->abortWarning(__("Insufficient rights"));
        exit();
    }


    if(!$person->office_email && !$person->personal_email) {
        $PH->abortWarning(__("Sending notifactions requires an email-address."));
        exit();
    }


    if(! ($person->user_rights & RIGHT_PERSON_EDIT_SELF)) {
        $PH->abortWarning(__("Since the user does not have the right to edit his own profile and therefore to adjust his password, sending an activation does not make sense."), ERROR_NOTE);
        exit();
    }

    if(! $person->can_login) {
        $PH->abortWarning(__("Sending an activation mail does not make sense, until the user is allowed to login. Please adjust his profile."), ERROR_NOTE);
        exit();
    }

    $person->settings |= USER_SETTING_NOTIFICATIONS;
    $person->settings |= USER_SETTING_SEND_ACTIVATION;

    {
        require_once(confGet('DIR_STREBER') . 'std/class_email_password_reminder.inc.php');
        $email= new EmailPasswordReminder($person);
        if($email->send()) {
            new FeedbackMessage(__("Activation mail has been sent."));
        }
    }

    ### display taskView ####
    if(!$PH->showFromPage()) {
        $PH->show('projView',['prj'=>$person->project]);
    }
}


/**
* Send notication mail for one person right now @ingroup pages
*/
function peopleFlushNotifications()
{
    global $PH;
    global $auth;

    ### get person ####
    $ids= getPassedIds('person','people_*');

    if(!$ids) {
        $PH->abortWarning(__("Select some people to notify"));
        return;
    }

    $counter=0;
    $errors=0;

    foreach($ids as $id) {
        if(!$person= Person::getEditableById($id)) {
            $PH->abortWarning("Invalid person-id!");
        }

        require_once(confGet('DIR_STREBER') . 'std/class_email_notification.inc.php');

        $email= new EmailNotification($person);
        if($email->information_count) {
            $send_result= $email->send();
            if($send_result === true) {
                $counter++;            
            }
            else {
                $errors++;            
            }
        }
    }

    ### reset language ###
    setLang($auth->cur_user->language);
    
    if($errors) {
        new FeedbackWarning(sprintf(__("Failed to mail %s people"), $errors));
    }
    else {
        new FeedbackMessage(sprintf(__("Sent notification to %s person(s)"),$counter));
    }

    ### display taskView ####
    if(!$PH->showFromPage()) {
        $PH->show('projView',['prj'=>$person->project]);
    }
}


/**
* edit user rights of a person 
*
* @ingroup pages
*
* the user-rights-validation is checked by pageHandler (requires RIGHT_PERSON_EDIT_RIGHTS)
*/
function personEditRights($person=NULL)
{
    global $PH;
    global $auth;
    global $g_user_right_names;

    ### get person ####
    if(!$person) {
        $ids= getPassedIds('person','people_*');

        if(!$ids) {
            $PH->abortWarning(__("Select some people to edit"));
            return;
        }
        if(!$person= Person::getEditableById($ids[0])) {
            $PH->abortWarning(__("Could not get Person"));
        }
    }

    ### set up page and write header ####
    {
        $page= new Page(['autofocus_field'=>'person_nickname']);
        $page->cur_tab='people';

        $page->crumbs= build_person_crumbs($person);
        $page->options=[
            new NaviOption([
                'target_id' => 'personEditRights',
            ]),
        ];

        $page->type=__('Edit Person','page type');
        $page->title= $person->name;
        $page->title_minor=  __('Adjust user-rights');
        echo(new PageHeader);
    }
    echo (new PageContentOpen);

    ### write form #####
    {
        require_once(confGet('DIR_STREBER') . 'render/render_form.inc.php');

        echo "<div>";
        echo __("Please consider that activating login-accounts might trigger security-issues.");
        echo "</div>";

        $form=new PageForm();
        $form->button_cancel=true;

        $form->add(new Form_checkbox("person_can_login",__('Person can login','form label'),$person->can_login));

        foreach($g_user_right_names as $value=>$key) {
            $form->add(new Form_checkbox("right_".$value, $key, $person->user_rights & $value));
        }
        echo ($form);

        $PH->go_submit= $PH->getValidPageId('personEditRightsSubmit');
        echo "<input type=hidden name='person' value='$person->id'>";

    }
    echo (new PageContentClose);
    echo (new PageHtmlEnd);
}




/**
* Submit changes to user rights of a person
*
* @ingroup pages
*
* the user-rights-validation is checked by pageHandler (requires RIGHT_PERSON_EDIT_RIGHTS)
*/
function personEditRightsSubmit()
{
    global $PH;
    global $g_user_right_names;


    ### cancel ###
    if(get('form_do_cancel')) {
        if(!$PH->showFromPage()) {
            $PH->show('home',[]);
        }
        exit();
    }

    ### get person ####
    $id= getOnePassedId('person');  # aborts if not found
    if(!$person = Person::getEditableById($id)) {
        $PH->abortWarning(__("Could not get person"));
        return;
    }

    $flag_ok= TRUE;     # was required for advanced form-validation (currently not required)


    ### get rights ###
    foreach($g_user_right_names as $value=>$key) {
        if(get("right_".$value)) {
            $person->user_rights |= $value;
        }
        else {
            $person->user_rights &= $value ^ RIGHT_ALL;
        }
    }


    /**
    * NOTE, if checkbox is not rendered in editForm, user-account will be disabled!
    * there seems no way the be sure the checkbox has been rendered, if it is not checked in form
    */
    if($can_login= get('person_can_login')) {
        $person->can_login= 1;
    }
    else {
        $person->can_login= 0;
    }

    ### if anything fine, update and go back ###
    if($flag_ok) {
        $person->update();
        new FeedbackMessage(__("User rights changed"));

        ### display taskView ####
        if(!$PH->showFromPage()) {
            $PH->show('home',[]);
        }
    }
    ### otherwise return to form ###
    else {
        $PH->show('personEditRights',NULL,$person);
    }
}






/**
* Link companies to person @ingroup pages
*/
function personLinkCompanies() {
    global $PH;

    $id = getOnePassedId('person','people_*');   # WARNS if multiple; ABORTS if no id found
    $person = Person::getEditableById($id);
    if(!$person) {
        $PH->abortWarning("ERROR: could not get Person");
        return;
    }

    ### set up page and write header ####
    {
        $page = new Page(['use_jscalendar'=>true, 'autofocus_field'=>'company_name']);
        $page->cur_tab = 'people';
        $page->type = __("Edit Person");
        $page->title = sprintf(__("Edit %s"),$person->name);
        $page->title_minor = __("Add related companies");


        $page->crumbs = build_person_crumbs($person);
        $page->options[] = new NaviOption([
            'target_id'     => 'personLinkCompanies',
        ]);

        echo(new PageHeader);
    }
    echo (new PageContentOpen);

    ### write form #####
    {
        require_once(confGet('DIR_STREBER') . 'pages/company.inc.php');
        require_once(confGet('DIR_STREBER') . 'render/render_form.inc.php');
        $companies = Company::getAll();
        $list = new ListBlock_companies();
        $list->show_functions = false;
        $list->show_icons = false;

        $list->render_list($companies);

        $PH->go_submit = 'personLinkCompaniesSubmit';

        echo "<input type=hidden name='person' value='$person->id'>";
        echo "<input class=button2 type=submit>";

    }
    echo (new PageContentClose);
    echo (new PageHtmlEnd);

}

/**
* companyLinkPeopleSubmit @ingroup pages
*/
function personLinkCompaniesSubmit()
{
    global $PH;
    require_once(confGet('DIR_STREBER') . 'db/class_company.inc.php');

    $id = getOnePassedId('person','people_*');
    $person = Person::getEditableById($id);
    if(!$person) {
        $PH->abortWarning("Could not get object...");
    }

    $company_ids = getPassedIds('company','companies_*');
    if(!$company_ids) {
        $PH->abortWarning(__("No companies selected..."));
    }

    $employments = $person->getEmployments();

    foreach($company_ids as $cid) {
        if(!$company = Company::getEditableById($cid)) {
            $PH->abortWarning("Could not access company by id");
        }

        #### company already related to person? ###
        $already_in = false;
        foreach($employments as $e) {
            if($e->company == $company->id) {
                $already_in = true;
                break;
            }
        }
        if(!$already_in) {
            $e_new = new Employment([
                'id'=>0,
                'person'=>$person->id,
                'company'=>$company->id,
            ]);
            $e_new->insert();
        }
        else {
            new FeedbackMessage(__("Company already related to person"));
        }
    }
    ### display personView ####
    if(!$PH->showFromPage()) {
        $PH->show('personView',['person'=>$person->id]);
    }
}

/**
* Unlink a person from a company @ingroup pages
*/
function personCompaniesDelete()
{
    global $PH;

    $id = getOnePassedId('person','people_*');
    $person = Person::getEditableById($id);
    if(!$person) {
        $PH->abortWarning("Could not get object...");
    }

    $company_ids = getPassedIds('company','companies_*');
    if(!$company_ids) {
        $PH->abortWarning(__("No companies selected..."));
    }

    $employments = $person->getEmployments();

    $counter = 0;
    $errors = 0;
    foreach($company_ids as $cid) {
        if(!$company = Company::getEditableById($cid)) {
            $PH->abortWarning("Could not access company by id");
        }

        $assigned_to = false;
        foreach($employments as $e) {
            if($e->company == $company->id) {
                $assigned_to = true;
                $e_id = $e->id;

                if($assigned_to){
                    $e_remove = Employment::getEditableById($e_id);
                    if(!$e_remove) {
                         $PH->abortWarning("Could not access employment by id");
                    }
                    else {
                        if($e_remove->delete()) {
                            $counter++;
                        }
                        else {
                            $errors++;
                        }
                    }
                }
                else {
                    $PH->abortWarning("Company isn't related to this person");
                }
            }
        }
    }

    if($errors) {
        new FeedbackWarning(sprintf(__("Failed to remove %s companies"),$errors));
    }
    else {
        new FeedbackMessage(sprintf(__("Removed %s companies"), $counter));
    }

    if(!$PH->showFromPage()) {
        $PH->show('personView',['person'=>$person->id]);
    }
}






/**
* Mark all items of a person as been viewed @ingroup pages
*
* if an item is viewed (not changed) depends on two facts:
* 1. item_person item exists
* 2. item.modfied < person.date_highlight_changes
*/
function personAllItemsViewed()
{
    global $PH;
    global $auth;

    $id = intval(getOnePassedId('person','people_*'));
    if($id) {
        if($id == $auth->cur_user->id) {
            $person= $auth->cur_user;
        }
        else {
            $person = Person::getEditableById($id);
            if(!$person) {
                $PH->abortWarning("Could not get object...");
            }
        }
    }
    else {
        ### profile and login ###
        if($auth->cur_user->user_rights & RIGHT_PERSON_EDIT_RIGHTS) {
            $person= $auth->cur_user;
        }
        else {
            $PH->abortWarning("Could not get object...");
        }
    }

    $person->date_highlight_changes = getGMTString();
    $person->update(['date_highlight_changes'],false);

    /**
    * note, we have to update the current user to get an emmidate effect
    */
    if($auth->cur_user->id == $person->id) {
        $auth->cur_user->date_highlight_changes = getGMTString();
    }

    new FeedbackMessage(sprintf(__("Marked all previous items as viewed.")));

    if(!$PH->showFromPage()) {
        $PH->show('personView',['person'=>$person->id]);
    }
}



/**
* Filter own changes
*
*/
function personToggleFilterOwnChanges()
{
    global $PH;
    global $auth;

    ### get person ####
    $id= getOnePassedId('person','people_*');

    if(!$p= Person::getEditableById($id)) {
        $PH->abortWarning("Invalid person-id!");
    }

    $p->settings ^= USER_SETTING_FILTER_OWN_CHANGES;
    $p->update(['settings'], false);

    if( $auth->cur_user && $p->id == $auth->cur_user->id) {
      $auth->cur_user->settings ^= USER_SETTING_FILTER_OWN_CHANGES;    
    }

    ### display taskView ####
    if(!$PH->showFromPage()) {
        $PH->show('projView',['prj'=>$person->project]);
    }
}


?>

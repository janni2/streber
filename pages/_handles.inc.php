<?php if(!function_exists('startedIndexPhp')) { header("location:../index.php"); exit();}

/**\file 
* Define pages and there options
* 
* read more at...
* - http://www.streber-pm.org/3391
* - http://www.streber-pm.org/3392
*/

/**\defgroup pages Pages
*
* The framework splits user interaction into Pages which are defined as PageHandles in _handles.inc.php
*/


new PageHandle(['id'=>'home',
    'req'=>'pages/home.inc.php',
    'title'=>__('Recent changes','Page option tab'),
    'test'=>'yes',

    'cleanurl'=>'home',
]);
new PageHandle(['id'=>'ajaxMoreChanges',
    'req'=>'pages/item_ajax.inc.php',
    'test'=>'no',
    'valid_for_crawlers'=>false,
    'valid_params'=> ['prj'=>'\d+', 'start'=>'\d+', 'count' => '\d+'],

]);


new PageHandle(['id'=>'homeTasks',
    'req'=>'pages/home.inc.php',
    'title'=>__('Your Tasks'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'homeBookmarks',
    'req'=>'pages/home.inc.php',
    'title'=>__('Bookmarks'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'homeListEfforts',
    'req'=>'pages/home_list_efforts.inc.php',
    'title'=>__('Efforts'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'homeTimetracking',
    'req'=>'pages/home_timetracking.inc.php',
    'title'=>__('Time tracking'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'ajaxUserEfforts',
    'req'=>'pages/home_timetracking.inc.php',
    'title'=>__('users efforts in json'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'ajaxUserTasks',
    'req'=>'pages/home_timetracking.inc.php',
    'title'=>__('users tasks in json'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'ajaxUserProjects',
    'req'=>'pages/home_timetracking.inc.php',
    'title'=>__('users project in json'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);
new PageHandleSubm(['id'=>'newEffortFromTimeTracking',
    'req'=>'pages/home_timetracking.inc.php',
    'valid_params'=>[],
]);


new PageHandle(['id'=>'ajaxSearch',
    'req'=>'pages/ajax_search.inc.php',
    'title'=>__(''),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);



new PageHandle(['id'=>'homeAllChanges',
    'req'=>'pages/home.inc.php',
    'title'=>__('Overall changes'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'homeMonthlyReport',
    'req'=>'pages/home_monthly_report.inc.php',
    'title'=>__('Report'),
    'test'=>'yes',
    
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'playground',
    'req'=>'pages/playground.inc.php',
    'title'=>__('Playground'),
    'test'=>'no',

    'cleanurl'=>'playground',
    'valid_for_crawlers'=>false,
]);


new PageHandle(['id'=>'itemView',
    'req'=>'pages/item.inc.php',
    'title'=>__('View item'),
    'test'=>'yes',
    'valid_params'=>['item'=>'\d+'],

    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['item' => '_ITEM_'],
]);


new PageHandleFunc(['id'=>'itemsSetPubLevel',
    'title'=>__('Set Public Level'),
    'req'=>'pages/item.inc.php',
    'test'=>'yes',
    'valid_params'=> ['item'=>'\d+', 'item_\d+'=>'\d+', 'from'=>'.*', 'item_pub_level' => '\d+'],
]);

new PageHandleFunc(['id'=>'itemsAsBookmark',
    'req'=>'pages/bookmark.inc.php',
    'title'=>__('Mark as bookmark'),

    'test'=>'yes',
    'test_params'=>['item'=>'_itemView_',],
]);
new PageHandleFunc(['id'=>'itemsRemoveBookmark',
    'req'=>'pages/bookmark.inc.php',
    'title'=>__('Remove bookmark'),

    'test'=>'yes',
    'test_params'=>['item'=>'_itemView_',],
]);
new PageHandleFunc(['id'=>'itemsSendNotification',
    'req'=>'pages/item.inc.php',
    'title'=>__('Send notification'),
    'test'=>'yes',
    'test_params'=>['item'=>'_itemView_',],
]);
new PageHandleFunc(['id'=>'itemsRemoveNotification',
    'req'=>'pages/item.inc.php',
    'title'=>__('Remove notification'),
    'test'=>'yes',
    'test_params'=>['item'=>'_itemView_',],
]);
new PageHandleForm(['id'=>'itemBookmarkEdit',
    'req'=>'pages/bookmark.inc.php',
    'title'=>__('Edit bookmarks'),
    'valid_params'=>[],
#    'test'=>'yes',
#    'test_params'=>array('id'=>'_ITEM_',),
]);

new PageHandleSubm(['id'=>'itemBookmarkEditSubmit',
    'req'=>'pages/bookmark.inc.php',
    'valid_params'=>[],
]);

new PageHandleForm(['id'=>'itemBookmarkEditMultiple',
    'req'=>'pages/bookmark.inc.php',
    'title'=>__('Edit multiple bookmarks'),
    'valid_params'=>[],
    #'test'=>'yes',
    #'test_params'=>array('id'=>'_ITEM_',),
]);

new PageHandleSubm(['id'=>'itemBookmarkEditMultipleSubmit',
    'req'=>'pages/bookmark.inc.php',
    'valid_params'=>[],
]);

new PageHandle(['id'=>'itemViewDiff',
    'req'=>'pages/item.inc.php',
    'title'=>__('view changes'),
    'valid_params'=>[
           'from'=>'.*',
           'item'=>'\*',
           'date1'=>'\S*',
           'date2'=>'\S*',
    ],

    'test'=>'yes',
    'test_params'=>['item'=>'_taskView_',],
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'topicExportAsHtml',
    'req'=>'pages/topic_export_as_html.inc.php',
    'title'=>__('Export as Html'),
    'valid_params'=>[
           'from'=>'.*',
           'tsk'=>'\*',
    ],
    'test'=>'no',
]);


/**
* collector for global views like projList, personList, home, etc.
*/
new PageHandle(['id'=>'globalView',
    'req'=>'pages/misc.inc.php',
    'test'=>'no',
    'valid_params'=>['id'=>'\d+'],

    #'cleanurl'=>'_PAGE_',
    #'cleanurl_mapping'=>array('id' => '_ITEM_'),
]);


/**
* project
*/
new PageHandle(['id'=>'projList',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Active Projects'),
    'valid_params'=>[  'from'=>'.*', 'format'=>''
    ],
    'test'=>'yes',

]);
new PageHandle(['id'=>'projListClosed',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Closed Projects'),
    'valid_params'=>[  'from'=>'.*', 'format'=>''
    ],
    'test'=>'yes',

    'cleanurl' => 'projClosed',
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'projListTemplates',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Project Templates'),
    'rights_required'=>RIGHT_PROJECT_CREATE,
    'valid_params'=>[  'from'=>'.*', 'format'=>''
    ],
    'test'=>'yes',
    'cleanurl' => 'projTemplates',
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'projView',
    'req'=>'pages/project_view.inc.php',
    'title'=>__('View Project'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],

    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['prj'=>'_ITEM_'],

]);
new PageHandle(['id'=>'projViewAsRSS',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('View Project as RSS'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'test'=>'no',
    'test_params'=>['prj'=>'_projectView_',],
    'http_auth'=>true,                        # implements HTTP Authentification
]);

new PageHandle(['id'=>'projViewMilestones',
    'req'       =>'pages/project_more.inc.php',
    'title'     =>__('Milestones'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            'preset'=>'.*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],

]);
new PageHandle(['id'=>'projViewDocu',
    'req'       =>'pages/project_more.inc.php',
    'title'     =>__('Documentation'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],

]);
new PageHandle(['id'=>'projViewVersions',
    'req'       =>'pages/project_more.inc.php',
    'title'     =>__('Versions'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            'preset'=>'.*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],
]);

new PageHandle(['id'=>'projViewEfforts',
    'req'       =>'pages/project_more.inc.php',
    'title'     =>__('View Project'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            'preset'=>'.*',
                            'person'=>'.*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'projExportEfforts',
    'req'       =>'pages/project_more.inc.php',
    'title'     =>__('View Project'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            'preset'=>'.*',
                            'person'=>'.*',
                            ],
    'valid_for_crawlers'=>false,
]);


new PageHandle(['id'=>'projViewEffortCalculations',
    'req'       =>'pages/project_more.inc.php',
    'title'     =>__('View Project'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            'preset'=>'.*',
                            'person'=>'.*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'projViewFiles',
    'req'       =>'pages/project_more.inc.php',
    'title'     =>__('Uploaded Files'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],
]);

new PageHandle(['id'=>'projViewChanges',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Changes'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            'preset'=>'.*',
                            'person'=>'.*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'projViewTasks',
    'req'=>'pages/project_view_tasks_in_groups.inc.php',
    'title'=>__('Tasks'),
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            'preset'=>'.*',
                            'for_milestone' => '\d*',
                            'person' => '.*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],

    #'cleanurl'=>'projViewTasks/_PROJECT_/_TASK_',
    #'cleanurl_mapping'=>array('prj' => '_PROJECT_', 'task' => '_TASK_'),

]);
new PageHandleFunc(['id'=>'projNew',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('New project'),
    'rights_required'=>RIGHT_PROJECT_CREATE,
    'valid_params'=>[  'from'=>'.*',
                            'company'=>'\d*',
                            ],
    'test'=>'yes',
    'valid_for_crawlers'=>false,
]);
new PageHandleFunc(['id'=>'projCreateTemplate',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Create Template'),
    'rights_required'=>RIGHT_PROJECT_CREATE,
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'valid_for_crawlers'=>false,
]);
new PageHandleFunc(['id'=>'projNewFromTemplate',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Project from Template'),
    'rights_required'=>RIGHT_PROJECT_CREATE,
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'valid_for_crawlers'=>false,
]);


new PageHandleForm(['id'=>'projEdit',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Edit Project'),
    'rights_required'=>RIGHT_PROJECT_EDIT,
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],
    'valid_for_crawlers'=>false,
]);
new PageHandleSubm(['id'=>'projEditSubmit',
    'req'=>'pages/project_more.inc.php',
    'rights_required'=>RIGHT_PROJECT_EDIT,
    'valid_params'=>[],
    'valid_for_crawlers'=>false,

]);

new PageHandleFunc(['id'=>'projDelete',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Delete Project'),
    'rights_required'=>RIGHT_PROJECT_DELETE,
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'valid_for_crawlers'=>false,
]);
new PageHandleFunc(['id'=>'projChangeStatus',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Change Project Status'),
    'rights_required'=>RIGHT_PROJECT_EDIT,
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
]);
new PageHandleForm(['id'=>'projAddPerson',
    'req'=>'pages/project_more.inc.php',
    'title'=>__('Add Team member'),
    'rights_required'=>RIGHT_PROJECT_EDIT,
    'valid_params'=>[  'from'=>'.*',
                            'prj'=>'\d*',
                            ],
    'test'=>'yes',
    'test_params'=>['prj'=>'_projectView_',],
]);
new PageHandleSubm(['id'=>'projAddPersonSubmit',
    'req'=>'pages/project_more.inc.php',
    'rights_required'=>RIGHT_PROJECT_EDIT,
    'valid_params'=>[],
]);

/**
* team-members aka projectperson
*/
/*
* currently implemented with proj.inc->addPerson()
*
new PageHandleFunc(array('id'=>'projectPersonNew',
    'req'       =>'pages/projectperson.inc.php',
    'title'     =>__('Add Team member'),
    'rights_required'=>RIGHT_PROJECT_ASSIGN,
    'test'=>'yes',
    'test_params'=>array('prj'=>'_projectView_',),
));
*/

new PageHandleForm(['id'=>'projectPersonEdit',
    'req'       =>'pages/projectperson.inc.php',
    'title'     =>__('Edit Team member'),
    'rights_required'=>RIGHT_PROJECT_ASSIGN,
    'test'=>'yes',
    'test_params'=>['projectperson'=>'_projectPersonEdit_',],
]);
new PageHandleSubm(['id'=>'projectPersonEditSubmit',
    'req'       =>'pages/projectperson.inc.php',
    'rights_required'=>RIGHT_PROJECT_ASSIGN,

]);
new PageHandleFunc(['id'=>'projectPersonDelete',
    'req'       =>'pages/projectperson.inc.php',
    'title'     =>__('Remove from team'),
    'rights_required'=>RIGHT_PROJECT_ASSIGN,

    'test'=>'complex',
    'test_params'=>['projectperson'=>'_projectPersonEdit_',],
]);




/**
* task
*/
new PageHandle(['id'=>'taskView',
    'req'=>'pages/task_view.inc.php',
    'title'=>__('View Task'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskView_',],

    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['tsk' => '_ITEM_'],

]);

new PageHandle(['id'=>'taskViewAsDocu',
    'req'=>'pages/task_view.inc.php',
    'title'=>__('View Task As Docu'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskView_',],

    #'cleanurl'=>'_ITEM_',
    #'cleanurl_mapping'=>array('tsk' => '_ITEM_'),
]);




new PageHandleForm(['id'=>'taskEdit',
    'req'=>'pages/task_edit.inc.php',
    'title'=>__('Edit Task'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);
new PageHandleSubm(['id'=>'taskEditSubmit',
    'req'=>'pages/task_edit.inc.php',

]);

new PageHandleForm(['id'=>'taskEditMultiple',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Edit multiple Tasks'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);
new PageHandleSubm(['id'=>'taskEditMultipleSubmit',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Edit multiple Tasks'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);


new PageHandle(['id'=>'taskViewEfforts',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('View Task Efforts'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskView_',],
    'valid_for_crawlers'=>false,
    'valid_for_crawlers'=>false,
]);

new PageHandleFunc(['id'=>'tasksDelete',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Delete Task(s)'),

]);
new PageHandleFunc(['id'=>'tasksUndelete',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Restore Task(s)'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);

new PageHandleFunc(['id'=>'tasksMoveToFolder',
    'req'=>'pages/task_move.inc.php',
    'title'=>__('Move tasks to folder'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskView_',],
]);

new PageHandle(['id'=>'ajaxListProjectFolders',
    'req'=>'pages/task_move.inc.php',
    'test'=>'no',
    'valid_for_crawlers'=>false,
    'valid_params'=> ['prj'=>'\d+'],

]);





new PageHandleFunc(['id'=>'tasksComplete',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Mark tasks as Complete'),


    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);
new PageHandleFunc(['id'=>'tasksApproved',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Mark tasks as Approved'),


    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);
new PageHandleFunc(['id'=>'tasksClosed',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Mark tasks as Closed'),
    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);
new PageHandleFunc(['id'=>'tasksReopen',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Mark tasks as Open'),


    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);


new PageHandleFunc(['id'=>'taskNew',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('New task'),
    'valid_params'=>[  'prj'=>'\d*',
                            'parent_task'=>'\d*',
                            'add_issue'=>'1',
                            'new_name'=>'.*',
                            'for_milestone'=>'\d*',
                            'task_category'=>'\d*',
                            'task_assign_to_0'=>'\d*',
                            'task_show_folder_as_documentation'=>'\d*',
    ],

    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],

]);
new PageHandleFunc(['id'=>'taskNewDocu',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('New task'),
    'valid_params'=>[  'prj'=>'\d*',
                            'parent_task'=>'\d*',
                            'add_issue'=>'1',
                            'new_name'=>'.*',
                            'for_milestone'=>'\d*',
                            'task_assign_to_0'=>'\d*',
    ],

    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],

]);


new PageHandleFunc(['id'=>'taskNewBug',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('New bug'),
    'valid_params'=>[  'prj'=>'\d*',
                            'parent_task'=>'\d*',
                            'add_issue'=>'1',
                            'for_milestone'=>'\d*',
                            'task_category'=>'\d*',
                            'task_assign_to_0'=>'\d*',
    ],

    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],
]);


new PageHandleFunc(['id'=>'taskNewFolder',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('New folder'),
    'valid_params'=>[  'prj'=>'\d*',
                            'parent_task'=>'\d*',
                            'add_issue'=>'1',
                            'new_name'=>'.*',
                            'for_milestone'=>'\d*',
                            'task_assign_to_0'=>'\d*',
    ],


    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],
]);

new PageHandleFunc(['id'=>'taskNewMilestone',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('New milestone'),
    'valid_params'=>[  'prj'=>'\d*',
                            'task_assign_to_0'=>'\d*',
    ],

    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],
]);

new PageHandleFunc(['id'=>'taskNewVersion',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('New released Version'),
    'valid_params'=>[  'prj'=>'\d*',
                            'task_assign_to_0'=>'\d*',
    ],

    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],
]);


new PageHandleFunc(['id'=>'taskToggleViewCollapsed',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Toggle view collapsed'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);

new PageHandleFunc(['id'=>'taskCollapseAllComments',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Toggle view collapsed'),
    'valid_params'=>[
           'comment'=>'\d*',
           'from'=>'.*',
    ],

    'test'=>'yes',
    'test_params'=>['comment'=>'_commentEdit_',],

]);
new PageHandleFunc(['id'=>'taskExpandAllComments',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Toggle view collapsed'),
    'valid_params'=>[
           'comment'=>'\d*',
           'from'=>'.*',
    ],

    'test'=>'yes',
    'test_params'=>['comment'=>'_commentEdit_',],
]);

new PageHandleFunc(['id'=>'taskAddIssueReport',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Add issue/bug report'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);


new PageHandleForm(['id'=>'taskEditDescription',
    'req'=>'pages/task_more.inc.php',
    'title'=>__('Edit Description'),

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskEdit_',],
]);
new PageHandleSubm(['id'=>'taskEditDescriptionSubmit',
    'req'=>'pages/task_more.inc.php',

]);

/**
* efforts
*/
new PageHandle(['id'=>'effortView',
    'req'=>'pages/effort.inc.php',
    'title'=>__('View effort'),
    'valid_params'=>[
           'effort'=>'\d*'],

#    'test'=>'yes',
#    'test_params'=>array('effort'=>'_effortView_',),

    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['effort' => '_ITEM_'],
    'valid_for_crawlers'=>false,

]);
new PageHandle(['id'=>'effortViewMultiple',
    'req'=>'pages/effort.inc.php',
    'title'=>__('View multiple efforts'),
    'valid_params'=>[
           'effort'=>'\d*'],

    #'test'=>'no',
    #'test_params'=>array('effort'=>'_effortViewMultiple_',),
    'valid_for_crawlers'=>false,
]);
new PageHandleFunc(['id'=>'effortNew',
    'req'=>'pages/effort.inc.php',
    'title'=>__('Log hours'),

    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_',],
]);
new PageHandleForm(['id'=>'effortEdit',
    'req'=>'pages/effort.inc.php',
    'title'=>__('Edit time effort'),

#    'test'=>'yes',
#    'test_params'=>array('effort'=>'_effortEdit_',),
]);
new PageHandleSubm(['id'=>'effortEditSubmit',
    'req'=>'pages/effort.inc.php',

]);
new PageHandleForm(['id'=>'effortEditMultiple',
    'req'=>'pages/effort.inc.php',
    'title'=>__('Edit multiple efforts'),

    #'test'=>'yes',
    #'test_params'=>array('effort'=>'_effortEdit_',),
]);

new PageHandle(['id'=>'effortShowAsCSV',
    'req'=>'pages/effort_show_as_csv.inc.php',
    'title'=>__('Show Efforts as CSV'),

    #'test'=>'yes',
    #'test_params'=>array('effort'=>'_effortEdit_',),
]);


new PageHandleSubm(['id'=>'effortEditMultipleSubmit',
    'req'=>'pages/effort.inc.php',
    'title'=>__('Edit multiple efforts'),

    #'test'=>'yes',
    #'test_params'=>array('tsk'=>'_effortEdit_',),

]);
new PageHandleFunc(['id'=>'effortsDelete',
    'req'=>'pages/effort.inc.php',
]);


/**
* comment
*/
new PageHandle(['id'=>'commentView',
    'req'=>'pages/comment.inc.php',
    'title'=>__('View comment'),
    'valid_params'=>[
           'comment'=>'\d*'],

    'test'=>'yes',
    'test_params'=>['comment'=>'_commentView_',],

    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['comment' => '_ITEM_'],
    'valid_for_crawlers'=>false,
    'valid_for_crawlers'=>false,
]);

new PageHandleFunc(['id'=>'commentNew',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Create comment'),
    'valid_params'=>[
           'parent_task'=>'\d*',
           'comment'=>'\d*',
           'prj'=>'\d*'],

    'test'=>'yes',
    'test_params'=>['prj'=>'_projectEdit_', 'parent_task'=>'_taskEdit_'],
]);
new PageHandleForm(['id'=>'commentEdit',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Edit comment'),

    'test'=>'yes',
    'test_params'=>['comment'=>'_commentEdit_',],
]);
new PageHandleSubm(['id'=>'commentEditSubmit',
    'req'=>'pages/comment.inc.php',

]);
new PageHandleFunc(['id'=>'commentsDelete',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Delete comment'),

    'test'=>'yes',
]);
new PageHandleFunc(['id'=>'commentsUndelete',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Delete comment'),

    'test'=>'yes',
]);
new PageHandleFunc(['id'=>'commentsMoveToFolder',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Delete comment'),

    'test'=>'yes',
]);
new PageHandleFunc(['id'=>'commentToggleViewCollapsed',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Toggle view collapsed'),
    'valid_params'=>[
           'comment'=>'\d*',
           'from'=>'.*',
    ],

    'test'=>'yes',
    'test_params'=>['comment'=>'_commentView_',],
]);
new PageHandleFunc(['id'=>'commentsCollapseView',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Toggle view collapsed'),
    'valid_params'=>[
           'comment'=>'\d*',
           'from'=>'.*',
    ],

    'test'=>'yes',
    'test_params'=>['comment'=>'_commentEdit_',],

]);
new PageHandleFunc(['id'=>'commentsExpandView',
    'req'=>'pages/comment.inc.php',
    'title'=>__('Toggle view collapsed'),
    'valid_params'=>[
           'comment'=>'\d*',
           'from'=>'.*',
    ],

    'test'=>'yes',
    'test_params'=>['comment'=>'_commentEdit_',],
]);


/**
* files
*/
new PageHandle(['id'=>'fileView',
    'req'=>'pages/file.inc.php',
    'title'=>__('View file'),

    'test'=>'yes',
    'test_params'=>['prj'=>'_fileView_',],

    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['file' => '_ITEM_'],
    'valid_for_crawlers'=>false,
]);

new PageHandleFunc(['id'=>'filesUpload',
    'req'=>'pages/file.inc.php',
    'title'=>__('Upload file'),
]);


new PageHandleFunc(['id'=>'fileUpdate',
    'req'=>'pages/file.inc.php',
    'title'=>__('Update file'),

#    'test'=>'yes',
#    'test_params'=>array('prj'=>'_projectEdit_',),
]);

new PageHandleForm(['id'=>'fileEdit',
    'req'=>'pages/file.inc.php',
    'title'=>__('Edit file'),

    'test'=>'yes',
#    'test_params'=>array('effort'=>'_fileEdit_',),
]);
new PageHandle(['id'=>'fileDownload',
    'req'=>'pages/file.inc.php',
    'title'=>__('Download'),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'fileDownloadAsImage',
    'req'=>'pages/file.inc.php',
    'title'=>__('Show file scaled'),
    'valid_for_crawlers'=>false,
]);

new PageHandleSubm(['id'=>'fileEditSubmit',
    'req'=>'pages/file.inc.php',

]);
new PageHandleFunc(['id'=>'filesDelete',
    'req'=>'pages/file.inc.php',
]);
new PageHandleFunc(['id'=>'filesMoveToFolder',
    'req'=>'pages/file.inc.php',
    'title'=>__('Move files to folder'),

    'valid_params'=>[
           'from'=>'.*',
           'files_\d+_chk'=>"\S+",
           'file' =>"\d+",
           'tsk' =>"\d+",
    ],

    'test'=>'yes',
    'test_params'=>['tsk'=>'_taskView_',],

]);



/**
* company
*/
new PageHandle(['id'=>'companyList',
    'req'=>'pages/company.inc.php',
    'title'=>__('List Companies'),
    'test'=>'yes',
    'valid_for_crawlers'=>false,

]);

new PageHandle(['id'=>'companyView',
    'req'=>'pages/company.inc.php',
    'title'=>__('View Company'),

    'test'=>'yes',
    'test_params'=>['company'=>'_companyView_',],

    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['company' => '_ITEM_'],
]);
new PageHandleFunc(['id'=>'companyNew',
    'req'=>'pages/company.inc.php',
    'title'=>__('New company'),
    'rights_required'=>RIGHT_COMPANY_CREATE,

    'test'=>'yes',
]);
new PageHandleForm(['id'=>'companyEdit',
    'req'=>'pages/company.inc.php',
    'title'=>__('Edit Company'),
    'rights_required'=>RIGHT_COMPANY_EDIT,

    'test'=>'yes',
    'test_params'=>['company'=>'_companyEdit_',],
]);
new PageHandleSubm(['id'=>'companyEditSubmit',
    'req'=>'pages/company.inc.php',
    'rights_required'=>RIGHT_COMPANY_EDIT,

]);
new PageHandleFunc(['id'=>'companyDelete',
    'req'=>'pages/company.inc.php',
    'title'=>__('Delete Company'),
    'rights_required'=>RIGHT_COMPANY_DELETE,

]);
new PageHandle(['id'=>'companyLinkPeople',
    'req'=>'pages/company.inc.php',
    'title'=>__('Link People'),
    'rights_required'=>RIGHT_COMPANY_EDIT,

    'test'=>'yes',
    'test_params'=>['company'=>'_companyEdit_',],      # test aborts / not enough params
    'valid_for_crawlers'=>false,
    'valid_for_crawlers'=>false,
]);
new PageHandleSubm(['id'=>'companyLinkPeopleSubmit',
    'req'=>'pages/company.inc.php',
    'rights_required'=>RIGHT_COMPANY_EDIT,

]);
new PageHandleFunc(['id'=>'companyPeopleDelete',
    'req'       =>'pages/company.inc.php',
    'title'     =>__('Remove people from company'),
    'rights_required'=>RIGHT_COMPANY_EDIT,

    'test'=>'yes',
    'test_params'=>['company'=>'_companyEdit_',],
]);

/**
* person
*/
new PageHandle(['id'=>'personList',
    'req'=>'pages/person.inc.php',
    'title'=>__('List People'),
    'test'=>'yes',
    'valid_for_crawlers'=>false,

]);


new PageHandle(['id'=>'personView',
    'req'=>'pages/person_view.inc.php',
    'title'=>__('View Person'),

    'test'=>'yes',
    'test_params'=>['person'=>'_personView_',],      # test aborts / not enough params
    'cleanurl'=>'_ITEM_',
    'cleanurl_mapping'=>['person' => '_ITEM_'],
]);
new PageHandleFunc(['id'=>'personNew',
    'req'=>'pages/person.inc.php',
    'title'=>__('New person'),
    'rights_required'=>RIGHT_PERSON_CREATE,

    'test'=>'yes',
]);
new PageHandleForm(['id'=>'personEdit',
    'req'=>'pages/person.inc.php',
    'title'=>__('Edit Person'),
    'rights_required'=>RIGHT_PERSON_EDIT_SELF,

    'test'=>'yes',
    'test_params'=>['person'=>'_personEdit_',],      # test aborts / not enough params
]);
new PageHandleSubm(['id'=>'personEditSubmit',
    'req'=>'pages/person.inc.php',
    'rights_required'=>RIGHT_PERSON_EDIT_SELF,
    'valid_for_tuid'=>true,                               # valid for temporary user ids

]);
new PageHandleForm(['id'=>'personEditRights',
    'rights_required'=>RIGHT_PERSON_EDIT_RIGHTS,
    'req'=>'pages/person.inc.php',
    'title'=>__('Edit user rights'),

    'test'=>'yes',
    'test_params'=>['person'=>'_personEdit_',],      # test aborts / not enough params
]);
new PageHandleSubm(['id'=>'personEditRightsSubmit',
    'rights_required'=>RIGHT_PERSON_EDIT_RIGHTS,
    'req'=>'pages/person.inc.php',

]);

new PageHandleFunc(['id'=>'personDelete',
    'req'=>'pages/person_delete.inc.php',
    'title'=>__('Delete Person'),
    'rights_required'=>RIGHT_PERSON_DELETE,
]);
new PageHandle(['id'=>'personViewProjects',
    'req'=>'pages/person.inc.php',
    'title'=>__('View Projects of Person'),
    'valid_params'=>[  'from'=>'.*',
                            'person'=>'\d*',
                            'preset'=>'.*',
                            'prj'=>'.*'
                            ],
    'test'=>'yes',
    'test_params'=>['person'=>'_personView_',],      # test aborts / not enough params
]);

new PageHandleSubm(['id'=>'personRevertChanges',
    'req'=>'pages/personRevertChanges.inc.php',
    'rights_required'=>RIGHT_PROJECT_EDIT,
]);

new PageHandle(['id'=>'personViewTasks',
    'req'=>'pages/person.inc.php',
    'title'=>__('View Task of Person'),
    'valid_params'=>[  'from'=>'.*',
                            'person'=>'\d*',
                            'preset'=>'.*',
                            'prj'=>'.*'
                            ],
    'test'=>'yes',
    'test_params'=>['person'=>'_personView_',],      # test aborts / not enough params
]);
new PageHandle(['id'=>'personViewEfforts',
    'req'=>'pages/person.inc.php',
    'title'=>__('View Efforts of Person'),
    'valid_params'=>[  'from'=>'.*',
                            'person'=>'\d*',
                            'preset'=>'.*',
                            'prj'=>'.*'
                            ],
    'test'=>'yes',
    'test_params'=>['person'=>'_personView_',],      # test aborts / not enough params
    'valid_for_crawlers'=>false,
]);
new PageHandle(['id'=>'personViewChanges',
    'req'=>'pages/person.inc.php',
    'title'=>__('View Changes of Person'),
    'valid_params'=>[  'from'=>'.*',
                            'person'=>'\d*',
                            'preset'=>'.*',
                            'prj'=>'.*'
                            ],
    'test'=>'yes',
    'test_params'=>['person'=>'_personView_',],      # test aborts / not enough params
    'valid_for_crawlers'=>false,
]);
new PageHandleFunc(['id'=>'personSendActivation',
    'req'       =>'pages/person.inc.php',
    'title'     =>__('Send Activation'),
    'rights_required'=>RIGHT_PERSON_EDIT_SELF,

    'test'=>'complex',
    'test_params'=>['projectperson'=>'_projectPersonEdit_',],
]);
new PageHandleFunc(['id'=>'peopleFlushNotifications',
    'req'       =>'pages/person.inc.php',
    'title'     =>__('Send Notifications'),
    'rights_required'=>RIGHT_PERSON_EDIT,
]);

new PageHandleForm(['id'=>'personRegister',
    'req'       =>'pages/person_register.inc.php',
    'title'     =>__('Register'),
    'test'=>'yes',

    'cleanurl'  => 'register',
]);
new PageHandleSubm(['id'=>'personRegisterSubmit',
    'req'       =>'pages/person_register_submit.inc.php',
    'test'=>'yes',
]);
new PageHandle(['id'=>'personLinkCompanies',
    'req'=>'pages/person.inc.php',
    'title'=>__('Link Companies'),
    'rights_required'=>RIGHT_PERSON_EDIT,

    'test'=>'yes',
    'test_params'=>['person'=>'_personEdit_',],      # test aborts / not enough params
    'valid_for_crawlers'=>false,
]);
new PageHandleSubm(['id'=>'personLinkCompaniesSubmit',
    'req'=>'pages/person.inc.php',
    'rights_required'=>RIGHT_PERSON_EDIT,

]);
new PageHandleFunc(['id'=>'personCompaniesDelete',
    'req'       =>'pages/person.inc.php',
    'title'     =>__('Remove companies from person'),
    'rights_required'=>RIGHT_PERSON_EDIT,

    'test'=>'yes',
    'test_params'=>['person'=>'_personEdit_',],
]);
new PageHandleFunc(['id'=>'personAllItemsViewed',
    'req'       =>'pages/person.inc.php',
    'title'     =>__('Mark all items as viewed'),
]);
new PageHandleFunc(['id'=>'personToggleFilterOwnChanges',
    'req'       =>'pages/person.inc.php',
    'title'     =>__('Toggle filter own changes'),
    'test'=>'yes',
    'test_params'=>['person'=>'_personEdit_',],
]);



/**
* notification-trigger for cron-jobs ( index.php?go=triggerSendNotificiations)
*/
new PageHandleFunc(['id'=>'triggerSendNotifications',
    'req'       =>'pages/misc.inc.php',
    'title'     =>__('Flush Notifications'),
    'valid_for_anonymous'=>true,
]);


/**
* Renders a captcha image with using the given number
*/
new PageHandleFunc(['id'=>'imageRenderCaptcha',
    'req'       =>'pages/misc.inc.php',
    'valid_for_anonymous'=>true,
    'valid_params'=>[
           'key'=>'.*',
    ],
]);




/**
* login
*/
new PageHandleForm(['id'=>'loginForm',
    'req'=>'pages/login.inc.php',
    'title'=>__('Login'),
    'valid_for_anonymous'=>true,
    'ignore_from_handles'=>true,
    'valid_params'=>[],

    'cleanurl'=>'login',
]);
new PageHandleSubm(['id'=>'loginFormSubmit',
    'req'=>'pages/login.inc.php',
    'valid_for_anonymous'=>true,
]);

new PageHandleForm(['id'=>'loginForgotPassword',
    'req'=>'pages/login.inc.php',
    'title'=>__('Forgot your password?'),
    'valid_for_anonymous'=>true,
    'ignore_from_handles'=>true,
    'valid_params'=>[],
    #'cleanurl'=>'loginForgotPassword',
]);

new PageHandleSubm(['id'=>'loginForgotPasswordSubmit',
    'req'=>'pages/login.inc.php',
    'valid_for_anonymous'=>true,
]);


new PageHandleSubm(['id'=>'loginFormSubmit2',
    'req'=>'pages/login.inc.php',
    'valid_for_anonymous'=>true,
    'ignore_from_handles'=>true,
]);

new PageHandleFunc(['id'=>'logout',
    'req'=>'pages/login.inc.php',
    'title'=>__('Logout'),
    'ignore_from_handles'=>true,
    'cleanurl'=>'logout',
]);
new PageHandle(['id'=>'helpLicense',
    'req'=>'pages/login.inc.php',
    'title'=>__('License'),
    'valid_for_anonymous'=>true,
    'ignore_from_handles'=>true,
    'cleanurl'=>'license',
]);

/**
* misc
*/
new PageHandleFunc(['id'=>'changeSort',
    'req'=>'pages/misc.inc.php',
    'valid_params'=>[
           'from'=>'.*',
           'table_id'=>'\S*',
           'column'=>'\S*',
           'page_id'=>'\S*',
           'list_style'=>'\S*',
    ],
]);
new PageHandleFunc(['id'=>'changeBlockStyle',
    'req'=>'pages/misc.inc.php',
    'valid_params'=>[
           'from'=>'.*',
           'style'=>'\S*',
           'list_style'=>'\S*',
           'block_id'=>'\S*',
           'page_id'=>'\S*',
    ],
]);
new PageHandleFunc(['id'=>'changeBlockGrouping',
    'req'=>'pages/misc.inc.php'
]);

new PageHandleFunc(['id'=>'itemsRestore',
    'req'=>'pages/misc.inc.php',
    'valid_params'=>[
           'item'=>'\d*',
           'from'=>'.*',
    ],
    'title'=>__('restore Item'),
]);


new PageHandleForm(['id'=>'itemsRemoveMany',
    'req'=>'pages/items_remove_many.inc.php',
    'title'=>__('Remove many items'),
    'rights_required'=>RIGHT_EDITALL,

]);

new PageHandleForm(['id'=>'itemsRemoveManyPreview',
    'req'=>'pages/items_remove_many_preview.inc.php',
    'title'=>__('Preview removed items'),
    'rights_required'=>RIGHT_EDITALL,
]);

new PageHandleSubm(['id'=>'itemsRemoveManySubmit',
    'req'=>'pages/items_remove_many_submit.inc.php',
    'rights_required'=>RIGHT_EDITALL,
]);




new PageHandle(['id'=>'error',
    'req'=>'pages/error.inc.php',
    'title'=>__('Error'),
    'valid_for_anonymous'=>true,    # without this PH->show() could be trapped in endless loop will crash php-cgi!
    'ignore_from_handles'=>true,
]);

new PageHandle(['id'=>'activateAccount',
    'req'=>'pages/login.inc.php',
    'title'=>__('Activate an account'),
    'valid_for_tuid'=>true,    # without this PH->show() could be trapped in endless loop will crash php-cgi!
    'ignore_from_handles'=>true,
    'valid_params'=>[
           'comment'=>'\d*',
           'from'=>'.*',
    ],
    'valid_for_crawlers'=>false,

]);

new PageHandle(['id'=>'systemInfo',
    'req'=>'pages/misc.inc.php',
    'title'=>__('System Information'),
    'ignore_from_handles'=>true,
    'rights_required'=>RIGHT_VIEWALL,

    'test'=>'yes',
    'test_params'=>[],
    'valid_for_crawlers'=>false,

]);

new PageHandle(['id'=>'showPhpInfo',
    'req'=>'pages/misc.inc.php',
    'title'=>__('PhpInfo'),
    'ignore_from_handles'=>true,

    'rights_required'=>RIGHT_VIEWALL,
    'test'=>'yes',
    'test_params'=>[],
    'valid_for_crawlers'=>false,

]);

new PageHandle(['id'=>'showLog',
    'req'=>'pages/misc.inc.php',
    'title'=>__('Filter errors.log'),
    'ignore_from_handles'=>true,

    'rights_required'=>RIGHT_VIEWALL,
    'valid_for_crawlers'=>false,

]);
new PageHandleFunc(['id'=>'deleteLog',
    'req'=>'pages/misc.inc.php',
    'title'=>__('Delete errors.log'),

    'rights_required'=>RIGHT_VIEWALL,
]);

new PageHandle(['id'=>'search',
    'req'=>'pages/search.inc.php',
    'title'=>__('Search'),
    'valid_for_crawlers'=>false,
]);

/**
* misc pages / ajax etc.
*/
new PageHandle(['id'=>'taskRenderDetailsViewResponse',
    'req'=>'pages/task_ajax.inc.php',
    'title'=>__(''),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'taskBuildListEntryResponse',
    'req'=>'pages/task_ajax.inc.php',
    'title'=>__(''),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'taskSetOrderId',
    'req'=>'pages/task_ajax.inc.php',
    'title'=>__(''),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'taskAjaxCreateNewTask',
    'req'=>'pages/task_ajax.inc.php',
    'title'=>__(''),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'taskAddComment',
    'req'=>'pages/task_ajax.inc.php',
    'title'=>__(''),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'taskSetProperty',
    'req'=>'pages/task_ajax.inc.php',
    'title'=>__(''),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'taskAssignToPerson',
    'req'=>'pages/task_ajax.inc.php',
    'title'=>__(''),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'itemLoadField',
    'req'=>'pages/item_ajax.inc.php',
    'title'=>__('Load Field'),
    'valid_for_crawlers'=>false,
]);

new PageHandle(['id'=>'itemSaveField',
    'req'=>'pages/item_ajax.inc.php',
    'title'=>__('Save Field'),
    'valid_for_crawlers'=>false,
]);

?>

<?php
# streber - a php5 based project management system  (c) 2005-2007  / www.streber-pm.org
# Distributed under the terms and conditions of the GPL as stated in lang/license.html

/**\file
* Welcome to the source-code. This is a good point to start reading.
*
* This is index.php - the master-control-page. There are NO other php-pages, except from
* install.php (which should have been deleted in normal installations).
*
* index.php does...
*
* 1. initialize the profiler
* 2. include config and customize
* 3. include core-components
* 4. authenticate the user
* 5. render a page (which means calling a function defined in a file at pages/XXX.inc)
*
*/

/*.
    require_module 'standard';
    require_module 'pcre';
    require_module 'mysql';
.*/

error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_STRICT |E_PARSE|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR);


/*****************************************************************************
* setup framework
*****************************************************************************/

### create a function to make sure we started at index.php ###
function startedIndexPhp() {  }

require_once('std/initial_setup.inc.php');
initialBasicFixes();
initProfiler();


### include std functions ###
require_once('std/common.inc.php');
require_once('std/errorhandler.inc.php');
require_once('conf/defines.inc.php');
require_once('conf/conf.inc.php');


### if no db_settings start installation ###
if(file_exists(confGet('DIR_SETTINGS').confGet('FILE_DB_SETTINGS'))) {
	require_once(confGet('DIR_SETTINGS').confGet('FILE_DB_SETTINGS'));
}
else {
    header("location:install/install.php");
    exit();
}

include_once(confGet('DIR_SETTINGS').confGet('SITE_SETTINGS'));

### user-settings ##
if(file_exists('customize.inc.php')) {
    require_once(confGet('DIR_STREBER') . 'customize.inc.php');
}

/**
* overwrite db-settings if page requested while unit testing
* read more at www.streber-pm.org/7276
*/
if(getServerVar('HTTP_USER_AGENT') == 'streber_unit_tester') {
    confChange('DB_TABLE_PREFIX', 'test_' . confGet('DB_TABLE_PREFIX'));
    confChange('LOG_LEVEL', '');    
}

### start output-buffering? ###
if(confGet('USE_FIREPHP')) {
    ob_start();
}

filterGlobalArrays();


/**
* run profiler and output measures in footer?
*/
if(confGet('USE_PROFILER')) {
    require_once(confGet('DIR_STREBER') . "std/profiler.inc.php");
}
else {
    ###  define empty functions ###
    function measure_start($id){};
    function measure_stop($id){};
    function render_measures(){return '';};
}

measure_start('time_complete'); # measure complete time (stops before profiling)
measure_start('core_includes'); # measure time for including core-components


### included database handler ###
$db_type = confGet('DB_TYPE');
if(file_exists("db/db_".$db_type."_class.php")){
    require_once(confGet('DIR_STREBER') . "db/db_".$db_type."_class.php");
}
else{
    trigger_error("Datebase handler not found for db-type '$db_type'", E_USER_ERROR);
}


### include the core-classes (php5) ###
require_once( confGet('DIR_STREBER') . 'db/db.inc.php');
require_once( confGet('DIR_STREBER') . 'std/class_auth.inc.php');
require_once( confGet('DIR_STREBER') . 'db/db_item.inc.php');
require_once( confGet('DIR_STREBER') . 'std/class_pagehandler.inc.php');

### trigger db request to validate the Database is talking to us ###
$dbh = new DB_Mysql;
if(!is_null(confGet('SQL_MODE'))) {
    $dbh->prepare('SET sql_mode = "'. confGet('SQL_MODE') .'"')->execute();
}
if ($result = $dbh->prepare('SELECT NOW()')) {
  $result->execute();
}

measure_stop('core_includes');


if(!$requested_page_id = get('go')) {
    require_once( confGet('DIR_STREBER') . "./std/check_version.inc.php");
    validateEnvironment();
}

/**
* authenticate user by cookie / start translation
*/
measure_start('authorize');

if(!$user = $auth->setCurUserByCookie()) {
    $user = $auth->setCurUserAsAnonymous();
}
measure_stop('authorize');



/** set language as early as here to start translation... */
{
    measure_start('language');
    if($user && !Auth::isAnonymousUser()) {
        $auth->storeUserCookie();                               # refresh user-cookie

        if(isset($auth->cur_user->language)
            && $auth->cur_user->language != ""
            && $auth->cur_user->language != "en"
        ) {
            setLang($auth->cur_user->language);
            Person::initFields();
        }
    }
    else {
        setLang(confGet('DEFAULT_LANGUAGE'));
        Person::initFields();
    }
    measure_stop('language');
}

/** include framework */
measure_start('plugins');
require_once( confGet('DIR_STREBER') . "std/constant_names.inc.php");
require_once( confGet('DIR_STREBER') . "render/render_page.inc.php");
require_once( confGet('DIR_STREBER') . "pages/_handles.inc.php");                 # already requires language-support
measure_stop('plugins');

global $PH;
$requested_page_id = get('go');

if(function_exists('postInitCustomize')) {
    postInitCustomize();
}

measure_start('init2');

if($g_tags_removed) {
    new FeedbackWarning( __('For security reasons html tags were removed from passed variables')
    . " " . sprintf(__("Read more about %s."), $PH->getWikiLink('security settings')));
}

/********************************************************************************
* route to pages
********************************************************************************/
### if index.php was called without target, check environment ###


$requested_page= $PH->getRequestedPage();

### pages with http auth ###
if($requested_page->http_auth) {
    if(!$user) {
        if($user= Auth::getUserByHttpAuth()) {
            $PH->show($requested_page->id);
            exit();
        }
        else {
           echo __('Sorry. Authentication failed');
           exit();
        }
    }
}

### valid user or anonymous user ###
if($user) {

    ### if no target-page show home ###
    if(!$requested_page_id) {


        ### if user has only one project go there ###
        $projects = $auth->cur_user->getProjects();
        if(count($projects) == 1) {
            setWelcomeToProjectMessage($projects[0]);
            $PH->show('projView',['prj'=>$projects[0]->id]);
        }
        else {
            new FeedbackMessage(sprintf( __("Welcome to %s", "Notice after login"), confGet('APP_NAME')));
            $PH->show('home',[]);
        }
        exit();
    }

    $PH->show($requested_page_id);
    exit();
}

### anonymous pages like Login or License ###
else if($requested_page_id && $requested_page && $requested_page->valid_for_anonymous) {
    $PH->show($requested_page_id);
    exit();
}

### identified by tuid (email notification, etc.)
else if(get('tuid') && $requested_page && $requested_page->valid_for_tuid) {
    if($auth->setCurUserByIdentifier(get('tuid'))) {
        log_message('...valid identifier-string(' . get('tuid') . ')', LOG_MESSAGE_DEBUG);

        ### set language ###
        if(isset($auth->cur_user->language)
            && $auth->cur_user->language != ""
            && $auth->cur_user->language != "en"
        ) {
            setLang($auth->cur_user->language);
        }

        ### store coookie ###
        $auth->storeUserCookie();

        ### render target page ###
        $PH->show($requested_page_id);
        exit();
    }
    else {
        new FeedbackWarning(__("Sorry, but this activation code is no longer valid. Please use the <b>forgot password link</b> below."));
        log_message('...invalid identifier-string(' . get('tuid') . ')', LOG_MESSAGE_DEBUG);
    }
}

### all other request lead to login-form ###
$PH->show('loginForm');
exit();




?>

<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit();
}
# streber - a php5 based project management system  (c) 2005-2007  / www.streber-pm.org
# Distributed under the terms and conditions of the GPL as stated in lang/license.html

/**\file
 * person object
 *
 * @author         Thomas Mann
 */

/**
* cache some db-elements
*
* those assoc. arrays hold references to objects from database
*  like       $id => object
*
*/
global $g_cache_people;
$g_cache_people = [];

/**
* People
*/
class Person extends DbProjectItem
{
    public $name;
    public $project;

    /**
    * constructor
    */
    public function __construct($id_or_array)
    {
        global $g_person_fields;
        $this->fields = &$g_person_fields;

        parent::__construct($id_or_array);
        if (!$this->type) {
            $this->type = ITEM_PERSON;
        }
    }

    /**
    * build translated fields for person class
    *
    * NOTE: This is called twice, because it might be translated AFTER a
    *       the current user has been created.
    */
    public static function initFields()
    {
        global $g_person_fields;
        $g_person_fields = [];
        addProjectItemFields($g_person_fields);

        foreach ([
            new FieldInternal(['name' => 'id',
                'default' => 0,
                'in_db_object' => 1,
                'in_db_item' => 1,
                'log_changes' => false,
            ]),
            new FieldInternal(['name' => 'state',    ### cached in project-table to speed up queries ###
                'default' => 1,
                'in_db_object' => 1,
                'in_db_item' => 1,
            ]),
            new FieldString(['name' => 'name',
                'title' => __('Full name'),
                'tooltip' => __('Required. Full name like (e.g. Thomas Mann)'),
                'required' => true,
            ]),
            new FieldString(['name' => 'nickname',
                'title' => __('Nickname'),
                'tooltip' => __('only required if user can login (e.g. pixtur)'),
            ]),
            new FieldString(['name' => 'tagline',
                'title' => __('Tagline'),
                'tooltip' => __('Optional: Additional tagline (eg. multimedia concepts)'),
            ]),
            new FieldString(['name' => 'mobile_phone',
                'title' => __('Mobile Phone'),
                'tooltip' => __('Optional: Mobile phone (eg. +49-172-12345678)'),
            ]),

            ### office stuff ###
            new FieldString(['name' => 'office_phone',
                'title' => __('Office Phone'),
                'tooltip' => __('Optional: Office Phone (eg. +49-30-12345678)'),
            ]),
            new FieldString(['name' => 'office_fax',
                'title' => __('Office Fax'),
                'tooltip' => __('Optional: Office Fax (eg. +49-30-12345678)'),
            ]),
            new FieldString(['name' => 'office_street',
                'title' => __('Office Street'),
                'tooltip' => __('Optional: Official Street and Number (eg. Poststreet 28)'),
            ]),
            new FieldString(['name' => 'office_zipcode',
                'title' => __('Office Zipcode'),
                'tooltip' => __('Optional: Official Zip-Code and City (eg. 12345 Berlin)'),
            ]),
            new FieldString(['name' => 'office_homepage',
                'title' => __('Office Page'),
                'tooltip' => __('Optional: (eg. www.pixtur.de)'),
            ]),
            new FieldString(['name' => 'office_email',
                'title' => __('Office E-Mail'),
                'tooltip' => __('Optional: (eg. thomas@pixtur.de)'),
            ]),

            ### personal stuff ###
            new FieldString(['name' => 'personal_phone',
                'title' => __('Personal Phone'),
                'tooltip' => __('Optional: Private Phone (eg. +49-30-12345678)'),
            ]),
            new FieldString(['name' => 'personal_fax',
                'title' => __('Personal Fax'),
                'tooltip' => __('Optional: Private Fax (eg. +49-30-12345678)'),
            ]),
            new FieldString(['name' => 'personal_street',
                'title' => __('Personal Street'),
                'tooltip' => __('Optional:  Private (eg. Poststreet 28)'),
            ]),
            new FieldString(['name' => 'personal_zipcode',
                'title' => __('Personal Zipcode'),
                'tooltip' => __('Optional: Private (eg. 12345 Berlin)'),
            ]),
            new FieldString(['name' => 'personal_homepage',
                'title' => __('Personal Page'),
                'tooltip' => __('Optional: (eg. www.pixtur.de)'),
            ]),
            new FieldString(['name' => 'personal_email',
                'title' => __('Personal E-Mail'),
                'tooltip' => __('Optional: (eg. thomas@pixtur.de)'),
            ]),
            new FieldDate(['name' => 'birthdate',
                'title' => __('Birthdate'),
                'tooltip' => __('Optional'),
            ]),

            new FieldString(['name' => 'color',
                'title' => __('Color'),
                'tooltip' => __('Optional: Color for graphical overviews (e.g. #FFFF00)'),
                'view_in_forms' => false,
            ]),

            new FieldText(['name' => 'description',
                'title' => __('Comments'),
                'tooltip' => 'Optional',
            ]),
            new FieldPassword(['name' => 'password',
                'view_in_forms' => false,
                'title' => __('Password'),
                'tooltip' => __('Only required if user can login', 'tooltip'),
                'log_changes' => false,
            ]),

            /**
            * reservated
            */
            new FieldInternal(['name' => 'security_question',
                'view_in_forms' => false,
                'export' => false,
            ]),

            new FieldInternal(['name' => 'security_answer',
                'view_in_forms' => false,
                'export' => false,
            ]),

            /**
            * used for...
            * - initializing project-member-roles
            * - custimizing the interface (like hiding advance options to clients)
            */
            new FieldInternal(['name' => 'profile',
                'title' => __('Profile'),
                'view_in_forms' => false,
                'default' => 3,
                'log_changes' => true,
            ]),

            /**
            * theme
            */
            new FieldInternal([
                'name' => 'theme',
                'title' => __('Theme', 'Formlabel'),
                'view_in_forms' => false,
                'default' => confGet('THEME_DEFAULT'),
                'log_changes' => true,
                'export' => false,
            ]),

            /**
            * language
            */
            new FieldInternal([
                'name' => 'language',
                'view_in_forms' => false,
                'default' => confGet('DEFAULT_LANGUAGE'),
                'log_changes' => true,
            ]),

            /**
            * at home show assigned only, unassigned, all open
            *
            * OBSOLETE
            */
            new FieldInternal([
                'name' => 'show_tasks_at_home',
                'view_in_forms' => false,
                'default' => confGet('SHOW_TASKS_AT_HOME_DEFAULT'),
            ]),

            /**
            * all items modified after this date will be highlighted if changed
            */
            new FieldDatetime(['name' => 'date_highlight_changes',
                'view_in_forms' => false,
                'log_changes' => false,
                'default' => FINIT_NOW,
            ]),

            /**
            * flag if person has an account
            */
            new FieldInternal(['name' => 'can_login',
                'view_in_forms' => false,
                'log_changes' => true,
            ]),

            new FieldDatetime(['name' => 'last_login',
                'view_in_forms' => false,
                'log_changes' => false,
                'default' => FINIT_NEVER,
            ]),

            /**
            * used for highlighting modified items
            */
            new FieldDatetime(['name' => 'last_logout',
                'view_in_forms' => false,
                'log_changes' => false,
                'default' => FINIT_NOW,
            ]),

            /**
            * bit-field of user-rights. See "std/auth.inc.php"
            */
            new FieldInternal(['name' => 'user_rights',
                'tooltip' => 'Optional',
                'log_changes' => true,
                'export' => false,
            ]),

            /**
            * md5 random-identifier for validating login
            */
            new FieldInternal(['name' => 'cookie_string',
                'log_changes' => false,
                'export' => false,
            ]),

            /**
            * ip-address of last valid login
            * - is checked if 'CHECK_IP_ADDRESS' is true
            */
            new FieldInternal(['name' => 'ip_address',
                'log_changes' => false,
                'export' => false,
            ]),

            /**
            * random-identifier for securitry
            *
            * - initialized on creation
            * - used for identifaction without password (like change password notification emails)
            */
            new FieldInternal(['name' => 'identifier',
                'default' => FINIT_RAND_MD5,
                'log_changes' => false,
                'export' => false,
            ]),

            /**
            * bit-field of misc settings
            */
            new FieldInternal(['name' => 'settings',
                'default' => (
                    confGet('PERSON_DEFAULT_SETTINGS')
                ),
                'log_changes' => false,
                'export' => false,
            ]),

            new FieldInternal(['name' => 'notification_last',
                'default' => FINIT_NEVER,
                'log_changes' => false,
                'export' => false,
            ]),

            /**
            * notification are off by default
            */
            new FieldInternal(['name' => 'notification_period',
                'default' => 0,
                'log_changes' => false,
            ]),

            /**
            * time zone
            * - client's time zone setting.
            * - TIME_OFFSET_AUTO will use javascript to detect client's time zone
            */
            new FieldInternal(['name' => 'time_zone',
                'default' => TIME_OFFSET_AUTO,
                'export' => false,
            ]),

            /**
            * time offset in seconds
            */
            new FieldInternal(['name' => 'time_offset',
                'default' => 0,
                'export' => false,
            ]),

            /**
            * reservated for non-project public-level (is not implemented / used)
            */
            new FieldInternal(['name' => 'user_level_create',
                'log_changes' => false,
                'export' => false,
            ]),
            new FieldInternal(['name' => 'user_level_view',
                'log_changes' => false,
                'export' => false,
            ]),
            new FieldInternal(['name' => 'user_level_edit',
                'log_changes' => false,
                'export' => false,
            ]),
            new FieldInternal(['name' => 'user_level_reduce',
                'log_changes' => false,
                'export' => false,
            ]),

            /* person category */
            new FieldInternal(['name' => 'category',
                'view_in_forms' => false,
                'default' => 0,
                'log_changes' => true,
            ]),
            new FieldString(['name' => 'salary_per_hour',
                'title' => __('Salary per hour') . ' ' . __('in Euro'),
                'default' => 0.0,
                'export' => false,
            ]),
            new FieldInternal(['name' => 'ldap',
                'view_in_forms' => false,
                'log_changes' => false,
                'default' => 0,
                'export' => false,
            ]),
        ] as $f) {
            $g_person_fields[$f->name] = $f;
        }
    }

    /**
    * query from db
    *
    * - returns NULL if failed
    */
    public static function getById($id, $use_cache = true)
    {
        $id = intval($id);
        global $g_cache_people;
        if ($use_cache && isset($g_cache_people[$id])) {
            $p = $g_cache_people[$id];
        } else {
            $p = new Person($id);
            $g_cache_people[$p->id] = $p;
        }
        if (!$p->id) {
            return null;
        }
        return $p;
    }

    /**
    * query if visible for current user
    *
    * - returns NULL if failed
    */
    public static function getVisibleById($id, $use_cache = true)
    {
        if (!is_int($id) && !is_string($id)) {
            trigger_error('Person::getVisibleById() requires int-paramter', E_USER_WARNING);
            return null;
        }

        $id = intval($id);
        global $g_cache_people;
        if ($use_cache && isset($g_cache_people[$id])) {
            $p = $g_cache_people[$id];
            return $p;
        } else {
            $p = null;
            $people = Person::getPeople([
                'id' => $id,
            ]);
            if (count($people) == 1) {
                if ($people[0]->id) {
                    $p = $people[0];
                    $g_cache_people[$p->id] = $p;
                    return $p;
                }
            }
        }

        return null;
    }

    /**
    * query if editable for current user
    */
    public static function getEditableById($id, $use_cache = false)
    {
        if (!is_int($id) && !is_string($id)) {
            trigger_error('Person::getVisibleById() requires int-paramter', E_USER_WARNING);
            return null;
        }
        $id = intval($id);
        global $auth;
        if (
            (
                $auth->cur_user->id == $id
             &&
             $auth->cur_user->user_rights & RIGHT_PERSON_EDIT_SELF
            )
            ||
            $auth->cur_user->user_rights & RIGHT_PERSON_EDIT
        ) {
            $people = Person::getPeople([
                'id' => $id,
            ]);
            if (count($people) == 1) {
                if ($people[0]->id) {
                    return $people[0];
                }
            }
        }
        return null;
    }

    public function isEditable()
    {
        if (Person::getEditableById($this->id)) {
            return true;
        }
    }

    public function getLink()
    {
        global $PH;
        if ($this->nickname) {
            $out = '<span title="' . asHtml($this->name) . '" class="item person">' . $PH->getLink('personView', $this->nickname, ['person' => $this->id]) . '</span>';
        } else {
            $out = '<span  title="' . asHtml($this->name) . '" class="item person">' . $PH->getLink('personView', $this->getShort(), ['person' => $this->id]) . '</span>';
        }
        return $out;
    }

    /**
    * get Objects from db-query
    */
    public static function queryFromDb($query_string)
    {
        $dbh = new DB_Mysql();

        $sth = $dbh->prepare($query_string);

        $sth->execute('', 1);
        $tmp = $sth->fetchall_assoc();
        $people = [];
        foreach ($tmp as $t) {
            $person = new Person($t);
            $people[] = $person;
        }
        return $people;
    }

    /**
    * getAll
    *
    * - use "has_id" to query one person if visible
    */
    #$order_by=NULL, $accounts_only=false, $has_id=NULL, $search=NULL)
    public static function getPeople($args = null)
    {
        global $auth;
        $prefix = confGet('DB_TABLE_PREFIX');

        ### default params ###
        $order_by = 'name';
        $visible_only = 'auto';     #
        $can_login = null;
        $id = null;
        $search = null;
        $identifier = null;
        $cookie_string = null;
        $project = null;     # all user projects
        $is_alive = true;
        #$perscat            = NULL;
        $pcategory_min = PCATEGORY_UNDEFINED;
        $pcategory_max = PCATEGORY_PARTNER;

        ### filter params ###
        if ($args) {
            foreach ($args as $key => $value) {
                if (!isset($$key) && !is_null($$key) && !$$key === '') {
                    trigger_error('unknown parameter', E_USER_NOTICE);
                } else {
                    $$key = $value;
                }
            }
        }

        if (!is_null($can_login)) {
            if ($can_login == '0') {
                $str_can_login = 'AND pers.can_login = 0';
            } else {
                $str_can_login = 'AND pers.can_login = 1';
            }
        } else {
            $str_can_login = '';
        }

        if (!is_null($project)) {
            $str_project = 'p.id';
        } else {
            $str_project = intval($project);
        }

        $str_id = $id
         ? 'AND pers.id=' . intval($id)
         : '';

        $AND_match = $search
        ? "AND MATCH (pers.name,pers.nickname,pers.tagline,pers.description) AGAINST ('" . asMatchString($search) . "*' IN BOOLEAN MODE)"
        : '';

        if ($visible_only == 'auto') {
            $visible_only = (($auth->cur_user->user_rights & RIGHT_PERSON_VIEWALL)
                            ||
                            ($auth->cur_user->user_rights & RIGHT_VIEWALL))
                         ? false
                         : true;
        }
        if (is_null($project)) {
            $str_project = 'p.id';
        } else {
            $str_project = intval($project);
            $visible_only = true;                   # project filtering only works in this mode
        }

        if (is_null($is_alive)) {                            # ignore
            $str_alive = '';
        } else {
            $str_alive = $is_alive
                ? 'AND pers.state=1'
                : 'AND pers.state=-1';
        }

        if (!is_null($pcategory_min) && !is_null($pcategory_max)) {
            $str_pcategory = 'AND (pers.category BETWEEN ' . $pcategory_min . ' AND ' . $pcategory_max . ')';
        } else {
            $str_pcategory = '';
        }

        ### all people ###
        if (!$visible_only) {
            $str =
                "SELECT i.*, pers.* from {$prefix}person pers, {$prefix}item i
                WHERE 1
                    $str_alive
                    $str_id
                    $str_can_login
                    $str_pcategory
                    AND i.id = pers.id
                    $AND_match


               " . getOrderByString($order_by);
        }

        ### only related people ###
        else {
            $str =
                "SELECT DISTINCT pers.*, ipers.* from {$prefix}person pers, {$prefix}project p, {$prefix}projectperson upp, {$prefix}projectperson pp, {$prefix}item ipp, {$prefix}item ipers
                WHERE
                        upp.person = {$auth->cur_user->id}
                    AND upp.state = 1           /* upp all user projectpeople */
                    AND upp.project = $str_project        /* all user projects */

                    AND p.state = 1
                    AND p.status > 0              /* ignore templates */
                    AND p.id = pp.project         /* all projectpeople in user's project*/

                    AND pp.state = 1
                    AND pp.id = ipp.id

                    AND ( ipp.pub_level >= upp.level_view
                          OR
                          ipp.created_by = {$auth->cur_user->id}
                    )
                    AND  pp.person = pers.id      /* all belonging person*/
                    $str_alive
                    $str_id
                    $str_can_login
                    $str_pcategory
                    $AND_match
                    AND pers.id = ipers.id

               " . getOrderByString($order_by);
        }

        $people = self::queryFromDb($str);                 # store in variable to pass by reference

        /**
        * be sure that the current user is listed
        * NOTE:
        * - constructing a query that insures the visibility of the current user
        *   is very complex because this does not depend on existing projects
        */
        if (!$search
            &&
            $auth && $auth->cur_user && $auth->cur_user->id
            &&
            (!$id || $id == $auth->cur_user->id)
            &&
            $is_alive !== false
        ) {
            $flag_user_found = false;
            foreach ($people as $p) {
                if ($p->id == $auth->cur_user->id) {
                    $flag_user_found = true;
                    break;
                }
            }
            if (!$flag_user_found) {
                $people[] = $auth->cur_user;
            }
        }

        return $people;
    }

    #------------------------------------------------------------
    # get person by nickname
    #------------------------------------------------------------
    public static function getByNickname($nickname)
    {
        $prefix = confGet('DB_TABLE_PREFIX');
        $tmp = self::queryFromDb("SELECT * FROM {$prefix}person WHERE nickname='" . asCleanString($nickname) . "'");
        if (!$tmp || count($tmp) != 1) {
            return false;
        }
        return $tmp[0];
    }

    #------------------------------------------------------------
    # get person by cookie_string (md5)
    #------------------------------------------------------------
    public static function getByCookieString($f_cookie_string)
    {
        $prefix = confGet('DB_TABLE_PREFIX');

        $tmp = self::queryFromDb("SELECT * FROM {$prefix}person WHERE cookie_string='" . asAlphaNumeric($f_cookie_string) . "'");
        if (!$tmp || count($tmp) != 1) {
            return false;
        }
        return $tmp[0];
    }

    #------------------------------------------------------------
    # get person by identifer_string (md5)
    #------------------------------------------------------------
    public static function getByIdentifierString($f_identifier_string)
    {
        $prefix = confGet('DB_TABLE_PREFIX');

        $tmp = self::queryFromDb("SELECT * FROM {$prefix}person WHERE identifier='" . asAlphaNumeric($f_identifier_string) . "'");
        if (!$tmp || count($tmp) != 1) {
            return false;
        }
        return $tmp[0];
    }

    #---------------------------
    # get Employments
    #---------------------------
    public function getEmployments()
    {
        $prefix = confGet('DB_TABLE_PREFIX');
        require_once(confGet('DIR_STREBER') . 'db/class_employment.inc.php');
        $dbh = new DB_Mysql();
        $sth = $dbh->prepare("
            SELECT * FROM {$prefix}employment em, {$prefix}item i
            WHERE   i.type = " . ITEM_EMPLOYMENT . "
            AND     i.state = 1
            AND     i.id = em.id
            AND     em.person = \"$this->id\"
            ");
        $sth->execute('', 1);
        $tmp = $sth->fetchall_assoc();
        $es = [];
        foreach ($tmp as $t) {
            $es[] = new Employment($t);
        }
        return $es;
    }

    /**
    * get project-people
    */
    public function getProjectPeople($args = null)
    {
        $prefix = confGet('DB_TABLE_PREFIX');
        global $auth;

        ### default parameter ###
        $order_by = null;
        $alive_only = true;
        $visible_only = ($auth->cur_user->user_rights & RIGHT_VIEWALL)
                        ? false
                        : true;

        ### filter parameter ###
        if ($args) {
            foreach ($args as $key => $value) {
                if (!isset($$key) && !is_null($$key) && !$$key === '') {
                    trigger_error('unknown parameter', E_USER_NOTICE);
                } else {
                    $$key = $value;
                }
            }
        }

        $AND_state = $alive_only
                    ? 'AND i.state = 1'
                    : '';

        require_once(confGet('DIR_STREBER') . 'db/class_projectperson.inc.php');
        $dbh = new DB_Mysql();

        ### ignore rights ###
        if (!$visible_only || $auth->cur_user->user_rights & RIGHT_PROJECT_ASSIGN) {
            $sth = $dbh->prepare(
                "SELECT i.*, pp.* from {$prefix}item i, {$prefix}projectperson pp
                WHERE

                        i.type = '" . ITEM_PROJECTPERSON . "'
                    $AND_state
                    AND i.project = pp.project

                    AND pp.person = $this->id
                    AND pp.id = i.id

                " . getOrderByString($order_by, 'name desc')
            );
        } else {
            $sth = $dbh->prepare(
                "SELECT i.*, pp.* from {$prefix}item i, {$prefix}projectperson pp, {$prefix}projectperson upp
                WHERE
                        upp.person = {$auth->cur_user->id}
                    AND upp.state = 1

                    AND i.type = '" . ITEM_PROJECTPERSON . "'
                    $AND_state
                    AND i.project = upp.project

                    AND (
                        i.pub_level >= upp.level_view
                        OR
                        i.created_by= {$auth->cur_user->id}
                    )
                    AND pp.id = i.id
                    AND pp.person = $this->id

                " . getOrderByString($order_by, 'name desc')
            );
        }
        $sth->execute('', 1);
        $tmp = $sth->fetchall_assoc();
        $ppeople = [];
        foreach ($tmp as $n) {
            $pperson = new ProjectPerson($n);
            $ppeople[] = $pperson;
        }
        return $ppeople;
    }

    /**
    * get Projects
    *
    * @@@ this should be refactured into Project::getProject()
    */
    public function getProjects($f_order_by = null, $f_status_min = STATUS_UPCOMING, $f_status_max = STATUS_COMPLETED)
    {
        global $auth;
        $prefix = confGet('DB_TABLE_PREFIX');
        $status_min = intval($f_status_min);
        $status_max = intval($f_status_max);

        ### all projects ###
        if ($auth->cur_user->user_rights & RIGHT_VIEWALL) {
            $str =
                "SELECT p.* from {$prefix}project p, {$prefix}projectperson pp
                WHERE
                       p.status <= $status_max
                   AND p.status >= $status_min
                   AND p.state = 1

                   AND pp.person = $this->id
                   AND pp.project = p.id
                   AND pp.state=1
                " . getOrderByString($f_order_by, 'prio, name');
        }

        ### only assigned projects ###
        else {
            $str =
                "SELECT p.* from {$prefix}project p, {$prefix}projectperson upp , {$prefix}projectperson pp
                WHERE
                        upp.person = {$auth->cur_user->id}
                    AND upp.state = 1
                    AND upp.project = pp.project

                   AND pp.person = $this->id
                   AND pp.project = p.id
                   AND pp.state=1

                    AND p.id = upp.project
                    AND   p.status <= $status_max
                    AND   p.status >= $status_min
                    AND   p.state = 1

                " . getOrderByString($f_order_by, 'prio, name');
        }

        $dbh = new DB_Mysql();
        $sth = $dbh->prepare($str);
        $sth->execute('', 1);
        $tmp = $sth->fetchall_assoc();

        $projects = [];
        foreach ($tmp as $n_array) {
            require_once(confGet('DIR_STREBER') . 'db/class_project.inc.php');
            if ($n_array['id']) {
                if ($proj = Project::getById($n_array['id'])) {
                    $projects[] = $proj;
                }
            }
        }
        return $projects;
    }

    /**
    *  get user efforts
    *
    * @@@ does NOT check for admin-rights to view all efforts
    */
    public function getEfforts($f_order_by = null)
    {
        /*
        global $auth;
        $prefix= confGet('DB_TABLE_PREFIX');
        require_once(confGet('DIR_STREBER') . 'db/class_effort.inc.php');

        $dbh = new DB_Mysql;
        $sth= $dbh->prepare(
                "SELECT i.*, e.*  from {$prefix}item i, {$prefix}effort e, {$prefix}project p, {$prefix}projectperson upp
                WHERE
                        upp.person = {$auth->cur_user->id}
                    AND upp.state = 1

                    AND i.type = '".ITEM_EFFORT."'
                    AND i.state = 1
                    AND i.project = upp.project
                    AND i.created_by = $this->id
                    AND (
                        i.pub_level >= upp.level_view
                        OR
                        i.created_by= {$auth->cur_user->id}
                    )

                    AND e.id= i.id
                    AND p.id= i.project
                ". getOrderByString($f_order_by, 'time_end DESC')
        );
        $sth->execute("",1);
        $tmp=$sth->fetchall_assoc();
        $efforts=array();
        foreach($tmp as $t) {
            $efforts[]=new Effort($t);
        }*/
        require_once(confGet('DIR_STREBER') . 'db/class_effort.inc.php');
        $efforts = Effort::getAll([
            'person' => $this->id,
        ]);

        return $efforts;
    }

    /**
    * get the task-assignments for a person
    * - this is a very basic function with validation of visbibility-rights
    * - used for notification
    */
    public function getTaskAssignments()
    {
        $dbh = new DB_Mysql();
        $prefix = confGet('DB_TABLE_PREFIX');

        $sth = $dbh->prepare(
            "
        SELECT  itp.*, tp.* from {$prefix}taskperson tp, {$prefix}item itp
        WHERE
                   tp.person = {$this->id}
               AND tp.id = itp.id
                       AND itp.state = 1
        "
        );

        $sth->execute('', 1);
        $tmp = $sth->fetchall_assoc();
        $taskpeople = [];
        require_once(confGet('DIR_STREBER') . 'db/class_taskperson.inc.php');

        foreach ($tmp as $tp) {
            $taskpeople[] = new TaskPerson($tp);
        }
        return $taskpeople;
    }

    public function getTaskAssignment($task_id = null)
    {
        $dbh = new DB_Mysql();
        $prefix = confGet('DB_TABLE_PREFIX');
        $task_id = intval($task_id);
        $sth = $dbh->prepare(
            "
        SELECT  itp.*, tp.* from {$prefix}taskperson tp, {$prefix}item itp
        WHERE tp.person = {$this->id}
        AND tp.task = {$task_id}
        AND tp.id = itp.id
        AND itp.state = 1
        "
        );

        $sth->execute('', 1);
        $tmp = $sth->fetch_row();
        $taskperson = 0;
        require_once(confGet('DIR_STREBER') . 'db/class_taskperson.inc.php');

        //foreach($tmp as $tp) {
        if ($tmp) {
            $taskperson = new TaskPerson($tmp[0]);
        }
        return $taskperson;
    }

    #---------------------------
    # get Companies
    #---------------------------
    public function getCompanies()
    {
        require_once(confGet('DIR_STREBER') . 'db/class_company.inc.php');
        $emps = $this->getEmployments();
        $cs = [];
        foreach ($emps as $e) {
            if ($e->company) {
                $c = Company::getById($e->company);
                if ($c) {
                    $cs[] = $c;
                }
            }
        }
        return $cs;
    }

    public function getCompanyLinks($show_max_number = 3)
    {
        $cs = $this->getCompanies();
        $buffer = '';
        $sep = ', ';
        $num = 0;
        $count = count($cs);
        $counter = 1;
        foreach ($cs as $c) {
            if ($counter == $count) {
                $sep = ' ';
            }

            $buffer .= $c->getLink() . $sep;

            if (++$num > $show_max_number) {
                break;
            }

            $counter++;
        }
        return $buffer;
    }

    /**
    * returns NULL if not set
    */
    public function getValidEmailAddress()
    {
        if ($this->office_email) {
            return $this->office_email;
        } elseif ($this->personal_email) {
            return $this->personal_email;
        }
    }

    /**
    * note, if we want to keep the user logged in between sessions (CHECK_IP_ADDRESS == false)
    * this function must only be used for building new Cookie-strings when explicitly logging out.
    */
    public function calcCookieString()
    {
        if (!function_exists('md5')) {
            trigger_error('md5() is not available.', E_USER_ERROR);
            return null;
        }
        return  md5($this->name . $this->password . md5(time() . microtime() . rand(12312, 123213) . rand(234423, 123213)));
    }

    /**
    * The identifier-string is used as token for notification-mails and password-remind mails
    * It is created out of the user-id and password. It is only recomputed, after password or
    * nickname of person has been changed.
    */
    public function calcIdentifierString()
    {
        if (!function_exists('md5')) {
            trigger_error('md5() is not available.', E_USER_ERROR);
        }
        return  md5($this->name . $this->nickname . $this->password);
    }
}

Person::initFields();

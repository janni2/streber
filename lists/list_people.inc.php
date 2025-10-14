<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit();
}
# streber - a php based project management system
# Copyright (c) 2005 Thomas Mann - thomas@pixtur.de
# Distributed under the terms and conditions of the GPL as stated in docs/license.txt

/**
 * derived ListBlock-class for listing people
 *
 * @includedby:     pages/company.inc, pages/person.inc, pages/proj.inc
 *
 * @author         Thomas Mann
 * @uses:           ListBlock
 * @usedby:
 *
 */
class ListBlock_people extends ListBlock
{
    public $filters = [];

    public function __construct($args = null)
    {
        parent::__construct($args);

        $this->id = 'people';
        $this->title = __('Your related people');

        $this->add_col(new ListBlockColSelect());
        $this->add_col(new ListBlockCol_PersonNickname());

        #$this->add_col( new ListBlockColFormat(array(
        #   'key'=>'nickname',
        #   'name'=>__("Name Short"),
        #   'tooltip'=>__("Shortnames used in other lists"),
        #   'sort'=>0,
        #   'format'=>'<nobr><a href="index.php?go=personView&amp;person={?id}">{?nickname}</a></nobr>'
        #)));

        $this->add_col(new ListBlockCol_PersonName());

        #$this->add_col( new ListBlockColFormat(array(
        #   'key'=>'name',
        #   'name'=>__("Person"),
        #   'tooltip'=>__("Task name. More Details as tooltips"),
        #   'sort'=>0,
        #   'format'=>'<nobr><span class="item person"><a class="item person" href="index.php?go=personView&amp;person={?id}">{?name}</a></span></nobr>'
        #)));

        $this->add_col(new ListBlockCol_PersonProfile());
        $this->add_col(new ListBlockColFormat([
            'key' => 'personal_phone',
            'name' => __('Private'),
            'format' => '<nobr>{?personal_phone}</nobr>',
        ]));
        $this->add_col(new ListBlockColFormat([
            'key' => 'mobile_phone',
            'name' => __('Mobil'),
            'format' => '<nobr>{?mobile_phone}</nobr>',
        ]));
        $this->add_col(new ListBlockColFormat([
            'key' => 'office_phone',
            'name' => __('Office'),
            'format' => '<nobr>{?office_phone}</nobr>',
        ]));
        $this->add_col(new ListBlockColFormat([
            'key' => 'tagline',
            'name' => __('Tagline'),
            'format' => '{?tagline}',
        ]));
        $this->add_col(new ListBlockColMethod([
            'name' => __('Companies'),
            'func' => 'getCompanyLinks',
            'id' => 'companies',
        ]));
        $this->add_col(new ListBlockCol_PersonProjects());
        $this->add_col(new ListBlockColTimeAgo([
            'name' => __('last login'),
            'key' => 'last_login',
        ]));
        $this->add_col(new ListBlockCol_PersonChanges());

        /*$this->add_col( new ListBlockCol_ProjectEffortSum);

        $this->add_col( new ListBlockColMethod(array(
            'name'=>"Tasks",
            'tooltip'=>"Number of open Tasks",
            'sort'=>0,
            'func'=>'getNumTasks',
            'style'=>'right'
        )));
        $this->add_col( new ListBlockColDate(array(
            'key'=>'date_start',
            'name'=>"Opened",
            'tooltip'=>"Day the Project opened",
            'sort'=>0,
        )));
        $this->add_col( new ListBlockColDate(array(
            'key'=>'date_closed',
            'name'=>"Closed",
            'tooltip'=>"Day the Project state changed to closed",
            'sort'=>0,
        )));
        */

        #---- functions ----
        global $PH;
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('personEdit')->id,
            'name' => __('Edit person'),
            'id' => 'personEdit',
            'icon' => 'edit',
            'context_menu' => 'submit',
        ]));
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('personEditRights')->id,
            'name' => __('Edit user rights'),
            'id' => 'personEditRights',
            'context_menu' => 'submit',
        ]));
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('personDelete')->id,
            'name' => __('Delete person'),
            'id' => 'personDelete',
            'icon' => 'delete',
        ]));
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('personNew')->id,
            'name' => __('Create new person'),
            'id' => 'personNew',
            'icon' => 'new',
            'context_menu' => 'submit',
        ]));
        $this->add_function(new ListFunction([
            'target' => $PH->getPage('itemsAsBookmark')->id,
            'name' => __('Mark as bookmark'),
            'id' => 'itemsAsBookmark',
            'context_menu' => 'submit',
        ]));
    }

    /**
    *   TB 2006-07-03 tsk: 1220
    *   print a complete list as html
    * - use filters
    * - use check list style (tree, list, grouped)
    * - check customization-values
    * - check sorting
    * - get objects from database
    *
    */
    public function print_automatic()
    {
        global $PH;

        #if(!$this->active_block_function=$this->getBlockStyleFromCookie()) {
        $this->active_block_function = 'list';
        #}

        $this->group_by = get("blockstyle_{$PH->cur_page->id}_{$this->id}_grouping");

        $s_cookie = "sort_{$PH->cur_page->id}_{$this->id}_{$this->active_block_function}";
        if ($sort = get($s_cookie)) {
            $this->query_options['order_by'] = $sort;
        }

        ### grouped view ###
        if ($this->active_block_function == 'grouped') {
            /**
            * @@@ later use only once...
            *
            *   $this->columns= filterOptions($this->columns,"CURPAGE.BLOCKS[{$this->id}].STYLE[{$this->active_block_function}].COLUMNS");
            */
            if (isset($this->columns[$this->group_by])) {
                unset($this->columns[$this->group_by]);
            }

            ### prepend key to sorting ###
            if (isset($this->query_options['order_by'])) {
                $this->query_options['order_by'] = $this->groupings->getActiveFromCookie() . ',' . $this->query_options['order_by'];
            } else {
                $this->query_options['order_by'] = $this->groupings->getActiveFromCookie();
            }
        }
        ### list view ###
        else {
            $pass = true;
        }

        ### add filter options ###
        foreach ($this->filters as $f) {
            foreach ($f->getQuerryAttributes() as $k => $v) {
                $this->query_options[$k] = $v;
            }
        }

        $people = Person::getPeople($this->query_options);
        $this->render_list($people);
    }
}

class ListBlockCol_PersonNickname extends ListBlockCol
{
    public $id = 'nickname';
    public $key = 'nickname';

    public function __construct($args = null)
    {
        parent::__construct($args);
        if (!$this->name) {
            $this->name = __('Nickname', 'column header');
        }
    }

    public function render_tr(&$person, $style = '')
    {
        global $PH;
        echo '<td>' . $PH->getLink('personView', asHtml($person->nickname), ['person' => $person->id]) . '</td>';
    }
}

class ListBlockCol_PersonName extends ListBlockCol
{
    public $id = 'name';
    public $key = 'name';

    public function __construct($args = null)
    {
        parent::__construct($args);
        if (!$this->name) {
            $this->name = __('Name', 'column header');
        }
    }

    public function render_tr(&$person, $style = '')
    {
        global $PH;

        echo '<td><b><nobr>'
            . $PH->getLink('personView', $person->name, ['person' => $person->id])
            . '</nobr></b></td>';
    }
}

/**
* column user-profile
*
*/
class ListBlockCol_PersonProfile extends ListBlockCol
{
    public $id = 'profile';
    public $key = 'profile';

    public function __construct($args = null)
    {
        parent::__construct($args);
        if (!$this->name) {
            $this->name = __('Profile', 'column header');
        }
        $this->tooltip = __('Account settings for user (do not confuse with project rights)');
    }
    public function render_tr(&$person, $style = '')
    {
        global $g_user_profile_names;
        global $g_user_profiles;

        $profile_num = $person->profile;
        $str_profile = $g_user_profile_names[$profile_num];

        #$default_rights= $g_user_profiles[$g_user_profile_names[$profile_num]]['default_user_rights'];
        $default_rights = $g_user_profiles[$profile_num]['default_user_rights'];

        if ($default_rights != $person->user_rights) {
            $str_profile .= __('(adjusted)');
        }

        echo "<td>$str_profile</td>";
    }
}

/**
* column active projects
*
*/
class ListBlockCol_PersonProjects extends ListBlockCol
{
    public $id = 'projects';
    public $width = '70%';

    public function __construct($args = null)
    {
        parent::__construct($args);
        if (!$this->name) {
            $this->name = __('Active Projects', 'column header');
        }
    }

    public function render_tr(&$person, $style = '')
    {
        global $PH;
        global $g_prio_names;

        $projects = $person->getProjects();
        echo '<td>';

        if ($projects) {
            $str_delimiter = '';
            foreach ($projects as $p) {
                $tooltip = '';
                if (isset($g_prio_names[$p->prio])) {
                    $tooltip = 'title="' . sprintf(__('Priority is %s'), $g_prio_names[$p->prio]) . '"';
                }
                $img_prio = "<img $tooltip src=\"" . getThemeFile("img/prio_{$p->prio}.png") . '">';
                $link = $PH->getLink('projView', $p->getShort(), ['prj' => $p->id]);

                echo  $str_delimiter . $img_prio . $link;

                $str_delimiter = ', ';
            }
        }
        echo '</td>';
    }
}

class ListBlockCol_PersonChanges extends ListBlockCol
{
    public $id = 'changes';

    public function __construct($args = null)
    {
        parent::__construct($args);
        if (!$this->name) {
            $this->name = __('recent changes', 'column header');
        }
        $this->tooltip = __('changes since YOUR last logout');
    }

    public function render_tr(&$person, $style = '', $format = 'html')
    {
        global $PH;
        global $auth;
        global $g_prio_names;
        global $csv_args;
        global $csv_count;

        $changes = DbProjectItem::getAll([
            'modified_by' => $person->id,
            'date_min' => $auth->cur_user->last_logout,
            'not_modified_by' => $auth->cur_user->id,
        ]);

        if ($format == 'csv') {
            if (count($changes)) {
                $csv_args[$csv_count++] = count($changes);
            } else {
                $csv_args[$csv_count++] = '';
            }
        } else {
            if (count($changes)) {
                echo '<td>' . count($changes) . '</td>';
            } else {
                echo '<td></td>';
            }
        }
    }
}

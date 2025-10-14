<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit();
}
# streber - a php5 based project management system  (c) 2005-2007  / www.streber-pm.org
# Distributed under the terms and conditions of the GPL as stated in lang/license.html

/**\file
* employments / jointable between company and person
*
* linking people to companies is not required with work with projects. It's only
* purpose now, is to display additional information in personView and projView.
*
*
* @includedby:     *
*
* @author         Thomas Mann
* @uses:           DbProjectList
* @usedby:
*
*/

class Employment extends DbProjectItem
{
    public $name;
    public $project;

    /**
    * constructor
    */
    public function __construct($id_or_array = null)
    {
        global $g_employment_fields;
        $this->fields = &$g_employment_fields;

        parent::__construct($id_or_array);
        if (!$this->type) {
            $this->type = ITEM_EMPLOYMENT;
        }
    }

    public static function initFields()
    {
        global $g_employment_fields;
        $g_employment_fields = [];

        addProjectItemFields($g_employment_fields);

        foreach ([
            new FieldInternal(['name' => 'id',
                'default' => 0,
                'in_db_object' => 1,
                'in_db_item' => 1,
            ]),
            new FieldInternal(['name' => 'person',
            ]),
            new FieldInternal(['name' => 'company',
            ]),
            new FieldString(['name' => 'comment',
            ]),
        ] as $f) {
            $g_employment_fields[$f->name] = $f;
        }
    }

    /**
    * query from db
    *
    * - returns NULL if failed
    */
    public static function getById($id)
    {
        $e = new Employment(intval($id));
        if ($e->id) {
            return $e;
        }
        return null;
    }

    /**
    * query if editable for current user
    */
    public static function getEditableById($id)
    {
        global $auth;
        if ($auth->cur_user->user_rights & RIGHT_COMPANY_EDIT) {
            return Employment::getById(intval($id));
        }
        return null;
    }
}

Employment::initFields();

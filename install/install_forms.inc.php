<?php

# streber - a php5 based project management system  (c) 2005-2007  / www.streber-pm.org
# Distributed under the terms and conditions of the GPL as stated in lang/license.html

/**
* rendering of the install-dialog
*
*
*/
global $g_form_fields;
$g_form_fields = [
    'db_type' => [
        'id' => 'db_type',
        'label' => 'Type database',
        'options' => $g_supported_db_types,
        'type' => 'option',
    ], 'hostname' => [
        'id' => 'hostname',
        'default' => 'localhost',
        'label' => ' Hostname (for Database Server)',
        'required' => true,
    ],
    'db_username' => [
        'id' => 'db_username',
        'default' => 'root',
        'label' => 'Username (for Database)',
        'required' => true,
    ],
    'db_password' => [
        'id' => 'db_password',
        'default' => '',
        'label' => 'Password (for Database)',
        'type' => 'password',
    ],
    'db_name' => [
        'id' => 'db_name',
        'default' => 'streber',
        'label' => 'Name of database',
        'required' => true,
    ],
/*    'db_admin_user'=>array(
        'id'        =>'db_admin_user',
        'default'   =>'',
        'label'     =>'Admin Username (to create Database)',
    ),
    'db_admin_password'=>array(
        'id'        =>'db_admin_password',
        'default'   =>'',
        'label'     =>'Admin Password (to create Database)',
        'comment'   =>'not required, if database already exists',
    ),*/
    'db_table_prefix' => [
        'id' => 'db_table_prefix',
        'default' => '',
        'label' => 'SQL Table prefix (e.g. "streb_")',
        'comment' => '',
    ],
    'continue_on_sql_errors' => [
        'id' => 'continue_on_sql_errors',
        'default' => 'on',
        'label' => 'Continue Upgrade on sql errors',
        'comment' => '',
        'type' => 'checkbox',
    ],
    'user_admin_name' => [
        'id' => 'user_admin_name',
        'default' => 'admin',
        'label' => 'Streber administrator name',
        'comment' => '',
        'required' => true,
    ],
    'user_admin_password' => [
        'id' => 'user_admin_password',
        'default' => '',
        'label' => 'Streber administrator password',
        'comment' => '',
        'type' => 'password',
    ],
    'site_name' => [
        'id' => 'site_name',
        'default' => 'Streber',
        'label' => 'Website name',
        'comment' => 'The name of this installation.',
        'required' => true,
    ],
    'site_email' => [
        'id' => 'site_email',
        'defualt' => 'postmaster@' . $_SERVER['HTTP_HOST'],
        'label' => 'Administrator\'s e-mail',
        'comment' => 'E-mail address of site Administrator.',
        'required' => true,
    ],
];

#========================================================================================================

/**
* installation header
*/
function print_InstallationHTMLOpen()
{
    ### Set uft8
    header('Content-type: text/html; charset=utf-8');

    ### Disable page caching ###
    header('Expires: -1');
    header('Cache-Control: post-check=0, pre-check=0');
    header('Pragma: no-cache');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
      <meta content="text/html; charset=utf-8" http-equiv="content-type">
      <meta content="Streber, pm, project management, tool, php, php5, oop, tasks, projects, users, teams, online, web-based, free, open source, gpl"
     name="KEYWORDS">
      <meta content="3 days" name="REVISIT-AFTER">
      <meta content="en" http-equiv="content-language">
      <title>Streber pm - a web based free open source project management tool with php 5 mysql</title>
      <link href="styles.css" rel="stylesheet" type="text/css">
    </head>
    <body>';
}

/**
* installation footer
*/
function print_InstallationHTMLClose()
{
    echo '</html>';
}

/**
* render form with essential information for installation
*
* this form is validated in print_step_done. If some fields are invalid, this
* function is called again.
*/
function print_setup_form()
{
    global $g_form_fields;

    ### create form ###
    {
        echo '<h2>Settings</h2>';
        echo '<form method=GET action="./install.php"><div  class=form>';

        foreach ($g_form_fields as $key => $value) {
            $f = &$g_form_fields[$key];

            if (!$value = get($f['id'])) {
                $value = '';
                if (isset($f['value'])) {
                    $value = $f['value'];
                } elseif (isset($f['default'])) {
                    $value = $f['default'];
                }
            }

            $class_additional = '';
            if (isset($f['required']) && $f['required']) {
                $class_additional .= ' required';
            }
            if (isset($f['error'])) {
                $class_additional .= ' error';
            }
            ### checkboxes ###
            if (isset($f['type']) && ($f['type'] == 'checkbox')) {
                $str_checked = '';
                if (isset($value) && $value) {
                    $str_checked = 'checked';
                }
                echo "<p><label for name='{$f['id']}' class=checkbox><input type=checkbox $str_checked name='{$f['id']}' value='{$f['id']}'></label>{$f['label']}:</p>";
            }
            ### options ###
            elseif (isset($f['type']) && ($f['type'] == 'option')) {
                ### only display if there is more than one option ###
                if (count($f['options']) > 1) {
                    echo "<p><label>{$f['label']}:</label><select class='inp$class_additional' name='{$f['id']}'>";
                    foreach ($f['options'] as $o) {
                        $selected = ($o == $value)
                                   ? 'selected'
                                   : '';

                        echo "<option value='$o'>$o</option>";
                    }
                    echo '</select></p>';
                } else {
                    echo "<input type='hidden' class='inp$class_additional' name='{$f['id']}' value='{$f['options'][0]}'>";
                }
            }
            ### password fields ###
            elseif (isset($f['type']) && ($f['type'] == 'password')) {
                echo "<p><label>{$f['label']}:</label><input type=password class='inp$class_additional' name='{$f['id']}' value='$value'></p>";
            }
            ### input fields ###
            else {
                echo "<p><label>{$f['label']}:</label><input class='inp$class_additional' name='{$f['id']}' value='$value'></p>";
            }
        }
        echo "<input class=button_submit type=submit value='install / upgrade'>";
        echo '<input type=hidden name=install_step value=form_submit>';
        echo '</div></form>';
    }
}

define('RESULT_GOOD', 0);
define('RESULT_FAILED', 1);
define('RESULT_PROBLEM', 2);

function print_testStart($p_message = null)
{
    if (!$p_message) {
        trigger_error('print_testStart called without message', E_USER_NOTICE);
        $p_message = '';
    }

    echo "<div class='test_start'>$p_message</div>";
}

function print_testResult($p_result, $p_message = '')
{
    $style = '';
    $msg = '?';
    if ($p_result == RESULT_FAILED) {
        $style = 'failed';
        $msg = 'FAILED';
    } elseif ($p_result == RESULT_GOOD) {
        $style = 'good';
        $msg = 'GOOD';
    } elseif ($p_result == RESULT_PROBLEM) {
        $style = 'problem';
        $msg = 'POTENTIAL PROBLEM';
    }
    echo "<div class='test_result $style'><b>$msg</b><br>$p_message</div><br>";
}

<?php

if (!function_exists('startedIndexPhp')) {
    header('location:../index.php');
    exit();
}

/**
* Defines basic interface to mysql
* - core functionality
* - required for querying requests to the database
*/
class MysqlException extends Exception
{
    public $backtrace;

    public function __construct($message = false, $code = false)
    {
        global $sql_obj;
        $this->message = '';
        if ($message) {
            $this->message = '<pre>' . $message;
        }

        ### define local variables for error.log ###

        $this->message .= $sql_obj->error;

        if (!$code) {
            $this->code = $sql_obj->errno;
        }

        if (function_exists('mysql_error') && mysql_error()) {
            $mysql_error = mysql_error();
        } elseif (confGet('DB_TYPE') == 'mysqli' && $sql_obj && function_exists('mysqli_error') && $sql_obj->getConnect() && mysqli_error($sql_obj->getConnect())) {
            $mysql_error = mysqli_error($sql_obj->getConnect());
        } else {
            $mysql_error = $sql_obj->error;
        }

        /**
        * triggering error will cause the $message-variable to be added to errors-log.
        * So we just put out an SQL-Warning without revealing details here.
        */
        trigger_error(sprintf(__('Database exception. Please read %s next steps on database errors.%s'), "<a href=http://streber.pixtur.de/index.php?go=taskView&tsk=1272'>", '</a>'), E_USER_ERROR);
    }
}

interface DB_Connection
{
    public function prepare($query);
    public function execute($query);
}

interface DB_Statement
{
    public function execute();
    public function bind_param($key, $value);
    public function fetch_row();
    public function fetch_assoc();
    public function fetchall_assoc();
}

class DB_Mysql implements DB_Connection
{
    protected $user;
    protected $pass;
    protected $dbhost;
    protected $dbname;
    protected $dbh;

    public function __construct($user = null, $pass = null, $dbhost = null, $dbname = null)
    {
        if ($user) {
            $this->user = $user;
        } else {
            $this->user = confGet('DB_USERNAME');
        }

        if ($pass) {
            $this->pass = $pass;
        } else {
            $this->pass = confGet('DB_PASSWORD');
        }
        if ($dbhost) {
            $this->dbhost = $dbhost;
        } else {
            $this->dbhost = confGet('HOSTNAME');
        }

        if ($dbname) {
            $this->dbname = $dbname;
        } else {
            $this->dbname = confGet('DB_NAME');
        }
    }

    /**
    * tries to connect to database
    * returns internal sql_obj-handler object
    */
    public function connect()
    {
        global $sql_obj;
        if (!is_object($sql_obj)) {
            $sql_obj = new sql_class($this->dbhost, $this->user, $this->pass, $this->dbname);
        }

        if (!$sql_obj->connect()) {
            throw new MysqlException();
        }
        if (!$sql_obj->selectdb()) {
            throw new MysqlException();
        }

        ### enable utf8 encoding
        if (confGet('DB_USE_UTF8_ENCODING')) {
            $sql_obj->execute('SET NAMES utf8;');
            $sql_obj->execute('SET CHARACTER SET utf8;');
        }
        return $sql_obj;
    }

    public function prepare($query)
    {
        global $sql_obj;
        if (!is_object($sql_obj)) {
            $this->connect();
        }

        return new DB_MysqlStatement($sql_obj, $query);
    }

    public function execute($query)
    {
        global $sql_obj;
        if (!is_object($sql_obj)) {
            $sql_obj = new sql_class($this->dbhost, $this->user, $this->pass, $this->dbname);
        }
        if (!$sql_obj->connect()) {
            throw new MysqlException();
        }
        $ret = $sql_obj->execute($query);
        if (!$ret) {
            throw new MysqlException();
        } elseif (!is_resource($ret)) {
            return true;
        } else {
            $stmt = new DB_MysqlStatement($sql_obj, $query);
            $stmt->result = $ret;
            return $stmt;
        }
    }

    public function lastId()
    {
        #echo 'lastID '.$this->id.'<br />';
        global $sql_obj;
        if (!is_object($sql_obj)) {
            $this->connect();
        }
        #echo 'lastID '.$sql_obj->lastId().'<br />';
        return $sql_obj->lastId();
    }

    /**
    * check if database is online and return current version
    *
    * this should be, if user-id is not set (checking always might
    * be too performance intensive)
    */
    public function getVersion()
    {
        global $sql_obj;
        if (!is_object($sql_obj)) {
            $sql_obj = new sql_class($this->dbhost, $this->user, $this->pass, $this->dbname);
        }

        if (!$sql_obj->connect()) {
            ### can't connect db... ###
            log_message("Can't connect database");
            return null;
        }
        if (!$sql_obj->selectdb()) {
            ### can't select... ###
            log_message("Can't select database");
            return null;
        }

        ### get version ###
        $prefix = confGet('DB_TABLE_PREFIX');

        $sql_command = "select * from {$prefix}db where updated is NULL";
        if ($sql_obj->execute($sql_command)) {
            $row = $sql_obj->fetchArray();
            return $row;
        } else {
            trigger_error("Can't selection version row from db: $sql_command");
            return null;
        }
    }
}

class DB_MysqlStatement implements DB_Statement
{
    public $result;
    public $binds;
    public $query;
    public $dbh;
    public function __construct($dbh, $query)
    {
        global $sql_obj;
        $this->query = $query;
        $this->dbh = $dbh;

        if (!is_object($dbh)) {
            throw new MysqlException('Not a valid database connection');
        }
    }

    public function bind_param($ph, $pv)
    {
        $this->binds[$ph] = $pv;
        return $this;
    }

    #-------------------------------------------------
    # execute()
    # - requires list of params
    #-------------------------------------------------
    public function execute()
    {
        global $sql_obj;
        $args = func_get_args();
        #if(count(func_get_args())) {
        #    throw new Exception("pass values as array");
        #}

        #--- bind args in member with 1 ----
        $this->binds = [];
        foreach ($args as $index => $name) {
            $this->binds[$index + 1] = $name;
        }

        $cnt = count($args);
        $query = $this->query;

        foreach ($this->binds as $ph => $pv) {
            #$query = @str_replace(":$ph", , $query);
            $query = preg_replace("/�$ph\b/", "'" . $pv . "'", $query);
        }

        if (!$sql_obj->execute($query)) {
            throw new MysqlException('Querry=' . $query . "\n");
        }

        #--- cound traffic for debug-output ----
        #global $DB_ITEMS_LOADED;
        #$DB_ITEMS_LOADED+= count($this->result);

        return $this;
    }

    public function fetch_row()
    {
        global $sql_obj;
        return $sql_obj->fetchRow();
    }

    public function fetch_assoc()
    {
        global $sql_obj;
        $row = [];

        if ($res = $sql_obj->fetchArray()) {
            foreach ($res as $key => $value) {
                $row[$key] = stripslashes($value);
            }
        }
        return $row;
    }

    public function fetchall_assoc()
    {
        global $sql_obj;
        $retval = [];

        /**
        * We do not have to strip slashes here, because this is done by fetch_assoc
        */
        while ($row = $this->fetch_assoc()) {
            $retval[] = $row;

            #--- cound traffic for debug-output ----
            global $DB_ITEMS_LOADED;
            $DB_ITEMS_LOADED += count($row);
        }
        return $retval;
    }
}

class _DB_Result
{
    protected $stmt;
    protected $result = [];
    private $rowIndex = 0;
    private $currIndex = 0;
    private $done = false;

    public function __construct(DB_Statement $stmt)
    {
        $this->stmt = $stmt;
    }
    public function first()
    {
        if (!$this->result) {
            $this->result[$this->rowIndex++] = $this->stmt->fetch_assoc();
        }
        $this->currIndex = 0;
        return $this;
    }
    public function last()
    {
        if (!$this->done) {
            array_push($this->result, $this->stmt->fetchall_assoc());
        }
        $this->done = true;
        $this->currIndex = $this->rowIndex = count($this->result) - 1;
        return $this;
    }
    public function next()
    {
        if ($this->done) {
            return false;
        }
        $offset = $this->currIndex + 1;
        if (!$this->result[$offset]) {
            $row = $this->stmt->fetch_assoc();
            if (!$row) {
                $this->done = true;
                return false;
            }
            $this->result[$offset] = $row;
            ++$this->rowIndex;
            ++$this->currIndex;
            return $this;
        } else {
            ++$this->currIndex;
            return $this;
        }
    }
    public function prev()
    {
        if ($this->currIndex == 0) {
            return false;
        }
        --$this->currIndex;
        return $this;
    }
    public function __get($value)
    {
        if (array_key_exists($value, $this->result[$this->currIndex])) {
            return $this->result[$this->currIndex][$value];
        }
    }
}

require_once(dirname(__FILE__) . '/../' . confGet('DIR_SETTINGS') . confGet('FILE_DB_SETTINGS'));

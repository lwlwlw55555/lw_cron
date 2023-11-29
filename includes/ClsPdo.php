<?php
class ClsPdo
{
    protected static $_instance = null;
    protected $config = null;
    protected $dbName = '';
    protected $dsn;
    protected $dbh;

    
    /**
     * 构造
     * 
     * @return ClsPdo
     */
    private function __construct($config)
    {
        $this->setDbh($config);
    }


    /**
     * 防止克隆
     * 
     */
    private function __clone() {}
    
    /**
     * Singleton instance
     * 
     * @return Object
     */
    public static function getInstance($config)
    {
        self::$_instance = new self($config);
        return self::$_instance;
    }
    
    public static function getInstanceRetry($config, $retry_count = 0)
    {
        try {
            self::$_instance = new self($config);
            return self::$_instance;
        } catch (Exception $e) {
            echo date("Y-m-d H:i:s") . " getInstanceRetry retry_count:{$retry_count} catch " . $e->getMessage() . PHP_EOL;
            return ClsPdo::getInstanceRetry($config, $retry_count++);
        }
    }
    
    /**
     * Query 查询
     *
     * @param String $strSql SQL语句
     * @param String $queryMode 查询方式(All or Row)
     * @param Boolean $debug
     * @return Array
     */
    public function getAll($strSql, $queryMode = 'All', $debug = false)
    {
        $recordset = $this->select($strSql, $debug);
        if ($recordset) {
            $recordset->setFetchMode(PDO::FETCH_ASSOC);
            if ($queryMode == 'All') {
                $result = $recordset->fetchAll();
            } elseif ($queryMode == 'Row') {
                $result = $recordset->fetch();
            }
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * execSql 执行SQL语句
     *
     * @param String $strSql
     * @param Boolean $debug
     * @return Int
     */
    public function query($strSql, $debug = false)
    {
        if ($debug === true) $this->debug($strSql);
        $result = $this->dbh->exec($strSql);
        $is_reconnect = $this->getPDOError();
        if ($is_reconnect){
            sleep(3);
            echo date("Y-m-d H:i:s")." 尝试重新连接".PHP_EOL;
            $this->setDbh($this->config);
            $result = $this->dbh->exec($strSql);
            $this->getPDOError(false);
        }
        return $result;
    }

    /**
     * strSql 执行SQL语句
     * 获取一行
     *
     * @param String $strSql
     * @param Boolean $debug
     */
    public function getRow($strSql, $debug = false){
        $strSql = trim($strSql . ' LIMIT 1');
        $recordset = $this->select($strSql, $debug);
        if ($recordset) {
            $recordset->setFetchMode(PDO::FETCH_ASSOC);
            $result = $recordset->fetch();
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * strSql 执行SQL语句
     * 获取一列
     *
     * @param String $strSql
     * @param Boolean $debug
     */
    public function getCol($strSql, $debug = false){
        $recordset = $this->select($strSql, $debug);
        if ($recordset) {
            $recordset->setFetchMode(PDO::FETCH_NUM);
            $result = array();
            while ($row = $recordset->fetch()) {
                $result[] = $row[0];
            }
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * strSql 执行SQL语句
     * 获取一个值
     *
     * @param String $strSql
     * @param Boolean $debug
     */
    public function getOne($strSql, $debug = false){
        $strSql = trim($strSql . ' LIMIT 1');
        $recordset = $this->select($strSql, $debug);
        if ($recordset) {
            $recordset->setFetchMode(PDO::FETCH_NUM);
            $result = $recordset->fetch();
            $result = $result[0];
        } else {
            $result = null;
        }
        return $result;
    }
   
    /**
     * 获取指定列的数量
     * 
     * @param string $table
     * @param string $field_name
     * @param string $where
     * @param bool $debug
     * @return int
     */
    public function getCount($table, $field_name, $where = '', $debug = false)
    {
        $strSql = "SELECT COUNT($field_name) AS NUM FROM $table";
        if ($where != '') $strSql .= " WHERE $where";
        if ($debug === true) $this->debug($strSql);
        $arrTemp = $this->query($strSql, 'Row');
        return $arrTemp['NUM'];
    }
    
    /**
     * 获取表引擎
     * 
     * @param String $dbName 库名
     * @param String $tableName 表名
     * @param Boolean $debug
     * @return String
     */
    public function getTableEngine($dbName, $tableName)
    {
        $strSql = "SHOW TABLE STATUS FROM $dbName WHERE Name='".$tableName."'";
        $arrayTableInfo = $this->query($strSql);
        $this->getPDOError();
        return $arrayTableInfo[0]['Engine'];
    }
    
    /**
     * beginTransaction 事务开始
     */
    public function beginTransaction()
    {
        $this->dbh->beginTransaction();
    }
    
    /**
     * commit 事务提交
     */
    public function commit()
    {
        $this->dbh->commit();
    }
    
    /**
     * rollback 事务回滚
     */
    public function rollback()
    {
        $this->dbh->rollback();
    }

    /**
     * checkFields 检查指定字段是否在指定数据表中存在
     *
     * @param String $table
     * @param array $arrayField
     */
    private function checkFields($table, $arrayFields)
    {
        $fields = $this->getFields($table);
        foreach ($arrayFields as $key => $value) {
            if (!in_array($key, $fields)) {
                $this->outputError("Unknown column `$key` in field list.");
            }
        }
    }
    
    /**
     * getFields 获取指定数据表中的全部字段名
     *
     * @param String $table 表名
     * @return array
     */
    private function getFields($table)
    {
        $fields = array();
        $recordset = $this->select("SHOW COLUMNS FROM {$table} ");
        $recordset->setFetchMode(PDO::FETCH_ASSOC);
        $result = $recordset->fetchAll();
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }
    
    /**
     * getPDOError 捕获PDO错误信息
     */
    private function getPDOError($retry = true)
    {
        if ($this->dbh->errorCode() != '00000') {
            $arrayError = $this->dbh->errorInfo();
            echo "error_code : ".$this->dbh->errorCode().PHP_EOL;
            var_dump($arrayError);
            if ($retry && ( $arrayError[1] == 2006 || $arrayError[2] == 'MySQL server has gone away' ) ){
                echo date("Y-m-d H:i:s")."MySQL Error:  MySQL server has gone away".PHP_EOL;
                return 1;
            }
            $this->outputError($arrayError[2]);
        }
    }
    
    /**
     * debug
     * 
     * @param mixed $debuginfo
     */
    private function debug($debuginfo)
    {
        var_dump($debuginfo);
        exit();
    }
    
    /**
     * 输出错误信息
     * 
     * @param String $strErrMsg
     */
    private function outputError($strErrMsg)
    {
        var_dump($this->config);
        throw new Exception('MySQL Error: '.$strErrMsg);
    }
    
    /**
     * destruct 关闭数据库连接
     */
    public function destruct()
    {
        $this->dbh = null;
    }

    function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = '') {
        $field_names = $this->getCol('DESC ' . $table);

        $sql = '';
        if ($mode == 'INSERT') {
            $fields = $values = array();
            foreach ($field_names AS $value) {
                if (array_key_exists($value, $field_values) == true) {
                    $fields[] = $value;
                    $values[] = "'" . $field_values[$value] . "'";
                }
            }

            if (!empty($fields)) {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        } else {
            $sets = array();
            foreach ($field_names AS $value) {
                if (array_key_exists($value, $field_values) == true) {
                    $sets[] = $value . " = '" . $field_values[$value] . "'";
                }
            }

            if (!empty($sets)) {
                $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
            }
        }

        if ($sql) {
            return $this->query($sql, $querymode);
        } else {
            return false;
        }
    }

    function autoReplaceExecute($table, $field_values, $mode = 'REPLACE', $where = '', $querymode = '') {
        $field_names = $this->getCol('DESC ' . $table);

        $sql = '';
        if ($mode == 'REPLACE') {
            $fields = $values = array();
            foreach ($field_names AS $value) {
                if (array_key_exists($value, $field_values) == true) {
                    $fields[] = $value;
                    $values[] = "'" . $field_values[$value] . "'";
                }
            }

            if (!empty($fields)) {
                $sql = 'REPLACE INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        } else {
            $sets = array();
            foreach ($field_names AS $value) {
                if (array_key_exists($value, $field_values) == true) {
                    $sets[] = $value . " = '" . $field_values[$value] . "'";
                }
            }

            if (!empty($sets)) {
                $sql = 'REPLACE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
            }
        }

        if ($sql) {
            return $this->query($sql, $querymode);
        } else {
            return false;
        }
    }

    function setDbh($config){
        $this->config = $config;
        $dbHost = $config['host'];
        $dbUser = $config['user'];
        $dbPasswd = $config['pass'];
        $dbName = $config['name'];
        $dbCharset = $config['charset'];
        $port = isset($config['port'])?$config['port']:3306;
        try {
            $this->dsn = 'mysql:host='.$dbHost.';port='.$port.';dbname='.$dbName;
            $this->dbh = new PDO($this->dsn, $dbUser, $dbPasswd);
            $this->dbh->exec('SET character_set_connection='.$dbCharset.', character_set_results='.$dbCharset.', character_set_client=binary');
        } catch (PDOException $e) {
            if ($e->getMessage() == 'MySQL server has gone away'){
                echo $e->getMessage().PHP_EOL;
                sleep(3);
                echo "重新连接".PHP_EOL;
                $this->dsn = 'mysql:host='.$dbHost.';port='.$port.';dbname='.$dbName;
                $this->dbh = new PDO($this->dsn, $dbUser, $dbPasswd);
                $this->dbh->exec('SET character_set_connection='.$dbCharset.', character_set_results='.$dbCharset.', character_set_client=binary');
            } else {
                $this->outputError($e->getMessage());
            }
        }

    }

    /**
     * 执行 查询SQL语句
     */

    public function select($strSql, $debug = false)
    {
        if ($debug === true) $this->debug($strSql);
        $result = $this->dbh->query($strSql);
        $is_reconnect = $this->getPDOError();
        if ($is_reconnect){
            sleep(3);
            echo date("Y-m-d H:i:s")." 尝试重新连接".PHP_EOL;
            $this->setDbh($this->config);
            $result = $this->dbh->query($strSql);
            $this->getPDOError(false);
        }
        return $result;
    }
}
?>

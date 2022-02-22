<?php
/**
 * 定义MYSQL操作数据库的类，在init.php中被实例化
 *
 */

class cls_mysql {
    var $link_id    = NULL;

    var $queryCount = 0;
    var $queryTime  = '';
    var $queryLog   = array();

    var $max_cache_time = 300; // 最大的缓存时间，以秒为单位

    var $cache_data_dir = 'caches/';
    var $root_path      = '';

    var $error_message  = array();
    var $platform       = '';
    var $version        = '';
    var $dbhash         = '';
    var $starttime      = 0;
    var $timeline       = 0;
    var $timezone       = 0;

    var $mysql_config_cache_file_time = 0;

    var $mysql_disable_cache_tables = array(); // 不允许被缓存的表，遇到将不会进行缓存
    var $db_conf;

    function __construct($db_conf) {
        $this->cls_mysql($db_conf);
    }

    function cls_mysql($db_conf) {
        $dbhost = $db_conf['host'];
        $dbuser = $db_conf['user'];
        $dbpw = $db_conf['pass'];
        $dbname = $db_conf['name'];
        $charset = $db_conf['charset'] ? $db_conf['charset'] : "utf8";
        $pconnect = intval($db_conf['pconnect']);
	$this->db_conf = $db_conf;
        if (defined('ROOT_PATH') && !$this->root_path) {
            $this->root_path = ROOT_PATH;
        }

        if ($pconnect) {
            if (!($this->link_id = @mysql_pconnect($dbhost, $dbuser, $dbpw))) {
                $this->ErrorMsg("Can't pConnect MySQL Server($dbhost)!");

                return false;
            }
        } else {
            if (PHP_VERSION >= '4.2') {
                $this->link_id = @mysql_connect($dbhost, $dbuser, $dbpw, true);
            } else {
                $this->link_id = @mysql_connect($dbhost, $dbuser, $dbpw);

                mt_srand((double)microtime() * 1000000); // 随机数函数初始化
            }
            if (!$this->link_id) {
                $this->ErrorMsg("Can't Connect Database Server !"); // Zandy 2007-11-27

                return false;
            }
        }

        $this->dbhash  = md5($this->root_path . $dbhost . $dbuser . $dbpw . $dbname);
        $this->version = mysql_get_server_info($this->link_id);

        //如果mysql 版本是 4.1+ 以上，需要对字符集进行初始化
        if ($this->version > '4.1') {
            if ($charset != 'latin1') {
                mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->link_id);
            }
            if ($this->version > '5.0.1') {
                mysql_query("SET sql_mode=''", $this->link_id);
            }
        }

        $sqlcache_config_file = $this->root_path . $this->cache_data_dir . 'sqlcache_config_file_' . $this->dbhash . '.php';

        @include($sqlcache_config_file);

        $this->starttime = time();

        if ($this->max_cache_time && $this->starttime > $this->mysql_config_cache_file_time + $this->max_cache_time) {
            if ($dbhost != '.') {
                $result = mysql_query("SHOW VARIABLES LIKE 'basedir'", $this->link_id);
                $row    = mysql_fetch_assoc($result);
                if (!empty($row['Value'][1]) && $row['Value'][1] == ':' && !empty($row['Value'][2]) && $row['Value'][2] == "\\") {
                    $this->platform = 'WINDOWS';
                } else {
                    $this->platform = 'OTHER';
                }
            } else {
                $this->platform = 'WINDOWS';
            }

            if ($this->platform == 'OTHER' &&
                ($dbhost != '.' && strtolower($dbhost) != 'localhost:3306' && $dbhost != '127.0.0.1:3306') ||
                (PHP_VERSION >= '5.1' && date_default_timezone_get() == 'UTC')) {
                $result = mysql_query("SELECT UNIX_TIMESTAMP() AS timeline, UNIX_TIMESTAMP('" . date('Y-m-d H:i:s', $this->starttime) . "') AS timezone", $this->link_id);
                $row    = mysql_fetch_assoc($result);

                if ($dbhost != '.' && strtolower($dbhost) != 'localhost:3306' && $dbhost != '127.0.0.1:3306') {
                    $this->timeline = $this->starttime - $row['timeline'];
                }

                if (PHP_VERSION >= '5.1' && date_default_timezone_get() == 'UTC') {
                    $this->timezone = $this->starttime - $row['timezone'];
                }
            }

            $content = '<' . "?php\r\n" .
                       '$this->mysql_config_cache_file_time = ' . $this->starttime . ";\r\n" .
                       '$this->timeline = ' . $this->timeline . ";\r\n" .
                       '$this->timezone = ' . $this->timezone . ";\r\n" .
                       '$this->platform = ' . "'" . $this->platform . "';\r\n?" . '>';

            @file_put_contents($sqlcache_config_file, $content);
        }

        /* 选择数据库 */
        if ($dbname) {
            if (mysql_select_db($dbname, $this->link_id) === false ) {
                $this->ErrorMsg("Can't select MySQL database($dbname)!");

                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    function select_database($dbname) {
        return mysql_select_db($dbname, $this->link_id);
    }

    function set_mysql_charset($charset) {
        /* 如果mysql 版本是 4.1+ 以上，需要对字符集进行初始化 */
        if ($this->version > '4.1') {
            if (in_array(strtolower($charset), array('gbk', 'big5', 'utf-8', 'utf8'))) {
                $charset = str_replace('-', '', $charset);
            }
            if ($charset != 'latin1') {
                mysql_query("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->link_id);
            }
        }
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return mysql_fetch_array($query, $result_type);
    }

    function query($sql, $type = '') {
        // print_r($this->link_id);
        mysql_select_db($this->db_conf['name'], $this->link_id);
        $this->queryCount++;
        $this->queryLog[] = $sql;
        if ($this->queryTime == '') {
            $this->queryTime = microtime();
        }

        /* 当当前的时间大于类初始化时间的时候，自动执行 ping 这个自动重新连接操作 */
        if (PHP_VERSION >= '4.3' && time() > $this->starttime + 1) {
            mysql_ping($this->link_id);
        }

        if (!($query = mysql_query($sql, $this->link_id)) && $type != 'SILENT') {
            $this->error_message[]['message'] = 'MySQL Query Error';
            $this->error_message[]['sql'] = $sql;
            $this->error_message[]['error'] = mysql_error($this->link_id);
            $this->error_message[]['errno'] = mysql_errno($this->link_id);

            $this->ErrorMsg();

            return false;
        }

        if (defined('DEBUG_MODE') && (DEBUG_MODE & 8) == 8) {
            $logfilename = $this->root_path . 'data/mysql_query_' . $this->dbhash . '_' . date('Y_m_d') . '.log';
            $str = $sql . "\n\n";

            if (PHP_VERSION >= '5.0') {
                file_put_contents($logfilename, $str, FILE_APPEND);
            } else {
                $fp = @fopen($logfilename, 'ab+');
                if ($fp) {
                    fwrite($fp, $str);
                    fclose($fp);
                }
            }
        }

        return $query;
    }
    
 	/**
     * add  at 2013.1
     * i like simple
     * @param string $sql
     */
    function exec($sql)
    {
        if (empty($sql)) {
            return null;
        }
        $q = $this->query($sql);
        if (stripos($sql, 'insert') === 0) {
            return $this->insert_id();
        } elseif(stripos($sql, 'replace') === 0){
        	return $this->insert_id();
        } 
        elseif (stripos($sql, 'update') === 0 || stripos($sql, 'delete') === 0) {
            return $this->affected_rows();
        } else {
            return $q;
        }
    }
    
    function start_transaction()
    {
        return $this->query("START TRANSACTION");
    }

    function commit()
    {
        return $this->query("COMMIT");
    }

    function rollback()
    {
        return $this->query("ROLLBACK");
    }

    function affected_rows() {
        return mysql_affected_rows($this->link_id);
    }

    function error() {
        return mysql_error($this->link_id);
    }

    function errno() {
        return mysql_errno($this->link_id);
    }

    function result($query, $row) {
        return @mysql_result($query, $row);
    }

    function num_rows($query) {
        return mysql_num_rows($query);
    }

    function num_fields($query) {
        return mysql_num_fields($query);
    }

    function free_result($query) {
        return mysql_free_result($query);
    }

    function insert_id() {
        return mysql_insert_id($this->link_id);
    }

    function fetchRow($query) {
        return mysql_fetch_assoc($query);
    }

    function fetch_object($query) {
        return mysql_fetch_object($query);
    }

    function fetch_fields($query)
    {
        return mysql_fetch_field($query);
    }

    function version() {
        return $this->version;
    }

    function ping() {
        if (PHP_VERSION >= '4.3') {
            return mysql_ping($this->link_id);
        } else {
            return false;
        }
    }

    function escape_string($unescaped_string) {
        if (PHP_VERSION >= '4.3') {
            return mysql_real_escape_string($unescaped_string);
        } else {
            return mysql_escape_string($unescaped_string);
        }
    }

    function close() {
        return mysql_close($this->link_id);
    }

    function ErrorMsg($message = '', $sql = '') {
        if ($message) {
            echo "<b>OUKU info</b>: $message\n\n";
        } else {
            echo "<b>MySQL server error report:";
            print_r($this->error_message);
        }

        exit;
    }

    /* 仿真 Adodb 函数 */
    function selectLimit($sql, $num, $start = 0) {
        if ($start == 0) {
            $sql .= ' LIMIT ' . $num;
        } else {
            $sql .= ' LIMIT ' . $start . ', ' . $num;
        }

        return $this->query($sql);
    }

    function getOne($sql, $limited = false) {
        if ($limited == true) {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false) {
            $row = mysql_fetch_row($res);

            return $row[0];
        } else {
            return false;
        }
    }

    function getOneCached($sql, $cached = 'FILEFIRST') {
        $sql = trim($sql . ' LIMIT 1');

        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst) {
            return $this->getOne($sql, true);
        } else {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true) {
                return $result['data'];
            }
        }

        $arr = $this->getOne($sql, true);

        if ($arr !== false && $cachefirst) {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    function getAll($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_assoc($res)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return false;
        }
    }

    function getAllCached($sql, $cached = 'FILEFIRST') {
        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst) {
            return $this->getAll($sql);
        } else {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true) {
                return $result['data'];
            }
        }

        $arr = $this->getAll($sql);

        if ($arr !== false && $cachefirst) {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    function getRow($sql, $limited = false) {
        if ($limited == true) {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false) {
            return mysql_fetch_assoc($res);
        } else {
            return false;
        }
    }

    function getRowCached($sql, $cached = 'FILEFIRST') {
        $sql = trim($sql . ' LIMIT 1');

        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst) {
            return $this->getRow($sql, true);
        } else {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true) {
                return $result['data'];
            }
        }

        $arr = $this->getRow($sql, true);

        if ($arr !== false && $cachefirst) {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
    }

    function getCol($sql) {
        $res = $this->query($sql);
        if ($res !== false) {
            $arr = array();
            while ($row = mysql_fetch_row($res)) {
                $arr[] = $row[0];
            }

            return $arr;
        } else {
            return false;
        }
    }

    function getColCached($sql, $cached = 'FILEFIRST') {
        $cachefirst = ($cached == 'FILEFIRST' || ($cached == 'MYSQLFIRST' && $this->platform != 'WINDOWS')) && $this->max_cache_time;
        if (!$cachefirst) {
            return $this->getCol($sql);
        } else {
            $result = $this->getSqlCacheData($sql, $cached);
            if (empty($result['storecache']) == true) {
                return $result['data'];
            }
        }

        $arr = $this->getCol($sql);

        if ($arr !== false && $cachefirst) {
            $this->setSqlCacheData($result, $arr);
        }

        return $arr;
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

    function autoReplace($table, $field_values, $update_values, $where = '', $querymode = '') {
        $field_descs = $this->getAll('DESC ' . $table);

        $primary_keys = array();
        foreach ($field_descs AS $value) {
            $field_names[] = $value['Field'];
            if ($value['Key'] == 'PRI') {
                $primary_keys[] = $value['Field'];
            }
        }

        $fields = $values = array();
        foreach ($field_names AS $value) {
            if (array_key_exists($value, $field_values) == true) {
                $fields[] = $value;
                $values[] = "'" . $field_values[$value] . "'";
            }
        }

        $sets = array();
        foreach ($update_values AS $key => $value) {
            if (array_key_exists($key, $field_values) == true) {
                if (is_numeric($value)) {
                    $sets[] = $key . ' = ' . $key . ' + ' . $value;
                } else {
                    $sets[] = $key . " = '" . $value . "'";
                }
            }
        }

        $sql = '';
        if (empty($primary_keys)) {
            if (!empty($fields)) {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        } else {
            if ($this->version() >= '4.1') {
                if (!empty($fields)) {
                    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                    if (!empty($sets)) {
                        $sql .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $sets);
                    }
                }
            } else {
                if (empty($where)) {
                    $where = array();
                    foreach ($primary_keys AS $value) {
                        if (is_numeric($value)) {
                            $where[] = $value . ' = ' . $field_values[$value];
                        } else {
                            $where[] = $value . " = '" . $field_values[$value] . "'";
                        }
                    }
                    $where = implode(' AND ', $where);
                }

                if ($where && (!empty($sets) || !empty($fields))) {
                    if (intval($this->getOne("SELECT COUNT(*) FROM $table WHERE $where")) > 0) {
                        if (!empty($sets)) {
                            $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
                        }
                    } else {
                        if (!empty($fields)) {
                            $sql = 'REPLACE INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                        }
                    }
                }
            }
        }

        if ($sql) {
            return $this->query($sql, $querymode);
        } else {
            return false;
        }
    }

    function setMaxCacheTime($second) {
        $this->max_cache_time = $second;
    }

    function getMaxCacheTime() {
        return $this->max_cache_time;
    }

    function getSqlCacheData($sql, $cached = '') {
        $sql = trim($sql);

        $result = array();
        $result['filename'] = $this->root_path . $this->cache_data_dir . 'sqlcache_' . abs(crc32($this->dbhash . $sql)) . '_' . md5($this->dbhash . $sql) . '.php';

        $data = @file_get_contents($result['filename']);
        if (isset($data{23})) {
            $filetime = substr($data, 13, 10);
            $data     = substr($data, 23);

            if (($cached == 'FILEFIRST' && time() > $filetime + $this->max_cache_time) || ($cached == 'MYSQLFIRST' && $this->table_lastupdate($this->get_table_name($sql)) > $filetime)) {
                $result['storecache'] = true;
            } else {
                $result['data'] = @unserialize($data);
                if ($result['data'] === false) {
                    $result['storecache'] = true;
                } else {
                    $result['storecache'] = false;
                }
            }
        } else {
            $result['storecache'] = true;
        }

        return $result;
    }

    function setSqlCacheData($result, $data) {
        if ($result['storecache'] === true && $result['filename']) {
            @file_put_contents($result['filename'], '<?php exit;?>' . time() . serialize($data));
            clearstatcache();
        }
    }

    /* 获取 SQL 语句中最后更新的表的时间，有多个表的情况下，返回最新的表的时间 */
    function table_lastupdate($tables) {
        $lastupdatetime = '0000-00-00 00:00:00';

        $tables = str_replace('`', '', $tables);
        $this->mysql_disable_cache_tables = str_replace('`', '', $this->mysql_disable_cache_tables);

        foreach ($tables AS $table) {
            if (in_array($table, $this->mysql_disable_cache_tables) == true) {
                $lastupdatetime = '2037-12-31 23:59:59';

                break;
            }

            if (strstr($table, '.') != NULL) {
                $tmp = explode('.', $table);
                $sql = 'SHOW TABLE STATUS FROM `' . trim($tmp[0]) . "` LIKE '" . trim($tmp[1]) . "'";
            } else {
                $sql = "SHOW TABLE STATUS LIKE '" . trim($table) . "'";
            }
            $result = mysql_query($sql, $this->link_id);

            $row = mysql_fetch_assoc($result);
            if ($row['Update_time'] > $lastupdatetime) {
                $lastupdatetime = $row['Update_time'];
            }
        }
        $lastupdatetime = strtotime($lastupdatetime) - $this->timezone + $this->timeline;

        return $lastupdatetime;
    }

    function get_table_name($query_item) {
        $query_item = trim($query_item);
        $table_names = array();

        /* 判断语句中是不是含有JOIN */
        if (stristr($query_item, ' JOIN ') == '') {
            /* 解析一般的SELECT FROM语句 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?(?:\s*,\s*(?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?)*)/is', $query_item, $table_names)) {
                $table_names = preg_replace('/((?:`?\w+`?\s*\.\s*)?`?\w+`?)[^,]*/', '\1', $table_names[1]);

                return preg_split('/\s*,\s*/', $table_names);
            }
        } else {
            /* 对含有JOIN的语句进行解析 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)(?:(?:\s*AS)?\s*`?\w+`?)?.*?JOIN.*$/is', $query_item, $table_names)) {
                $other_table_names = array();
                preg_match_all('/JOIN\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)\s*/i', $query_item, $other_table_names);

                return array_merge(array($table_names[1]), $other_table_names[1]);
            }
        }

        return $table_names;
    }

    /* 设置不允许进行缓存的表 */
    function set_disable_cache_tables($tables) {
        if (!is_array($tables)) {
            $tables = explode(',', $tables);
        }

        foreach ($tables AS $table) {
            $this->mysql_disable_cache_tables[] = $table;
        }

        array_unique($this->mysql_disable_cache_tables);
    }
    
	function table($table_name) {
		global $db_name, $prefix;
		return '`' . $db_name . '`.`' . $prefix . $table_name . '`';
	}    
	
	    /**
     * add by Zandy at 2010.12
     * i like quote
     * @param string $sql
     */
    function quote($unescaped_string)
    {
        return mysql_real_escape_string($unescaped_string, $this->link_id);
    }
	
	  /**
     * add by Zandy at 2010.12
     * i like simple
     * @param string $tablename
     * @param mixed $values
     * @param boolean $replace
     * @param boolean $ignore
     */
    function insert($tablename, $values, $replace = false, $ignore = false)
    {
        if (empty($values)) {
            return null;
        }
        $sql = $replace ? 'REPLACE ' : 'INSERT ';
        $sql .= $ignore ? 'IGNORE ' : '';
        $sql .= 'INTO ' . $tablename . ' SET ';
        $sql .= $this->prepareSQL($values, ', ');
        $q = $this->query($sql);
        return $this->insert_id();
    }
    function update($tablename, $values, $where, $limit = 0)
    {
        $sql = 'UPDATE ' . $tablename . ' SET ';
        $sql .= $this->prepareSQL($values);
        $sql .= " WHERE " . $this->prepareSQL($where, ' AND ');
        if ($limit > 0) {
            $sql .= " LIMIT " . $limit;
        }
        $q = $this->query($sql);
        return $this->affected_rows();
    }
	
	  /**
     * 处理之后可以直接拼接在 sql 里
     * add by Zandy at 2010.12
     * @param mixed $data  string int float array
     * @param string $join
     */
    function prepareSQL($data, $join = ', ')
    {
        if (is_int($data) || is_float($data)) {
            return $data;
        } elseif (is_bool($data)) {
            return $data ? 1 : 0;
        } elseif (is_null($data)) {
            return '';
        } elseif (is_string($data)) {
            return $data;
        } elseif (is_array($data)) {
            foreach ($data as $k => $v) {
                if (preg_match('/^\d+$/', $k)) {
                    $data[$k] = $this->prepareSQL($v, $join);
                } elseif (stripos($v, "$k + ") === 0) {
                    // like "UPDATE xxx SET clicks = clicks + 1 WHERE yyy"
                    $data[$k] = "$k = $v";
                } elseif (stripos($v, "ZYMI::") === 0) {
                    // use mysql internal function or constant with param
                    // usage: $order['userId'] = 'ZYMI::replace(uuid(), "-", "")';
                    // return userId = replace(uuid(), "-", "")
                    $data[$k] = "$k = " . substr($v, 6);
                } elseif (preg_match('/[A-Z_]+\s*\(\s*\)/', $v)) {
                    // use mysql internal function with no param
                    $data[$k] = "$k = $v";
                } else {
                    $tmp = $this->prepareSQL($v, $join);
                    $data[$k] = "$k = '" . $this->quote($tmp) . "'";
                }
            }
            return join($join, $data);
        } else {
            return (string) $data;
        }
    }
}

?>

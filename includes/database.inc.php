<?php
require_once __DIR__ . '/config.inc.php';

class Database
{
    public $last_result = null;
    protected $link = null;

    /*
    * if connect=true, attempts to connect using default constants
    */
    public function __construct($connect=true)
    {
        $connected = false;
        if($connect)
        {
            $connected = $this->connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE);
        }
        return $connected;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * opens or reuses a connection to a MySQL server.
     */
    public function connect($server,$username,$password,$database)
    {
        $connected = false;
        if(!($this->link))
        {
            for($i = 0; $i < MAX_ATTEMPTS_CONNECT_FAILS; $i++)
            {
                $this->link = mysqli_connect($server,$username,$password,$database);
                if($this->link)
                {
                    $connected = true;
                    break;

                }
            }
        }

        return $connected;
    }

    /**
     * closes the non-persistent connection to the MySQL server
     */
    public function close()
    {
        if($result = @mysqli_close($this->link))
        {
            $this->link = false;
        }
        $this->last_result = $result;
        return $result;
    }

    /**
     * sends a unique query to the currently active database
     */
    public function query($sql)
    {
        // Check if we are at least connected
        if(empty($this->link))
        {
            echo "not connected";
            return null;
        }


        $result = mysqli_query($this->link, $sql);
        if(!$result)
        {
            $mysql_error_no = mysqli_errno($this->link);
            /* Check the MySQL error */
            if($mysql_error_no == MYSQL_WAIT_TIMEOUT_ERROR_NO1 || $mysql_error_no == MYSQL_WAIT_TIMEOUT_ERROR_NO2)
            {
                /* Close existing connection */
                mysqli_close($this->link);
                $this->link = 0;

                /* Trying to reconnect */
                $this->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, true);

                /* Run the query again */
                $result = mysqli_query($this->link, $sql);
                if($result)
                {
                    return $result;
                }
            }
        }
        $this->last_result = $result;
        return $result;
    }

    /**
     * returns an array that corresponds to the fetched row and moves the internal data pointer ahead
     */
    public function fetch_array($result, $method = MYSQLI_ASSOC)
    {
        $row = @mysqli_fetch_array($result, $method);
        return $row;
    }

    /**
     * retrieves the number of rows from a result set.
     */
    public function num_rows($result)
    {
        $rows = @mysqli_num_rows($result);
        return $rows;
    }

    /**
     * get the number of affected rows by the last INSERT, UPDATE, REPLACE or DELETE query associated with $this->link
     */
    public function affected_rows()
    {
        $result = @mysqli_affected_rows($this->link);
        return $result;
    }

    /**
     * moves the internal row pointer of the MySQL result associated with the specified result identifier to point to the specified row number.
     * the next call to a MySQL fetch function, such as $this->fetch_array, would return that row.
     */
    public function data_seek($result, $row_number)
    {
        $seek = @mysqli_data_seek($result, $row_number);
        return $seek;
    }

    /**
     * retrieves the ID generated for an AUTO_INCREMENT column by the previous query (usually INSERT).
     */
    public function insert_id()
    {
        return mysqli_insert_id($this->link);
    }

    /**
     * free all memory associated with the result identifier result
     */
    public function free_result($result)
    {
    	if (!function_exists('mysqli_free_result'))
    		return;
        $result = @mysqli_free_result($result);
        return $result;
    }

    /**
     * returns all rows selected by SQL query
     */
    public function get_all($sql)
    {
        $results = array();
        if(!($result = $this->query($sql)))
        {
            return $results;
        }
        while($row = $this->fetch_array($result))
        {
            $results[] = $row;
        }
        $this->free_result($result);
        return $results;
    }

    /**
     * returns the first row selected by a SQL query.
     */
    public function get_row($sql)
    {
        if(!($result = $this->query($sql)))
        {
            return array();
        }
        while($row = $this->fetch_array($result))
        {
            $this->free_result($result);
            return $row;
        }
        // in case of no results
        return array();
    }

    /**
     * returns the values of the first column of a SELECT SQL query into an array.
     */
    public function get_col($sql)
    {
        $results = array();
        if(!($result = $this->query($sql)))
        {
            return $results;
        }
        while($row = $this->fetch_array($result, MYSQLI_NUM))
        {
            $results[] = @$row[0];
        }
        $this->free_result($result);
        return $results;
    }

    /**
     * returns the value of the first field of the first row of a SELECT SQL query.
     */
    public function get_one($sql)
    {
        if(!($result = $this->query($sql)))
        {
            return false;
        }
        while($row = $this->fetch_array($result, MYSQLI_NUM))
        {
            $this->free_result($result);
            return @$row[0];
        }
        return false;
    }

    /**
     * returns information on fields of a certain table
     */
    public function show_fields($table, $fields_only = false)
    {
        return $this->get_assoc("SHOW FIELDS FROM `". $table ."`");
    }

    /**
     * returns a list of tables for the current database.
     */
    public function list_tables()
    {
        return $this->get_col("SHOW TABLES");
    }

    /**
     * inserts a row into a table.
     */
    public function insert($table, $data = array(), $ignore=FALSE)
    {
        if(!($temp_fields = $this->show_fields($table)))
        {
            return false;
        }
        if(empty($data))
        {
            return false;
        }

        $new_data = array();
        foreach(array_keys($data) AS $field)
        {
            if(!empty($temp_fields[$field]))
            {
                $data[$field] = $this->escape_string($data[$field]);
                $new_data[$field] = $data[$field];
            }
        }
        $sql =  "INSERT " . ($ignore == TRUE ? "IGNORE " : "") . "INTO `" . $table . "` "
               ."(`". implode("`,`", array_keys($new_data)) ."`) "
               ."VALUES "
               ."('". implode("','", array_values($new_data)) ."')";


        if($this->query($sql))
        {
            if(!($id = $this->insert_id()))
            {
                return false;
            }
            return $id;
        }
        return false;
    }

    /**
     * updates one or several rows in a table depending on the matching criteria.
     */
    public function update($table, $data = array(), $where)
    {
        if(!is_array($where))
        {
            error_log("update(): 3rd argument must be an array");
            return false;
        }
        if(!($temp_fields = $this->show_fields($table)))
        {
            return false;
        }
        if(empty($data))
        {
            return array();
        }
        $new_data = array();
        $new_where = array();
        foreach(array_keys($temp_fields) AS $field)
        {
            if(isset($data[$field]))
            {
                $data[$field] = $this->escape_string($data[$field]);
                $new_data[$field] = $data[$field];
            }
            if(isset($where[$field]))
            {
                $new_where[$field] = $where[$field];
            }
        }
        $sql =  "UPDATE `". $table ."` "
               ."SET ";
        $data_fields = array();
        $where_fields = array();
        foreach($new_data AS $field => $value)
        {
            $data_fields[] = "`". $field ."` = '". $value ."'";
        }
        foreach($new_where AS $field => $value)
        {
            if(!$this->is_escaped($value))
            {
                $value = addslashes($value);
            }
            $where_fields[] = "`". $field ."` = '". $value ."'";
        }
        $sql .= implode(", ", $data_fields) ." WHERE ". implode(" AND ", $where_fields);
        return $this->query($sql);
    }

    /**
     * deletes one or several rows in a table depending on the matching criteria.
     */
    public function delete($table, $where=array())
    {
        if(!is_array($where))
        {
            return false;
        }
        if(!($temp_fields = $this->show_fields($table)))
        {
            return false;
        }
        $new_where = array();
        foreach(array_keys($where) AS $field)
        {
            if(!empty($temp_fields[$field]))
            {
                $new_where[$field] = $where[$field];
            }
        }
        $sql_where = array();
        foreach($new_where AS $field => $value)
        {
            $value = $this->escape_string($value);
            $sql_where[] = "`". $field ."` = '". $value ."'";
        }
        $sql =  "DELETE FROM `". $table ."` ";
        if(!empty($sql_where))
        {
            $sql .= "WHERE ". implode(" AND ", $sql_where);
        }
        return $this->query($sql);
    }

    public function get_assoc($sql, $once=false)
    {
        $results = array();
        if(!($result = $this->query($sql)))
        {
            return $results;
        }
        while($row = $this->fetch_array($result, MYSQLI_ASSOC))
        {
            $key = array_shift($row);
            if($once && isset($results[$key]))
            {
                continue;
            }
            if(count($row) > 1)
            {
                $value = $row;
            }
            else
            {
                $value = current($row);
            }
            $results[$key] = $value;
        }
        $this->free_result($result);
        return $results;
    }

    /**
     * tells if the submitted data is escaped or not
     */
    public function is_escaped($data)
    {
        return (substr_count($data, "\'") == substr_count($data, "'"));
    }

    /**
     * returns the text of the error message from previous operation
     */
    public function last_error()
    {
        $error_code = mysqli_errno($this->link);
        $error_msg = mysqli_error($this->link);

        return "MySQL Error($error_code): $error_msg";
    }

    /**
     * escapes a string for safe insertion into a SQL query.
     */
    public function escape_string($data, $force=false)
    {
        if($force || !$this->is_escaped($data))
        {
            return @mysqli_real_escape_string($this->link, $data);
        }
        return $data;
    }

    /**
     * returns the total rows found not limited to the limit when using SQL_CALC_FOUND_ROWS in the query
     */
    public function get_found_rows()
    {
        $sql = "SELECT FOUND_ROWS()";

        return $this->get_one($sql);
    }

    /**
     * returns 1 if records are found or 0 if none are found, faster than count rows since it does not have to retreive anything
     */
    public function exists($sql)
    {
        $sql = "SELECT EXISTS(" . $sql . ")";

        return $this->get_one($sql);
    }

    public function last_insert_id()
    {
        return @mysqli_insert_id($this->link);
    }
}

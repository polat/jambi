<?php

/**
 * Class Database
 *
 * PDO interface for the database communication
 * Used by both admin and content side of the site
 */
class Database extends PDO
{
    /**
     * Database constructor.
     * Create a Database class instance and return it, if it is possible, otherwise return false.
     *
     * @param string $DB_TYPE Database type
     * @param string $DB_HOST Database host
     * @param string $DB_NAME Database name
     * @param string $DB_USER Database user
     * @param string $DB_PASS Database password
     */
    public function __construct($DB_TYPE, $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS)
    {
        try {
            parent::__construct($DB_TYPE . ':host=' . $DB_HOST . ';dbname=' . $DB_NAME, $DB_USER, $DB_PASS, [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Execute select query and fetch the data as an array.
     *
     * @access public
     * @param string $sql SQL string
     * @param array $bindValue Parameters to bind
     * @param int $fetchMode PDO Fetch mode
     * @return array
     */
    public function select($sql, array $bindValue = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $sth = $this->prepare($sql);

        foreach ($bindValue as $key => $value) {
            $sth->bindValue("$key", $value);
        }

        $sth->execute();
        return $sth->fetchAll($fetchMode);
    }

    /**
     * Execute select query and fetch one data as an array.
     *
     * @access public
     * @param string $sql An SQL string
     * @param array $bindValue Parameters to bind
     * @param int $fetchMode A PDO Fetch mode
     * @return array
     */
    public function selectOne($sql, array $bindValue = [], $fetchMode = PDO::FETCH_ASSOC)
    {
        $sth = $this->prepare($sql);

        foreach ($bindValue as $key => $value) {
            $sth->bindValue("$key", $value);
        }

        $sth->execute();
        return $sth->fetch($fetchMode);
    }

    /**
     * Execute insert query and return bool on success false on failure.
     *
     * @access public
     * @param string $table Name of table to insert into
     * @param array $data Associative array
     * @return bool True on success or false on failure.
     */
    public function insert($table, array $data)
    {
        ksort($data);
        $fieldNames = implode('`, `', array_keys($data));
        $fieldValues = ':' . implode(', :', array_keys($data));
        $sth = $this->prepare("INSERT INTO `$table` (`$fieldNames`) VALUES ($fieldValues)");

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        try {
            return $sth->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Execute insert multiple query and return bool on success false on failure.
     *
     * @access public
     * @param string $table Name of table to insert into
     * @param array $data Associative array
     * @return bool True on success or false on failure.
     */
    public function insertMultiple($table, array $data)
    {
        foreach ($data as $val) {
            ksort($val);
            $fieldNames = implode('`, `', array_keys($val));
            $fieldValues = ':' . implode(', :', array_keys($val));
            $sth = $this->prepare("INSERT INTO `$table` (`$fieldNames`) VALUES ($fieldValues)");

            foreach ($val as $key => $value) {
                $sth->bindValue(":$key", $value);
            }

            try {
                $sth->execute();
            } catch (PDOException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute update query and return bool on success, false on failure.
     *
     * @access public
     * @param string $table Name of table to insert into
     * @param array $data Associative array
     * @param string $where WHERE expression part of the query
     * @return bool True on success, false on failure
     */
    public function update($table, array $data, $where)
    {
        ksort($data);
        $fieldDetails = null;

        foreach ($data as $key => $value) {
            $fieldDetails .= "`$key`=:$key,";
        }

        $fieldDetails = rtrim($fieldDetails, ',');
        $sth = $this->prepare("UPDATE `$table` SET $fieldDetails WHERE $where");

        foreach ($data as $key => $value) {
            $sth->bindValue(":$key", $value);
        }

        return $sth->execute();
    }

    /**
     * Execute delete query and return the number of the rows that were modified.
     *
     * @access public
     * @param string $table Name of the table
     * @param string $where Where expression part of the query
     * @param integer $limit Row limit of the deletion
     * @return integer Affected Rows
     */
    public function delete($table, $where, $limit = 1)
    {
        return $this->exec("DELETE FROM `$table` WHERE $where LIMIT $limit");
    }

    /**
     * Execute delete query for multiple data and return the number of the rows that were modified.
     *
     * @access public
     * @param string $table Name of the table
     * @param string $where Where expression part of the query
     * @param integer $limit Row limit of the deletion
     * @return integer Affected Rows
     */
    public function deleteAll($table, $where, $limit = 1)
    {
        $result = null;
        $list = explode(',', $where);

        foreach ($list as $id) {
            $result = $this->exec("DELETE FROM `$table` WHERE `id` = '$id' LIMIT $limit") ? true : false;
        }

        return $result;
    }

    /**
     * Check database for $this->table and return if table exist.
     *
     * @access public
     * @param string $table Table name
     * @return bool true on table exist, false on not exist.
     */
    public function existsTable($table)
    {
        $results = $this->select("SELECT * FROM information_schema.tables WHERE table_schema = :db AND table_name = :table LIMIT 1", array('db' => DB_NAME, 'table' => $table));
        return !empty($results) ? true : false;
    }

    /**
     * Execute select query and check if table's column is exist. Return true on if exist, false on not exist.
     *
     * @access public
     * @param string $column Name of column for check if exist
     * @param string $table Name of table
     * @return bool
     */
    public function existsColumnInTable($column, $table)
    {
        $select = $this->select("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :DB_NAME AND TABLE_NAME = :table AND COLUMN_NAME = :column", array('DB_NAME' => DB_NAME, 'table' => $table, 'column' => $column));
        return empty($select) ? false : true;
    }

    /**
     * Return table's last id.
     *
     * @access public
     * @param string $table Name of table to get last id
     * @return int
     */
    public function getLastId($table)
    {
        $select = $this->selectOne("SELECT `id` FROM `$table` ORDER BY `id` DESC LIMIT 1");
        return $select['id'];
    }

    /**
     * Return database tables as array.
     *
     * @access public
     * @return array
     */
    public function getDbTables()
    {
        $query = $this->query('SHOW TABLES');
        $tables = $query->fetchAll(PDO::FETCH_COLUMN);
        $result = array();
        $i = 0;

        foreach ($tables as $table) {
            $result[$i++] = $table;
        }

        return $result;
    }
}
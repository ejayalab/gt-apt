<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Database {

    private $resource_connection = null;
    private $resource_query = null;
    var $debugOutput = FALSE; // Set "TRUE" or "1" if you want database errors outputted. Set to "2" if you also want successfull database actions outputted.
    var $debug_lastBuiltQuery = ''; // Internally: Set to last built query (not necessarily executed...)
    var $store_lastBuiltQuery = FALSE; // Set "TRUE" if you want the last built query to be stored in $debug_lastBuiltQuery independent of $this->debugOutput

    function __construct() {
        global $CFG;

        if (!isset($CFG->dbhost) or empty($CFG->dbhost)) {

            $error = get_string('dbhost_not_defined');
            print_error($error);
        }

        if (!isset($CFG->dbuser) or empty($CFG->dbuser)) {

            $error = get_string('dbuser_not_defined');
            print_error($error);
        }

        if (!isset($CFG->dbpass) or empty($CFG->dbpass)) {

            $error = get_string('dbpass_not_defined');
            print_error($error);
        }

        $this->resource_connection = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass);

        if (!$this->resource_connection) {

            $error = get_string('connection_db_failed') . $this->sql_error();
            print_error($error);
        }

        if (!isset($CFG->dbname) or empty($CFG->dbname)) {

            $error = get_string('dbname_not_defined');
            print_error($error);
        }

        if (!mysql_select_db($CFG->dbname, $this->resource_connection)) {

            $error = get_string('cant_use_db') . $CFG->dbname . $this->sql_error();
        }
    }

    /**
     * Escaping and quoting values for SQL statements.
     * Usage count/core: 100
     *
     * @param	string		Input string
     * @param	string		Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     * @return	string		Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     * @see quoteStr()
     */
    function fullQuoteStr($str, $table) {
        return '\'' . mysql_real_escape_string($str, $this->resource_connection) . '\'';
    }

    /**
     * Will fullquote all values in the one-dimensional array so they are ready to "implode" for an sql query.
     *
     * @param	array		Array with values (either associative or non-associative array)
     * @param	string		Table name for which to quote
     * @param	string/array		List/array of keys NOT to quote (eg. SQL functions) - ONLY for associative arrays
     * @return	array		The input array with the values quoted
     * @see cleanIntArray()
     */
    function fullQuoteArray($arr, $table, $noQuote = FALSE) {
        if (is_string($noQuote)) {
            $noQuote = explode(',', $noQuote);
            // sanity check
        } elseif (!is_array($noQuote)) {
            $noQuote = FALSE;
        }

        foreach ($arr as $k => $v) {
            if ($noQuote === FALSE || !in_array($k, $noQuote)) {
                $arr[$k] = $this->fullQuoteStr($v, $table);
            }
        }
        return $arr;
    }

    /**
     * Executes query
     * mysql_query() wrapper function
     * Beware: Use of this method should be avoided as it is experimentally supported by DBAL. You should consider
     *         using exec_SELECTquery() and similar methods instead.
     * Usage count/core: 1
     *
     * @param	string		Query to execute
     * @return	pointer		Result pointer / DBAL object
     */
    function sql_query($query) {
        $res = mysql_query($query, $this->resource_connection);

        if (!$res) {

            $message = get_string('execute_query_failed') . ', query: ' . $query . ' ' . $this->sql_error();
            print_debug($message);
            return false;
        }

        if ($this->debugOutput) {
            print_debug('sql_query', $query);
        }
        return $res;
    }

    /**
     * Free result memory
     * mysql_free_result() wrapper function
     * Usage count/core: 3
     *
     * @param	pointer		MySQL result pointer to free / DBAL object
     * @return	boolean		Returns TRUE on success or FALSE on failure.
     */
    function sql_free_result($res) {
        if ($res) {
            return mysql_free_result($res);
        } else {
            return FALSE;
        }
    }

    /**
     * Creates a SELECT SQL-statement
     * Usage count/core: 11
     *
     * @param	string		See exec_SELECTquery()
     * @param	string		See exec_SELECTquery()
     * @param	string		See exec_SELECTquery()
     * @param	string		See exec_SELECTquery()
     * @param	string		See exec_SELECTquery()
     * @param	string		See exec_SELECTquery()
     * @return	string		Full SQL query for SELECT
     */
    function SELECTquery($select_fields, $from_table, $where_clause = '', $groupBy = '', $orderBy = '', $limit = '') {

        // Table and fieldnames should be "SQL-injection-safe" when supplied to this function
        // Build basic query:
        $query = 'SELECT ' . $select_fields . ' FROM ' . $from_table .
                (strlen($where_clause) > 0 ? ' WHERE ' . $where_clause : '');

        // Group by:
        $query .= (strlen($groupBy) > 0 ? ' GROUP BY ' . $groupBy : '');

        // Order by:
        $query .= (strlen($orderBy) > 0 ? ' ORDER BY ' . $orderBy : '');

        // Limit:
        $query .= (strlen($limit) > 0 ? ' LIMIT ' . $limit : '');

        // Return query:
        if ($this->debugOutput || $this->store_lastBuiltQuery) {
            $this->debug_lastBuiltQuery = $query;
        }
        return $query;
    }

    /**
     * Creates and executes a SELECT SQL-statement
     * Using this function specifically allow us to handle the LIMIT feature independently of DB.
     * Usage count/core: 340
     *
     * @param	string		List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param	string		Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param	string		additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param	string		Optional GROUP BY field(s), if none, supply blank string.
     * @param	string		Optional ORDER BY field(s), if none, supply blank string.
     * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @return	pointer		MySQL result pointer / DBAL object
     */
    function exec_SELECTquery($select_fields, $from_table, $where_clause = '', $groupBy = '', $orderBy = '', $limit = '') {
        $query = $this->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
        $res = $this->sql_query($query);

        if ($this->debugOutput) {
            print_debug('exec_SELECTquery');
        }

        return $res;
    }

    function get_records($from_table, $select_fields = '*', $where_clause = '', $groupBy = '', $orderBy = '', $limit = '') {
        $records = array();
        $res = $this->exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);

        if (!empty($res)) {

            while ($row = mysql_fetch_object($res)) {

                $records[] = $row;
            }

            if (is_array($records) and count($records)) {

                return $records;
            }
        }

        return false;
    }

    /**
     * Creates an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     * Usage count/core: 4
     *
     * @param	string		See exec_INSERTquery()
     * @param	array		See exec_INSERTquery()
     * @param	string/array		See fullQuoteArray()
     * @return	string		Full SQL query for INSERT (unless $fields_values does not contain any elements in which case it will be false)
     */
    function INSERTquery($table, $fields_values, $no_quote_fields = FALSE) {

        // Table and fieldnames should be "SQL-injection-safe" when supplied to this
        // function (contrary to values in the arrays which may be insecure).
        if (is_array($fields_values) && count($fields_values)) {

            // quote and escape values
            $fields_values = $this->fullQuoteArray($fields_values, $table, $no_quote_fields);

            // Build query:
            $query = 'INSERT INTO ' . $table .
                    ' (' . implode(',', array_keys($fields_values)) . ') VALUES ' .
                    '(' . implode(',', $fields_values) . ')';

            // Return query:
            if ($this->debugOutput || $this->store_lastBuiltQuery) {
                $this->debug_lastBuiltQuery = $query;
            }
            return $query;
        }
    }

    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     * Using this function specifically allows us to handle BLOB and CLOB fields depending on DB
     * Usage count/core: 47
     *
     * @param	string		Table name
     * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param	string/array		See fullQuoteArray()
     * @return	int		Last id inserted in database
     */
    function exec_INSERTquery($table, $fields_values, $no_quote_fields = FALSE) {
        $res = $this->sql_query($this->INSERTquery($table, $fields_values, $no_quote_fields));
        if ($this->debugOutput) {
            print_debug('exec_INSERTquery');
        }

        $last_id = $this->sql_insert_id();

        return $last_id;
    }

    /**
     * Creates an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     * Usage count/core: 6
     *
     * @param	string		See exec_UPDATEquery()
     * @param	string		See exec_UPDATEquery()
     * @param	array		See exec_UPDATEquery()
     * @param	array		See fullQuoteArray()
     * @return	string		Full SQL query for UPDATE
     */
    function UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE) {
        // Table and fieldnames should be "SQL-injection-safe" when supplied to this
        // function (contrary to values in the arrays which may be insecure).
        if (is_string($where)) {
            $fields = array();
            if (is_array($fields_values) && count($fields_values)) {

                // quote and escape values
                $nArr = $this->fullQuoteArray($fields_values, $table, $no_quote_fields);

                foreach ($nArr as $k => $v) {
                    $fields[] = $k . '=' . $v;
                }
            }

            // Build query:
            $query = 'UPDATE ' . $table . ' SET ' . implode(',', $fields) .
                    (strlen($where) > 0 ? ' WHERE ' . $where : '');

            if ($this->debugOutput || $this->store_lastBuiltQuery) {
                $this->debug_lastBuiltQuery = $query;
            }
            return $query;
        } else {

            print_debug('"Where" clause argument for UPDATE query was not a string in $this->UPDATEquery()');
            return false;
        }
    }

    /**
     * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     * Using this function specifically allow us to handle BLOB and CLOB fields depending on DB
     * Usage count/core: 50
     *
     * @param	string		Database tablename
     * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param	string/array		See fullQuoteArray()
     * @return	pointer		MySQL result pointer / DBAL object
     */
    function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE) {
        $res = $this->sql_query($this->UPDATEquery($table, $where, $fields_values, $no_quote_fields));
        if ($this->debugOutput) {
            print_debug('exec_UPDATEquery');
        }
        
        if(!empty($res)){
            $message = get_string('updated_rows') . ' ' . $this->sql_affected_rows();
            print_debug($message);
        }
        
        return $res;
    }

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
     * mysql_affected_rows() wrapper function
     * Usage count/core: 1
     *
     * @return	integer		Number of rows affected by last query
     */
    function sql_affected_rows() {
        return mysql_affected_rows($this->resource_connection);
    }

    /**
     * Get the ID generated from the previous INSERT operation
     * mysql_insert_id() wrapper function
     * Usage count/core: 13
     *
     * @return	integer		The uid of the last inserted record.
     */
    function sql_insert_id() {
        return mysql_insert_id($this->resource_connection);
    }

    function sql_error() {
        return ', mysql error: ' . mysql_error($this->resource_connection);
    }

    function debug_enable() {

        $this->debugOutput = TRUE;
        $this->store_lastBuiltQuery = TRUE;
    }

}

?>

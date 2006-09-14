<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Urs Weiss (urs@tupa-dns.org)
*  All rights reserved
*
*  This file is part of TUPA.
*
*  TUPA is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  TUPA is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * DB functions
 *
 * @package 	TUPA
 * @author  	Kaspar Skarhoj <kasperYYYY@typo3.com>
 * @author	Urs Weiss <urs@tupa-dns.org>
 */
 class lib_DB {

 		// Debug:
//	var $debugOutput = FALSE;		// Set "TRUE" if you want database errors outputted.
//	var $debug_lastBuiltQuery = '';		// Internally: Set to last built query (not necessarily executed...)
//	var $store_lastBuiltQuery = FALSE;	// Set "TRUE" if you want the last built query to be stored in $debug_lastBuiltQuery independent of $this->debugOutput

		// Default link identifier:
	var $link;


	/**
	 * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 * Using this function specifically allows us to handle BLOB and CLOB fields depending on DB
	 *
	 * @param	string		Table name
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @return	pointer		MySQL result pointer / DBAL object
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function exec_INSERTquery($table,$fields_values)	{
		$res = mysql_query($this->INSERTquery($table,$fields_values), $this->link);
//		if ($this->debugOutput)	$this->debug('exec_INSERTquery');
		return $res;
	}

	/**
	 * Executes a simple INSERT-query. Used in installer to insert multiple rows with one SQL-query.
	 *
	 * @param	string		Table name
	 * @param	string		Fields to insert
	 * @param	string		Values to insert
	 * @return	pointer		MySQL result pointer / DBAL object
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function exec_simpleINSERTquery($table, $fields, $values)	{
		$res = mysql_query('INSERT INTO '. $table .' '. $fields .' VALUES '. $values, $this->link);
//		if ($this->debugOutput)	$this->debug('exec_INSERTquery');
		return $res;
	}

	/**
	 * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
	 * Using this function specifically allow us to handle BLOB and CLOB fields depending on DB
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @return	pointer		MySQL result pointer
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function exec_UPDATEquery($table,$where,$fields_values)	{
		$res = mysql_query($this->UPDATEquery($table,$where,$fields_values), $this->link);
//		if ($this->debugOutput)	$this->debug('exec_UPDATEquery');
		return $res;
	}

	/**
	 * Creates and executes a DELETE SQL-statement for $table where $where-clause
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
	 * @return	pointer		MySQL result pointer
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function exec_DELETEquery($table,$where)	{
		$res = mysql_query($this->DELETEquery($table,$where), $this->link);
//		if ($this->debugOutput)	$this->debug('exec_DELETEquery');
		return $res;
	}

	/**
	 * Creates and executes a SELECT SQL-statement
	 * Using this function specifically allow us to handle the LIMIT feature independently of DB.
	 *
	 * @param	string		List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
	 * @param	string		Table(s) from which to select. This is what comes right after "FROM ...". Required value.
	 * @param	string		Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	pointer		MySQL result pointer
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')	{
		$res = mysql_query($this->SELECTquery($select_fields,$from_table,$where_clause,$groupBy,$orderBy,$limit), $this->link);
//		if ($this->debugOutput)	$this->debug('exec_SELECTquery');
		return $res;
	}

	/**
	 * Creates and executes a SELECT query, selecting fields ($select) from two/three tables joined
	 * Use $mm_table together with $local_table or $foreign_table to select over two tables. Or use all three tables to select the full MM-relation.
	 * The JOIN is done with [$local_table].uid <--> [$mm_table].uid_local  / [$mm_table].uid_foreign <--> [$foreign_table].uid
	 * The function is very useful for selecting MM-relations between tables adhering to the MM-format used by TCE (TYPO3 Core Engine). See the section on $TCA in Inside TYPO3 for more details.
	 *
	 * @param	string		Field list for SELECT
	 * @param	string		Tablename, local table
	 * @param	string		Tablename, relation table
	 * @param	string		Tablename, foreign table
	 * @param	string		Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	pointer		MySQL result pointer / DBAL object
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see exec_SELECTquery()
	 */
	function exec_SELECT_mm_query($select,$local_table,$mm_table,$foreign_table,$whereClause='',$groupBy='',$orderBy='',$limit='')	{
		$mmWhere = $local_table ? $local_table.'.uid='.$mm_table.'.uid_local' : '';
		$mmWhere.= ($local_table AND $foreign_table) ? ' AND ' : '';
		$mmWhere.= $foreign_table ? $foreign_table.'.uid='.$mm_table.'.uid_foreign' : '';
		return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					$select,
					($local_table ? $local_table.',' : '').$mm_table.($foreign_table ? ','.$foreign_table : ''),
					$mmWhere.' '.$whereClause,		// whereClauseMightContainGroupOrderBy
					$groupBy,
					$orderBy,
					$limit
				);
	}

	/**
	 * Executes a select based on input query parts array
	 *
	 * Usage: 9
	 *
	 * @param	array		Query parts array
	 * @return	pointer		MySQL select result pointer
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see exec_SELECTquery()
	 */
	function exec_SELECT_queryArray($queryParts)	{
		return $this->exec_SELECTquery(
					$queryParts['SELECT'],
					$queryParts['FROM'],
					$queryParts['WHERE'],
					$queryParts['GROUPBY'],
					$queryParts['ORDERBY'],
					$queryParts['LIMIT']
				);
	}

	/**
	 * Creates and executes a SELECT SQL-statement AND traverse result set and returns array with records in.
	 *
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		If set, the result array will carry this field names value as index. Requires that field to be selected of course!
	 * @return	array		Array of rows.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function exec_SELECTgetRows($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='',$uidIndexField='')	{
		$res = $this->exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy,$orderBy,$limit);
//		if ($this->debugOutput)	$this->debug('exec_SELECTquery');

		unset($output);
		if (!mysql_error())	{
			$output = array();

			if ($uidIndexField)	{
				while($tempRow = $this->sql_fetch_assoc($res))	{
					$output[$tempRow[$uidIndexField]] = $tempRow;
				}
			} else {
				while($output[] = $this->sql_fetch_assoc($res));
				array_pop($output);
			}
		}
		return $output;
	}




	/**
	 * Gets list of id's
	 *
	 * @param	string		Field list for SELECT
	 * @param	string		Tablename, local table
	 * @param	string		Field of where clause
	 * @param	string		List for IN statement in where clause
	 * @return	array		Array with list and count.
	 */
	function exec_SELECTgetIdList($select_fields, $from_table, $where_field, $where_list, $returnArrList=false) {
		$where_clause = '';

		if ($where_field != '' && $where_list != '') {
			$where_clause = $where_field .' IN (' .lib_DB::fullQuoteStrList($where_list) .')';
		}
		$res = $this->exec_SELECTquery($select_fields, $from_table, $where_clause);

		$id = array();
		if (!mysql_error())	{
			$idListArr = array();
			while ($row = mysql_fetch_array($res)) {
				$idListArr[] = $row[0];
			}
			$id['count'] = count($idListArr);
			if ($returnArrList) {
				$id['list'] = $idListArr;
			} else {
				$id['list'] = implode(',', $idListArr);
			}
		} else {
			$id['count'] = '';
			$id['list'] = '';
		}
		return $id;
	}

	/**
	 * Gets username of user.
	 *
	 * @param	integer		User id
	 * @return	string		Firstname, name
	 */
	function exec_SELECTgetUserName($id) {
		$res = $this->exec_SELECTquery('username', 'users', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
		$row = mysql_fetch_assoc($res);
		lib_div::stripSlashesOnArray($row);
		lib_div::htmlspecialcharOnArray($row);
		$userName = $row['username'];

		return $userName;
	}

	/**
	 * Gets full name of user.
	 *
	 * @param	integer		User id
	 * @return	string		Firstname, name
	 */
	function exec_SELECTgetName($id) {
		$res = $this->exec_SELECTquery('firstname, name', 'users', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
		$row = mysql_fetch_assoc($res);
		lib_div::stripSlashesOnArray($row);
		lib_div::htmlspecialcharOnArray($row);
		$userName = $row['firstname'] .' '. $row['name'];

		return $userName;
	}

	/**
	 * Gets name of group.
	 *
	 * @param	integer		Group id
	 * @return	string		Groupname
	 */
	function exec_SELECTgetGroupName($id) {
		$res = $this->exec_SELECTquery('name', 'groups', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
		$row = mysql_fetch_assoc($res);
		lib_div::stripSlashesOnArray($row);
		lib_div::htmlspecialcharOnArray($row);
		return  $row['name'];
	}

	/**
	 * Gets name of template.
	 *
	 * @param	integer		Template id
	 * @return	string		Templatename
	 */
	function exec_SELECTgetTemplateName($id) {
		$res = $this->exec_SELECTquery('name', 'templates', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
		$row = mysql_fetch_assoc($res);
		lib_div::stripSlashesOnArray($row);
		lib_div::htmlspecialcharOnArray($row);
		return  $row['name'];
	}

	/**
	 * Gets name of domain.
	 *
	 * @param	integer		Domain id
	 * @return	string		Domainname
	 */
	function exec_SELECTgetDomainName($id) {
		$res = $this->exec_SELECTquery('*', 'domains', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
		$row = mysql_fetch_assoc($res);
		return  $row['name'];
	}

	/**
	 * Gets group id from users id.
	 *
	 * @param	integer		User id
	 * @return	string		Group id
	 */
	function exec_SELECTgetGroupIdOfUser($id='') {
		if (!$id) $id = $_SESSION['uid'];
		$res = $this->exec_SELECTquery('grp_id', 'users', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
		$row = mysql_fetch_assoc($res);
		return  $row['grp_id'];
	}

	/**
	 * Gets user id's of group id.
	 *
	 * @param	integer		Group id
	 * @return	array		User id's
	 */
	function exec_SELECTgetUserIdsOfGroup($id) {
		$idArr = array();
		$res = $this->exec_SELECTquery('id', 'users', 'grp_id='. lib_DB::fullQuoteStr($id), '', '', '');
		while ($row = mysql_fetch_assoc($res)) {
			$idArr[] = $row['id'];
		}
		return implode(',', $idArr);
	}



	/**
	 * Gets group limit of given column.
	 *
	 * @param	integer		Group id
	 * @param	string		column name
	 * @return	array		row value
	 */
	function exec_SELECTgetLimitOfGroup($id, $field) {
		$res = $this->exec_SELECTquery($field, 'groups', 'id='. lib_DB::fullQuoteStr($id), '', '', '');
		$row = mysql_fetch_assoc($res);
		return  $row[$field];
	}



	/**
	 * Gets user limit of given column.
	 *
	 * @param	integer		User id
	 * @param	string		column name
	 * @return	array		row value
	 */
	function exec_SELECTgetLimitOfUser($id='', $field) {
		if (!$id) $id = $_SESSION['uid'];
		$res = $this->exec_SELECTquery($field, 'users', 'id='. lib_DB::fullQuoteStr($id), '', '', '');
		$row = mysql_fetch_assoc($res);
		return  $row[$field];
	}



	/**
	 * Adds an additional column to table
	 *
	 * @param	string		Table name
	 * @param	string		column name
	 * @param	string		column type
	 * @param	string		additional stuff
	 * @return	void
	 */
	function addNewColumn($table, $name, $type, $addi) {
		$cmd = 'ALTER TABLE '. $table .' ADD '. $name .' '. $type .' '. $addi;
		$this->sql_query($cmd);
		if (mysql_error()) return false;
		return true;
	}


	/**
	 * Removes an column from table
	 *
	 * @param	string		Table name
	 * @param	string		column name
	 * @return	void
	 */
	function delColumn($table, $name) {
		$cmd = 'ALTER TABLE '.  $table .' DROP COLUMN '. $name;
		$this->sql_query($cmd);
		if (mysql_error()) return false;
		return true;
	}


	/**
	 * Renames a table in DB
	 *
	 * @param	string		Table name
	 * @param	string		column name
	 * @return	void
	 */
	function renameTable($source, $target) {
		$cmd = 'RENAME TABLE '.  $source .' TO '. $target;
		$this->sql_query($cmd);
		if (mysql_error()) return false;
		return true;
	}


	/**
	 * Changes field names in different tables.
	 *
	 * @param	array		Array with field changes
	 * @param	boolean	force (deletes column if it exists)
	 * @return	void
	 */
	function changeFieldNames($changeArr, $force) {
		foreach ($changeArr as $table => $fields) {
			if ($this->admin_check_table_exists($table)) {
				foreach ($fields as $field) {
					if ($this->admin_check_column_exists($table, $field[0])) {
						if ($this->admin_check_column_exists($table, $field[1]) && $force) {
							if (!$this->delColumn($table, $field[1])) {
								return false;
							}
						}
						$cmd = 'ALTER TABLE '. $table .' CHANGE '. $field[0] .' '. $field[1] .' '. $field[2];
						$this->sql_query($cmd);
						if (mysql_error()) return false;
					} else return false;
				}
			} else return false;
		}
		return true;
	}


	/**************************************
	 *
	 * Query building
	 *
	 **************************************/

	/**
	 * Creates an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 *
	 * @param	string		See exec_INSERTquery()
	 * @param	array		See exec_INSERTquery()
	 * @return	string		Full SQL query for INSERT (unless $fields_values does not contain any elements in which case it will be false)
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @depreciated			use exec_INSERTquery() instead if possible!
	 */
	function INSERTquery($table,$fields_values)	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function (contrary to values in the arrays which may be insecure).
		if (is_array($fields_values) && count($fields_values))	{

				// Add slashes old-school:
			foreach($fields_values as $k => $v)	{
				$fields_values[$k] = $this->fullQuoteStr($fields_values[$k], $table);
			}

				// Build query:
			$query = 'INSERT INTO '.$table.'
				(
					'.implode(',
					',array_keys($fields_values)).'
				) VALUES (
					'.implode(',
					',$fields_values).'
				)';

				// Return query:
//			if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
			return $query;
		}
	}

	/**
	 * Creates an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
	 *
	 * @param	string		See exec_UPDATEquery()
	 * @param	string		See exec_UPDATEquery()
	 * @param	array		See exec_UPDATEquery()
	 * @return	string		Full SQL query for UPDATE (unless $fields_values does not contain any elements in which case it will be false)
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @depreciated			use exec_UPDATEquery() instead if possible!
	 */
	function UPDATEquery($table,$where,$fields_values)	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function (contrary to values in the arrays which may be insecure).
		if (is_string($where))	{
			if (is_array($fields_values) && count($fields_values))	{

					// Add slashes old-school:
				$nArr = array();
				foreach($fields_values as $k => $v)	{
					if (is_null($v)) {
						$nArr[] = $k.'=NULL';
					} else {
						$nArr[] = $k.'='.$this->fullQuoteStr($v, $table);
					}
				}

					// Build query:
				$query = 'UPDATE '.$table.'
					SET
						'.implode(',
						',$nArr).
					(strlen($where)>0 ? '
					WHERE
						'.$where : '');

					// Return query:
//				if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
				return $query;
			}
		} else {
			die('<strong>TUPA Fatal Error:</strong> "Where" clause argument for UPDATE query was not a string in $this->UPDATEquery() !');
		}
	}

	/**
	 * Creates a DELETE SQL-statement for $table where $where-clause
	 *
	 * @param	string		See exec_DELETEquery()
	 * @param	string		See exec_DELETEquery()
	 * @return	string		Full SQL query for DELETE
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @depreciated			use exec_DELETEquery() instead if possible!
	 */
	function DELETEquery($table,$where)	{
		if (is_string($where))	{

				// Table and fieldnames should be "SQL-injection-safe" when supplied to this function
			$query = 'DELETE FROM '.$table.
				(strlen($where)>0 ? '
				WHERE
					'.$where : '');

//			if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
			return $query;
		} else {
			die('<strong>TUPA Fatal Error:</strong> "Where" clause argument for DELETE query was not a string in $this->DELETEquery() !');
		}
	}

	/**
	 * Creates a SELECT SQL-statement
	 *
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @param	string		See exec_SELECTquery()
	 * @return	string		Full SQL query for SELECT
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @depreciated			use exec_SELECTquery() instead if possible!
	 */
	function SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function
			// Build basic query:
		$query = 'SELECT '.$select_fields.'
			FROM '.$from_table.
			(strlen($where_clause)>0 ? '
			WHERE
				'.$where_clause : '');

			// Group by:
		if (strlen($groupBy)>0)	{
			$query.= '
			GROUP BY '.$groupBy;
		}
			// Order by:
		if (strlen($orderBy)>0)	{
			$query.= '
			ORDER BY '.$orderBy;
		}
			// Group by:
		if (strlen($limit)>0)	{
			$query.= '
			LIMIT '.$limit;
		}

			// Return query:
//		if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
		return $query;
	}

	/**
	 * Returns a WHERE clause that can find a value ($value) in a list field ($field)
	 * For instance a record in the database might contain a list of numbers, "34,234,5" (with no spaces between). This query would be able to select that record based on the value "34", "234" or "5" regardless of their positioni in the list (left, middle or right).
	 * Is nice to look up list-relations to records or files in TYPO3 database tables.
	 *
	 * @param	string		Field name
	 * @param	string		Value to find in list
	 * @param	string		Table in which we are searching (for DBAL detection of quoteStr() method)
	 * @return	string		WHERE clause for a query
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
/*	function listQuery($field, $value, $table)	{
		$command = $this->quoteStr($value, $table);
		$where = '('.$field.' LIKE \'%,'.$command.',%\' OR '.$field.' LIKE \''.$command.',%\' OR '.$field.' LIKE \'%,'.$command.'\' OR '.$field.'=\''.$command.'\')';
		return $where;
	}*/

	/**
	 * Returns a WHERE clause which will make an AND search for the words in the $searchWords array in any of the fields in array $fields.
	 *
	 * @param	array		Array of search words
	 * @param	array		Array of fields
	 * @param	string		Table in which we are searching (for DBAL detection of quoteStr() method)
	 * @return	string		WHERE clause for search
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function searchQuery($searchWords,$fields,$table)	{
		$queryParts = array();

		foreach($searchWords as $sw)	{
			$like=' LIKE \'%'.$this->quoteStr($sw, $table).'%\'';
			$queryParts[] = $table.'.'.implode($like.' OR '.$table.'.',$fields).$like;
		}
		$query = '('.implode(') AND (',$queryParts).')';
		return $query ;
	}


	/**
	 * Generates the search query for show pages
	 *
	 * @param	array		Config array
	 * @param	array		Array of fields to search in text search
	 * @return	array		Array to use with exec_SELECT_queryArrray() later
	 */
	function genShowSearchQuery($conf, $searchFields) {
		global $TUPA_CONF_VARS, $USER;
		$queryParts = array();
		$sqlArr = array();

		foreach ($searchFields as $key => $value) {
			switch ($key) {
				case 'sfield':
					if (isset($value)) {
						$searchWords[] = $value;
						$fields = explode(',', $TUPA_CONF_VARS[strtoupper($conf['csite'])]['showFields']);
						$queryParts[] = $this->searchQuery($searchWords, $fields, $conf['csite']);
					}
					break;
				case 'sgroup':
					// only search for group if no user is set
					if ((!isset($searchFields['suser']) || $searchFields['suser'] == 0) && isset($value) && $value > 0) {
						if ($conf['csite'] == 'users') {
							$queryParts[] = 'grp_id='. $this->fullQuoteStr($value);
						} else {
							$userIdsList = $this->exec_SELECTgetUserIdsOfGroup($value);
							$queryParts[] = 'usr_id IN ('. ($userIdsList ? $userIdsList : '\'\'') .')';
						}
					}
					break;
				case 'suser':
					if (isset($value) && $value > 0) {
						$queryParts[] = 'usr_id='. $this->fullQuoteStr($value);
					}
					break;
			}
		}

		// Set char search if no textsearch set
		if (!isset($searchFields['sfield'])) {
			switch ($conf['show']['char']) {
				case 'ALL': if($conf['csite'] == 'domains') $queryParts[] = 'NOT (name RLIKE "in\-addr\.arpa$")'; break;
				case '#':  $queryParts[] = 'name RLIKE "^[0-9]" AND NOT (name RLIKE "in\-addr\.arpa$")'; break;
				case 'REV': $queryParts[] = 'name RLIKE "in\-addr\.arpa$"'; break;
				default: $queryParts[] = 'name LIKE '. $this->fullQuoteStr($conf['show']['char'] .'%'); break;
			}
		}

		// Set special sql query parts for domains to get usr_id
		if ($conf['csite'] == 'domains') {
			$sqlArr['SELECT'] = 'domains.*, domain_owners.usr_id';
			$sqlArr['FROM'] = $conf['csite'] .', domain_owners';
			$queryParts[] = 'domains.id = domain_owners.dom_id';
		} else {
			$sqlArr['SELECT'] = '*';
			$sqlArr['FROM'] = $conf['csite'];
		}

		// Set list of user ids the loggend in user is allowed to see
		if (!$USER->hasPerm($conf['csite'] .'_admin')) {
			if ($USER->hasPerm($conf['csite'] .'_show_group')) {
				$userIdsList =  $this->exec_SELECTgetUserIdsOfGroup($this->exec_SELECTgetGroupIdOfUser($_SESSION['uid']));
			} else {
				$userIdsList = $_SESSION['uid'];
			}
			$queryParts[] = ($conf['csite'] == 'users' || $conf['csite'] == 'groups' ? 'id' : 'usr_id') .' IN ('. lib_DB::fullQuoteStrList($userIdsList) .')';
		}
		$sqlArr['WHERE'] = implode(' AND ', $queryParts);

		return $sqlArr;
	}
















	/**************************************
	 *
	 * Various helper functions
	 *
	 * Functions recommended to be used for
	 * - escaping values,
	 * - cleaning lists of values,
	 * - stripping of excess ORDER BY/GROUP BY keywords
	 *
	 **************************************/

	/**
	 * Escaping and quoting values for SQL statements.
	 *
	 * @param	string		Input string
	 * @return	string		Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see quoteStr()
	 */
	function fullQuoteStr($str) {
		return '\''.addslashes($str).'\'';
	}

	/**
	 * Escaping and quoting a list of values values for "WHERE x IN (list)" SQL statements.
	 *
	 * @param	string		Input string (sist of values)
	 * @return	string		Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see quoteStr()
	 */
	function fullQuoteStrList($list) {
		$quotedValueArr = array();
		$valueArr = lib_div::trimExplode(',', $list);
		foreach ($valueArr as $str) {
			$quotedValueArr[] = '\''.addslashes($str).'\'';
		}
		return implode(',', $quotedValueArr);
	}

	/**
	 * Substitution for PHP function "addslashes()"
	 * Use this function instead of the PHP addslashes() function when you build queries - this will prepare your code for DBAL.
	 * NOTICE: You must wrap the output of this function in SINGLE QUOTES to be DBAL compatible. Unless you have to apply the single quotes yourself you should rather use ->fullQuoteStr()!
	 *
	 * @param	string		Input string
	 * @return	string		Output string; Quotes (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see quoteStr()
	 */
	function quoteStr($str)	{
		return addslashes($str);
	}

	/**
	 * Will convert all values in the one-dimentional array to integers.
	 * Useful when you want to make sure an array contains only integers before imploding them in a select-list.
	 *
	 * @param	array		Array with values
	 * @return	array		The input array with all values passed through intval()
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see cleanIntList()
	 */
	function cleanIntArray($arr)	{
		foreach($arr as $k => $v)	{
			$arr[$k] = intval($arr[$k]);
		}
		return $arr;
	}

	/**
	 * Will force all entries in the input comma list to integers
	 * Useful when you want to make sure a commalist of supposed integers really contain only integers; You want to know that when you don't trust content that could go into an SQL statement.
	 *
	 * @param	string		List of comma-separated values which should be integers
	 * @return	string		The input list but with every value passed through intval()
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see cleanIntArray()
	 */
	function cleanIntList($list)	{
		return implode(',',lib_div::intExplode(',',$list));
	}

	/**
	 * Removes the prefix "ORDER BY" from the input string.
	 * This function is used when you call the exec_SELECTquery() function and want to pass the ORDER BY parameter by can't guarantee that "ORDER BY" is not prefixed.
	 * Generally; This function provides a work-around to the situation where you cannot pass only the fields by which to order the result.
	 * Usage count/core: 11
	 *
	 * @param	string		eg. "ORDER BY title, uid"
	 * @return	string		eg. "title, uid"
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see exec_SELECTquery(), stripGroupBy()
	 */
/*	function stripOrderBy($str)	{
		return preg_replace('/^ORDER[[:space:]]+BY[[:space:]]+/i','',trim($str));
	}*/

	/**
	 * Removes the prefix "GROUP BY" from the input string.
	 * This function is used when you call the SELECTquery() function and want to pass the GROUP BY parameter by can't guarantee that "GROUP BY" is not prefixed.
	 * Generally; This function provides a work-around to the situation where you cannot pass only the fields by which to order the result.
	 *
	 * @param	string		eg. "GROUP BY title, uid"
	 * @return	string		eg. "title, uid"
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 * @see exec_SELECTquery(), stripOrderBy()
	 */
/*	function stripGroupBy($str)	{
		return preg_replace('/^GROUP[[:space:]]+BY[[:space:]]+/i','',trim($str));
	}*/

	/**
	 * Takes the last part of a query, eg. "... uid=123 GROUP BY title ORDER BY title LIMIT 5,2" and splits each part into a table (WHERE, GROUPBY, ORDERBY, LIMIT)
	 * Work-around function for use where you know some userdefined end to an SQL clause is supplied and you need to separate these factors.
	 *
	 * @param	string		Input string
	 * @return	array
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
/*	function splitGroupOrderLimit($str)	{
		$str = ' '.$str;	// Prepending a space to make sure "[[:space:]]+" will find a space there for the first element.
			// Init output array:
		$wgolParts = array(
			'WHERE' => '',
			'GROUPBY' => '',
			'ORDERBY' => '',
			'LIMIT' => ''
		);

			// Find LIMIT:
		if (preg_match('/^(.*)[[:space:]]+LIMIT[[:space:]]+([[:alnum:][:space:],._]+)$/i',$str,$reg))	{
			$wgolParts['LIMIT'] = trim($reg[2]);
			$str = $reg[1];
		}

			// Find ORDER BY:
		if (preg_match('/^(.*)[[:space:]]+ORDER[[:space:]]+BY[[:space:]]+([[:alnum:][:space:],._]+)$/i',$str,$reg))	{
			$wgolParts['ORDERBY'] = trim($reg[2]);
			$str = $reg[1];
		}

			// Find GROUP BY:
		if (preg_match('/^(.*)[[:space:]]+GROUP[[:space:]]+BY[[:space:]]+([[:alnum:][:space:],._]+)$/i',$str,$reg))	{
			$wgolParts['GROUPBY'] = trim($reg[2]);
			$str = $reg[1];
		}

			// Rest is assumed to be "WHERE" clause:
		$wgolParts['WHERE'] = $str;

		return $wgolParts;
	}*/















	/**************************************
	 *
	 * MySQL wrapper functions
	 * (For use in your applications)
	 *
	 **************************************/

	/**
	 * Executes query
	 * mysql() wrapper function
	 * DEPRECIATED - use exec_* functions from this class instead!
	 *
	 * @param	string		Database name
	 * @param	string		Query to execute
	 * @return	pointer		Result pointer
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
/*	function sql($db,$query)	{
		$res = mysql_query($query, $this->link);
		if ($this->debugOutput)	$this->debug('sql');
		return $res;
	}*/

	/**
	 * Executes query
	 * mysql_query() wrapper function
	 *
	 * @param	string		Query to execute
	 * @return	pointer		Result pointer / DBAL object
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_query($query)	{
		$res = mysql_query($query, $this->link);
//		if ($this->debugOutput)	$this->debug('sql_query');
		return $res;
	}

	/**
	 * Returns the error status on the last sql() execution
	 * mysql_error() wrapper function
	 *
	 * @return	string		MySQL error string.
	 */
/*	function sql_error()	{
		return mysql_error($this->link);
	}*/

	/**
	 * Returns the number of selected rows.
	 * mysql_num_rows() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query)
	 * @return	integer		Number of resulting rows.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_num_rows($res)	{
		return mysql_num_rows($res);
	}

	/**
	 * Returns an associative array that corresponds to the fetched row, or FALSE if there are no more rows.
	 * mysql_fetch_assoc() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query)
	 * @return	array		Associative array of result row.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_fetch_assoc($res)	{
		return mysql_fetch_assoc($res);
	}

	/**
	 * Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
	 * The array contains the values in numerical indices.
	 * mysql_fetch_row() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query)
	 * @return	array		Array with result rows.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_fetch_row($res)	{
		return mysql_fetch_row($res);
	}

	/**
	 * Free result memory
	 * mysql_free_result() wrapper function
	 *
	 * @param	pointer		MySQL result pointer to free
	 * @return	boolean	Returns TRUE on success or FALSE on failure.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_free_result($res)	{
		return mysql_free_result($res);
	}

	/**
	 * Get the ID generated from the previous INSERT operation
	 * mysql_insert_id() wrapper function
	 *
	 * @return	integer		The uid of the last inserted record.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_insert_id()	{
		return mysql_insert_id($this->link);
	}

	/**
	 * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
	 * mysql_affected_rows() wrapper function
	 *
	 * @return	integer		Number of rows affected by last query
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_affected_rows()	{
		return mysql_affected_rows($this->link);
	}

	/**
	 * Move internal result pointer
	 * mysql_data_seek() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query) / DBAL object
	 * @param	integer		Seek result number.
	 * @return	boolean		Returns TRUE on success or FALSE on failure.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
/*	function sql_data_seek($res,$seek)	{
		return mysql_data_seek($res,$seek);
	}*/

	/**
	 * Get the type of the specified field in a result
	 * mysql_field_type() wrapper function
	 *
	 * @param	pointer		MySQL result pointer (of SELECT query) / DBAL object
	 * @param	integer		Field index.
	 * @return	string		Returns the name of the specified field index
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
/*	function sql_field_type($res,$pointer)	{
		return mysql_field_type($res,$pointer);
	}*/


	 /**
	 * Open a (persistent) connection to a MySQL server
	 * mysql_pconnect() wrapper function
	 *
	 * @param	string		Database host IP/domain
	 * @param	string		Username to connect with.
	 * @param	string		Password to connect with.
	 * @return	pointer		Returns a positive MySQL persistent link identifier on success, or FALSE on error.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_pconnect($TUPA_db_host, $TUPA_db_port, $TUPA_db_username, $TUPA_db_password)	{
		if ($GLOBALS['TUPA_CONF_VARS']['SYS']['noPconnect'])	{
			$this->link = @mysql_connect($TUPA_db_host .':'. $TUPA_db_port, $TUPA_db_username, $TUPA_db_password);
		} else {
			$this->link = @mysql_pconnect($TUPA_db_host .':'. $TUPA_db_port, $TUPA_db_username, $TUPA_db_password);
		}
		return $this->link;
	}

	/**
	 * Select a MySQL database
	 * mysql_select_db() wrapper function
	 *
	 * @param	string		Database to connect to.
	 * @return	boolean	Returns TRUE on success or FALSE on failure.
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function sql_select_db($TUPA_db)	{
		return mysql_select_db($TUPA_db, $this->link);
	}






	/**************************************
	 *
	 * SQL admin functions
	 * (For use in the Install Tool)
	 *
	 **************************************/

	/**
	 * Listing databases from current MySQL connection. NOTICE: It WILL try to select those databases and thus break selection of current database.
	 * Use in Install Tool only!
	 *
	 * @return	array		Each entry represents a database name
	 */
	function admin_get_dbs()	{
		$dbArr = array();
		$db_list = mysql_list_dbs($this->link);
		while ($row = mysql_fetch_object($db_list)) {
			if ($this->sql_select_db($row->Database))	{
				$dbArr[] = $row->Database;
			}
		}
		return $dbArr;
	}

	/**
	 * Returns the list of tables from the default database, TYPA_db
	 *
	 * @return	array		Tables in an array (tablename is in both key and value)
	 */
	function admin_get_tables()	{
		$whichTables = array();
		$tables_result = mysql_list_tables(TUPA_db, $this->link);
		if (!mysql_error())	{
			while ($theTable = mysql_fetch_assoc($tables_result)) {
				$whichTables[current($theTable)] = current($theTable);
			}
		}
		return $whichTables;
	}

	/**
	 * Checks if a table exists in database
	 *
	 * @param 	string		List of tables to check
	 * @return	boolean	Table exists or not
	 */
	function admin_check_table_exists($tableNames)	{
		$tableNames = lib_div::trimExplode(',', $tableNames);

		foreach ($tableNames as $tableName) {
			$res = mysql_query('DESCRIBE '. $tableName, $this->link);
			if (mysql_error()) return false;
		}
		return true;
	}


	/**
	 * Checks if a column exists in table
	 *
	 * @param 	string		Table name
	 * @param 	string		List of columns to check
	 * @return	boolean	Column exists or not
	 */
	function admin_check_column_exists($table, $columns) {
		$columns = lib_div::trimExplode(',', $columns);

		foreach ($columns as $column) {
			$res = $this->exec_SELECTquery($column, $table, '', '', '', 1);
			if (mysql_error()) return false;
		}
		return true;
	}


	/**
	 * Returns information about each field in the $table
	 * This function is important not only for the Install Tool but probably for DBALs as well since they might need to look up table specific information in order to construct correct queries. In such cases this information should probably be cached for quick delivery
	 *
	 * @param	string		Table name
	 * @return	array		Field information in an associative array with fieldname => field row
	 */
	function admin_get_fields($tableName)	{
		$output = array();

		$columns_res = mysql_query('SHOW columns FROM '.$tableName, $this->link);
		while($fieldRow = mysql_fetch_assoc($columns_res))	{
			if ($fieldRow["Default"] == 'NULL') { $fieldRow['Default'] = 'isNullField'; }
			$output[$fieldRow['Field']] = $fieldRow;
		}

		return $output;
	}

	/**
	 * Returns information about each index key in the $table
	 * In a DBAL this should look up the right handler for the table and return compatible information
	 *
	 * @param	string		Table name
	 * @return	array		Key information in a numeric array
	 */
	function admin_get_keys($tableName)	{
		$output = array();

		$keyRes = mysql_query('SHOW keys FROM '.$tableName, $this->link);
		while($keyRow = mysql_fetch_assoc($keyRes))	{
			$output[] = $keyRow;
		}

		return $output;
	}

	/**
	 * mysql() wrapper function, used by the Install Tool queries regarding management of the database!
	 *
	 * @param	string		Query to execute
	 * @return	pointer		Result pointer
	 */
	function admin_query($query)	{
		$res = mysql_query($query, $this->link);
	//	if ($this->debugOutput)	$this->debug('admin_query');
		return $res;
	}



	/******************************
	 *
	 * Debugging
	 *
	 ******************************/

	/**
	 * Debug function: Outputs error if any
	 *
	 * @param	string		Function calling debug()
	 * @return	void
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
/*	function debug($func)	{

		$error = $this->sql_error();
		if ($error)		{
			echo t3lib_div::view_array(array(
				'caller' => 't3lib_DB::'.$func,
				'ERROR' => $error,
				'lastBuiltQuery' => $this->debug_lastBuiltQuery,
				'debug_backtrace' => function_exists('debug_backtrace') ? next(debug_backtrace()) : 'N/A'
			));
		}
	}*/
 }
?>
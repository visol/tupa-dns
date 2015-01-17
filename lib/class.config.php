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
 * Read/Write to config/config_site.inc.php
 *
 * @package 	TUPA
 * @author 	Kasper Skarhoj <kasperYYYY@typo3.com>
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

class lib_config {
	var $updateIdentity = '';					// Set to string which identifies the script using this class.
	var $deletedPrefixKey = 'zzz_deleted_';		// Prefix used for tables/fields when deleted/renamed.
	var $config_editPointToken = 'INSTALLER EDIT POINT TOKEN - all lines after this points may be changed by the installer!';		// If set and addLinesOnly is disabled, lines will be change only if they are after this token (on a single line!) in the file
	var $allowUpdateConfig = 1;					// If true, this class will allow the user to update the config_site.inc.php file.
	var $config_addLinesOnly = 0;				// If this is set, modifications to config_site.inc.php is done by adding new lines to the array only. If unset, existing values are recognized and changed.

	var $setConfig = 0;						// Used to indicate that a value is change in the line-array of the config and that it should be written.
	//var $messages = array();					// Used to set (error)messages from the executing functions like writing config and such
	var $touchedLine = 0;						// updated with line in config_site.inc.php file that was changed.


	/*************************************
	 * CONFIGURATION FILE
	 *************************************/

	/**
	 * This functions takes an array with lines from config_site.inc.php, finds a variable and inserts the new value.
	 *
	 * @param	array		$line_array	the config_site.inc.php file exploded into an array by linebreaks. (see writeToConfig_control())
	 * @param	string		$variable	The variable name to find and substitute. This string must match the first part of a trimmed line in the line-array. Matching is done backwards so the last appearing line will be substituted.
	 * @param	string		$value		Is the value to be insert for the variable
	 * @return	void
	 */
	function setValueInConfigFile(&$line_array, $variable, $value) {
		if (!$this->checkForBadString($value)) return 0;

		// Initialize:
		$found = 0;
		$this->touchedLine = '';
		$commentKey = '## ';
		$inArray = in_array($commentKey . $this->config_editPointToken, $line_array);
		$tokenSet = ($this->config_editPointToken && !$inArray);		// Flag is set if the token should be set but is not yet...
		$stopAtToken = ($this->config_editPointToken && $inArray);

			// Search for variable name:
		if (!$this->config_addLinesOnly && !$tokenSet) {
			$line_array = array_reverse($line_array);
			foreach($line_array as $k => $v) {
				$v2 = trim($v);
				if ($stopAtToken && !strcmp($v2, $commentKey . $this->config_editPointToken)) break;		// If stopAtToken and token found, break out of the loop..
				if (!strcmp(substr($v2, 0, strlen($variable .' ')), $variable.' ')) {
					$mainparts = explode($variable, $v, 2);
					if (count($mainparts)==2) {	// should ALWAYS be....
						$line_array[$k] = $mainparts[0] . $variable ." = '". $this->slashValueForSingleDashes($value)."'; ";
						$this->touchedLine = count($line_array)-$k-1;
						$found = 1;
						break;
					}
				}
			}
			$line_array = array_reverse($line_array);
		}
		if (!$found) {
			if ($tokenSet) {
				$line_array[] = $commentKey . $this->config_editPointToken;
				$line_array[] = '';
			}
			$line_array[] = $variable ." = '". $this->slashValueForSingleDashes($value) ."';";
				// ". $comment;
			$this->touchedLine = -1;
		}
		$this->messages[] = $variable ." = '". htmlspecialchars($value) ."'";
		$this->setConfig = 1;
	}

	/**
	 * Writes or returns lines from config_site.inc.php
	 *
	 * @param	array		Array of lines to write back to config_site.inc.php. Possibly
	 * @param 	string		Absolute path of alternative file to use (Notice: this path is not validated in terms of being inside 'TUPA space')
	 * @return	mixed		If $inlines is not an array it will return an array with the lines from config_site.inc.php. Otherwise it will return a status string, either "continue" (updated) or "nochange" (not updated)
	 */
	function writeToConfig_control($inlines='', $absFullPath='') {
		$writeToConfig_dat['file'] = $absFullPath ? $absFullPath : PATH_config .'config_site.inc.php';

			// Checking write state of config_site.inc.php:
		if (!$this->allowUpdateConfig) {
			die("->allowUpdateConfig flag in the install object is not set and therefore 'config_site.inc.php' cannot be altered.");
		}
		if (!@is_writable($writeToConfig_dat['file'])) {
			die($writeToConfig_dat['file'].' is not writable!');
		}

			// Splitting config_site.inc.php file into lines:
		$lines = explode(chr(10), trim(lib_div::getFileContent($writeToConfig_dat['file'])));
		$writeToConfig_dat['endLine'] = array_pop($lines);	// Getting "? >" ending.

			// Checking if "updated" line was set by this tool - if so remove old line.
		$updatedLine = array_pop($lines);
		/*$writeToConfig_dat['updatedText'] = '// Updated by '.$this->updateIdentity.' ';
		if (!strstr($updatedLine, $writeToConfig_dat['updatedText'])) {
			array_push($lines,$updatedLine);
		}*/

		if (is_array($inlines)) {	// Setting a line and write:
				// Setting configuration
			//$updatedLine = $writeToConfig_dat['updatedText'].date('d-m-Y H:i:s');
			array_push($inlines,$updatedLine);
			array_push($inlines,$writeToConfig_dat['endLine']);

			if ($this->setConfig) {
				lib_div::writeFile($writeToConfig_dat['file'], implode(chr(10), $inlines));

				if (strcmp(lib_div::getFileContent($writeToConfig_dat['file']), implode(chr(10), $inlines))) {
					die('config/config_site.inc.php was NOT updated properly (written content didn\'t match file content) - maybe write access problem?');
				}
				return 'continue';
			} else {
				return 'nochange';
			}
		} else {	// Return lines found in localconf.php
			return $lines;
		}
	}

	/**
	 * Checking for linebreaks in the string
	 *
	 * @param	string		String to test
	 * @return	boolean		Returns TRUE if string is OK
	 * @see setValueInLocalconfFile()
	 */
	function checkForBadString($string)	{
		if (ereg('['. chr(10) . chr(13) .']',$string)){
			return false;
		} else return true;
	}

	/**
	 * Replaces ' with \' and \ with \\
	 *
	 * @param	string		Input value
	 * @return	string		Output value
	 * @see setValueInLocalconfFile()
	 */
	function slashValueForSingleDashes($value)	{
		return str_replace("'", "\'", str_replace('\\', '\\\\', $value));
	}





	/*************************************
	 * SQL
	 *************************************/

	/**
	 * Reads the field definitions for the input sql-file string
	 *
	 * @param	string		$sqlContent: Should be a string read from an sql-file made with 'mysqldump [database_name] -d'
	 * @return	array		Array with information about table.
	 */
	function getFieldDefinitions_sqlContent($sqlContent) {
		$lines = lib_div::trimExplode(chr(10), $sqlContent,1);
		$isTable = '';

		foreach($lines as $value) {
			if ($value[0]!='#') {
				// remove '`' from lines and replace double spaces to single space from lines
				$value = preg_replace('/`/i','',$value);
				$value = preg_replace('/  /i',' ',$value);
				if (!$isTable) {
					$parts = explode(' ',$value);
					if ($parts[0]=='CREATE' && $parts[1]=='TABLE') {
						$isTable = $parts[2];
						if (TYPO3_OS=='WIN') { 	// tablenames are always lowercase on windows!
							$isTable = strtolower($isTable);
						}
					}
				} else {
					if (substr($value,0,1)==')' && substr($value,-1)==';') {
						preg_match('/(ENGINE|TYPE)=([a-zA-Z]*)/',$value,$ttype);
						$total[$isTable]['extra']['ttype'] = $ttype[2];
						$isTable = '';
					} else {
						$lineV = ereg_replace(',$','',$value);
						$parts = explode(' ',$lineV,2);

							// Make sure there is no default value when auto_increment is set
						if(stristr($parts[1],'auto_increment')) {
							$parts[1] = preg_replace('/ default \'0\'/i','',$parts[1]);
						}
							// "default" is always lower-case
						if(strstr($parts[1], ' DEFAULT '))	{
							$parts[1] = str_replace(' DEFAULT ', ' default ', $parts[1]);
						}
							// Change order of "default" and "null" statements
						$parts[1] = preg_replace('/(.*) (default .*) (NOT NULL)/', '$1 $3 $2', $parts[1]);
						$parts[1] = preg_replace('/(.*) (default .*) (NULL)/', '$1 $3 $2', $parts[1]);

						if ($parts[0]!='PRIMARY' && $parts[0]!='KEY' && $parts[0]!='UNIQUE') {
							$total[$isTable]['fields'][$parts[0]] = $parts[1];
						} else {
							if ($parts[0] == 'UNIQUE') {
								$newParts = explode(' ',$parts[1],3);
								$newPart = $newParts[1];
							} else {
								$newParts = explode(' ',$parts[1],2);
								$newPart = $newParts[0];
							}
							$total[$isTable]['keys'][($parts[0]=='PRIMARY'?$parts[0]:$newPart)] = $lineV;
						}
					}
				}
			}
		}
		return $total;
	}


	/**
	 * Reads the field definitions for the current database
	 *
	 * @return	array		Array with information about table.
	 */
	function getFieldDefinitions_database() {
		$total = array();
		$GLOBALS['TUPA_DB']->sql_select_db(TUPA_db);

		$tables = $GLOBALS['TUPA_DB']->admin_get_tables(TUPA_db);
		foreach($tables as $tableName) {

				// Fields:
			$fieldInformation = $GLOBALS['TUPA_DB']->admin_get_fields($tableName);
			foreach($fieldInformation as $fN => $fieldRow) {
				$total[$tableName]['fields'][$fN] = $this->assembleFieldDefinition($fieldRow);
			}

				// Keys:
			$keyInformation = $GLOBALS['TUPA_DB']->admin_get_keys($tableName);
			foreach($keyInformation as $keyRow) {
				$tempKeys[$tableName][$keyRow['Key_name']][$keyRow['Seq_in_index']] = $keyRow['Column_name'];
				$tempKeysPrefix[$tableName][$keyRow['Key_name']]= ($keyRow['Key_name']=='PRIMARY'?'PRIMARY KEY':($keyRow['Non_unique']?'KEY':'UNIQUE KEY').' '.$keyRow['Key_name']);
			}
		}

			// Compile information:
		if (is_array($tempKeys)) {
			foreach($tempKeys as $table => $keyInf) {
				foreach($keyInf as $kName => $index) {
					ksort($index);
					$total[$table]['keys'][$kName] = $tempKeysPrefix[$table][$kName].' ('.implode(',',$index).')';
				}
			}
		}

		return $total;
	}

	/**
	 * Compares two arrays with field information and returns information about fields that are MISSING and fields that have CHANGED.
	 * FDsrc and FDcomp can be switched if you want the list of stuff to remove rather than update.
	 *
	 * @param	array		Field definitions, source (from getFieldDefinitions_sqlContent())
	 * @param	array		Field definitions, comparison. (from getFieldDefinitions_database())
	 * @param	string		Table names (in list) which is the ONLY one observed.
	 * @return	array		Returns an array with 1) all elements from $FSsrc that is not in $FDcomp (in key 'extra') and 2) all elements from $FSsrc that is difference from the ones in $FDcomp
	 */
	function getDatabaseExtra($FDsrc, $FDcomp, $onlyTableList='') {
		$extraArr = array();
		$diffArr = array();

		if (is_array($FDsrc))	{
			foreach($FDsrc as $table => $info) {
				if (!strlen($onlyTableList) || t3lib_div::inList($onlyTableList, $table)) {
					if (!isset($FDcomp[$table])) {
						$extraArr[$table] = $info;		// If the table was not in the FDcomp-array, the result array is loaded with that table.
						$extraArr[$table]['whole_table']=1;
					} else {
						$keyTypes = explode(',','fields,keys');
						foreach($keyTypes as $theKey)	{
							if (is_array($info[$theKey]))	{
								foreach($info[$theKey] as $fieldN => $fieldC) {
									if (!isset($FDcomp[$table][$theKey][$fieldN])) {
										$extraArr[$table][$theKey][$fieldN] = $fieldC;
									} elseif (strcmp($FDcomp[$table][$theKey][$fieldN], $fieldC)) {
										$diffArr[$table][$theKey][$fieldN] = $fieldC;
										$diffArr_cur[$table][$theKey][$fieldN] = $FDcomp[$table][$theKey][$fieldN];
									}
								}
							}
						}
					}
				}
			}
		}

		$output = array(
			'extra' => $extraArr,
			'diff' => $diffArr,
			'diff_currentValues' => $diffArr_cur
		);

		return $output;
	}

	/**
	 * Returns an array with SQL-statements that is needed to update according to the diff-array
	 *
	 * @param	array		Array with differences of current and needed DB settings. (from getDatabaseExtra())
	 * @param	string		List of fields in diff array to take notice of.
	 * @return	array		Array of SQL statements (organized in keys depending on type)
	 */
	function getUpdateSuggestions($diffArr,$keyList='extra,diff') {
		$statements = array();
		$deletedPrefixKey = $this->deletedPrefixKey;
		$remove = 0;
		if ($keyList == 'remove') {
			$remove = 1;
			$keyList = 'extra';
		}
		$keyList = explode(',',$keyList);
		foreach($keyList as $theKey)	{
			if (is_array($diffArr[$theKey]))	{
				foreach($diffArr[$theKey] as $table => $info) {
					$whole_table = array();
					if (is_array($info['fields']))	{
						foreach($info['fields'] as $fN => $fV) {
							if ($info['whole_table'])	{
								$whole_table[]=$fN.' '.$fV;
							} else {
								if ($theKey=='extra') {
									if ($remove) {
										if (substr($fN,0,strlen($deletedPrefixKey))!=$deletedPrefixKey) {
											$statement = 'ALTER TABLE '.$table.' CHANGE '.$fN.' '.$deletedPrefixKey.$fN.' '.$fV.';';
											$statements['change'][md5($statement)] = $statement;
										} else {
											$statement = 'ALTER TABLE '.$table.' DROP '.$fN.';';
											$statements['drop'][md5($statement)] = $statement;
										}
									} else {
										$statement = 'ALTER TABLE '.$table.' ADD '.$fN.' '.$fV.';';
										$statements['add'][md5($statement)] = $statement;
									}
								} elseif ($theKey=='diff') {
									$statement = 'ALTER TABLE '.$table.' CHANGE '.$fN.' '.$fN.' '.$fV.';';
									$statements['change'][md5($statement)] = $statement;
									$statements['change_currentValue'][md5($statement)] = $diffArr['diff_currentValues'][$table]['fields'][$fN];
								}
							}
						}
					}
					if (is_array($info['keys'])) {
						foreach($info['keys'] as $fN => $fV) {
							if ($info['whole_table']) {
								if ($fN=='PRIMARY') {
									$whole_table[] = $fV;
								} else {
									$whole_table[] = $fV;
								}
							} else {
								if ($theKey=='extra') {
									if ($remove) {
										$statement = 'ALTER TABLE '.$table.($fN=='PRIMARY' ? ' DROP PRIMARY KEY' : ' DROP KEY '.$fN).';';
										$statements['drop'][md5($statement)] = $statement;
									} else {
										$statement = 'ALTER TABLE '.$table.' ADD '.$fV.';';
										$statements['add'][md5($statement)] = $statement;
									}
								} elseif ($theKey=='diff') {
									$statement = 'ALTER TABLE '.$table.($fN=='PRIMARY' ? ' DROP PRIMARY KEY' : ' DROP KEY '.$fN).';';
									$statements['change'][md5($statement)] = $statement;
									$statement = 'ALTER TABLE '.$table.' ADD '.$fV.';';
									$statements['change'][md5($statement)] = $statement;
								}
							}
						}
					}
					if ($info['whole_table']) {
						if ($remove) {
							if (substr($table,0,strlen($deletedPrefixKey))!=$deletedPrefixKey) {
								$statement = 'ALTER TABLE '.$table.' RENAME '.$deletedPrefixKey.$table.';';
								$statements['change_table'][md5($statement)]=$statement;
							} else {
								$statement = 'DROP TABLE '.$table.';';
								$statements['drop_table'][md5($statement)]=$statement;
							}
							// count:
							$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('count(*)', $table, '');
							list($count) = $GLOBALS['TUPA_DB']->sql_fetch_row($res);
							$statements['tables_count'][md5($statement)] = $count?'Records in table: '.$count:'';
						} else {
							$statement = 'CREATE TABLE '.$table." (\n".implode(",\n",$whole_table)."\n)";
							$statement .= ($info['extra']['ttype']) ? ' ENGINE='.$info['extra']['ttype'].';' : ';';
							$statements['create_table'][md5($statement)]=$statement;
						}
					}
				}
			}
		}

		return $statements;
	}

	/**
	 * Converts a result row with field information into the SQL field definition string
	 *
	 * @param	array		MySQL result row.
	 * @return	string		Field definition
	 */
	function assembleFieldDefinition($row) {
		$null = false;
		$field[] = $row['Type'];
		if (!$row['Null'] || $row['Null'] == 'NO') { $field[] = 'NOT NULL'; }
		if ($row['Null'] == 'YES') { $null = true; }
		if (!strstr($row['Type'],'blob') && !strstr($row['Type'],'text')) {
				// Add a default value if the field is not auto-incremented (these fields never have a default definition).
			if (!stristr($row['Extra'],'auto_increment'))	{
				$field[] = 'default '. ($null ? 'NULL' : "'".(addslashes($row['Default']))."'");
			}
		}
		if ($row['Extra']) { $field[] = $row['Extra']; }

		return implode(' ',$field);
	}

	/**
	 * Returns an array where every entry is a single sql-statement. Input must be formatted like an ordinary MySQL-dump files
	 *
	 * @param	string		$sqlcode	The sql-file content. Provided that 1) every query in the input is ended with ';' and that a line in the file contains only one query or a part of a query.
	 * @param	boolean		If set, non-sql (like comments and blank lines) are not included in the final product)
	 * @param	string		Regex to filter SQL lines to include.
	 * @return	array		Array of SQL statements.
	 */
	function getStatementArray($sqlcode,$removeNonSQL=0,$query_regex='') {
		$sqlcodeArr = explode(chr(10),$sqlcode);

		// Based on the assumption that the sql-dump has
		$statementArray = array();
		$statementArrayPointer = 0;

		foreach($sqlcodeArr as $linecontent) {
			$is_set = 0;
			if(stristr($linecontent,'auto_increment')) {
				$linecontent = eregi_replace(' default \'0\'','',$linecontent);
			}

			if (!$removeNonSQL || (strcmp(trim($linecontent),'') && substr(trim($linecontent),0,1)!='#' && substr(trim($linecontent),0,2)!='--')) {		// '--' is seen as mysqldump comments from server version 3.23.49
				$statementArray[$statementArrayPointer].= $linecontent;
				$is_set = 1;
			}
			if (substr(trim($linecontent),-1)==';') {
				if (isset($statementArray[$statementArrayPointer]))	{
					if (!trim($statementArray[$statementArrayPointer]) || ($query_regex && !eregi($query_regex,trim($statementArray[$statementArrayPointer]))))	{
						unset($statementArray[$statementArrayPointer]);
					}
				}
				$statementArrayPointer++;
			} elseif ($is_set) {
				$statementArray[$statementArrayPointer].=chr(10);
			}
		}
		return $statementArray;
	}

	/**
	 * Returns tables to create and how many records in each
	 *
	 * @param	array		Array of SQL statements to analyse.
	 * @param	boolean		If set, will count number of INSERT INTO statements following that table definition
	 * @return	array		Array with table definitions in index 0 and count in index 1
	 */
	function getCreateTables($statements, $insertCountFlag=0) {
		$crTables = array();
		foreach($statements as $linecontent) {
			if (eregi('^create[[:space:]]*table[[:space:]]*([[:alnum:]_]*)',substr($linecontent,0,100),$reg)) {
				$table = trim($reg[1]);
				if ($table)	{
					if (TYPO3_OS=='WIN') { $table=strtolower($table); }	// table names are always lowercase on Windows!
					$sqlLines = explode(chr(10), $linecontent);
					foreach($sqlLines as $k=>$v) {
						if(stristr($v,'auto_increment')) {
							$sqlLines[$k] = eregi_replace(' default \'0\'','',$v);
						}
					}
					$linecontent = implode(chr(10), $sqlLines);
					$crTables[$table] = $linecontent;
				}
			} elseif ($insertCountFlag && eregi('^insert[[:space:]]*into[[:space:]]*([[:alnum:]_]*)',substr($linecontent,0,100),$reg)) {
				$nTable = trim($reg[1]);
				$insertCount[$nTable]++;
			}
		}
		return array($crTables,$insertCount);
	}

	/**
	 * Extracts all insert statements from $statement array where content is inserted into $table
	 *
	 * @param	array		Array of SQL statements
	 * @param	string		Table name
	 * @return	array		Array of INSERT INTO statements where table match $table
	 */
	function getTableInsertStatements($statements, $table) {
		$outStatements=array();
		foreach($statements as $linecontent) {
			if (eregi('^insert[[:space:]]*into[[:space:]]*([[:alnum:]_]*)',substr($linecontent,0,100),$reg)) {
				$nTable = trim($reg[1]);
				if ($nTable && !strcmp($table,$nTable)) {
					$outStatements[]=$linecontent;
				}
			}
		}
		return $outStatements;
	}

	/**
	 * Performs the queries passed from the input array.
	 *
	 * @param	array		Array of SQL queries to execute.
	 * @param	array		Array with keys that must match keys in $arr. Only where a key in this array is set and true will the query be executed (meant to be passed from a form checkbox)
	 * @return	void
	 */
	function performUpdateQueries($arr,$keyArr) {
		if (is_array($arr)) {
			foreach($arr as $key => $string) {
				if (isset($keyArr[$key]) && $keyArr[$key]) {
					$GLOBALS['TUPA_DB']->admin_query($string);
				}
			}
		}
	}


	/**
	 * Creates a table which checkboxes for updating database.
	 *
	 * @param	array		Array of statements (key / value pairs where key is used for the checkboxes)
	 * @param	string		Label for the table.
	 * @param	boolean		If set, then checkboxes are set by default.
	 * @param	array		Array of "current values" for each key/value pair in $arr. Shown if given.
	 * @param	boolean		If set, will show the prefix "Current value" if $currentValue is given.
	 * @return	string		HTML table with checkboxes for update. Must be wrapped in a form.
	 */
	function generateUpdateDatabaseForm_checkboxes($arr,$label,$checked=1,$currentValue=array(),$cVfullMsg=0) {
		$out = array();
		if (is_array($arr)) {
			foreach($arr as $key => $string) {
				$out[]='
					<tr>
						<td valign="top"><input type="checkbox" name="'.$this->dbUpdateCheckboxPrefix.'['.$key.']" value="1"'.($checked?' checked="checked"':'').' /></td>
						<td nowrap="nowrap">'.nl2br(htmlspecialchars($string)).'</td>
					</tr>';
				if (isset($currentValue[$key])) {
					$out[]='
					<tr>
						<td valign="top"></td>
						<td nowrap="nowrap" style="color : #666666;">'.nl2br((!$cVfullMsg?"Current value: ":"").'<em>'.$currentValue[$key].'</em>').'</td>
					</tr>';
				}
			}

			// Compile rows:
			$content = '
				<!-- Update database fields / tables -->
				<h3>'.$label.'</h3>
				<table border="0" cellpadding="2" cellspacing="2" class="update-db-fields">'.implode('',$out).'
				</table>';
		}

		return $content;
	}
}
?>

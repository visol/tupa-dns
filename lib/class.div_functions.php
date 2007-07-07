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
 * A lot of helpful functions
 *
 * @package 	TUPA
 * @author  	Kaspar Skaarhoj <kasperYYYY@typo3.com>
 * @author	Urs Weiss <urs@tupa-dns.org>
 */
class lib_div {

	/*************************
	 *
	 * STRING FUNCTIONS
	 *
	 *************************/

	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 *
	 * @param	string		Version number on format x.x.x
	 * @return	integer		Integer version of version number (where each part can count to 999)
	 */
	function int_from_ver($verNumberStr)	{
		$verParts = explode('.',$verNumberStr);
		return intval((int)$verParts[0].str_pad((int)$verParts[1],3,'0',STR_PAD_LEFT).str_pad((int)$verParts[2],3,'0',STR_PAD_LEFT));
	}

	/**
	 * Takes comma-separated lists and arrays and removes all duplicates
	 * If a value in the list is trim(empty), the value is ignored.
	 *
	 * @param	string		Accept multiple parameters wich can be separated lists of values and arrays.
	 * @param	string		Separator
	 * @return	string		Returns the list without any duplicates of values, space around values are trimmed
	 */
	function uniqueList($in_list, $seperator=',')	{
		return implode($seperator, array_unique(lib_div::trimExplode($seperator, $in_list, 1)));
	}

	/**
	 * Returns the directory part of a path without trailing slash
	 * If there is no dir-part, then an empty string is returned.
	 * Behaviour:
	 *
	 * '/dir1/dir2/script.php' => '/dir1/dir2'
	 * '/dir1/' => '/dir1'
	 * 'dir1/script.php' => 'dir1'
	 * 'd/script.php' => 'd'
	 * '/script.php' => ''
	 * '' => ''
	 *
	 * @param	string		Directory name / path
	 * @return	string		Processed input value. See function description.
	 */
	function dirname($path)	{
		$p=lib_div::revExplode('/',$path,2);
		return count($p)==2?$p[0]:'';
	}

	/**
	 * Checks if there is a trailing slash at the end of the path and adds it if not
	 *
	 * @param	string		Directory path
	 * @return	string		Path with trailing slash.
	 */
	function checkAddTrailingSlash($path) {
		if ($path == '') return $path;
		if (strrpos($path, '/') != strlen($path)-1) {
			$path .= '/';
		}
		return $path;
	}

	/**
	 * Removes comma (if present) in the end of string
	 *
	 * @param	string		String from which the comma in the end (if any) will be removed.
	 * @return	string
	 */
	function rm_endcomma($string)	{
		return ereg_replace(',$','',$string);
	}

	/**
	 * Tests if the input is an integer.
	 *
	 * @param	mixed		Any input variable to test.
	 * @return	boolean		Returns true if string is an integer
	 */
	function testInt($var)	{
		return !strcmp($var,intval($var));
	}

	/**
	 * Returns true if the first part of $str matches the string $partStr
	 *
	 * @param	string		Full string to check
	 * @param	string		Reference string which must be found as the "first part" of the full string
	 * @return	boolean		True if $partStr was found to be equal to the first part of $str
	 */
	function isFirstPartOfStr($str,$partStr)	{
		// Returns true, if the first part of a $str equals $partStr and $partStr is not ''
		$psLen = strlen($partStr);
		if ($psLen)	{
			return substr($str,0,$psLen)==(string)$partStr;
		} else return false;
	}

	/**
	 * Formats the input integer $sizeInBytes as bytes/kilobytes/megabytes (-/K/M)
	 *
	 * @param	integer		Number of bytes to format.
	 * @param	integer		Number of decimals after pont.
	 * @param	boolean	return as array: [0]=>value [1]=>label
	 * @return	mixed		Formatted representation of the byte number, for output or as array.
	 */
	function formatSize($sizeInBytes, $dec=1, $array=false) {
		// Set labels:
		$labels = ' b| kb| Mb| Gb';
		$labelArr = explode('|',$labels);

		// Find size:
		if ($sizeInBytes>900) {
			if ($sizeInBytes>900000000)	{	// GB
				$val = $sizeInBytes/(1024*1024*1024);
				$formated = number_format($val, $dec, '.', '').$labelArr[3];
			} elseif ($sizeInBytes>900000)	{	// MB
				$val = $sizeInBytes/(1024*1024);
				$formated = number_format($val, $dec, '.', '').$labelArr[2];
			} else {	// KB
				$val = $sizeInBytes/(1024);
				$formated = number_format($val, $dec, '.', '').$labelArr[1];
			}
		} else {	// Bytes
			$formated = $sizeInBytes.$labelArr[0];
		}

		if ($array) {
			return lib_div::trimExplode(' ', $formated);
		} else {
			return $formated;
		}
	}


	/**
	 * Converts kb, Mb, Gb to bytes
	 *
	 * @param	integer		Value to convert
	 * @param	string		Label to convert from
	 * @return	string		Value in bytes
	 */
	function convertToBytes($sizeToConv, $label) {
		// Find size:
		switch ($label) {
			case 'kb':
				return $sizeToConv*1024;
				break;
			case 'Mb':
				return $sizeToConv*1024*1024;
				break;
			case 'Gb':
				return  $sizeToConv*1024*1024*1024;
				break;
			default:
				return $sizeToConv;
				break;
		}
	}


	/**
	 * Gets the max_upload_filesize value from php config and converts it to bytes.
	 *
	 * @return	string		Value in bytes
	 */
	function convertMaxUploadFilesize() {
		$maxSize = ini_get('upload_max_filesize');
		if (preg_match('/M/', $maxSize)) {
			$maxSize = $maxSize * 1024 * 1024;
		}
		return $maxSize;
	}


	/**
	 * Inverse version of htmlspecialchars()
	 *
	 * @param	string		Value where &gt;, &lt;, &quot; and &amp; should be converted to regular chars.
	 * @return	string		Converted result.
	 */
	function htmlspecialchars_decode($value)	{
		$value = str_replace('&gt;','>',$value);
		$value = str_replace('&lt;','<',$value);
		$value = str_replace('&quot;','"',$value);
		$value = str_replace('&amp;','&',$value);
		return $value;
	}

	/**
	 * Re-converts HTML entities if they have been converted by htmlspecialchars()
	 *
	 * @param	string		String which contains eg. "&amp;amp;" which should stay "&amp;". Or "&amp;#1234;" to "&#1234;". Or "&amp;#x1b;" to "&#x1b;"
	 * @return	string		Converted result.
	 */
	function deHSCentities($str)	{
		return ereg_replace('&amp;([#[:alnum:]]*;)','&\1',$str);
	}

	/**
	 * This function is used to escape any ' -characters when transferring text to JavaScript!
	 *
	 * @param	string		String to escape
	 * @param	boolean		If set, also backslashes are escaped.
	 * @param	string		The character to escape, default is ' (single-quote)
	 * @return	string		Processed input string
	 */
	function slashJS($string,$extended=0,$char="'")	{
		if ($extended)	{$string = str_replace ("\\", "\\\\", $string);}
		return str_replace ($char, "\\".$char, $string);
	}

	/**
	 * This function is used to escape any ' and " -characters when transferring text to JavaScript!
	 *
	 * @param	string		String to escape
	 * @param	boolean	If set, also backslashes are escaped.
	 * @return	string		Processed input string
	 */
	function convertMsgForJS($string) {
		$string = lib_div::slashJS($string);
		$string = str_replace('"', '&quot;', $string);
		return $string;
	}

	/**
	 * Version of rawurlencode() where all spaces (%20) are re-converted to space-characters.
	 * Usefull when passing text to JavaScript where you simply url-encode it to get around problems with syntax-errors, linebreaks etc.
	 *
	 * @param	string		String to raw-url-encode with spaces preserved
	 * @return	string		Rawurlencoded result of input string, but with all %20 (space chars) converted to real spaces.
	 */
	function rawUrlEncodeJS($str)	{
		return str_replace('%20',' ',rawurlencode($str));
	}

	/**
	 * rawurlencode which preserves "/" chars
	 * Usefull when filepaths should keep the "/" chars, but have all other special chars encoded.
	 *
	 * @param	string		Input string
	 * @return	string		Output string
	 */
	function rawUrlEncodeFP($str)	{
		return str_replace('%2F','/',rawurlencode($str));
	}

	/**
	 * Checking syntax of input email address
	 *
	 * @param	string		Input string to evaluate
	 * @return	boolean		Returns true if the $email address (input string) is valid; Has a "@", domain name with at least one period and only allowed a-z characters.
	 */
	function validEmail($email)	{
		$email = trim ($email);
		if (strstr($email,' '))	 return false;
		return ereg('^[A-Za-z0-9\._-]+[@][A-Za-z0-9\._-]+[\.].[A-Za-z0-9]+$',$email) ? TRUE : FALSE;
	}


	/**
	 * Checking if selectbox value is set (not '' or'0')
	 *
	 * @param	string		Input string to evaluate
	 * @return	boolean		Returns true if selectbox has a valid value
	 */
	function checkSelectValue($value) {
		$value = trim ($value);
		if ($value != '' && $value > 0) return true;
		return false;
	}

	/**
	 * Escapes colons (:) in string used in RRD config options
	 *
	 * @param	string		Input string to evaluate
	 * @return	boolean		Returns true if selectbox has a valid value
	 */
	function escapeColon($string) {
		return str_replace(':', '\:', $string);
	}




	/*************************
	 *
	 * ARRAY FUNCTIONS
	 *
	 *************************/

	/**
	 * Check if an item exists in an array
	 * Please note that the order of parameters is reverse compared to the php4-function in_array()!!!
	 *
	 * @param	array		$in_array		one-dimensional array of items
	 * @param	string		$item 	item to check for
	 * @return	boolean		true if $item is in the one-dimensional array $in_array
	 * @internal
	 */
	function inArray($in_array,$item)	{
		if (is_array($in_array))	{
			while (list(,$val)=each($in_array))	{
				if (!is_array($val) && !strcmp($val,$item)) return true;
			}
		}
	}

	/**
	 * Explodes a $string delimited by $delim and passes each item in the array through intval().
	 * Corresponds to explode(), but with conversion to integers for all values.
	 *
	 * @param	string		Delimiter string to explode with
	 * @param	string		The string to explode
	 * @return	array		Exploded values, all converted to integers
	 */
	function intExplode($delim, $string)	{
		$temp = explode($delim,$string);
		while(list($key,$val)=each($temp))	{
			$temp[$key]=intval($val);
		}
		reset($temp);
		return $temp;
	}

	/**
	 * Reverse explode which explodes the string counting from behind.
	 * Thus lib_div::revExplode(':','my:words:here',2) will return array('my:words','here')
	 *
	 * @param	string		Delimiter string to explode with
	 * @param	string		The string to explode
	 * @param	integer		Number of array entries
	 * @return	array		Exploded values
	 */
	function revExplode($delim, $string, $count=0)	{
		$temp = explode($delim,strrev($string),$count);
		while(list($key,$val)=each($temp))	{
			$temp[$key]=strrev($val);
		}
		$temp=array_reverse($temp);
		reset($temp);
		return $temp;
	}

	/**
	 * Explodes a string and trims all values for whitespace in the ends.
	 * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
	 *
	 * @param	string		Delimiter string to explode with
	 * @param	string		The string to explode
	 * @param	boolean	If set, all empty values (='') will NOT be set in output
	 * @return	array		Exploded values
	 */
	function trimExplode($delim, $string, $onlyNonEmptyValues=0)	{
		$temp = explode($delim,$string);
		$newtemp=array();
		while(list(,$val)=each($temp))	{
			if (!$onlyNonEmptyValues || strcmp('',trim($val)))	{
				$newtemp[]=trim($val);
			}
		}
		reset($newtemp);
		return $newtemp;
	}

	/**
	 * Removes the value $cmpValue from the $array if found there. Returns the modified array
	 *
	 * @param	array		Array containing the values
	 * @param	string		Value to search for and if found remove array entry where found.
	 * @return	array		Output array with entries removed if search string is found
	 */
	function removeArrayEntryByValue($array,$cmpValue)	{
		if (is_array($array))	{
			reset($array);
			while(list($k,$v)=each($array))	{
				if (is_array($v))	{
					$array[$k] = lib_div::removeArrayEntryByValue($v,$cmpValue);
				} else {
					if (!strcmp($v,$cmpValue))	{
						unset($array[$k]);
					}
				}
			}
		}
		reset($array);
		return $array;
	}


	/**
	 * AddSlash array
	 * This function traverses a multidimentional array and adds slashes to the values.
	 * NOTE that the input array is and argument by reference.!!
	 * Twin-function to stripSlashesOnArray
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function addSlashesOnArray(&$theArray)	{
		if (is_array($theArray))	{
			reset($theArray);
			while(list($Akey,$AVal)=each($theArray))	{
				if (is_array($AVal))	{
					lib_div::addSlashesOnArray($theArray[$Akey]);
				} else {
					$theArray[$Akey] = addslashes($AVal);
				}
			}
			reset($theArray);
		}
	}

	/**
	 * StripSlash array
	 * This function traverses a multidimentional array and strips slashes to the values.
	 * NOTE that the input array is and argument by reference.!!
	 * Twin-function to addSlashesOnArray
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function stripSlashesOnArray(&$theArray)	{
		if (is_array($theArray))	{
			reset($theArray);
			while(list($Akey,$AVal)=each($theArray))	{
				if (is_array($AVal))	{
					lib_div::stripSlashesOnArray($theArray[$Akey]);
				} else {
					$theArray[$Akey] = stripslashes($AVal);
				}
			}
			reset($theArray);
		}
	}

	/**
	 * Strip tags on array
	 * This function traverses a multidimentional array and strip tags on the values.
	 * NOTE that the input array is and argument by reference.!!
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function stripTagsOnArray(&$theArray)	{
		if (is_array($theArray))	{
			reset($theArray);
			while(list($Akey,$AVal)=each($theArray))	{
				if (is_array($AVal))	{
					lib_div::stripTagsOnArray($theArray[$Akey]);
				} else {
					$theArray[$Akey] = strip_tags($AVal);
				}
			}
			reset($theArray);
		}
	}

	/**
	 * htmlspecialchars on array
	 * This function traverses a multidimentional array and htmlspecialchars the values.
	 * NOTE that the input array is and argument by reference.!!
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function htmlspecialcharOnArray(&$theArray)	{
		if (is_array($theArray))	{
			reset($theArray);
			while(list($Akey,$AVal)=each($theArray))	{
				if (is_array($AVal))	{
					lib_div::htmlspecialcharOnArray($theArray[$Akey]);
				} else {
					$theArray[$Akey] = htmlspecialchars($AVal);
				}
			}
			reset($theArray);
		}
	}


	/**
	 * Either slashes ($cmd=add) or strips ($cmd=strip) array $arr depending on $cmd
	 *
	 * @param	array		Multidimensional input array
	 * @param	string		"add" or "strip", depending on usage you wish.
	 * @return	array
	 */
	function slashArray($arr,$cmd)	{
		if ($cmd=='strip')	lib_div::stripSlashesOnArray($arr);
		if ($cmd=='add')	lib_div::addSlashesOnArray($arr);
		return $arr;
	}

	/**
	 * Merges two arrays recursively and "binary safe" (integer keys are overridden as well), overruling similar values in the first array ($arr0) with the values of the second array ($arr1)
	 * In case of identical keys, ie. keeping the values of the second.
	 *
	 * @param	array		First array
	 * @param	array		Second array, overruling the first array
	 * @param	boolean		If set, keys that are NOT found in $arr0 (first array) will not be set. Thus only existing value can/will be overruled from second array.
	 * @param	boolean		If set, values from $arr1 will overrule if they are empty. Default: true
	 * @return	array		Resulting array where $arr1 values has overruled $arr0 values
	 */
	function array_merge_recursive_overrule($arr0,$arr1,$notAddKeys=0,$includeEmtpyValues=true) {
		reset($arr1);
		while(list($key,$val) = each($arr1)) {
			if(isset($arr0[$key]) && is_array($arr0[$key])) {
				if (isset($arr1[$key]) && is_array($arr1[$key]))	{
					$arr0[$key] = lib_div::array_merge_recursive_overrule($arr0[$key],$arr1[$key],$notAddKeys);
				}
			} else {
				if ($notAddKeys) {
					if (isset($arr0[$key])) {
						if ($includeEmtpyValues OR $val) {
							$arr0[$key] = $val;
						}
					}
				} else {
					if ($includeEmtpyValues OR $val) {
						$arr0[$key] = $val;
					}
				}
			}
		}
		reset($arr0);
		return $arr0;
	}

	/**
	 * An array_merge function where the keys are NOT renumbered as they happen to be with the real php-array_merge function. It is "binary safe" in the sense that integer keys are overridden as well.
	 *
	 * @param	array		First array
	 * @param	array		Second array
	 * @return	array		Merged result.
	 */
	function array_merge($arr1,$arr2)	{
		return $arr2+$arr1;
	}


	/**
	 * Generates a multidimensional array with submitted array of keys.
	 *
	 * @param	array		Input array of keys of the new array
	 * @param	string		Value which the last key becomes
	 * @return	array		Multi-Dim array ($x[kone][ktwo][...] = xy)
	 */
	function genMultiDimArr($keyArr, $value) {
		$x = array();
		if (count($keyArr) > '1') {
			$val = array_shift($keyArr);
			$x[$val] = lib_div::genMultiDimArr($keyArr, $value);
		} else {
			$x[$keyArr[0]] = $value;
		}
		return $x;
	}



	function &makeInstance($className)	{
		$newInstance = class_exists('ux_'.$className) ? lib_div::makeInstance('ux_'.$className) : new $className; 
		return $newInstance;
	}


	/**
	 * Checks for malicious file paths.
	 * Returns true if no '//', '..' or '\' is in the $theFile
	 * This should make sure that the path is not pointing 'backwards' and further doesn't contain double/back slashes.
	 * So it's compatible with the UNIX style path strings.
	 *
	 * @param       string          Filepath to evaluate
	 * @return      boolean         True, if no '//', '..' or '\' is in the $theFile
	 * @todo        Possible improvement: Should it rawurldecode the string first to check if any of these characters is encoded ?
	 */
	function validPathStr($theFile) {
		if (!strstr($theFile,'//') && !strstr($theFile,'..') && !strstr($theFile,'\\')) return true;

		return false;
	}


	/**
	 * Print error message with header, text etc.
	 *
	 * @param	string		Header string
	 * @param	string		Content string
	 * @param	boolean	Print header.
	 * @return	void
	 */
	function printError($text,$header,$head=1)	{
		// This prints out a TUPA error message.

		echo $head?'<html>
			<head>
				<title>Error!</title>
			</head>
			<body bgcolor="white" topmargin="100" leftmargin="0" marginwidth="0" marginheight="100">':'';
		echo '<div align="center">
				<table border="0" cellspacing="0" cellpadding="0" width="333">
					<tr>
						<td bgcolor="black">
							<table width="100%" border="0" cellspacing="1" cellpadding="10">
								<tr>
									<td bgcolor="#F4F0E8">
										<font face="verdana,arial,helvetica" size="2">';
		echo '<b><center><font size="+1">'.$header.'</font></center></b><br />'.$text;
		echo '							</font>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>';
		echo $head?'
			</body>
		</html>':'';
	}

	/**
	 * Changes the the first char of string to upper case.
	 * Optional it also cuts of the last char
	 *
	 * @param	string		String to process
	 * @param	boolean	Cut last char if set
	 * @return	string		resulting string
	 */
	function firstUpper($string, $cutLast=false) {
		$lenght = '';
		$first = strtoupper(substr($string, '0', '1'));
		$cutLast ? $lenght = strlen($string)-2 : $lenght = strlen($string)-1;
		$rest = substr($string, '1', $lenght);

		return $first.$rest;
	}

	/**
	 * Check if an non integer value is in array.
	 *
	 * @param	array		array to check
	 * @return	boolean
	 */
	function isNonIntInArray($arr) {
		foreach($arr as $k => $v) {
			if (is_array($arr[$k])) {
				if (lib_div::isNonIntInArray($arr[$k])) return true;
			} else {
				if ($arr[$k] == (int)$arr[$k]) $arr[$k] = (int)$arr[$k];
				if (!is_int($arr[$k])) return true;
			}
		}
		return false;
	}


	/**
	 * Checks if a value exists in a comma seperated list of values
	 *
	 * @param	string		value to check
	 * @param  	string		list of possible values
	 * @return	boolean
	 */
	function inList($item, $list) {
		return strstr(','. $list .',', ','. $item .',') ? true : false;
		/*$listArr = lib_div::trimExplode(',', $list);
		if (in_array($value, $listArr)) {
			return true;
		} else {
			return false;
		}*/
	}



	/**
	 * Removes an item from a list of items.
	 *
	 * @param	string		element to remove
	 * @param	string		comma-separated list of items (string)
	 * @param 	string		explode list with this char
	 * @return	string		new comma-separated list of items
	 */
	function rmFromList($element, $list, $separator=',') {
		$items = explode($separator, $list);
		while(list($k, $v) = each($items))	{
			if ($v == $element) { unset($items[$k]); }
		}
		return implode($separator, $items);
	}



	/**
	 * Split single form rows to array.
	 * The submitted formdata is in the format name_1.
	 * The number is splitted from array and used as row number in generated array.
	 *
	 * @param	array		formdata arrays
	 * @param 	string		the field which holds the id
	 * @param 	integer		the id
	 * @todo 	Change how empty rows are marked
	 */
	function parseRecordsToArray($fData, $idField=false, $id=false) {
		$skipKey = '';
		foreach ($fData['data'] as $key => $value) {
			$keyArr = explode('_', $key);
			if (is_numeric($keyArr['1']) && $skipKey != $keyArr['1']) {
				// Check the type value - when empty remove record from array
				if ($keyArr['0'] == 'type' && $value == '') {
					unset($dataArr[$keyArr['1']]);
					$skipKey = $keyArr['1'];
					continue;
				} elseif ($keyArr['0'] == 'prio' && $value == '') {
					continue;
				}
				$dataArr[$keyArr['1']][$keyArr['0']] = $value;

				// Add the id of the template (on update only)
				if ($id && $idField) {
					$dataArr[$keyArr['1']][$idField] = $id;
				}

				// Add id
				if (array_key_exists('id_'. $keyArr['1'], $fData['hidden'])) { 
					$dataArr[$keyArr['1']]['id'] = $fData['hidden']['id_'. $keyArr['1']];
				}

				// Add the sorting
				$dataArr[$keyArr['1']]['tupasorting'] = $fData['hidden']['tupasorting_'. $keyArr['1']];
			}
		}
		return $dataArr;
	}



	/**
	 * Check an array of record rows for valid values.
	 * Already done on Client side. But we want to be sure of course.
	 *
	 * @param	array		formdata arrays
	 * @return	array		array of single rows
	 */
	function checkRecordFields($dataArr) {
		global $TUPA_CONF_VARS, $LANG;

		// Some used special regular expressions
		$regexDomain = '/'. $TUPA_CONF_VARS['REGEX']['templateDomain'] .'/i';
		$regexHost = '/'. $TUPA_CONF_VARS['REGEX']['host'] .'/i';
		$regexIPv4 = '/'. $TUPA_CONF_VARS['REGEX']['IPv4'] .'/';
		$regexIPv6 = '/'. $TUPA_CONF_VARS['REGEX']['IPv6'] .'/';
		$regexPTR = '/'. $TUPA_CONF_VARS['REGEX']['PTR'] .'/i';
		$regexHINFO = '/'. $TUPA_CONF_VARS['REGEX']['HINFO'] .'/i';
		$regexRP = '/'. $TUPA_CONF_VARS['REGEX']['RP'] .'/i';
		$regexSRVN = '/'. $TUPA_CONF_VARS['REGEX']['SRV_NAME'] .'/i';
		$regexSRVC = '/'. $TUPA_CONF_VARS['REGEX']['SRV_CONTENT'] .'/i';
		$regexUrl = '/'. $TUPA_CONF_VARS['REGEX']['url'] .'/i';

		while (list(, $dataRow) = each($dataArr)) {
			switch ($dataRow['type']) {
				case 'SOA':
					if (!is_numeric($dataRow['ttl']) || $dataRow['ttl'] == '' || !($dataRow['ttl'] >= $TUPA_CONF_VARS['DNS']['minSoaTTL'] && $dataRow['ttl'] <= $TUPA_CONF_VARS['DNS']['maxSoaTTL'])) return $LANG->getLang('soaTtlError', array('min'=>$TUPA_CONF_VARS['DNS']['minSoaTTL'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaTTL']));
					// Split the SOA content: [0]=>primary, [1]=>hostmaster, [2]=>serial, [3]=>refresh, [4]=>retry, [5]=>expire, [6]=>ttl
					$soaData = explode(' ', $dataRow['content']);
					if ($soaData[0] == '' || !preg_match($regexDomain, $soaData[0])) return $LANG->getLang('soaPrimaryError');
					if ($soaData[1] == '' || !lib_div::validEmail($soaData[1])) return $LANG->getLang('soaHostmasterError');
					if (!is_numeric($soaData[3]) || $soaData[3] == '' || !($soaData[3] >= $TUPA_CONF_VARS['DNS']['minSoaRefresh'] && $soaData[3] <= $TUPA_CONF_VARS['DNS']['maxSoaRefresh'])) return $LANG->getLang('soaRefreshError', array('min'=>$TUPA_CONF_VARS['DNS']['minSoaRefresh'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaRefresh']));
					if (!is_numeric($soaData[4]) || $soaData[4] == '' || !($soaData[4] >= $TUPA_CONF_VARS['DNS']['minSoaRetry'] && $soaData[4] <= $TUPA_CONF_VARS['DNS']['maxSoaRetry'])) return $LANG->getLang('soaRetryError', array('min'=>$TUPA_CONF_VARS['DNS']['minSoaRetry'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaRetry']));
					if (!is_numeric($soaData[5]) || $soaData[5] == '' || !($soaData[5] >= $TUPA_CONF_VARS['DNS']['minSoaExpire'] && $soaData[5] <= $TUPA_CONF_VARS['DNS']['maxSoaExpire'])) return $LANG->getLang('soaExpireError', array('min'=>$TUPA_CONF_VARS['DNS']['minSoaExpire'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaExpire']));
					if (!is_numeric($soaData[6]) || $soaData[6] == '' || !($soaData[6] >= $TUPA_CONF_VARS['DNS']['minSoaTTL'] && $soaData[6] <= $TUPA_CONF_VARS['DNS']['maxSoaTTL'])) return $LANG->getLang('soaTtlError', array('min'=>$TUPA_CONF_VARS['DNS']['minSoaTTL'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaTTL']));
					break;
				case 'A':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexIPv4, $dataRow['content'])) return $LANG->getLang('ipError');
					break;

				case 'AAAA':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexIPv6, $dataRow['content'])) return $LANG->getLang('ipv6Error');
					break;

				case 'MX':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (!isset($dataRow['prio']) || !is_numeric($dataRow['prio']) || !($dataRow['prio'] >= 0 && $dataRow['prio'] <= 65535)) return $LANG->getLang('prioError');
					if (!preg_match($regexDomain, $dataRow['content'])) return $LANG->getLang('domainError');
					break;

				case 'NS':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexDomain, $dataRow['content'])) return $LANG->getLang('domainError');
					break;

				case 'PTR':
					if ($dataRow['name'] != '' && !preg_match($regexPTR, $dataRow['name'])) return $LANG->getLang('ptrError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexDomain, $dataRow['content'])) return $LANG->getLang('domainError');
					break;

				case 'CNAME':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexDomain, $dataRow['content'])) return $LANG->getLang('domainError');
					break;

				case 'TXT':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					break;

				case 'HINFO':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexHINFO, $dataRow['content'])) return $LANG->getLang('hinfoError');
					break;

				case 'RP':
					if ($dataRow['name'] != '' && !preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexRP, $dataRow['content'])) return $LANG->getLang('rpError');
					break;

				case 'SRV':
					if (!preg_match($regexSRVN, $dataRow['name'])) return $LANG->getLang('srvNameError');
					if (!isset($dataRow['prio']) || !is_numeric($dataRow['prio']) || !($dataRow['prio'] >= 0 && $dataRow['prio'] <= 65535)) return $LANG->getLang('prioError');
					if (!preg_match($regexSRVC, $dataRow['content'])) return $LANG->getLang('srvContentError');
					break;

				case 'MBOXFW':
					if (!lib_div::validEmail($dataRow['name'])) return $LANG->getLang('emailError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!lib_div::validEmail($dataRow['content'])) return $LANG->getLang('emailError');
					break;

				case 'URL':
					if (!preg_match($regexHost, $dataRow['name'])) return $LANG->getLang('hostError');
					if (isset($dataRow['prio']) && $dataRow['prio'] != '') return $LANG->getLang('prioEmptyError');
					if (!preg_match($regexUrl, $dataRow['content'])) return $LANG->getLang('urlError');
					break;
			}
		}
	}


	/**
	 * Initialisize the marker array and sets default keys
	 *
	 * @return	array		markerArray
	 */
	function setDefaultMarkerArray () {
		$markerArray = array();
		$markerArray['text_style'] = 'form-text-style';
		$markerArray['text_style_right'] = 'form-text-style-right';
		$markerArray['title_style'] = 'form-subtitle';
		$markerArray['title_style_2'] = 'form-subtitle-2';
		$markerArray['title_style_right'] = 'form-subtitle-right';
		$markerArray['table_header'] = 'table-header';

		return $markerArray;
	}


	/**
	 * Generates a simple array from 1 to count.
	 *
	 * @param	integer		numbers of pages to generate
	 * @return	array		Array of page numbers
	 */
	function genSimplePageArray($count) {
		$pageArray = array();
		for ($i = '1'; $i <= $count; $i++) {
			$pageArray[] = $i;
		}
		return $pageArray;
	}


	/**
	 * Checks if the path/file exists and returns it, or returns the default (fallback) skin file if it does not exists in skin dir.
	 *
	 * @param 	string		Path to file to check
	 * @return	string		HTML image tag.
	 */
	function getSkinFilePath($path) {
		global $TUPA_CONF_VARS;
		// Check relative path
		$checkPath = substr($path, 0, 1) == '/' ? $path : PATH_site . $path;

		// Check if file exists in users selected skin
		if (file_exists($checkPath)) {
			return $path;
		} else {
			$skin = $TUPA_CONF_VARS['SKINS']['skin'];
			$skinFallback = $TUPA_CONF_VARS['SKINS']['skinFallback'];
			return str_replace('skins/'. $skin .'/', 'skins/'. $skinFallback .'/', $path);
		}
	}


	/**
	 * A replacement of the PHP isset() function because it is buggy on some 4.3.x versions when checking arrays.
	 *
	 * @param 	mixed		Value to check
	 * @param 	string		Array path to check ('first=>second' checks $x['first']['second'])
	 * @param 	boolean	Strict valur check (not '', 0 or NULL)
	 * @return	string		HTML image tag.
	 */
	function isset_value($var, $check='', $strict=true) {
		// Check if $var is an array
		if (is_array($var)) {
			// If $check is set we have to check a subarray too
			if ($check!='') {
				// Get subarray names, cut the first one and put the rest back to a list
				$checkArr = explode('=>', $check);
				$thisCheck = array_shift($checkArr);
				$check = implode('=>', $checkArr);

				//$TBE_TEMPLATE->addMessage('debug', $thisCheck);
				if (array_key_exists($thisCheck, $var)) {
					return lib_div::isset_value($var[$thisCheck], $check, $strict);
				} else {
					return false;
				}
			// Is array but no subarrray to check => is true anyway
			} else {
				return true;
			}
		// Not an array, check if it has an valid value
		} elseif (!is_array($var) && $check == '') {
			if ($strict) {
				if ($var != '' && $var != null) return true ;
				//if ($var) return true ;
				return false;
			} else {
				return true;
			}
		// If it is something other, or not a valid value return false
		} else {
			return false;
		}
	}



	/**
	 * Splits the preferences and permissions from the rest of the submitted values in separate array to serialize them for database.
	 * The function checks the submitted data also.
	 *
	 * @param 	array		Formdata
	 * @param 	string		Has to be "user" or "group" to get the current preferences from $TUPA_CONF_VARS
	 * @param 	array		User or group id to get current preferences from.
	 * @return	array		Data array to insert or update in database
	 */
	function splitSpecialPartsFromFormdata($fd, $table, $id='') {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $LANG;
		settype($id, 'integer');
		$error = false;
		$dataArr = array();
		$permArr = array();
		$prefArr = array();

		if (!is_array($fd) || !is_string($table) || !is_int($id)) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('wrongDataError'));
			return false;
		}

		// Get the current prefs
		if ($id) {
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('preferences', $table, 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
			$row = mysql_fetch_assoc($res);
			$prefArr = unserialize($row['preferences']);
		}

		// Get the correct subarray or return an error
/*		if ($part == 'user') { $part = 'PREFS'; } elseif ($part == 'group') { $part = 'GPREFS'; } else { $part=false; }
		if (!$part) {
			$TBE_TEMPLATE->addMessage('debug', 'Developer: You have to use "user" or "group" in function splitPrefsFromFormdata()!');
			return false;
		}*/

		foreach ($fd as $key => $value) {
			if (substr($key, '0', '4') == 'pref') {
				$key = substr($key, '5');
				// Check vars
				$regexDomain = '/'. $TUPA_CONF_VARS['REGEX']['domain'] .'/';
				switch ($key) {
					case 'SYS_language':
						if (strlen($value) > '10') $error = $LANG->getLang('languageError');
						break;
					case 'PREFS_linesPerSite':
						if (!is_numeric($value) || !($value >= $TUPA_CONF_VARS['PREFS']['minLinesPerSite'] && $value <= $TUPA_CONF_VARS['PREFS']['maxLinesPerSite'])) $error = $LANG->getLang('rowsPerSiteError', array('min'=>$TUPA_CONF_VARS['PREFS']['minLinesPerSite'], 'max'=>$TUPA_CONF_VARS['PREFS']['maxLinesPerSite']));
						break;
					case 'PREFS_naviShowPages':
						if (!is_numeric($value) || !($value >= $TUPA_CONF_VARS['PREFS']['minNaviShowPages'] && $value <= $TUPA_CONF_VARS['PREFS']['maxNaviShowPages'])) $error = $LANG->getLang('naviShowPagesError', array('min'=>$TUPA_CONF_VARS['PREFS']['minNaviShowPages'], 'max'=>$TUPA_CONF_VARS['PREFS']['maxNaviShowPages']));
						break;
					case 'DNS_soaPrimary':
						if ($value != '' && !preg_match($regexDomain, $value)) $error = $LANG->getLang('soaPrimaryError');
						break;
					case 'DNS_soaHostmaster':
						if ($value != '' && !lib_div::validEmail($value)) $error = $LANG->getLang('soaPrimaryError');
						break;
				}
				$keyArr = explode('_', $key);
				$tmpPrefArr = lib_div::genMultiDimArr($keyArr, $value);
				$prefArr = lib_div::array_merge_recursive_overrule($prefArr, $tmpPrefArr, '0');
			} elseif (substr($key, '0', '4') == 'perm') {
				if (substr($key, strlen($key)-3) != 'all') {
					// value has to be 0 or 1
					if (!lib_div::inList($value, ',0,1')) {
						// set error message
						$error = $LANG->getLang('permissionProblemError');
					}
					$key = substr($key, '5');
					$keyArr = explode('_', $key);
					$tmpPermArr = lib_div::genMultiDimArr($keyArr, $value);
					$permArr = lib_div::array_merge_recursive_overrule($permArr, $tmpPermArr, '0');
				}
			} else {
				// Skip if it is a temp value
				if (substr($key, '0', '4') == 'temp') continue;
				 
				$groupId = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($id);
				switch($key) {
					case 'grp_id':
						if (!is_numeric($value)) $error = $LANG->getLang('groupError');
						break;
					case 'email':
						if (!lib_div::validEmail($value)) $error = $LANG->getLang('emailError');
						break;
					case 'max_users':
						if (!is_numeric($value)) $error = $LANG->getLang('maxGroupUsersError');
						break;
					case 'max_domains':
						if ($table == 'users') {
							$groupLimit = $GLOBALS['TUPA_DB']->exec_SELECTgetLimitOfGroup($groupId, 'max_domains');
							if ($groupLimit > 0 && (!is_numeric($value) || $value > $groupLimit)) $error = $LANG->getLang('maxDomainsError', array('groupLimit'=>$groupLimit));
						} elseif ($table == 'groups') {
							if (!is_numeric($value)) $error = $LANG->getLang('maxGroupDomainsError');
						}
						break;
					case 'max_templates':
						if ($table == 'users') {
							$groupLimit = $GLOBALS['TUPA_DB']->exec_SELECTgetLimitOfGroup($groupId, 'max_templates');
							if ($groupLimit > 0 && (!is_numeric($value) || $value > $groupLimit)) $error = $LANG->getLang('maxTemplatesError', array('groupLimit'=>$groupLimit));
						} elseif ($table == 'groups') {
							if (!is_numeric($value)) $error = $LANG->getLang('maxGroupTemplatesError');
						}
						break;
				}
				$dataArr[$key] = $value;
			}

			// Stop processing if we have an error in submitted values
			if ($error) {
				$addTasks = '';
				if ($table == 'users') $addTasks = 'clearFieldValues(\'formdata\', \'oldpassword,password,cpassword\');';
				$TBE_TEMPLATE->addMessage('error', $error, $addTasks);
				return false;
			}
		}

		// serialize the preferences and permissions, and add it to the dataArr to save in db
		if (count($prefArr) > 0) {
			$dataArr['preferences'] = serialize($prefArr);
		}
		if (count($permArr) > 0) {
			$dataArr['permissions'] = serialize($permArr);
		}

		return $dataArr;
	}



	/**
	 * Checks if the group or user limit is exeeded and sets message.
	 *
	 * @param 	array		config array
	 * @return	boolean	exeeded or not
	 */
	function limitExceeded($conf) {
		global $USER, $LANG, $TBE_TEMPLATE;
		$limitExceeded = false;

		if ($conf['csite'] != 'groups' && $USER->hasPerm($conf['csite'] .'_add,'. $conf['csite'] .'_add_group')) {
			if ($USER->hasPerm('users_add') && $conf['csite'] == 'users') {
				$groupId = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser();
				$groupLimit = $GLOBALS['TUPA_DB']->exec_SELECTgetLimitOfGroup($groupId, 'max_users');
				$groupItemCount = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'users', 'grp_id='. lib_DB::fullQuoteStr($groupId)));
				$limitExceeded = $groupLimit > '0' && $groupLimit <= $groupItemCount ? true : false;
			} else {
				$groupId = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser();
				$groupLimit = $GLOBALS['TUPA_DB']->exec_SELECTgetLimitOfGroup($groupId, 'max_'. $conf['csite']);
				$userLimit =  $GLOBALS['TUPA_DB']->exec_SELECTgetLimitOfUser('', 'max_'. $conf['csite']);
				if ($conf['csite'] == 'domains') {
					$userIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetUserIdsOfGroup($groupId);
					$groupItemCount = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('usr_id', 'domain_owners', 'usr_id IN ('. lib_DB::fullQuoteStrList($userIdList) .')'));
					$userItemCount = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('usr_id', 'domain_owners', 'usr_id='. lib_DB::fullQuoteStr($_SESSION['uid'])));
				} else {
					$userIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetUserIdsOfGroup($groupId);
					$groupItemCount = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('id', $conf['csite'], 'usr_id IN ('. lib_DB::fullQuoteStrList($userIdList) .')'));
					$userItemCount = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('id', $conf['csite'], 'usr_id='. lib_DB::fullQuoteStr($_SESSION['uid'])));
				}
				$limitExceeded = $groupLimit > '0' && $groupLimit <= $groupItemCount || $userLimit > '0' && $userLimit <= $userItemCount ? true : false;
			}
		}
		if ($limitExceeded) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang($conf['csite'] .'LimitExceeded'));
		}
		return $limitExceeded;
	}


	/**
	 * Gets the skin file path and image size.
	 *
	 * @param 	string		path of image (in skin folder)
	 * @return	array		image path and size
	 */
	function getImageInfo($path) {
		$icon = array();
		$icon['path'] = lib_div::getSkinFilePath(PATH_images . $path);
		$icon['size'] = @getimagesize(PATH_site . $icon['path']);
		return $icon;
	}


	/**
	 * Split list into an array (ex. key1=val2,key2=val2,...)
	 *
	 * @param 	string		First delimiter
	 * @param 	string		Second delimiter
	 * @param 	string		The string to split
	 * @return	array		Resulting array
	 */
	function doubleTrimExplode($delim1, $delim2, $string) {
		$resultArr = array();
		if ($string) {
			$firstArr = lib_div::trimExplode($delim1, $string);
			foreach ($firstArr as $value) {
				$value = lib_div::trimExplode($delim2, $value);
				$resultArr[$value[0]] = $value[1];
			}
		}
		return $resultArr;
	}


	/**
	 * Gets content of file
	 *
	 * @param 	string		File name
	 * @return	string		Content of file
	 */
	function getFileContent($file)	{
		$content = '';

		if($fd = fopen($file,'rb'))    {
			while (!feof($fd))	{
				$content.=fread($fd, 5000);
			}
			fclose($fd);
			return $content;
		}
	}




	/**
	 * Gets rtrim'ed content of file into an array line by line
	 *
	 * @param 	string		File name
	 * @return	array		Content of file
	 */
	function getFileRtrim($file)	{
		$content = array();
		if($results = @file($file))    {
			foreach ($results as $value) {
				$content[] = rtrim($value);
			}
			return $content;
		}
		return false;
	}



	/**
	 * Writes $content to the file $file
	 *
	 * @param	string		Filepath to write to
	 * @param	string		Content to write
	 * @return	boolean		True if the file was successfully opened and written to.
	 */
	function writeFile($file,$content)	{
		if($fd = @fopen($file,'wb'))	{
			fwrite($fd, $content);
			fclose($fd);

			//t3lib_div::fixPermissions($file);	// Change the permissions of the file

			return true;
		}
		return false;
	}


	/**
	 * Gets microtime in ms.
	 *
	 * @return	float		time in ms
	 */
	function microtime_float() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}


	/**
	 * Tries to recursive create a directory
	 *
	 * @param 	string		Absolute path of directory
	 * @return	boolean	True if the creation was successfully
	 */
	function mkdirRecursive($path) {
		if (!is_dir($path)) {
			lib_div::mkdirRecursive(dirname($path));
			if (!@mkdir($path, 0700)) return false;
		}
		return true;
	}


	/**
	 * Tries to recursive delete a directory
	 *
	 * @param 	string		Absolute path of directory
	 * @return	boolean	True if the deletion was successfully
	 */
	function rmdirRecursive($path) {
		$path = lib_div::checkAddTrailingSlash($path);

		if ($handle = opendir($path)) {
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..') {
					if (is_dir($path . $file)) {
						if (!lib_div::rmdirRecursive($path . $file)) return false;
					} elseif (is_file($path . $file)) {
						if (!unlink($path . $file)) return false;
					}
				}
			}
			closedir($handle);

			if (!@rmdir($path)) return false;

			return true;
		}
		return false;
	}



	/**
	 * Tries to recursive create directoryon a ftp server
	 *
	 * @param 	integer?	FTP connectio ID
	 * @param 	string		Absolute path of directory on ftp server
	 * @return	boolean	True if the creation was successfully
	 */
	function ftp_mkdirRecursive($connId, $path) {
		if (!@ftp_chdir($connId, $path)) {
			lib_div::ftp_mkdirRecursive($connId, dirname($path));
			if (!@ftp_mkdir($connId, $path)) return false;
			// Try to chmod new dir to make it a little bit safer
			lib_div::ftpChmod($connId, 0700, $path);
		}
		return true;
	}


	/**
	 * Tries to chmod file/dir on ftp server
	 *
	 * @param 	integer?	FTP connectio ID
	 * @param 	string		Absolute path of directory on ftp server
	 * @return	boolean	True if the creation was successfully
	 * @todo 			Check PHP version because PHP5 has implemented function of this.
	 */
	   function ftpChmod($ftpstream, $chmod, $file) {
	   	$old = error_reporting();
	   	error_reporting(0);
	   	$result = @ftp_site($ftpstream, "CHMOD ".$chmod." ".$file);
	   	error_reporting($old);
	   	return $result;
	   }


	/**
	 * Tries to recursive create directory on a ssh server
	 *
	 * @param 	integer?	SSH connectio ID
	 * @param 	integer?	SFTP subsystem connectio ID
	 * @param 	string		Absolute path of directory on ssh server
	 * @return	boolean	True if the creation was successfully
	 */
	function ssh_mkdirRecursive($connId, $sftpId, $path) {
		$stdio  = ssh2_exec($this->connId, 'cd '. $path);
		$stderr = ssh2_fetch_stream($stdio, SSH2_STREAM_STDERR);
		stream_set_blocking($stdio, true);

		while($data = fread($stdio, 4096)) {
			$dataStd .= $data;
		}
		while($data = fread($stderr, 1024)) {
			$dataErr .= $data;
		}
		fclose($stdio);
		fclose($stderr);

		if ($dataErr) {
			lib_div::ssh_mkdirRecursive($connId, $sftpId, dirname($path));
			if (!@ssh2_sftp_mkdir($sftpId, $path)) return false;
			// Try to chmod new dir to make it a little bit safer
			ssh2_exec($connId, 'chmod 700 '. $path);
		}
		return true;
	}


	/**
	 * Opposite of gzencode. Decodes a gzip'ed file.
	 *
	 * @param 	string		compressed data
	 * @return	boolean	True if the creation was successfully
	 */
	function gzdecode($data) {
		$len = strlen($data);
		if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
			return false;  // Not GZIP format (See RFC 1952)
		}
		$method = ord(substr($data,2,1));  // Compression method
		$flags  = ord(substr($data,3,1));  // Flags
		if ($flags & 31 != $flags) {
			// Reserved bits are set -- NOT ALLOWED by RFC 1952
			return false;
		}
		// NOTE: $mtime may be negative (PHP integer limitations)
		$mtime = unpack("V", substr($data,4,4));
		$mtime = $mtime[1];
		$xfl  = substr($data,8,1);
		$os    = substr($data,8,1);
		$headerlen = 10;
		$extralen  = 0;
		$extra    = "";
		if ($flags & 4) {
			// 2-byte length prefixed EXTRA data in header
			if ($len - $headerlen - 2 < 8) {
				return false;    // Invalid format
			}
			$extralen = unpack("v",substr($data,8,2));
			$extralen = $extralen[1];
			if ($len - $headerlen - 2 - $extralen < 8) {
				return false;    // Invalid format
			}
			$extra = substr($data,10,$extralen);
			$headerlen += 2 + $extralen;
		}

		$filenamelen = 0;
		$filename = "";
		if ($flags & 8) {
			// C-style string file NAME data in header
			if ($len - $headerlen - 1 < 8) {
				return false;    // Invalid format
			}
			$filenamelen = strpos(substr($data,8+$extralen),chr(0));
			if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
				return false;    // Invalid format
			}
			$filename = substr($data,$headerlen,$filenamelen);
			$headerlen += $filenamelen + 1;
		}

		$commentlen = 0;
		$comment = "";
		if ($flags & 16) {
			// C-style string COMMENT data in header
			if ($len - $headerlen - 1 < 8) {
				return false;    // Invalid format
			}
			$commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0));
			if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
				return false;    // Invalid header format
			}
			$comment = substr($data,$headerlen,$commentlen);
			$headerlen += $commentlen + 1;
		}

		$headercrc = "";
		if ($flags & 1) {
			// 2-bytes (lowest order) of CRC32 on header present
			if ($len - $headerlen - 2 < 8) {
				return false;    // Invalid format
			}
			$calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
			$headercrc = unpack("v", substr($data,$headerlen,2));
			$headercrc = $headercrc[1];
			if ($headercrc != $calccrc) {
				return false;    // Bad header CRC
			}
			$headerlen += 2;
		}

		// GZIP FOOTER - These be negative due to PHP's limitations
		$datacrc = unpack("V",substr($data,-8,4));
		$datacrc = $datacrc[1];
		$isize = unpack("V",substr($data,-4));
		$isize = $isize[1];

		// Perform the decompression:
		$bodylen = $len-$headerlen-8;
		if ($bodylen < 1) {
			// This should never happen - IMPLEMENTATION BUG!
			return null;
		}
		$body = substr($data,$headerlen,$bodylen);
		$data = "";
		if ($bodylen > 0) {
			switch ($method) {
				case 8:
				// Currently the only supported compression method:
				$data = gzinflate($body);
				break;
				default:
				// Unknown compression method
				return false;
			}
		} else {
			// I'm not sure if zero-byte body content is allowed.
			// Allow it for now...  Do nothing...
		}

		// Verifiy decompressed size and CRC32:
		// NOTE: This may fail with large data sizes depending on how
		//      PHP's integer limitations affect strlen() since $isize
		//      may be negative for large sizes.
		if ($isize != strlen($data) || crc32($data) != $datacrc) {
			// Bad format!  Length or CRC doesn't match!
			return false;
		}
		return $data;
	}


	/**
	 * Checks if a program is installed in path and executable
	 *
	 * @param 	string		Program name
	 * @param 	integer
	 * @return	boolean	True if the creation was successfully
	 */
	function checkProgramInPath($progName, $level=0) {
		$envPath = lib_div::trimExplode(':', $_ENV['PATH']);

		if ($level == 0) {
			reset($envPath);
			while (list(, $path) = each($envPath)) {
				if (@is_file($path .'/'. $progName) && @is_executable($path .'/'. $progName)) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Sends a simple plain text mail
	 *
	 * @param 	string		eMail address to send mail to
	 * @param 	string		Subject of eMail
	 * @param 	string		Main message
	 * @return	void
	 */
	function sendMail($to, $subject, $message) {
		global $TUPA_CONF_VARS;

		$fromEmail = $TUPA_CONF_VARS['SYS']['notificationEmail'];
		$fromName = $TUPA_CONF_VARS['SYS']['notificationName'];
		$message = wordwrap($message, 70);

		// Set mail headers
		$headers = '';
		$headers .= "X-Sender:  $to <$to>\n";
		$headers .="From: $fromName <$fromEmail>\n";
		$headers .= "Reply-To: $fromName <$fromEmail>\n";
		$headers .= "Date: ".date("r")."\n";
		$headers .= "Message-ID: <".date("YmdHis")."tupadns@".$_SERVER['SERVER_NAME'].">\n";
		$headers .= "Subject: $subject\n";
		$headers .= "Return-Path: $fromName <$fromEmail>\n";
		$headers .= "Delivered-to: $fromName <$fromEmail>\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/plain;charset=utf-8\n";
		$headers .= "X-Priority: 3\n";
		$headers .= "Importance: Normal\n";
		$headers .= "X-MSMail-Priority: Normal\n";
		$headers .= "X-Mailer: TUPA PowerDNS Administration\n";

		// Send mail
		mail($to, $subject, $message, $headers);
	}


	/**
	 * Calculates when the next backup should be executed
	 *
	 * @return	integer		Timestamp
	 */
	function calcNextBackupExec($freq, $time, $wday, $day) {
		global $BACKUP;

		// Split time into hours and minutes
		$time = lib_div::trimExplode(':', $time);

		switch ($freq) {
			case 1:		// Daily
				$backupTime = mktime($time[0], $time[1], 0, date("n"), date("j"), date("Y"));

				if (time() >= $backupTime) {
					return $backupTime + 86400;
				} else {
					return $backupTime;
				}
				break;
			case 2:		// Weekly
				$tmpTime = lib_div::getDateFromWeekNr(date("Y"), date("W"), $wday);
				$backupTime = mktime($time[0], $time[1], 0, date("n", $tmpTime), date("j", $tmpTime), date("Y", $tmpTime));

				if (time() >= $backupTime) {
					return $backupTime + 7 * 86400;
				} else {
					return $backupTime;
				}
				break;
			case 3:		// Monthly
				$backupDay = $BACKUP->day > date("t") ? date("t") : $day;
				$backupTime = mktime($time[0], $time[1], 0, date("n"), $backupDay, date("Y"));

				if (time() >= $backupTime) {
					if (date("n") == 12) {
						$month = 1;
						$year = date("Y") + 1;
					} else {
						$month = date("n") + 1;
						$year = date("Y");
					}

					$nextMonthDays = date("t", mktime(1, 0, 0, $month, 1, $year));

					$backupDay = $BACKUP->day > $nextMonthDays ? $nextMonthDays : $day;
					return  mktime($time[0], $time[1], 0, $month, $backupDay, $year);
				} else {
					return $backupTime;
				}
				break;
		}
		return false;
	}


	/**
	 * Gets timestamp with given year, week nr. and day of week
	 *
	 * @param 	integer		Year
	 * @param 	integer		Week nr.
	 * @param 	integer		Day of week (1=Monday, 7=Sunday)
	 * @return	integer		Timestamp
	 */
	function getDateFromWeekNr($aYear, $aWeek, $aDay) {
		$FirstDayOfWeek=1;		//First day of week is Monday
		$BaseDate=4;			//We calculate from 4/1 which is always in week 1
		$CJDDelta=2415019;		//Based on start of Chronological Julian Day
		$StartDate = floor(mktime(1,0,0,01,$BaseDate,$aYear)/86400)+25569;	//The date to start with
		$Offset = ($aWeek-1) * 7 - lib_div::mod(floor($StartDate) + $CJDDelta + 8 - $FirstDayOfWeek,7) + $aDay - 1;
		return ($StartDate + $Offset-25569)*86400-3600;
	}

	/**
	 * Additional function for getDateFromWeekNr()
	 *
	 * @param 	integer		Number
	 * @param 	integer		Div
	 * @return	integer		result
	 */
	function mod($number, $div) {
		return $number - floor($number/$div)*$div;
	}


	/**
	 * Gets the content of a file (or the output of a script) from a remote server
	 *
	 * @param 	string		URL to get
	 * @param 	integer		Port to connect to
	 * @param 	string		File to get on server
	 * @return	string		Content of remote page
	 */
/*	function getContentFromRemoteServer($host, $port, $file) {
		$content = '';

		$fd = fsockopen ($host, $port, $errno, $errstr, 10);
		if ($fd) {
//			fputs($fd, "Host: $host\r\n");
			fputs($fd, "GET $file HTTP/1.1\r\nHost: $host\r\nUser-Agent: PHP Script\r\nConnection: close\r\n\r\n");
//   			fputs($fd, "User-Agent: PHP Script\r\n");

			while (!feof($fd)) {
				$content .= fgets($fd, 5000);
			}
			fclose($fd);
		}

		return $content;
	}*/



	/**
	 * Removes all comments in "$content"
	 *
	 * @param 	string		content
	 * @return	string		Content
	 */
	function removeHtmlComments($content) {
		return preg_replace ("/<!--.+?-->/is", "", $content);
	}



	/**
	 * Checks recursivly if directory and content is writeable
	 *
	 * @param 	string		Directory path
	 * @return	boolean	Everything is writeable or not
	 */
	function checkDirContentWritable($dir) {
		$dir = lib_div::checkAddTrailingSlash($dir);

		$folder = opendir($dir);
		while($file = readdir( $folder )) {
			if($file != '.' && $file != '..' &&
				(!is_writable($dir . $file) || (is_dir($dir . $file) && !lib_div::checkDirContentWritable($dir . $file)))) {
				closedir($folder);
				return false;
			}
		}
		closedir($folder);
		return true;
	}




	/**
	 * Returns HTML-code, which is a visual representation of a multidimensional array
	 * Returns false if $array_in is not an array
	 *
	 * @param	array		Array to view
	 * @return	string		HTML output
	 */
	function view_array($array_in) {
		if (is_array($array_in)) {
			$result='<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">';
			if (!count($array_in))	{$result.= '<tr><td><font face="Verdana,Arial" size="1"><b>'.htmlspecialchars("EMPTY!").'</b></font></td></tr>';}
			while (list($key,$val)=each($array_in))	{
				$result.= '<tr><td><font face="Verdana,Arial" size="1">'.htmlspecialchars((string)$key).'</font></td><td>';
				if (is_array($array_in[$key])) {
					$result.=lib_div::view_array($array_in[$key]);
				} else
					$result.= '<font face="Verdana,Arial" size="1" color="red">'.nl2br(htmlspecialchars((string)$val)).'<br /></font>';
				$result.= '</td></tr>';
			}
			$result.= '</table>';
		} else {
			$result  = false;
		}
		return $result;
	}


	/**
	 * Makes debug output
	 * Prints $var in bold between two vertical lines
	 * If not $var the word 'debug' is printed
	 * If $var is an array, the array is printed by lib_div::print_array()
	 *
	 * @param	mixed		Variable to print
	 * @param	mixed		If the parameter is a string it will be used as header. Otherwise number of break tags to apply after (positive integer) or before (negative integer) the output. If it is 999 strings are wrapped with nl2br() function.
	 * @return	void
	 */
	function debug($var='',$brOrHeader=0) {
		global $TBE_TEMPLATE;

		$result = '';

		if ($brOrHeader && !lib_div::testInt($brOrHeader)) {
			$result .= '<table border="0" cellpadding="0" cellspacing="0" bgcolor="white" style="border:0px; margin-top:3px; margin-bottom:3px;"><tr><td style="background-color:#bbbbbb; font-family: verdana,arial; font-weight: bold; font-size: 10px;">'.htmlspecialchars((string)$brOrHeader).'</td></tr><td>';
		} elseif ($brOrHeader<0) {
			for($a=0;$a<abs(intval($brOrHeader));$a++) {
				$result .= '<br />';
			}
		}

		if (is_array($var)) {
			$result .= lib_div::view_array($var);
		} elseif (is_object($var))	{
			$result .= '<b>|Object:<pre>';
			$result .= print_r($var, true);
			$result .= '</pre>|</b>';
		} elseif ((string)$var!='') {
			$result .= '<b>|'. ($brOrHeader == 999 ? nl2br(htmlspecialchars((string)$var)) : htmlspecialchars((string)$var)) .'|</b>';
		} else {
			$result .= '<b>| debug |</b>';
		}

		if ($brOrHeader && !lib_div::testInt($brOrHeader)) {
			$result .= '</td></tr></table>';
		} elseif ($brOrHeader>0 && $brOrHeader != 999) {
			for($a=0;$a<intval($brOrHeader);$a++) {
				$result .= '<br />';
			}
		}

		$TBE_TEMPLATE->addMessage('debug', $result);
	}



	/**
	 * Untars a file
	 *
	 * @param	string		Path to file
	 * @return	void
	 * @author 	Dennis Wronka
	 */
	function untar($tarfile) {
		$workDir = lib_div::checkAddTrailingSlash(dirname($tarfile));

		$tarfile = fopen($tarfile, 'r');
		if ($tarfile === false) return false;

		$datainfo = '';
		$data = '';
		while (!feof($tarfile)) {
			$readdata = fread($tarfile, 512);
			if (substr($readdata, 257, 5) == 'ustar') {
				if (!empty($datainfo)) {
					$poscount = 0;
					$name = '';
					while (substr($datainfo,$poscount, 1) != chr(0)) {
						$name .= substr($datainfo, $poscount, 1);
						$poscount++;
					}

					if (!empty($name)) {
						if (substr($name, -1) == '/') {
							// Create dir
							mkdir($workDir . $name);
						} else {
							// Extract
							$datasize = strlen($data) - 1;
							while ((substr($data, $datasize, 1) == chr(0)) && ($datasize > -1)) {
								$datasize--;
							}
							$datasize++;
							$filedata = '';
							for ($datacount = 0; $datacount < $datasize; $datacount++) {
								$filedata .= substr($data, $datacount, 1);
							}
							$file = fopen($workDir . $name,'w');
							fwrite($file, $filedata);
							fclose($file);
						}
					}
					$datainfo = $readdata;
					$data = '';
				} else {
					$datainfo = $readdata;
				}
			} else {
				$data .= $readdata;
			}
		}

		if (!empty($datainfo)) {
			$poscount = 0;
			$name='';
			while (substr($datainfo, $poscount, 1) != chr(0)) {
				$name .= substr($datainfo, $poscount, 1);
				$poscount++;
			}

			if (!empty($name)) {
				if (substr($name, -1) == '/') {
					// Create dir
					mkdir($workDir . $name);
				} else {
					// Extract
					$datasize = strlen($data) - 1;
					while ((substr($data, $datasize, 1) == chr(0)) && ($datasize > -1)) {
						$datasize--;
					}
					$datasize++;
					$filedata = '';
					for ($datacount = 0; $datacount < $datasize; $datacount++) {
						$filedata .= substr($data, $datacount, 1);
					}
					$file = fopen($workDir . $name,'w');
					fwrite($file, $filedata);
					fclose($file);
				}
			}
			$datainfo = $readdata;
			$data='';
		}
		fclose($tarfile);

		return true;
	}



	/**
	 * Gets the name of the uncopmressed filename
	 *
	 * @param	string		Path to file
	 * @return	void
	 * @author 	Dennis Wronka
	 */
	function getUncompressedFilename($compressedfilename) {
		$compressedfilenameparts = explode('.', $compressedfilename);
		for ($count = 0; $count < count($compressedfilenameparts) - 1; $count++) {
			$uncompressedfilenameparts[] = $compressedfilenameparts[$count];
		}
		if ($compressedfilenameparts[count($compressedfilenameparts) - 1] == 'tgz') {
			$uncompressedfilenameparts[] = 'tar';
		}
		$uncompressedfilename = implode('.', $uncompressedfilenameparts);

		return $uncompressedfilename;
	}



	/**
	 * Unbzips a file
	 *
	 * @param	string		Path to bz2 file
	 * @return	void
	 * @author 	Dennis Wronka
	 */
	function bunzip2($bz2file) {
		$content = '';
		$file = bzopen($bz2file,'r');

		if ($file === false) return false;

		while (!feof($file)) {
			$content .= bzread($file, 1);
		}
		bzclose($file);

		$file = fopen(lib_div::getUncompressedFilename($bz2file), 'w');
		if ($file === false) return false;

		fwrite($file, $content);
		fclose($file);

		unlink($bz2file);

		return true;
	}



	/**
	 * Ungzips a file
	 *
	 * @param	string		Path to gzip file
	 * @return	void
	 * @author 	Dennis Wronka
	 */
	function gunzip($gzfile) {
		$content = '';
		$file = gzopen($gzfile, 'r');
		if ($file === false) return false;

		while (!gzeof($file)) {
			$content .= gzread($file, 1);
		}
		gzclose($file);
		$file = fopen(lib_div::getUncompressedFilename($gzfile), 'w');
		if ($file === false) return false;

		fwrite($file, $content);
		fclose($file);

		unlink($gzfile);

		return true;
	}



	/**
	 * Gets type of an archive
	 *
	 * @param	string		Filename
	 * @return	string		Filetype
	 * @author 	Dennis Wronka
	 */
	function getArchiveType($filename) {
		$archivetype = 'unknown';
		$filenameparts = explode('.', $filename);
		$fileExt = $filenameparts[count($filenameparts) - 1];

		switch ($fileExt) {
			case 'gz':
			case 'tgz':
				$archivetype = 'gzip';
				break;
			case 'bz2':
				$archivetype = 'bzip2';
				break;
			case 'tar':
				$archivetype = 'tar';
				break;
		}

		return $archivetype;
	}



	/**
	 * Grabs a file from a remote server and writes it to a local file
	 *
	 * @param	string		URL to remote file
	 * @param	string		Path to local file
	 * @return	mixed		false on error or size of file
	 */
	function readRemoteWriteLocal($url, $local) {
		$content = '';

		// Check path
		if (!lib_div::validPathStr($local)) return false;

		// Open remote file and read it
		$fh = fopen($url, 'r');
		if ($fh === false) return false;

		while (!feof($fh)) {
			$content .= fread($fh, 1);
		}
		fclose($fh);

		// Write to local file
		$fh = fopen($local, 'w');
		if ($fh === false) return false;

		fwrite($fh, $content);
		fclose($fh);

		return strlen($content);
	}


	/**
	 * Optimizes the given JavaScript code: Removes all comments, line breakes and tablulators
	 *
	 * @param	string		Code to optimize
	 * @return	string		Optimized code
	 */
	function optimizeJsCode($content) {
		global $TUPA_CONF_VARS;

		$searchArr = array(
			'/\/\/.+?$/mis',
			'/\/\*.+?\*\//is',
			'/\t/',
			'/\r/',
			'/\n/'
		);

		$replaceArr = array(
			'',
			'',
			'',
			'',
			''
		);

		return $TUPA_CONF_VARS['SYS']['optimizeJsCode'] ? preg_replace($searchArr, $replaceArr, $content) : $content;
	}



	/**
	 * Checking for linebreaks in the string
	 *
	 * @param	string			String to test
	 * @return	boolean		Returns TRUE if string is OK
	 */
	function hasLinebreak($string) {
		if (ereg('['.chr(10).chr(13).']', $string)) return true;
		return false;
	}
}
?>
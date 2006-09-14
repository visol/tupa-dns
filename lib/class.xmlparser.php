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
 * Simple and good XML parser
 *
 * @package 	TUPA
 * @author	Kris ?? <kris@h3x.com>
 * @link 	http://www.devdump.com/phpxml.php
 */

class XMLParser {
	function GetChildren($vals, &$i)
	{
		$children = array();     // Contains node data

		/* Node has CDATA before it's children */
		if (isset($vals[$i]['value']))
		$children['VALUE'] = $vals[$i]['value'];

		/* Loop through children */
		while (++$i < count($vals)) {
			switch ($vals[$i]['type']) {
				/* Node has CDATA after one of it's children
				(Add to cdata found before if this is the case) */
				case 'cdata':
					if (isset($children['VALUE']))
					$children['VALUE'] .= $vals[$i]['value'];
					else
					$children['VALUE'] = $vals[$i]['value'];
					break;
				/* At end of current branch */
				case 'complete':
					if (isset($vals[$i]['attributes'])) {
						$children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
						$index = count($children[$vals[$i]['tag']])-1;

						if (isset($vals[$i]['value']))
						$children[$vals[$i]['tag']][$index]['VALUE'] = $vals[$i]['value'];
						else
						$children[$vals[$i]['tag']][$index]['VALUE'] = '';
					} else {
						if (isset($vals[$i]['value']))
						$children[$vals[$i]['tag']][]['VALUE'] = $vals[$i]['value'];
						else
						$children[$vals[$i]['tag']][]['VALUE'] = '';
					}
				break;
				/* Node has more children */
				case 'open':
					if (isset($vals[$i]['attributes'])) {
						$children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
						$index = count($children[$vals[$i]['tag']])-1;
						$children[$vals[$i]['tag']][$index] = array_merge($children[$vals[$i]['tag']][$index],XMLParser::GetChildren($vals, $i));
					} else {
						$children[$vals[$i]['tag']][] = XMLParser::GetChildren($vals, $i);
					}
					break;
				/* End of node, return collected data */
				case 'close':
					return $children;
			}
		}
	}

	/* Function will attempt to open the xmlloc as a local file, on fail it will attempt to open it as a web link */
	function GetXMLTree($xmlloc) {
		$vals = '';
		$index = '';

		if (file_exists($xmlloc)) {
			$data = implode('', file($xmlloc));
		} elseif (preg_match('/^(http|https)/', $xmlloc)) {
			// This one made troubles on my system sometimes
			// $fp = fopen($xmlloc,'r');
			// // Limit to 10Mb (which should be enough)
			// $data = fread($fp, 10240000);
			// $data = str_replace("\r", '', $data);
			// $data = str_replace("\n", '', $data);

			// This is an easier and simpler one (which works)
			$data = implode('', file($xmlloc));
		} else {
			$data = $xmlloc;
		}

		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $data, $vals, $index);
		xml_parser_free($parser);

		$tree = array();
		$i = 0;

		if (isset($vals[$i]['attributes'])) {
			$tree[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
			$index = count($tree[$vals[$i]['tag']])-1;
			$tree[$vals[$i]['tag']][$index] =  array_merge($tree[$vals[$i]['tag']][$index], XMLParser::GetChildren($vals, $i));
		} else {
			$tree[$vals[$i]['tag']][] = XMLParser::GetChildren($vals, $i);
		}

		return $tree;
	}
}

?>
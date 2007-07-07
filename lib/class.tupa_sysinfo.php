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
 * Show System information
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_sysinfo {

	/**
	 * Shows short system information (top-right)
	 *
	 * @return 	string
	 */
	function updateShortSysinfo() {
		global $TBE_TEMPLATE, $USER;

		if (!$USER->hasPerm('admin')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$detailsPath = lib_div::getSkinFilePath(PATH_images .'icons/details.png');
		$detailsSize = @getimagesize(PATH_site . $detailsPath);

		$subpart =  tupa_sysinfo::substituteSysinfoMarkers('###SHORT_SYSINFO###');

		$showDetails = '<a href="javascript:void(0);" onclick="showSysinfo();"><img src="'. $detailsPath .'" '. $detailsSize .' /></a>';
		$subpart = $TBE_TEMPLATE->substituteMarker($subpart, '###SHOW_DETAILS###', $showDetails);

		return $subpart;
	}


	/**
	 * Shows all system informations
	 *
	 * @return 	string
	 */
	function showSysinfo() {
		global $TBE_TEMPLATE, $USER;

		if (!$USER->hasPerm('admin')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$subpart = tupa_sysinfo::substituteSysinfoMarkers('###DETAILED_SYSINFO###', true);

		return $subpart;
	}



	/**
	 * Substitutes all possible markers with system info value
	 *
	 * @param 	string		Marker of subpart
	 * @param 	boolean	RRD charts are also generated when true (only needed for full sysinfo)
	 * @return 	string		substituted template
	 */
	function substituteSysinfoMarkers($subpartMarker, $doAll=false) {
		global $TBE_TEMPLATE, $USER, $LANG;

		if (!$USER->hasPerm('admin')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}
		$markerArray = array();

		// Get template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'sysinfo.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, $subpartMarker);

		// Include Sysinfo class
		if (@is_file(PATH_lib .'sysinfo/class.'. PHP_OS .'.inc.php')) {
			require(PATH_lib .'sysinfo/class.'. PHP_OS .'.inc.php');
			$SYSINFO = new sysinfo();
		} else {
			return $LANG->getLang('sysinfoUpdateError', array('OS' => PHP_OS));
		}

		$markerArray['label_hostname'] = $LANG->getLang('labelSysHostname');
		$markerArray['hostname'] = $SYSINFO->chostname();
		$markerArray['label_ip'] = $LANG->getLang('labelSysIp');
		$markerArray['ip'] = $SYSINFO->ip_addr();
		$markerArray['label_kernel'] = $LANG->getLang('labelSysKernel');
		$markerArray['kernel'] = $SYSINFO->kernel();
		$markerArray['label_uptime'] = $LANG->getLang('labelSysUptime');
		$markerArray['uptime'] = tupa_sysinfo::parseSysinfoValue('uptime', $SYSINFO->uptime());
		$markerArray['label_loadavg'] = $LANG->getLang('labelSysLoadavg');
		$markerArray['loadavg'] = tupa_sysinfo::parseSysinfoValue('loadavg', $SYSINFO->loadavg());
		$markerArray['label_network'] = $LANG->getLang('labelSysNetwork');
		$markerArray['network'] = tupa_sysinfo::parseSysinfoValue('network', $SYSINFO->network());
		$markerArray['label_memory'] = $LANG->getLang('labelSysMemory');
		$markerArray['memory'] = tupa_sysinfo::parseSysinfoValue('memory', $SYSINFO->memory());
		$markerArray['label_filesystem'] = $LANG->getLang('labelSysFilesystem');
		$markerArray['filesystem'] = tupa_sysinfo::parseSysinfoValue('filesystem', $SYSINFO->filesystems());
		$markerArray['distro'] = $SYSINFO->distro();

		$markerArray['label_pdns_state'] = $LANG->getLang('labelSysPdnsState');
		$markerArray['pdns_state'] = tupa_sysinfo::pdns();

		if ($doAll) {
			$markerArray['distroicon'] = tupa_sysinfo::parseSysinfoValue('distroicon', $SYSINFO->distroicon());

			// Generates udp/tcp queries charts
			$markerArray['label_pdns'] = 'PDNS Statistics';
			$queryImgDay = '<a name="rrdaqueries">'. tupa_sysinfo::genRrdImage('queries', 'day') .'</a>';
			$queryImgOther = '<div id="rrdqueries" style="display: none">'. tupa_sysinfo::genRrdImage('queries', 'week') .'<br />'. tupa_sysinfo::genRrdImage('queries', 'month') .'<br />'. tupa_sysinfo::genRrdImage('queries', 'year') .'</div>';
			$markerArray['pdns_queries'] =  $queryImgDay . $queryImgOther .'<hr class="rrdcharts" />';

			// Generates latency charts
			$queryImgDay = '<a name="rrdalatency">'. tupa_sysinfo::genRrdImage('latency', 'day') .'</a>';
			$queryImgOther = '<div id="rrdlatency" style="display: none">'. tupa_sysinfo::genRrdImage('latency', 'week') .'<br />'. tupa_sysinfo::genRrdImage('latency', 'month') .'<br />'. tupa_sysinfo::genRrdImage('latency', 'year') .'</div>';
			$markerArray['pdns_latency'] =  $queryImgDay . $queryImgOther;
		}

		// Substitute markers
		return  $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
	}




	/**
	 * Checks an system information threshold
	 *
	 * @param	string		Key in sysinfo array
	 * @param	string		Data to check
	 * @return	boolean	New rendered data
	 */
	function checkSysinfoThreshold($tkey, $data) {
		global $TUPA_CONF_VARS;

		$threshold =$TUPA_CONF_VARS['SYSINFO'][$tkey];
		if ($threshold) {
			if (!is_array($data)) $data[0] = $data;
			while(list($c, $value) = each($data)) {
				if ($value > $threshold) {
					$data[$c] = '<span class="sysinfoOverThreshold">'. $value .'</span>';
				}
			}
			if (count($data) == 1) $data = $data[0];
		}

		return $data;
	}



	/**
	 * Parses the value(s) from sysinfo class
	 *
	 * @param	string		Sysinfo to parse
	 * @param	mixed		Value(s)
	 * @return	string		Parsed values in a string
	 */
	function parseSysinfoValue($key, $value) {
		global $LANG;
		$output = '';

		switch ($key) {
			case 'uptime':
				if (is_array($value)) {
					$days = $value[0];
					$hours = $value[1];
					$min = $value[2];

					if ($days != 0) {
						$output = $days .' '. ($days == 1 ? $LANG->getLang('day') : $LANG->getLang('days')) .', ';
					}
					if ($hours != 0) {
						$output .= $hours .' '. ($hours == 1 ? $LANG->getLang('hour') : $LANG->getLang('hours')) .', ';
					}
					$output .= $min .' '. ($min == 1 ? $LANG->getLang('minute') : $LANG->getLang('minutes'));
				} else return $value;
				break;
			case 'loadavg':
				if (is_array($value)) {
					$results = tupa_sysinfo::checkSysinfoThreshold('loadUpperThreshold', $value);
					$output = $results[0] .' / '. $results[1] .' / '. $results[2];
				} else return $value;
				break;
			case 'network':
				if(!is_array($value)) {
					$output = $LANG->getLang('sysInfoNetInfoUnavailable');
					break;
				}

				$output = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
					<td><strong>'. $LANG->getLang('labelSysInterface') .'</strong></td><td><strong>'. $LANG->getLang('labelSysReceived') .'</strong></td><td><strong>'. $LANG->getLang('labelSysSent') .'</strong></td><td><strong>'. $LANG->getLang('labelSysErrorDrop') .'</strong></td></tr>';
				foreach ($value as $ifName => $ifArray) {
					$output .= '<tr>';

					$ifArray['tx_bytes'] = lib_div::formatSize($ifArray['tx_bytes']);
					$ifArray['rx_bytes'] = lib_div::formatSize($ifArray['rx_bytes']);

					$output .= '<td>'. $ifName .'</td><td>'. $ifArray['rx_bytes'] .'</td><td>'. $ifArray['tx_bytes'] .'</td><td>'. $ifArray['errs'] .' / '. $ifArray['drop'] .'</td>';
					$output .= '</tr>';
				}
				$output .= '</table>';
				break;
			case 'memory':
				if(!is_array($value)) {
					$output = $LANG->getLang('sysInfoMemInfoUnavailable');
					break;
				}
				
				$output = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
					<td><strong>'. $LANG->getLang('labelSysType') .'</strong></td><td align="right"><strong>'. $LANG->getLang('labelSysTotal') .'</strong></td><td align="right"><strong>'. $LANG->getLang('labelSysUsed') .'</strong></td><td align="right"><strong>'. $LANG->getLang('labelSysFree') .'</strong></td><td><strong>'. $LANG->getLang('labelSysPercent') .'</strong></td><td width="1%" align="right">&nbsp;</td></tr>';
				foreach ($value as $memName => $memArray) {
					if (isset($memArray['total']) && $memArray['total'] > 0) {
						$output .= '<tr>';

						$memArray['total'] = lib_div::formatSize($memArray['total'] * 1024);
						$memArray['used'] = lib_div::formatSize($memArray['used'] * 1024);
						$memArray['free'] = lib_div::formatSize($memArray['free'] * 1024);
						$usageBar = tupa_sysinfo::genUsageBar($memArray['percent']);

						$output .= '<td>'. $memName .'</td><td align="right">'. $memArray['total'] .'</td><td align="right">'. $memArray['used'] .'</td><td align="right">'. $memArray['free'] .'</td><td>'. $usageBar .'</td><td align="right"> '. $memArray['percent'] .'%</td>';
						$output .= '</tr>';
					}
				}
				$output .= '</table>';
				break;
			case 'filesystem':
				if(!is_array($value)) {
					$output = $LANG->getLang('sysInfoFsInfoUnavailable');
					break;
				}
				
				$output = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
					<td><strong>'. $LANG->getLang('labelSysMount') .'</strong></td><td><strong>'. $LANG->getLang('labelSysType') .'</strong></td><td><strong>'. $LANG->getLang('labelSysPartition') .'</strong></td><td align="right"><strong>'. $LANG->getLang('labelSysTotal') .'</strong></td><td align="right"><strong>'. $LANG->getLang('labelSysUsed') .'</strong></td><td align="right"><strong>'. $LANG->getLang('labelSysFree') .'</strong></td><td><strong>'. $LANG->getLang('labelSysPercent') .'</strong></td><td width="1%" align="right">&nbsp;</td></tr>';
				//$output = print_r($value, true);
				foreach ($value as $fsArray) {
						if ($fsArray['size'] > 0) {
							$output .= '<tr>';

							$fsArray['size'] = lib_div::formatSize($fsArray['size'] * 1024);
							$fsArray['used'] = lib_div::formatSize($fsArray['used'] * 1024);
							$fsArray['free'] = lib_div::formatSize($fsArray['free'] * 1024);
							$usageBar = tupa_sysinfo::genUsageBar($fsArray['percent']);

							$output .= '<td>'. $fsArray['mount'] .'</td><td>'. $fsArray['fstype'] .'</td><td>'. $fsArray['disk'] .'</td><td align="right">'. $fsArray['size'] .'</td><td align="right">'. $fsArray['used'] .'</td><td align="right">'. $fsArray['free'] .'</td><td>'. $usageBar .'</td><td align="right"> '. $fsArray['percent'] .'%</td>';
							$output .= '</tr>';
					}
				}
				$output .= '</table>';
				break;
			case 'distroicon':
				$imgPath = 'images/distroicons/'. $value;
				$imgSize = @getimagesize(PATH_site . $imgPath);
				//$imgSize = 'width="16" height="16"';
				$output = '<img src="'. $imgPath .'" '. $imgSize .' hspace="5" vspace="5" />';
				break;
			default:
				return $value;
				break;
		}

		return $output;
	}


	/**
	 * Generates the percentual usage bar
	 *
	 * @param	string		Percent
	 * @return	string		Usage bar
	 */
	function genUsageBar($percent) {
		$percent > 95 ? $bgcolor = '#FF0000' : $bgcolor = '#00FF00';
		return '<div style="width: '. $percent .'%; height: 10px; background-color: '. $bgcolor.';"">&nbsp;</div>';
	}



	/**
	 * Generates the RRD charts
	 *
	 * @param	string		Part to get values (queries / latency)
	 * @param	string		Time range to get values for chart (day / week / month / year)
	 * @return	string		Image tag and expand icon (plus) on day charts
	 */
	function genRrdImage($part, $time) {
		global $TUPA_CONF_VARS, $LANG;

		// Get base RRD configuration
		$rrdConfig = $TUPA_CONF_VARS['RRD']['CONFIG'];
		$rrdFile = PATH_site .'stats/db/'. $part .'.rrd';
		//$rrdFile = PATH_site .'stats/db/test.rrd';
		$rrdFont = $rrdConfig['FONT']['FILE'];
		$rrdOutFile = PATH_site .'stats/'. $part .'-'. $time .'.png';
		$showExpand = false;

		$rrdTool = PHP_OS == 'WINNT' ? 'rrdtool.exe graph - ' : 'rrdtool graph ';
		$rrdPartOptions = array();	// Options of single linea/areas
		$rrdOptions = array();	// Base RRD options (everything with --xy)
		$rrdOptions[] = 'interlaced';
		$rrdOptions[] = 'vertical "'. utf8_decode($LANG->getLang('rrd'. lib_div::firstUpper($part) .'VertTitle')) .'"';

		// unset font file config and get full font path
		unset($rrdConfig['FONT']['FILE']);
		if (substr($rrdFont, 0, 1) != '/' && substr($rrdFont, 1,1) != ':') {
			$rrdFont = PATH_site . $rrdFont;
		}

		// Set time specific options
		switch ($time) {
			default:
			case 'day':
				$rrdOptions[] = 'start -1d';
				$rrdOptions[] = 'x-grid HOUR:1:DAY:1:HOUR:2:0:%R';
				$showExpand = true;
				break;
			case 'week':
				$rrdOptions[] = 'start -1w';
				$rrdOptions[] = 'x-grid HOUR:6:DAY:1:DAY:1:86400:%a';
				break;
			case 'month':
				$rrdOptions[] = 'start -1m';
				$rrdOptions[] = 'x-grid DAY:1:WEEK:1:WEEK:1:604000:"Week %V"';
				break;
			case 'year':
				$rrdOptions[] = 'start -1y';
				$rrdOptions[] = 'x-grid DAY:4:MONTH:1:MONTH:1:2416000:%b';
				break;
		}

		// Set title
		$rrdOptions[] = 'title "'. utf8_decode($LANG->getLang('rrd'. lib_div::firstUpper($part) .'Title')) .' '. utf8_decode($LANG->getLang('rrdPeriod'. lib_div::firstUpper($time) .'')) .'"';

		// Set RRD base configuration options
		foreach ($rrdConfig as $key => $value) {
			$key = strtolower($key);
			if (is_array($value)) {
				foreach ($value as $key2 => $value2) {
					if ($key == 'font') {
						$rrdOptions[] = $key .' '. strtoupper($key2) .':'. $value2 .':'. $rrdFont;
					} elseif ($key == 'color') {
						$rrdOptions[] = $key .' '. strtoupper($key2) . $value2;
					}
				}
			} else {
				$rrdOptions[] = $key .' '. $value;
			}
		}


		// Get RRD configuration of submitted part
		$rrdConfig = $TUPA_CONF_VARS['RRD'][strtoupper($part)];

		foreach ($rrdConfig as $value) {
			$rrdPartOptions[] = 'DEF:'. $value['NAME'] .'=' . $rrdFile . ':'. $value['NAME'] .':'. $value['DATA'];
			$rrdPartOptions[] = $value['STYLE'] .':'. $value['NAME'] . $value['COLOR'] .':"'. $value['NAME'] .'"';

			if (isset($value['LEGEND'])) {
				$legsTotal = count($value['LEGEND']);
				$legCount = 0;
				foreach ($value['LEGEND'] as $value2) {
					$legCount++;
					$rrdPartOptions[] = 'COMMENT:"'. lib_div::escapeColon(utf8_decode($LANG->getLang('rrd'. lib_div::firstUpper(strtolower($value2['DATA']))))) .'"';
					$rrdPartOptions[] = 'GPRINT:'. $value['NAME'] .':'. $value2['DATA'] .':"'. $value2['VALUE_FORMAT'] . ($legCount == $legsTotal ? '\n' : '') .'"';
				}
			}
		}

		//Merge the stuff together and execute it
		$rrdOptions = ' --'. implode(' --', $rrdOptions);
		$rrdPartOptions = ' '. implode(' ', $rrdPartOptions);
		$command = $rrdTool . $rrdOutFile . $rrdOptions . $rrdPartOptions;

		if (exec($command)) {
			$imgPath = 'stats/'. $part .'-'. $time .'.png';
			$imgSize = @getimagesize(PATH_site . $imgPath);

			$output = '<img src="'. $imgPath .'?'. rand(0,100) .'" border="0" '. $imgSize[3] .' nopngfix="1" />';

			if ($showExpand) {
				$plusIconPath = lib_div::getSkinFilePath(PATH_images .'icons/bplus.png');
				$plusIconSize = @getimagesize(PATH_site . $plusIconPath);
				$minusIconPath = lib_div::getSkinFilePath(PATH_images .'icons/bminus.png');
//				$minusIconSize = @getimagesize(PATH_site . $minusIconPath);
				if (IE_PNGFIX) {
					$expOnClick = 'expdiv = document.getElementById(\'rrd'. $part .'\'); if (expdiv.style.display == \'none\') { expdiv.style.display = \'block\'; this.firstChild.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\''. $minusIconPath .'\\\', sizingMethod=scale)\';} else { expdiv.style.display = \'none\'; this.firstChild.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\''. $plusIconPath .'\\\', sizingMethod=scale)\'; }';
				} else {
					$expOnClick = 'expdiv = document.getElementById(\'rrd'. $part .'\'); if (expdiv.style.display == \'none\') { expdiv.style.display = \'block\'; this.firstChild.src = \''. $minusIconPath .'\';} else { expdiv.style.display = \'none\'; this.firstChild.src = \''. $plusIconPath .'\'; }';
				}
				$output .= '<br /><a href="#rrda'. $part .'" onclick="'. $expOnClick .'"><img src="'. $plusIconPath .'" '. $plusIconSize .' border="0" /></a>';
			}

			return $output;
		} else {
			return $LANG->getLang('rrdGenError');
		}
	}


	/**
	 * Checks if DNS port (TCP) accepts connections
	 *
	 * @return array	Running or not
	 */
	function pdns() {
		global $LANG;

		$result = '';
		$target = getenv('SERVER_ADDR');
		$tport = 53;

		$sock = @fsockopen($target, $tport, $errnum, $error, 2);
		if (!$sock) {
			$result = '<span class="sysinfoError">'. $LANG->getLang('sysPdnsStopped') .'</span>';
		} else {
			$result = '<span class="sysinfoOK">'. $LANG->getLang('sysPdnsRunning') .'</span>';
			@fclose($sock);
		}

		return $result;
	}

}
?>
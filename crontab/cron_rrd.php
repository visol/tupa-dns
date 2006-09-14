<?php
#!/usr/bin/php

/**
 * Language help file (EN)
 *
 * @package 	TUPA
 * @author 	Urs Weiss <urs@tupa-dns.org>
 */

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


if ($_SERVER['REMOTE_ADDR']) {
	die('It\'s not allowed to call this script from a remote host!');
}

// Set the database filenames
$rrdDbQueries = PATH_site .'stats/db/queries.rrd';
$rrdDbLatency = PATH_site .'stats/db/latency.rrd';

// Get pdns_control and rrdtool program on windows and linux
$pdnsControl = 'pdns_control';
$rrdTool = 'rrdtool';
$devNull = ' 2> /dev/null';
$winAdd = ' ';

/*if (PHP_OS == 'WINNT') {
	$pdnsControl .= '.exe';
	$rrdTool .= '.exe';
	$devNull = '';
	$winAdd = ' - ';
}*/


// Create the .rrd file if it does not exist
if (!file_exists($rrdDbQueries)) {
	system($rrdTool .' create'. $winAdd . $rrdDbQueries .' \
		 --step 120 \
		 DS:udp-queries:DERIVE:240:0:U \
		 DS:tcp-queries:DERIVE:240:0:U \
		 RRA:AVERAGE:0.5:1:750 \
		 RRA:AVERAGE:0.5:11:520 \
		 RRA:AVERAGE:0.5:45:520 \
		 RRA:AVERAGE:0.5:520:520\
		 RRA:MIN:0.5:1:750 \
		 RRA:MIN:0.5:11:520 \
		 RRA:MIN:0.5:45:520 \
		 RRA:MIN:0.5:520:520 \
		 RRA:MAX:0.5:1:750 \
		 RRA:MAX:0.5:11:520 \
		 RRA:MAX:0.5:45:520 \
		 RRA:MAX:0.5:520:520'
	);
}
if (!file_exists($rrdDbLatency)) {
	system($rrdTool .' create'. $winAdd . $rrdDbLatency .' \
		 --step 120 \
		 DS:latency:GAUGE:240:0:U \
		 RRA:AVERAGE:0.5:1:750 \
		 RRA:AVERAGE:0.5:11:520 \
		 RRA:AVERAGE:0.5:45:520 \
		 RRA:AVERAGE:0.5:520:520\
		 RRA:MIN:0.5:1:750 \
		 RRA:MIN:0.5:11:520 \
		 RRA:MIN:0.5:45:520 \
		 RRA:MIN:0.5:520:520 \
		 RRA:MAX:0.5:1:750 \
		 RRA:MAX:0.5:11:520 \
		 RRA:MAX:0.5:45:520 \
		 RRA:MAX:0.5:520:520'
	);
}

if ($demoData) {
	// DEMO DATA GENERATOR - begin
	$count_file = PATH_site .'stats/db/queries-demo.txt';
	if (!file_exists($count_file)) {
		$fh = fopen($count_file, 'w');
		fwrite($fh, '1000'."\n".'2000');
		fclose($fh);
	}
	$fh = fopen($count_file, 'r+');
	$content = file($count_file);
	$udp_queries = rtrim($content[0]);
	$tcp_queries = rtrim($content[1]);
	$tmp = ($udp_queries + rand(400,600)) ."\n". ($tcp_queries + rand(200,400));
	fwrite($fh, $tmp);
	fclose($fh);
	
	$count_file = PATH_site .'stats/db/latency-demo.txt';
	if (!file_exists($count_file)) {
		$fh = fopen($count_file, 'w');
		fwrite($fh, '2100');
		fclose($fh);
	}
	$fh = fopen($count_file, 'r+');
	$content2 = file($count_file);
	$latency = rtrim($content2[0])/1000000;
	$tmp = ($content2[0] + rand(-100,100));
	if ($tmp > 5000 || $tmp < 100) $tmp = 2100;
	fwrite($fh, $tmp);
	fclose($fh);
	// DEMO DATA GENERATOR - end
} else {
	if ($configName) {
		$configName = ' --config-name='. $configName;
	}
	$udp_queries = exec($pdnsControl . $configName .' show udp-queries'. $devNull);
	$tcp_queries = exec($pdnsControl . $configName .' show tcp-queries'. $devNull);
	$latency = exec($pdnsControl . $configName .' show latency'. $devNull);
	
	// convert latency from usec to sec
	if ($latency) { $latency = $latency/1000000; }
}


// Update the .rrd file
system($rrdTool .' update'. $winAdd . $rrdDbQueries .' N:'. $udp_queries .':'. $tcp_queries);
system($rrdTool .' update'. $winAdd .$rrdDbLatency .' N:'. $latency);
?>
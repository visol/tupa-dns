<?php 

// phpSysInfo - A PHP System Information Script
// http://phpsysinfo.sourceforge.net/

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

// $Id: class.SunOS.inc.php,v 1.9 2004/06/26 01:50:21 webbie Exp $

class sysinfo {
	// Extract kernel values via kstat() interface
	function kstat($key) {
		$m = exec('kstat -p d '. $key);
		list($key, $value) = split("\t", trim($m), 2);
		return $value;
	}

	// get our canonical hostname
	function chostname() {
		if (exec('uname -n', $output)) {
			$result = gethostbyaddr(gethostbyname($output[0]));
		} else {
			$result = 'N.A.';
		}
		return $result;
	}
	
	// get the IP address of our canonical hostname
	function ip_addr() {
		if (!($result = getenv('SERVER_ADDR'))) {
			$result = gethostbyname($this->chostname());
		}
		return $result;
	}

	function kernel () {
		$os = '';
		$version = '';
		
		if (exec('uname -s', $output)) {
			$os = $output[0];
		}
		if (exec('uname -r', $output)) {
			$version = $output[0];
		}
		return $os . ' ' . $version;
	}

	function uptime() {
		$sys_ticks = time() - $this->kstat('unix:0:system_misc:boot_time');

		$min = $sys_ticks / 60;
		$hours = $min / 60;
		$days = floor($hours / 24);
		$hours = floor($hours - ($days * 24));
		$min = floor($min - ($days * 60 * 24) - ($hours * 60));

		$result = array($days, $hours, $min);

		return $result;
	}

	function loadavg() {
		$load1 = $this->kstat('unix:0:system_misc:avenrun_1min');
		$load5 = $this->kstat('unix:0:system_misc:avenrun_5min');
		$load15 = $this->kstat('unix:0:system_misc:avenrun_15min');
		$results = array($load1, $load5, $load15);
		return $results;
	}

	function network() {
		$results = array();

		if (exec('netstat -ni | awk \'(NF ==10){print;}\'', $lines)) {
			$lines = split("\n", $netstat);
			$results = array();
			for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
				$ar_buf = preg_split("/\s+/", $lines[$i]);
				if ((!empty($ar_buf[0])) && ($ar_buf[0] != 'Name')) {
					$results[$ar_buf[0]] = array();
	
					$results[$ar_buf[0]]['rx_bytes'] = 0;
					$results[$ar_buf[0]]['rx_packets'] = $ar_buf[4];
					$results[$ar_buf[0]]['rx_errs'] = $ar_buf[5];
					$results[$ar_buf[0]]['rx_drop'] = 0;
	
					$results[$ar_buf[0]]['tx_bytes'] = 0;
					$results[$ar_buf[0]]['tx_packets'] = $ar_buf[6];
					$results[$ar_buf[0]]['tx_errs'] = $ar_buf[7];
					$results[$ar_buf[0]]['tx_drop'] = 0;
	
					$results[$ar_buf[0]]['errs'] = $ar_buf[4] + $ar_buf[7];
					$results[$ar_buf[0]]['drop'] = 0;
	
					preg_match('/^(\D+)(\d+)$/', $ar_buf[0], $intf);
					$prefix = $intf[1] . ':' . $intf[2] . ':' . $intf[1] . $intf[2] . ':';
					$cnt = $this->kstat($prefix . 'drop');
	
					if ($cnt > 0) {
						$results[$ar_buf[0]]['rx_drop'] = $cnt;
					}
					$cnt = $this->kstat($prefix . 'obytes64');
	
					if ($cnt > 0) {
						$results[$ar_buf[0]]['tx_bytes'] = $cnt;
					}
				}
			}
		}
		return $results;
	}

	function memory() {
		$results['devswap'] = array();
		$results['ram'] = array();

		$pagesize = $this->kstat('unix:0:seg_cache:slab_size');
		$results['ram']['total'] = $this->kstat('unix:0:system_pages:pagestotal') * $pagesize;
		$results['ram']['used'] = $this->kstat('unix:0:system_pages:pageslocked') * $pagesize;
		$results['ram']['free'] = $this->kstat('unix:0:system_pages:pagesfree') * $pagesize;
		$results['ram']['shared'] = 0;
		$results['ram']['buffers'] = 0;
		$results['ram']['cached'] = 0;

		$results['ram']['t_used'] = $results['ram']['used'] - $results['ram']['cached'] - $results['ram']['buffers'];
		$results['ram']['t_free'] = $results['ram']['total'] - $results['ram']['t_used'];
		$results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']);

		$results['swap'] = array();
		$results['swap']['total'] = $this->kstat('unix:0:vminfo:swap_avail') / 1024;
		$results['swap']['used'] = $this->kstat('unix:0:vminfo:swap_alloc') / 1024;
		$results['swap']['free'] = $this->kstat('unix:0:vminfo:swap_free') / 1024;
		$results['swap']['percent'] = round(($ar_buf[1] * 100) / $ar_buf[0]);
		$results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);
		return $results;
	}

	function filesystems() {
		$results = array();
		
		if (exec('df -k', $mounts)) {
			$dftypes = exec('df -n', $mounttypes);
	
			for ($i = 1, $max = count($mounts); $i < $max; $i++) {
				$ar_buf = preg_split('/\s+/', $mounts[$i], 6);
				$ty_buf = split(':', $mounttypes[$i-1], 2);
	
				$results[$i - 1] = array();
	
				$results[$i - 1]['disk'] = $ar_buf[0];
				$results[$i - 1]['size'] = $ar_buf[1];
				$results[$i - 1]['used'] = $ar_buf[2];
				$results[$i - 1]['free'] = $ar_buf[3];
				$results[$i - 1]['percent'] = round(($results[$i - 1]['used'] * 100) / $results[$i - 1]['size']) . '%';
				$results[$i - 1]['mount'] = $ar_buf[5];
				$results[$i - 1]['fstype'] = $ty_buf[1];
			}
		}
		return $results;
	}

	function distro() {
		$result = 'SunOS';
		return $result;
	}

	function distroicon() {
		$result = 'sunos.gif';
		return $result;
	}
}

?>

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

// $Id: class.BSD.common.inc.php,v 1.23 2004/07/02 02:32:23 webbie Exp $

class bsd_common {
	var $dmesg;
	// Our constructor
	// this function is run on the initialization of this class
	function bsd_common() {
		// initialize all the variables we need from our parent class
		$this->sysinfo();
	}
	
	// grabs a key from sysctl(8)
	function grab_key ($key) {
		if (exec('sysctl -n '. $key, $output)) {
			$result = $output[0];
		} else {
			$result = 'N.A.';
		}
		return $result;
	}

	// get our canonical hostname
	function chostname() {
		if (exec('hostname', $output)) {
			$result = $output[0];
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

	function kernel() {
		$s = $this->grab_key('kern.version');
		$a = explode(':', $s);
		return $a[0] . $a[1] . ':' . $a[2];
	}

	function uptime() {
		$sys_ticks = $this->get_sys_ticks();

		$min = $sys_ticks / 60;
		$hours = $min / 60;
		$days = floor($hours / 24);
		$hours = floor($hours - ($days * 24));
		$min = floor($min - ($days * 60 * 24) - ($hours * 60));

		$result = array($days, $hours, $min);

		return $result;
	}

	function loadavg() {
		$s = $this->grab_key('vm.loadavg');
		$s = ereg_replace('{ ', '', $s);
		$s = ereg_replace(' }', '', $s);
		$results = explode(' ', $s);

		return $results;
	}

	function memory() {
		$s = $this->grab_key('hw.physmem');

		if (PHP_OS == 'FreeBSD' || PHP_OS == 'OpenBSD') {
			// vmstat on fbsd 4.4 or greater outputs kbytes not hw.pagesize
			// I should probably add some version checking here, but for now
			// we only support fbsd 4.4
			$pagesize = 1024;
		} else {
			$pagesize = $this->grab_key('hw.pagesize');
		}

		$results['ram'] = array();

		if (exec('vmstat', $lines)) {
			for ($i = 0, $max = count($lines); $i < $max; $i++) {
				$ar_buf = preg_split("/\s+/", $lines[$i], 19);
	
				if ($i == 2) {
					$results['ram']['free'] = $ar_buf[5] * $pagesize / 1024;
				}
			}
	
			$results['ram']['total'] = $s / 1024;
			$results['ram']['shared'] = 0;
			$results['ram']['buffers'] = 0;
			$results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
			$results['ram']['cached'] = 0;
			$results['ram']['t_used'] = $results['ram']['used'];
			$results['ram']['t_free'] = $results['ram']['free'];
	
			$results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']);
		}

		if (PHP_OS == 'OpenBSD') {
			exec('swapctl -l -k', $lines);
		} else {
			exec('swapinfo -k', $lines);
		}

		$results['swap']['total'] = 0;
		$results['swap']['used'] = 0;
		$results['swap']['free'] = 0;

		for ($i = 0, $max = count($lines); $i < $max; $i++) {
			$ar_buf = preg_split("/\s+/", $lines[$i], 6);

			if ($ar_buf[0] != 'Total') {
				$results['swap']['total'] = $results['swap']['total'] + $ar_buf[1];
				$results['swap']['used'] = $results['swap']['used'] + $ar_buf[2];
				$results['swap']['free'] = $results['swap']['free'] + $ar_buf[3];
			}
		}
		$results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);

		return $results;
	}

	function filesystems() {
		$results = array();
		
		if (exec('df -k', $mounts)) {
			$fstype = array();
	
			if (exec('mount', $lines)) {
				$i = 0;
				while (list(, $line) = each($lines)) {
					ereg('(.*) \((.*)\)', $line, $a);
		
					$m = explode(' ', $a[0]);
					$fsdev[$m[0]] = $a[2];
				}
		
				for ($i = 1, $j = 0, $max = count($mounts); $i < $max; $i++) {
					$ar_buf = preg_split("/\s+/", $mounts[$i], 6);
					// skip the proc filesystem
					if ($ar_buf[0] == 'procfs' || $ar_buf[0] == 'linprocfs' || $ar_buf[0] == 'kernfs' || $ar_buf[0] == 'devfs') {
						continue;
					}
					
					// Remove % from value
					$ar_buf[4] = str_replace('%', '', $ar_buf[4]);
					
					$results[$j] = array();
		
					$results[$j]['disk'] = $ar_buf[0];
					$results[$j]['size'] = $ar_buf[1];
					$results[$j]['used'] = $ar_buf[2];
					$results[$j]['free'] = $ar_buf[3];
					$results[$j]['percent'] = $ar_buf[4];
					$results[$j]['mount'] = $ar_buf[5];
					($fstype[$ar_buf[5]]) ? $results[$j]['fstype'] = $fstype[$ar_buf[5]] : $results[$j]['fstype'] = $fsdev[$ar_buf[0]];
					$j++;
				}
			}
		} 
		return $results;
	}

	function distro() {
		if (exec('uname -s', $output)) {
			$result = $output[0];
		} else {
			$result = 'N.A.';
		}
		return $result;
	}
}

?>

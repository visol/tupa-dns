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
// $Id: class.Linux.inc.php,v 1.34 2004/08/11 06:55:15 webbie Exp $

/**
 * Linux SysInfo class
 * The class is taken from phpSysInfo version 2.3
 * Cutted to the needed informations and changed to work with safemode enabled for TUPA.
 *
 * @package 	SYSINFO
 */
class sysinfo {
	/**
	 * Gets canonical hostname
	 *
	 * @return string	hostname
	 */
	function chostname() {
		if ($result = lib_div::getFileRtrim('/proc/sys/kernel/hostname')) {
			$result = gethostbyaddr(gethostbyname($result[0]));
		} else {
			$result = 'N.A.';
		}
		return $result;
	}

	/**
	 * Gets the IP address of anonical hostname
	 *
	 * @return string	IP address
	 */
	function ip_addr() {
		if (!($result = getenv('SERVER_ADDR'))) {
			$result = gethostbyname($this->chostname());
		}
		return $result;
	}

	/**
	 * Gets kernel version
	 *
	 * @return string	kernel version
	 */
	function kernel() {
		if ($result = lib_div::getFileRtrim('/proc/version')) {
			$buf = $result[0];

			if (preg_match('/version (.*?) /', $buf, $ar_buf)) {
				$result = $ar_buf[1];

				if (preg_match('/SMP/', $buf)) {
					$result .= ' (SMP)';
				}
			} else {
				$result = 'N.A.';
			}
		} else {
			$result = 'N.A.';
		}
		return $result;
	}

	/**
	 * Gets system uptime
	 *
	 * @return string	uptime
	 */
	function uptime() {
		if ($result = lib_div::getFileRtrim('/proc/uptime')) {
			$ar_buf = split(' ', $result[0]);
	
			$sys_ticks = trim($ar_buf[0]);
	
			$min = $sys_ticks / 60;
			$hours = $min / 60;
			$days = floor($hours / 24);
			$hours = floor($hours - ($days * 24));
			$min = floor($min - ($days * 60 * 24) - ($hours * 60));
	
			$result = array($days, $hours, $min);
		} else {
			$result = 'N.A.';
		}
		return $result;
	}

	/**
	 * Gets processor load
	 *
	 * @return array	1/5/15 min load
	 */
	function loadavg() {
		if ($results = lib_div::getFileRtrim('/proc/loadavg')) {
			$results = split(' ', $results[0]);
		} else {
			$results = 'N.A.';
		}
		
		return $results;
	}


	/**
	 * Gets network interface informations
	 *
	 * @return array	network information	
	 */
	function network() {
		$results = array();

		if ($output = lib_div::getFileRtrim('/proc/net/dev')) {
			while (list(,$buf) = each($output)) {
				if (preg_match('/:/', $buf)) {
					list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
					$stats = preg_split('/\s+/', trim($stats_list));
					$results[$dev_name] = array();

					$results[$dev_name]['rx_bytes'] = $stats[0];
					$results[$dev_name]['rx_packets'] = $stats[1];
					$results[$dev_name]['rx_errs'] = $stats[2];
					$results[$dev_name]['rx_drop'] = $stats[3];

					$results[$dev_name]['tx_bytes'] = $stats[8];
					$results[$dev_name]['tx_packets'] = $stats[9];
					$results[$dev_name]['tx_errs'] = $stats[10];
					$results[$dev_name]['tx_drop'] = $stats[11];

					$results[$dev_name]['errs'] = $stats[2] + $stats[10];
					$results[$dev_name]['drop'] = $stats[3] + $stats[11];
				}
			}
		}
		return $results;
	}

	/**
	 * Gets memory / swap informations
	 *
	 * @return array	mem/swap informations
	 */
	function memory() {
		if ($output = lib_div::getFileRtrim('/proc/meminfo')) {
			$results['ram'] = array();
			$results['swap'] = array();
			$results['devswap'] = array();

			while (list(,$buf) = each($output)) {
				if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
					$results['ram']['total'] = $ar_buf[1];
				} else if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
					$results['ram']['free'] = $ar_buf[1];
				} else if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
					$results['ram']['cached'] = $ar_buf[1];
				} else if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
					$results['ram']['buffers'] = $ar_buf[1];
				} else if (preg_match('/^SwapTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
					$results['swap']['total'] = $ar_buf[1];
				} else if (preg_match('/^SwapFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
					$results['swap']['free'] = $ar_buf[1];
				}
			}
			$results['ram']['shared'] = 0;
			$results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
			$results['swap']['used'] = $results['swap']['total'] - $results['swap']['free'];

			if (file_exists('/proc/swaps') && count(file('/proc/swaps')) > 1) {
				$swaps = lib_div::getFileRtrim('/proc/swaps');
				
				while (list(,$swap) = each($swaps)) {
					$swapdevs[] = $swap;
				}
	
				for ($i = 1; $i < (count($swapdevs) - 1); $i++) {
					$ar_buf = preg_split('/\s+/', $swapdevs[$i], 6);
	
					$results['devswap'][$i - 1] = array();
					$results['devswap'][$i - 1]['dev'] = $ar_buf[0];
					$results['devswap'][$i - 1]['total'] = $ar_buf[2];
					$results['devswap'][$i - 1]['used'] = $ar_buf[3];
					$results['devswap'][$i - 1]['free'] = ($results['devswap'][$i - 1]['total'] - $results['devswap'][$i - 1]['used']);
					$results['devswap'][$i - 1]['percent'] = round(($ar_buf[3] * 100) / $ar_buf[2]);
				}
			} else {
				$results['devswap'] = array();
			}
			
			// I don't like this since buffers and cache really aren't
			// 'used' per say, but I get too many emails about it.
			$results['ram']['t_used'] = $results['ram']['used'];
			$results['ram']['t_free'] = $results['ram']['total'] - $results['ram']['t_used'];
			$results['ram']['percent'] = round(($results['ram']['t_used'] * 100) / $results['ram']['total']);
			$results['swap']['percent'] = $results['swap']['total'] > 0 ? round(($results['swap']['used'] * 100) / $results['swap']['total']) : 0;
		} else {
			$results['ram'] = array();
			$results['swap'] = array();
			$results['devswap'] = array();
		}
		return $results;
	}

	/**
	 * Gets filesystem informations
	 *
	 * @return array	FS info
	 */
	function filesystems() {
		exec('df -kP', $output);
		while (list(,$mount) = each($output)) {
			$mounts[] = $mount;
		}

		$fstype = array();

		if ($output = lib_div::getFileRtrim('/proc/mounts')) {
			while (list(,$buf) = each($output)) {
				list($dev, $mpoint, $type) = preg_split('/\s+/', trim($buf), 4);
				$fstype[$mpoint] = $type;
				$fsdev[$dev] = $type;
			}
		}

		for ($i = 1, $max = count($mounts); $i < $max; $i++) {
			$ar_buf = preg_split('/\s+/', $mounts[$i], 6);

			$results[$i - 1] = array();

			$results[$i - 1]['disk'] = $ar_buf[0];
			$results[$i - 1]['size'] = $ar_buf[1];
			$results[$i - 1]['used'] = $ar_buf[2];
			$results[$i - 1]['free'] = $ar_buf[3];
			$results[$i - 1]['percent'] = round(($results[$i - 1]['used'] * 100) / $results[$i - 1]['size']);
			$results[$i - 1]['mount'] = $ar_buf[5];
			($fstype[$ar_buf[5]]) ? $results[$i - 1]['fstype'] = $fstype[$ar_buf[5]] : $results[$i - 1]['fstype'] = $fsdev[$ar_buf[0]];
		}
		
		return $results;
	}
	
	
	/**
	 * Gets distrobution informations
	 *
	 * @return array	distro info
	 */
	function distro() {
		$result = 'N.A.';
		$distroFileArr = array('debian_version','SuSE-release','mandrake-release','fedora-release','redhat-release','gentoo-release','slackware-version','eos-version','trustix-release','arch-release');
		
		foreach ($distroFileArr as $distroFile) {
			if (file_exists('/etc/'. $distroFile)) {
				$buf = lib_div::getFileRtrim('/etc/'. $distroFile);
				$result = ($distroFile == 'debian_version' ? 'Debian ' : '') . trim($buf[0]);
			}
		}
		return $result;
	}


	function distroicon() {
		if (file_exists('/etc/debian_version')) {
			$result = 'Debian.gif';
		} elseif (file_exists('/etc/SuSE-release')) {
			$result = 'Suse.gif';
		} elseif (file_exists('/etc/mandrake-release')) {
			$result = 'Mandrake.gif';
		} elseif (file_exists('/etc/fedora-release')) {
			$result = 'Fedora.gif';
		} elseif (file_exists('/etc/redhat-release')) {
			$result = 'Redhat.gif';
		} elseif (file_exists('/etc/gentoo-release')) {
			$result = 'Gentoo.gif';
		} elseif (file_exists('/etc/slackware-version')) {
			$result = 'Slackware.gif';
		} elseif (file_exists('/etc/eos-version')) {
			$result = 'free-eos.gif';
		} elseif (file_exists('/etc/trustix-release')) {
			$result = 'Trustix.gif';
		} elseif (file_exists('/etc/arch-release')) {
			$result = 'Arch.gif';
		} else {
			$result = 'clear.gif';
		}
		return $result;
	}
}
?>

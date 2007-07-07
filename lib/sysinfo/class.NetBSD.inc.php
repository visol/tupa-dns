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

// $Id: class.NetBSD.inc.php,v 1.11 2004/06/26 23:46:36 webbie Exp $

require(PATH_lib .'sysinfo/class.BSD.common.inc.php');

class sysinfo extends bsd_common {
	var $cpu_regexp;
	var $scsi_regexp;
	// Our contstructor
	// this function is run on the initialization of this class
	function sysinfo() {
		$this->cpu_regexp = "^cpu(.*)\, (.*) MHz";
		$this->scsi_regexp1 = "^(.*) at scsibus.*: <(.*)> .*";
		$this->scsi_regexp2 = "^(da[0-9]): (.*)MB ";
	}

	function get_sys_ticks() {
		$a = $this->grab_key('kern.boottime');
		$sys_ticks = time() - $a;
		return $sys_ticks;
	}

	function network() {
		$results = array();
		
		if (exec('netstat -nbdi | cut -c1-25,44- | grep "^[a-z]*[0-9][ \t].*Link', $lines_b)) {
			exec('netstat -ndi | cut -c1-25,44- | grep "^[a-z]*[0-9][ \t].*Link', $lines_n);
	
			for ($i = 0, $max = sizeof($lines_b); $i < $max; $i++) {
				$ar_buf_b = preg_split("/\s+/", $lines_b[$i]);
				$ar_buf_n = preg_split("/\s+/", $lines_n[$i]);
				if (!empty($ar_buf_b[0]) && !empty($ar_buf_n[3])) {
					$results[$ar_buf_b[0]] = array();
	
					$results[$ar_buf_b[0]]['rx_bytes'] = $ar_buf_b[3];
					$results[$ar_buf_b[0]]['rx_packets'] = $ar_buf_n[3];
					$results[$ar_buf_b[0]]['rx_errs'] = $ar_buf_n[4];
					$results[$ar_buf_b[0]]['rx_drop'] = $ar_buf_n[8];
	
					$results[$ar_buf_b[0]]['tx_bytes'] = $ar_buf_b[4];
					$results[$ar_buf_b[0]]['tx_packets'] = $ar_buf_n[5];
					$results[$ar_buf_b[0]]['tx_errs'] = $ar_buf_n[6];
					$results[$ar_buf_b[0]]['tx_drop'] = $ar_buf_n[8];
	
					$results[$ar_buf_b[0]]['errs'] = $ar_buf_n[4] + $ar_buf_n[6];
					$results[$ar_buf_b[0]]['drop'] = $ar_buf_n[8];
				}
			}
		}
		return $results;
	}

	function memory() {
		$s = $this->grab_key('hw.physmem');
		$pagesize = $this->grab_key('hw.pagesize');

		$results['ram'] = array();

		if (exec('vmstat -s', $lines)) {
			for ($i = 0, $max = count($lines); $i < $max; $i++) {
				$ar_buf = preg_split("/\s+/", $lines[$i], 19);
	
				if ($i == 3) {
					$results['ram']['free'] = $ar_buf[1] * $pagesize / 1024;
				} elseif ($i == 19) {
					$results['swap']['total'] = $ar_buf[1] * $pagesize / 1024;
				} elseif ($i == 20) {
					$results['swap']['used'] = $ar_buf[1] * $pagesize / 1024;
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
		

			$results['swap']['free'] = $results['swap']['total'] - $results['swap']['used'];
			$results['swap']['percent'] = $results['swap']['total'] > 0 ? round(($results['swap']['used'] * 100) / $results['swap']['total']) : 0;
		}

		return $results;
	}

	function distroicon() {
		$result = 'NetBSD.gif';
		return($result);
	}

}

?>

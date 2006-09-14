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

// $Id: class.OpenBSD.inc.php,v 1.14 2004/07/02 02:33:16 webbie Exp $

require(PATH_lib .'sysinfo/class.BSD.common.inc.php');

class sysinfo extends bsd_common {
	var $cpu_regexp;
	var $scsi_regexp;
	// Our contstructor
	// this function is run on the initialization of this class
	function sysinfo() {
		$this->cpu_regexp = "^cpu(.*) (.*) MHz";
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
		
		if (exec('netstat -nbdi | cut -c1-25,44- | grep Link | grep -v \'* \'', $lines_b)) {
			exec('netstat -ndi | cut -c1-25,44- | grep Link | grep -v \'* \'', $lines_n);
	
			$results = array();
			for ($i = 0, $max = count($lines_b); $i < $max; $i++) {
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

	function distroicon () {
		$result = 'OpenBSD.gif';
		return $result;
	}

}

?>

<?php
/**
 * Skin specific config vars
 *
 * @package 	TUPA
  * @author 			Urs Weiss <urs@tupa-dns.org>
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


$TUPA_CONF_VARS = lib_div::array_merge_recursive_overrule($TUPA_CONF_VARS, array(
	'HELP' => array(		// Help options
		'draggableXPos' => 235,				// Base x position for "draggable layer" help in px
		'draggableYPos' => 620				// Base y position for "draggable layer" help in px
	),
	'RRD' => array(	// RRD graphics generation (Should only be changed in template config)
		'CONFIG' => array(
			'WIDTH' => 500,				// Width of the canvas (not of the generated image)
			'HEIGHT' => 100,				// Height of the canvas (not of the generated image)
			'TABWIDTH' => 25,				// With in pixels of a tabulator
			'COLOR' => array(	// Colors
				'BACK' => '#ffffff',			// Background color of the image
				'CANVAS' => '#ffffff',			// Canvas background color
				'SHADEA' => '#ffffff',			// Top and left border color
				'SHADEB' => '#ffffff'			// Bottom and right border color
			),
			'FONT' => array(	// Font and sizes
				'FILE' => 'lib/rrd_font.ttf',		// Used ttf font
				'LEGEND' => 7,			// Legend text size
				'AXIS' => 6,				// Axis text size
				'UNIT' => 7,				// Unit sizes
				'TITLE' => 7				// Title size
			)
		),
		'QUERIES' => array(	// PDNS queries chart
			0 => array(	// udp-queries
				'NAME' => 'udp-queries',		// RRD's vname (A-Z, a-z, 0-9, -, _ and maximal 255 chars)
				'DATA' => 'AVERAGE',		// Data to draw (normaly AVERAGE, maybe MIN or MAX)
				'STYLE' => 'AREA',			// Style of the drawn data (AREA or LINE)
				'COLOR' => '#00ff00',		// Color of the area/line
				'LEGEND' => array(
					0 => array(
						'DATA' => 'MIN',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					1 => array(
						'DATA' => 'AVERAGE',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					2 => array(
						'DATA' => 'MAX',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					)
				)
			),
			1 => array(	// tcp-queries
				'NAME' => 'tcp-queries',		// RRD's vname (A-Z, a-z, 0-9, -, _ and maximal 255 chars)
				'DATA' => 'AVERAGE',		// Data to draw (normaly AVERAGE, maybe MIN or MAX)
				'STYLE' => 'LINE',			// Style of the drawn data (AREA or LINE)
				'COLOR' => '#0000ff',			// Color of the area/line
				'LEGEND' => array(
					0 => array(
						'DATA' => 'MIN',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					1 => array(
						'DATA' => 'AVERAGE',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					),
					2 => array(
						'DATA' => 'MAX',
						'VALUE_FORMAT' => '%5.1lf %Sqps',	// Value format
					)
				)
			)
		),
		'LATENCY' => array(	// PDNS latency chart
			0 => array(	// latency
				'NAME' => 'latency',			// RRD's vname (A-Z, a-z, 0-9, -, _ and maximal 255 chars)
				'DATA' => 'AVERAGE',		// Data to draw (normaly AVERAGE, maybe MIN or MAX)
				'STYLE' => 'LINE',			// Style of the drawn data (AREA or LINE)
				'COLOR' => '#0000ff',		// Color of the area/line
				'LEGEND' => array(
					0 => array(
						'DATA' => 'MIN',
						'VALUE_FORMAT' => '%4.1lf %Ssec',	// Value format
					),
					1 => array(
						'DATA' => 'AVERAGE',
						'VALUE_FORMAT' => '%4.lf %Ssec',	// Value format
					),
					2 => array(
						'DATA' => 'MAX',
						'VALUE_FORMAT' => '%4.1lf %Ssec',	// Value format
					)
				)
			),
		)
	)
)
);

?>
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
 * Generating page content.
 * Wrapping around parts, generate form tags, substitute template parts,
 * generatze tables, message-array, ...
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */
class template {
	var $message;
	//var $uploadFieldCount = 0;

	/**
	 * Returns generator meta tag
	 *
	 * @return	string		<meta> tag with name "GENERATOR"
	 */
	function generator()	{
		$str = 'TUPA '.TUPA_branch.', http://www.whtiy.ch, &#169; Urs Weiss 2005-2006.';
		return '<meta name="GENERATOR" content="'.$str .'" />';
	}



	/**
	 * Returns <h1> header
	 *
	 * @param	string		header content
	 * @return	string		formated header
	 */
	function header($string)	{
		$string = '
			<h1>'. $string. '</h1>
			';
		return $string;
	}



	/**
	 * Returns string wrapped in CDATA "tags" for XML / XHTML (wrap content of <script> and <style> sections in those!)
	 *
	 * @param	string		Input string
	 * @return	string		Output string
	 * @author			Kasper Skaarhoj <kasperYYYY@typo3.com>
	 */
	function wrapInCData($string)	{
		$string = '/*<![CDATA[*/'.
			$string.
			'/*]]>*/';

		return $string;
	}

	/**
	 * Wraps the input string in script tags.
	 * Automatic re-identing of the JS code is done by using the first line as ident reference.
	 * This is nice for identing JS code with PHP code on the same level.
	 *
	 * @param	string		Input string
	 * @param	boolean	Wrap script element in linebreaks? Default is TRUE.
	 * @return	string		Output string
	 * @author			Kasper Skaarhoj <kasperYYYY@typo3.com>
	 */
	function wrapScriptTags($string, $linebreak=TRUE)	{
		if(trim($string)) {
				// <script wrapped in nl?
			$cr = $linebreak? "\n" : '';

				// remove nl from the beginning
			$string = preg_replace ('/^\n+/', '', $string);
				// re-ident to one tab using the first line as reference
			if(preg_match('/^(\t+)/',$string,$match)) {
				$string = str_replace($match[1],"\t", $string);
			}
			$string = $cr.'<script type="text/javascript">
	/*<![CDATA[*/
	'.$string.'
	/*]]>*/
	</script>'.$cr;
		}
		return trim($string);
	}




	/**
	 * Wraps the input string in div tags with the given id.
	 * Used for design reasons
	 *
	 * @param	string		id of div
	 * @param	string		Input string
	 * @return	string		Output string
	 */

	function wrapInDiv($id, $content='&nbsp;', $params='') {
		return '<div id="'. $id .'" '. $params .'>'. $content .'</div>' . "\n";
	}




	/**
	 * Wraps the input string in div tags with the given id.
	 * Used for design reasons
	 *
	 * @param	string		id of div
	 * @param	string		Input string
	 * @return	string		Output string
	 */

	function wrapInFormTags($content, $JS_validated, $formName='formdata') {
		return '<form action="#" name="'. $formName .'" onsubmit="cleanMessages(); if (validateForm(this,false,false,false,false,16)) { '. $JS_validated .' return false; } else { return false; }">'. $content .'</form>' . "\n";
	}





	/**
	 * Reads the fileContent of $fName and returns it.
	 *
	 * @param	string		Absolute filepath to record
	 * @return	string		The content returned
	 */
	function fileContent($fName)	{
		$incFile = $fName;
		if ($incFile && $fd=fopen($incFile,'rb'))	{
			$content = '';
			while (!feof($fd))	{
				$content.=fread($fd, 5000);
			}
			fclose( $fd );
			return $content;
		}
	}


	/**
	 * Returns a subpart from the input content stream.
	 * A subpart is a part of the input stream which is encapsulated in a string matching the input string, $marker. If this string is found inside of HTML comment tags the start/end points of the content block returned will be that right outside that comment block.
	 * Example: The contennt string is "Hello <!--###sub1### begin--> World. How are <!--###sub1### end--> you?" If $marker is "###sub1###" then the content returned is " World. How are ". The input content string could just as well have been "Hello ###sub1### World. How are ###sub1### you?" and the result would be the same
	 *
	 * @param	string		The content stream, typically HTML template content.
	 * @param	string		The marker string, typically on the form "###[the marker string]###"
	 * @return	string		The subpart found, if found.
	 * @author			Kasper Skaarhoj <kasperYYYY@typo3.com>
	 */
	function getSubpart($content, $marker)	{
		if ($marker && strstr($content,$marker))	{
			$start = strpos($content, $marker)+strlen($marker);
			$stop = @strpos($content, $marker, $start+1);
			$sub = substr($content, $start, $stop-$start);

			$reg=Array();
			ereg('^[^<]*-->',$sub,$reg);
			$start+=strlen($reg[0]);

			$reg=Array();
			ereg('<!--[^>]*$',$sub,$reg);
			$stop-=strlen($reg[0]);

			return substr($content, $start, $stop-$start);
		}
	}

	/**
	 * Substitute subpart in input template stream.
	 * This function substitutes a subpart in $content with the content of $subpartContent.
	 *
	 * @param	string		The content stream, typically HTML template content.
	 * @param	string		The marker string, typically on the form "###[the marker string]###"
	 * @param	mixed		The content to insert instead of the subpart found. If a string, then just plain substitution happens (includes removing the HTML comments of the subpart if found). If $subpartContent happens to be an array, it's [0] and [1] elements are wrapped around the EXISTING content of the subpart (fetched by getSubpart()) thereby not removing the original content.
	 * @param	boolean	If $recursive is set, the function calls itself with the content set to the remaining part of the content after the second marker. This means that proceding subparts are ALSO substituted!
	 * @return	string		The processed HTML content string.
	 * @author			Kasper Skaarhoj <kasperYYYY@typo3.com>
	 */
	function substituteSubpart($content,$marker,$subpartContent,$recursive=1)	{
		$start = strpos($content, $marker);
		$stop = @strpos($content, $marker, $start+1)+strlen($marker);
		if ($start && $stop>$start)	{
				// code before
			$before = substr($content, 0, $start);
			$reg=Array();
			ereg('<!--[^>]*$',$before,$reg);
			$start-=strlen($reg[0]);
			$before = substr($content, 0, $start);
				// code after
			$after = substr($content, $stop);
			$reg=Array();
			ereg('^[^<]*-->',$after,$reg);
			$stop+=strlen($reg[0]);
			$after = substr($content, $stop);
				// replace?
			if (is_array($subpartContent))	{
				$substContent=$subpartContent[0].$this->getSubpart($content,$marker).$subpartContent[1];
			} else {
				$substContent=$subpartContent;
			}

			if ($recursive && strpos($after, $marker))	{
				return $before.$substContent.$this->substituteSubpart($after,$marker,$subpartContent);
			} else {
				return $before.$substContent.$after;
			}
		} else {
			return $content;
		}
	}

	/**
	 * Substitutes a marker string in the input content (by a simple str_replace())
	 *
	 * @param	string		The content stream, typically HTML template content.
	 * @param	string		The marker string, typically on the form "###[the marker string]###"
	 * @param	mixed		The content to insert instead of the marker string found.
	 * @return	string		The processed HTML content string.
	 * @author			Kasper Skaarhoj <kasperYYYY@typo3.com>
	 */
	function substituteMarker($content,$marker,$markContent) {
		return str_replace($marker,$markContent,$content);
	}



	/**
	 * Traverses the input $markContentArray array and for each key the marker by the same name (possibly wrapped and in upper case) will be substituted with the keys value in the array.
	 * This is very useful if you have a data-record to substitute in some content. In particular when you use the $wrap and $uppercase values to pre-process the markers. Eg. a key name like "myfield" could effectively be represented by the marker "###MYFIELD###" if the wrap value was "###|###" and the $uppercase boolean true.
	 *
	 * @param	string		The content stream, typically HTML template content.
	 * @param	array		The array of key/value pairs being marker/content values used in the substitution. For each element in this array the function will substitute a marker in the content stream with the content.
	 * @param	string		A wrap value - [part 1] | [part 2] - for the markers before substitution
	 * @param	boolean	If set, all marker string substitution is done with upper-case markers.
	 * @return	string		The processed output stream
	 * @author			Kasper Skaarhoj <kasperYYYY@typo3.com>
	 */
	function substituteMarkerArray($content,$markContentArray,$wrap='',$uppercase=0)	{
		if (is_array($markContentArray))	{
			reset($markContentArray);
			$wrapArr=lib_div::trimExplode('|',$wrap);
			while(list($marker,$markContent)=each($markContentArray))	{
				if($uppercase)	$marker=strtoupper($marker);
				if(strcmp($wrap,'')) $marker=$wrapArr[0].$marker.$wrapArr[1];
				$content=str_replace($marker,$markContent,$content);
			}
		}
		return $content;
	}




	/**
	 * Parses the content searching for Tab-Markers, wraps them with div tag, generates navigation and JavaScript.
	 *
	 * @param	string		Content to parse
	 * @return	string		Parsed content
	 */
	function parseTabs($content) {
		global  $LANG;
		$markerArray = array();

		// Get all markers which begins with "TAB_"
		preg_match_all("|###TAB_(.*)###|U", $content, $reg, PREG_PATTERN_ORDER);

		if (is_array($reg) && count($reg[0])>0) {
			// make array values unique
			$reg[0] = array_unique($reg[0]);
			$reg[1] = array_unique($reg[1]);

			// Debug
			// $this->addMessage('debug', nl2br(print_r($reg, true)));

			// Clean JS tabArray first of all
			$tabJS = 'tabArray.length=0;';
			$tabCounter = '0';
			$registerNavigation = '<div id="tab-menu"><table nowrap="nowrap" cellpadding="0" cellspacing="0" border="0"><tr>';
			foreach ($reg[1] as $key => $value) {
				// Generate register entry
				$registerNavigation .= '<td onmouseover="tabMouseOver(this);" onmouseout="tabMouseOut(this);" id="TAB_'. $value .'_MENU" class="tab-ina"><a href="javascript:void(0);" onclick="this.blur(); tabActivate(\''. $tabCounter .'\'); return false;">'. $LANG->getLang('tab'. lib_div::firstUpper($value)) .'</a></td><td>&nbsp</td>';

				// Wrap part in div tags
				$tabContentWrap = array();
				$tabContentWrap[0] = '<div id="TAB_'. $value .'" style="display: none;">';
				$tabContentWrap[1] = '</div>';
				$content = $this->substituteSubpart($content, $reg[0][$key], $tabContentWrap);

				// Add element to JS array
				$tabJS .= 'tabArray['. $tabCounter .'] = "TAB_'. $value .'";';

				$tabCounter++;
			}
			$registerNavigation .= '</tr></table></div>';

			// Substitute register navigation
			$markerArray['register_navigation'] = $registerNavigation;
			$content = $this->substituteMarkerArray($content, $markerArray, '###|###', '1');

			// Activate first tab from JS
			$tabJS .= 'tabActivate(\'0\');';

			// Add JS to the end of the content
			//$this->addMessage('debug', $tabJS);
			$this->addMessage('', '', $tabJS);
		}
		return $content;
	}




	/**
	 *  KOIVI PNG Alpha IMG Tag Replacer for PHP (C) 2004 Justin Koivisto
	 *  Version 2.0.12
	 *  Last Modified: 12/30/2005
	 *
	 *  This library is free software; you can redistribute it and/or modify it
	 *  under the terms of the GNU Lesser General Public License as published by
	 *  the Free Software Foundation; either version 2.1 of the License, or (at
	 *  your option) any later version.
	 *
	 *  This library is distributed in the hope that it will be useful, but
	 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
	 *  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
	 *  License for more details.
	 *
	 *  You should have received a copy of the GNU Lesser General Public License
	 *  along with this library; if not, write to the Free Software Foundation,
	 *  Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
	 *
	 *  Full license agreement notice can be found in the LICENSE file contained
	 *  within this distribution package.
	 *
	 *  Justin Koivisto
	 *  justin.koivisto@gmail.com
	 *  http://koivi.com
	 *
	 *  Modifies IMG and INPUT tags for MSIE5+ browsers to ensure that PNG-24
	 *  transparencies are displayed correctly.  Replaces original SRC attribute
	 *  with a binary transparent PNG file (spacer.png) that is located in the same
	 *  directory as the orignal image, and adds the STYLE attribute needed to for
	 *  the browser. (Matching is case-insensitive. However, the width attribute
	 *  should come before height.
	 *
	 *  Also replaces code for PNG images specified as backgrounds via:
	 *  background-image: url(image.png); or background-image: url('image.png');
	 *  When using PNG images in the background, there is no need to use a spacer.png
	 *  image. (Only supports inline CSS at this point.)
	 *
	 *  ##### CUSTOM TUPA CHANGES: #####
	 *  - Replaced all "$_SERVER['DOCUMENT_ROOT']" with "PATH_site"
	 *  - Check for nopngfix - tag (nopngfix="1") which skips the processing for marked image
	 *  - Moved IE browser check to init.php for constant definition
	 *
	 *  @param string $x  String containing the content to search and replace in.
	 *  @param string $img_path   The path to the directory with the spacer image relative to
	 *                      the DOCUMENT_ROOT. If none os supplied, the spacer.png image
	 *                      should be in the same directory as PNG-24 image.
	 *  @param string $sizeMeth   String containing the sizingMethod to be used in the
	 *                      Microsoft.AlphaImageLoader call. Possible values are:
	 *                      crop - Clips the image to fit the dimensions of the object.
	 *                      image - Enlarges or reduces the border of the object to fit
	 *                              the dimensions of the image.
	 *                      scale - Default. Stretches or shrinks the image to fill the borders
	 *                              of the object.
	 *  @param bool   $inScript  Boolean flag indicating whether or not to replace IMG tags that
	 *                      appear within SCRIPT tags in the passed content. If used, may cause
	 *                      javascript parse errors when the IMG tags is defined in a javascript
	 *                      string. (Which is why the options was added.)
	 *  @return string
	 *
	 */
	function replacePngTags($x,$img_path='',$sizeMeth='scale',$inScript=FALSE){
		$arr2=array();
		// make sure that we are only replacing for the Windows versions of Internet
		// Explorer 5.5+
		if(!IE_PNGFIX) return $x;

		if($inScript){
			// first, I want to remove all scripts from the page...
			$saved_scripts=array();
			$placeholders=array();
			preg_match_all('`<script[^>]*>(.*)</script>`isU',$x,$scripts);
			for($i=0;$i<count($scripts[0]);$i++){
				$x=str_replace($scripts[0][$i],'replacePngTags_ScriptTag-'.$i,$x);
				$saved_scripts[]=$scripts[0][$i];
				$placeholders[]='replacePngTags_ScriptTag-'.$i;
			}
		}

		// find all the png images in backgrounds
		preg_match_all('/background-image:\s*url\(([\\"\\\']?)([^\)]+\.png)\1\);/Uis',$x,$background);
		for($i=0;$i<count($background[0]);$i++){
			// simply replace:
			//  "background-image: url('image.png');"
			// with:
			//  "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(
			//      enabled=true, sizingMethod=scale, src='image.png');"
			// I don't think that the background-repeat styles will work with this...
			$x=str_replace($background[0][$i],'filter:progid:DXImageTransform.'.
			'Microsoft.AlphaImageLoader(enabled=true, sizingMethod='.$sizeMeth.
			', src=\''.$background[2][$i].'\');',$x);
		}

		// find all the IMG tags with ".png" in them
		$pattern='/<(input|img)[^>]*src=([\\"\\\']?)([^>]*\.png)\2[^>]*>/i';
		preg_match_all($pattern,$x,$images);
		for($num_images=0;$num_images<count($images[0]);$num_images++){
			// Check if we have a nopngfix tag
			if (preg_match('/nopngfix=([\\"\\\']?)([1])\\1/i', $images[0][$num_images], $pngFix)) continue;

			// for each found image pattern
			$original=$images[0][$num_images];
			$quote=$images[2][$num_images];
			$atts=''; $width=0; $height=0; $modified=$original;

			// We do this so that we can put our spacer.png image in the same
			// directory as the image - if a path wasn't passed to the function
			if(empty($img_path)){
				$tmp=split('[\\/]',$images[3][$num_images]);
				$this_img=array_pop($tmp);
				$img_path=join('/',$tmp);
				if(empty($img_path)){
					// this was a relative URI, image should be in this directory
					$tmp=split('[\\/]',$_SERVER['SCRIPT_NAME']);
					array_pop($tmp);    // trash the script name, we only want the directory name
					$img_path=join('/',$tmp).'/';
				}else{
					$img_path.='/';
				}
			}else if(substr($img_path,-1)!='/'){
				// in case the supplied path didn't end with a /
				$img_path.='/';
			}

			// If the size is defined by styles, find them
			preg_match_all(
			'/style=([\\"\\\']).*(\s?width:\s?([0-9]+(px|%));).*'.
			'(\s?height:\s?([0-9]+(px|%));).*\\1/Ui',
			$images[0][$num_images],$arr2);
			if(is_array($arr2) && count($arr2[0])){
				// size was defined by styles, get values
				$width=$arr2[3][0];
				$height=$arr2[6][0];

				// remove the width and height from the style
				$stripper=str_replace(' ','\s','/('.$arr2[2][0].'|'.$arr2[5][0].')/');
				// Also remove any empty style tags
				$modified=preg_replace(
				'`style='.$arr2[1][0].$arr2[1][0].'`i',
				'',
				preg_replace($stripper,'',$modified));
			}else{
				// size was not defined by styles, get values from attributes
				preg_match_all('/width=([\\"\\\']?)([0-9%]+)\\1/i',$images[0][$num_images],$arr2);
				if(is_array($arr2) && count($arr2[0])){
					$width=$arr2[2][0];
					if(is_numeric($width))
					$width.='px';

					// remove width from the tag
					$modified=str_replace($arr2[0][0],'',$modified);
				}
				preg_match_all('/height=([\\"\\\']?)([0-9%]+)\\1/i',$images[0][$num_images],$arr2);
				if(is_array($arr2) && count($arr2[0])){
					$height=$arr2[2][0];
					if(is_numeric($height))
					$height.='px';

					// remove height from the tag
					$modified=str_replace($arr2[0][0],'',$modified);
				}
			}

			if($width==0 || $height==0){
				// width and height not defined in HTML attributes or css style, try to get
				// them from the image itself
				// this does not work in all conditions... It is best to define width and
				// height in your img tag or with inline styles..
				if(file_exists(PATH_site.$img_path.$images[3][$num_images])){
					// image is on this filesystem, get width & height
					$size=getimagesize(PATH_site.$img_path.$images[3][$num_images]);
					$width=$size[0].'px';
					$height=$size[1].'px';
				}else if(file_exists(PATH_site.$images[3][$num_images])){
					// image is on this filesystem, get width & height
					$size=getimagesize(PATH_site.$images[3][$num_images]);
					$width=$size[0].'px';
					$height=$size[1].'px';
				}
			}

			// end quote is already supplied by originial src attribute
			$replace_src_with=$quote.$img_path.'spacer.png'.$quote.' style="width: '.$width.
			'; height: '.$height.'; filter: progid:DXImageTransform.'.
			'Microsoft.AlphaImageLoader(src=\''.$images[3][$num_images].'\', sizingMethod='.
			$sizeMeth.');"';

			// now create the new tag from the old
			$new_tag=str_replace($quote.$images[3][$num_images].$quote,$replace_src_with,
			str_replace('  ',' ',$modified));
			// now place the new tag into the content
			$x=str_replace($original,$new_tag,$x);
		}

		if($inScript){
			// before the return, put the script tags back in. (I was having problems when there was
			// javascript that had image tags for PNGs in it when using this function...
			$x=str_replace($placeholders,$saved_scripts,$x);
		}

		return $x;
	}



	/**
	 * Generates the search form tags, removes selected char and set correct part.
	 *
	 * @param	array		Configuration array
	 * @return	string		complete search form
	 */
	function genSearchForm($conf) {
		global $USER, $LANG;
		$setConfSearch = array();

		// last done search
		$lastSearch = isset($conf['show']['search']) ? lib_div::doubleTrimExplode(',', '=', $conf['show']['search']) : '';
//array_key_exists('sfield', $lastSearch)
		$searchString =  is_array($lastSearch) && isset($lastSearch['sfield']) ? $lastSearch['sfield'] : '';
		$content = '<input type="text" name="sfield" size="20" maxlength="255" class="field" value="'. $searchString .'" />';
		$setConfSearch[] = '\'sfield=\' + this.sfield.value';

		// Add group selection if an user admin is logged in
		if ($USER->hasPerm('users_admin') && $conf['csite'] != 'groups') {
			$onChange = '';
			$selectedId = is_array($lastSearch) && isset($lastSearch['sgroup']) ? $lastSearch['sgroup'] : '';
			$groupOptions = $this->groupSelectOptions($selectedId);
			if ($USER->hasPerm('users_show_group') && $conf['csite'] != 'users') {
				$onChange = 'onchange="getUsersOfGroup(this.form.name, \'suser\', this.value, true)"';
			}
			$content .= '<select name="sgroup" '. $onChange.'  class="field">'. $groupOptions .'</select>';
			$setConfSearch[] = '\',sgroup=\' + this.sgroup.value';
		}

		// Add user selection if an (domain) user admin is logged in
		if ($USER->hasPerm('users_show_group') && $USER->hasPerm($conf['csite'] .'_show_group') && $conf['csite'] != 'groups' && $conf['csite'] != 'users') {
			if ($USER->hasPerm('users_admin')) {
				$groupId = '';
			} else {
				$groupId = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']);
			}
			$selectedId = is_array($lastSearch) && isset($lastSearch['suser']) ? $lastSearch['suser'] : '';
			$userOptions = $this->userSelectOptions($groupId, $selectedId);
			$content .= '<select name="suser"  class="field">'. $userOptions .'</select>';
			$setConfSearch[] = '\',suser=\' + this.suser.value';
		}

		$content .= '<input type="submit" value="'. $LANG->getLang('buttonSearch') .'" class="button" />';

		// Merge the setConfSearch to a string
		$setConfSearch = implode(' + ', $setConfSearch);
		return $this->wrapInFormTags($content, 'setConf(\'show=>search\', '. $setConfSearch .', true); setConf(\'show=>char\', \'dchar\'); updateData(\''. $conf['csite'] .'\');', 'sform');
	}


	/**
	 * Generates a button with onClick action and adds additional params.
	 *
	 * @param	string		Value of button
	 * @param	string		onclick action
	 * @param	string		other parameters of button
	 * @return	string		single <input>-button-tag
	 */
	function genSingleButton($value, $onclick, $otherParams='') {
		$content = '<input type="button" value="'. $value .'" class="button" onclick="'. $onclick .'" '. $otherParams .' />';
		return $content;
	}


	/**
	 * Generates a link list form A to Z, numbers and reverse for domains.
	 *
	 * @param	array		Configuration array
	 * @param	boolean	Show a "reverse" link for domains
	 * @return	string		List of characters (and numbers/reverse) wrapped in <div>-tag
	 */
	function genCharLinks ($conf, $reverse=FALSE) {
		$content = '';

		$content .= '<span name="dchar"></span>';
		$content .= '<a href="javascript:void(0);" name="ALL" onclick="setConf(\'show=>char\', \'ALL\', true); updateData(\''. $conf['csite'] .'\');" onfocus="if(this.blur)this.blur()">[ALL]</a>';
		$content .= '<a href="javascript:void(0);" name="'. chr(35) .'" onclick="setConf(\'show=>char\', \''. chr(35) .'\', true); updateData(\''. $conf['csite'] .'\');" onfocus="if(this.blur)this.blur()">'. chr(35) .'</a>';

		for ($i='65'; $i <= '90'; $i++) {
			$content .= '<a href="javascript:void(0);" name="'. chr($i) .'" onclick="setConf(\'show=>char\', \''. chr($i) .'\', true); updateData(\''. $conf['csite'] .'\');" onfocus="if(this.blur)this.blur()">'. chr($i) .'</a>';
		}

		// If showing domains add reverse link
		if ($reverse) $content .= '<a href="javascript:void(0);" name="REV" onclick="setConf(\'show=>char\', \'REV\', true); updateData(\''. $conf['csite'] .'\');" onfocus="if(this.blur)this.blur()">[reverse]</a>';

		$content = $this->wrapInDiv('char-list', $content);
		return $content;
	}



	/**
	 * Generates the page number navigation with the configured values.
	 *
	 * @param	array		Configuration array
	 * @return	string		The page navigation
	 */
	function genNavigation($conf, $totalLines, $linesPerSite, $minElements) {
		$content = '';
		$currentPage = isset($conf['show']['page']) ? $conf['show']['page'] : '1';

		$totalSites = ceil($totalLines / $linesPerSite);
		$pageArray = lib_div::genSimplePageArray($totalSites);
		if (count($pageArray) == '0') $pageArray = array('0' => '1');

		$lowest = min($pageArray);
		$key = array_search($currentPage, $pageArray);
		$keyMax = array_search(max($pageArray), $pageArray);

		// Eval needed JS function
		switch ($conf['part']) {
			case 'showLogMessages':
			case 'updateLogMessages':
				$JsFunction = 'updateLogMessages(d.forms[0]);';
				break;
			default: // show/update Data
				$JsFunction = 'updateData(\''. $conf['csite'] .'\');';
				break;
		}

		// select the correct "PREV" image and set the link if any
		if ($currentPage == $lowest) {
			$content .= '<span class="navi-ina">&lt;&lt;</span>';
		} else {
			$content .= '<span class="navi-act"><a href="javascript:void(0);" onclick="setConf(\'show=>page\', \''. ($currentPage - 1) .'\'); '. $JsFunction .'" onfocus="if(this.blur)this.blur()">&lt;&lt;</a></span>';
		}

		if ($key < ($minElements / 2) || count($pageArray) <= $minElements) {
			$startPos = '0';
		} elseif($keyMax + 1 < ($currentPage + ceil($minElements / 2)-1) && count($pageArray) > $minElements) {
			$startPos = $keyMax - $minElements +1;
		} elseif (count($pageArray) > $minElements) {
			$startPos = $key - ceil($minElements / 2);
		}

		$endPos = $startPos + $minElements;

		while ($startPos < $endPos) {
			if ($startPos < $totalSites) {
				$pageNum = $pageArray[$startPos];
			} else {
				$pageNum = '';
			}
			$pageNum ? $pageNum = $pageNum : $pageNum = $startPos + 1;

			if ($pageNum == $currentPage)  {
				$content .= '<span class="navi-cur">'. $pageNum .'</span>';
			} elseif (in_array($pageNum, $pageArray)) {
				$content .= '<span class="navi-act"><a href="javascript:void(0);" onclick="setConf(\'show=>page\', \''. $pageNum .'\'); '. $JsFunction .'" onfocus="if(this.blur)this.blur()">'. $pageNum .'</a></span>';
			} else {
				$content .= '<span class="navi-ina">'. $pageNum .'</span>';
			}

			$startPos++;
		}

		// select the correct "NEXT" image and set the link if any
		if ($currentPage < $pageArray[$keyMax]) {
			$content .= '<span class="navi-act"><a href="javascript:void(0);" onclick="setConf(\'show=>page\', \''. ($currentPage + 1) .'\'); '. $JsFunction .'" onfocus="if(this.blur)this.blur()">&gt;&gt;</a></span>';
		} else {
			$content .= '<span class="navi-ina">&gt;&gt;</span>';
		}

		$content = $this->wrapInDiv('site-navi', $content);
		return $content;
	}






	/**
	 * Generates the message field needed to display success/error/debug messages.
	 *
	 * @return	string	simple <ul>-tag
	 */
	function  genMessageField() {
		return $content = '
			<ul id="messages">
			</ul>';
	}



	/**
	 * Adds a new message to the message-array wich is displayed in the message field.
	 *
	 * @param	string		Type of message (error/success/debug)
	 * @param	string		The message
	 * @param	string		JavaScript to execute
	 * @param	boolean	Deletes the message-array first
	 */
	function addMessage($type, $content, $additionalTask='', $delete=false) {
		if ($delete) $this->message = array();

		$this->message[] = array('type'=>$type, 'content'=>$content, 'additionalTask'=>$additionalTask);
	}



	/**
	 * Shows "no permission" message if someone tries to access to a page without needed permissions.
	 *
	 * @param	boolean	Switches to an other, better message for some pages
	 * @return	string		Message field with no permission message
	 */
	function noPermissionMessage($access=false) {
		global $USER, $LANG;
		$this->addMessage('error', $access ? $LANG->getLang('noAccessPermissions') : $LANG->getLang('noPagePermissions'), '', true);
		// Debugging
		// $this->addMessage('debug', nl2br(print_r($USER->perm, true)));
		$content = $this->header($LANG->getLang('noPermissionsTitle'));
		$content .= $this->genMessageField();

		return $content;
	}




	/**
	 * Generates all/show/add/edit/delete checkboxes used for user permissions.
	 *
	 * @param	string		Marker array used to replace in template
	 * @param	string		Group to generate checkboxes for
	 * @param	string		Current user permissions-array to set checked attribute
	 * @return	array		The extended marker array
	 */
	function genPermissionCheckboxes($markerArray, $group, $userPerms='') {
//		global  $LANG;
		$upperGroup = strtoupper($group);
		//$partList == '' ? $partArr = explode(',', 'show,add,edit,delete') : $partArr = explode(',', $partList);
		$partArr = explode(',', 'all,show,add,edit,delete');
		foreach ($partArr as $part) {
			$onCheck = '';
			$onUncheck = '';
			$checked = '';

			switch ($part) {
				case 'all':
					$onCheck = 'perm_'. $upperGroup .'_show|1|1,perm_'. $upperGroup .'_add|1|1,perm_'. $upperGroup .'_edit|1|1,perm_'. $upperGroup .'_delete|1|1';
					$onUncheck = 'perm_'. $upperGroup .'_show|1|0,perm_'. $upperGroup .'_add|1|0,perm_'. $upperGroup .'_edit|1|0,perm_'. $upperGroup .'_delete|1|0';
					break;
				case 'show':
					$onUncheck = 'perm_'. $upperGroup .'_all|1|0,perm_'. $upperGroup .'_add|1|0,perm_'. $upperGroup .'_edit|1|0,perm_'. $upperGroup .'_delete|1|0';
					break;
				case 'add':
				case 'edit':
				case 'delete':
					$onCheck = 'perm_'. $upperGroup .'_show|1|1';
					$onUncheck = 'perm_'. $upperGroup .'_all|1|0';
					break;
			}
			if ($onCheck OR $onUncheck) {
				$onChange = 'onclick="toggleFields(this, \''. $onCheck .'\', \''. $onUncheck .'\');"';
			}

			if ($part != 'all' && isset($userPerms[$upperGroup][$part]) && $userPerms[$upperGroup][$part]) $checked = 'checked="checked"';
			$markerArray['input_perm_'. $group .'_'. $part] = '<input type="checkbox" name="perm_'. $upperGroup .'_'. $part .'" value="1" '. $onChange .' '. $checked .' />';
		}
		return $markerArray;
	}


	/**
	 * Returns the login box image, whether the default or an image from the rotation folder.
	 *
	 * @return	string		HTML image tag.
	 */
	function makeLoginBoxImage()	{
		$loginImage = 'tupa-v0.1.png';
		$imgSize = @getimagesize(PATH_site .'images/'. $loginImage);

		$loginboxImage = '<img src="'. 'images/'. $loginImage .'" '. $imgSize['3'] .' id="login-image" alt="Loginbox Image" title="Loginbox Image" />';

		// Return image tag:
		return $loginboxImage;
	}



	/**
	 * Generates an option for each configured language.
	 *
	 * @return	string		Options for select-box
	 */
	function languageSelectOptions($selectedId='') {
		global $LANG;
		$options = '<option value="0">'. $LANG->getLang('prefsLanguageSelect') .'</option>';
		foreach ($LANG->langArr as $lang) {
			// Get language info file
			$xmlInfoPath = PATH_lang . $lang .'/info.xml';
			if (file_exists($xmlInfoPath)) {
				$xmlData = file_get_contents($xmlInfoPath);

				if ($xmlData) {
					// Get the values
					$infoArr = XMLParser::GetXMLTree($xmlInfoPath);

					$selected = $lang == $selectedId ? ' selected' : '';
					$langName = $infoArr['LANGUAGE'][0]['NAME'][0]['VALUE'];
					$options .= '<option value="'. $lang .'"'. $selected .'>'. stripslashes($langName) .'</option>';
				}
			}
		}
		return $options;
	}



	/**
	 * Generates an option for each configured skin.
	 *
	 * @return	string		Options for select-box
	 */
	function skinSelectOptions($selectedId='') {
		global $TUPA_CONF_VARS;
		$options = '';

		// Get skins array
		$skinArr = explode('|', $TUPA_CONF_VARS['SKINS']['skins']);

		foreach ($skinArr as $skin) {
			// Get skin info file
			$xmlInfoPath = PATH_site . 'skins/'. $skin .'/info.xml';
			if (file_exists($xmlInfoPath)) {
				$xmlData = file_get_contents($xmlInfoPath);

				if ($xmlData) {
					// Get the values
					$infoArr = XMLParser::GetXMLTree($xmlInfoPath);

					$selected = $skin == $selectedId ? ' selected' : '';
					$skinName = $infoArr['SKIN'][0]['NAME'][0]['VALUE'];
					$options .= '<option value="'. $skin .'"'. $selected .'>'. stripslashes($skinName) .'</option>';
				}
			}
		}
		return $options;
	}


	/**
	 * Generates options for max items per site select-box of logging.
	 *
	 * @return	string		Options for select-box
	 */
	function logMaxSelectOptions($selectedId='') {
		global $TUPA_CONF_VARS;

		$options = '';
		$showItemAmount = lib_div::trimExplode(',', $TUPA_CONF_VARS['LOGGING']['showItemAmount']);
		foreach ($showItemAmount as $value) {
			$selected = $value == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $value .'" '. $selected .'>'. $value .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for all groups..
	 *
	 * @param 	string		Optionally Selected id
	 * @param 	string		List of group ID's to exclude
	 * @param 	mixed		False or language-key to use for it
	 * @return	string		Options for select-box
	 */
	function groupSelectOptions($selectedId='', $excludeIds='', $addEmpty='selectGroup') {
		global $LANG;

		$options = '';
		$sqlWhere = '';
		if ($excludeIds) $sqlWhere = 'id NOT IN ('. lib_DB::fullQuoteStrList($excludeIds) .')';
		$res = $GLOBALS['TUPA_DB']->exec_SELECTgetRows('id, name', 'groups', $sqlWhere, '', 'name', '');
		lib_div::stripSlashesOnArray($res);
		lib_div::htmlspecialcharOnArray($res);
		if ($addEmpty !== false) $options .= '<option value="0" '. ($selectedId === 0 ? 'selected' : '') .'>'. $LANG->getLang($addEmpty) .'</option>';
		foreach ($res as $value) {
			$selected = $value['id'] == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $value['id'] .'" '. $selected .'>'. stripslashes($value['name']) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for users from group id or all if empty.
	 *
	 * @param 	integer		ID of group
	 * @param 	integer		Selected entry
	 * @param 	string		List of user ID's to exclude
	 * @param 	mixed		False or language-key to use for it
	 * @return	string		Options for select-box
	 */
	function userSelectOptions($grp_id='', $selectedId='', $excludeIds='', $addEmpty='selectUser') {
		global $LANG;

		$options = '';
		$sqlWhereArr = array();
		$sqlWhere = '';
		if ($grp_id) $sqlWhereArr[] = 'grp_id='. lib_DB::fullQuoteStr($grp_id);
		if ($excludeIds) $sqlWhereArr[] = 'id NOT IN ('. lib_db::fullQuoteStrList($excludeIds) .')';
		$sqlWhere = implode(' AND ', $sqlWhereArr);
		$res = $GLOBALS['TUPA_DB']->exec_SELECTgetRows('id,username', 'users', $sqlWhere, '', 'name', '');
		if ($addEmpty !== false) $options .= '<option value="0" '. ($selectedId === 0 ? 'selected' : '') .'>'. $LANG->getLang($addEmpty) .'</option>';
		foreach ($res as $value) {
			$selected = $value['id'] == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $value['id'] .'" '. $selected .'>'. stripslashes($value['username']) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for templates from user id or all if empty.
	 *
	 * @param 	string		List of user-ID's to get templates from
	 * @param 	integer		ID pf selected option
	 * @return	string		Options for select-box
	 */
	function templateSelectOptions($userIdList='', $selectedId='') {
		global $LANG;

		$sqlWhere = '';
		if ($userIdList) {
			$sqlWhere = 'usr_id IN ('. lib_DB::fullQuoteStrList($userIdList) .')';
		}
		$res = $GLOBALS['TUPA_DB']->exec_SELECTgetRows('id,name', 'templates', $sqlWhere, '', 'name', '');
		$options = '<option value="0">'. $LANG->getLang('selectTemplate') .'</option>';
		foreach ($res as $value) {
			$selected = $value['id'] == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $value['id'] .'" '. $selected .'>'. stripslashes($value['name']) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for all available parts.
	 *
	 * @return	string		Options for select-box
	 */
	function logPartSelectOptions() {
		global $LANG;

		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('part', 'logging', '', 'part', 'part', '');
		$options = '<option value="0">'. $LANG->getLang('selectPart') .'</option>';
		while ($value = mysql_fetch_array($res)) {
			$options .= '<option value="'. $value['0'] .'">'. stripslashes($LANG->getLang('logPart'. lib_div::firstUpper($value['0']))) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for all available types.
	 *
	 * @return	string		Options for select-box
	 */
	function logTypeSelectOptions() {
		global $LANG;

		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('type', 'logging', '', 'type', 'type', '');
		$options = '<option value="0">'. $LANG->getLang('selectType') .'</option>';
		while ($value = mysql_fetch_array($res)) {
			$options .= '<option value="'. $value['0'] .'">'. stripslashes($LANG->getLang('logType'. lib_div::firstUpper($value['0']))) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for log refresh interval.
	 *
	 * @return	string		Options for select-box
	 */
	function logRefreshSelectOptions() {
		global $TUPA_CONF_VARS, $LANG;

		$options = '<option value="0">'. $LANG->getLang('logRefreshNever') .'</option>';
		$refreshValues = lib_div::trimExplode(',', $TUPA_CONF_VARS['LOGGING']['refresh']);
		
		foreach ($refreshValues as $value) {
			$options .= '<option value="'. $value .'">'. stripslashes($value .' '. $LANG->getLang('seconds')) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for the start page selection.
	 *
	 * @return	string		Options for select-box
	 */
	function startPageSelectOptions($selectedId='') {
		global $MENU, $LANG;

		$MENU->mainMenu();
		$options = '<option value="">'. $LANG->getLang('prefsStartPageSelect') .'</option>';
		foreach ($MENU->startPageArr as $key => $value) {
			$selected = $key == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $key .'" '. $selected .'>'. stripslashes($LANG->getLang('menu'. lib_div::firstUpper($key))) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options help display selection.
	 * 0=disabled / 1=layer / 2=popup
	 *
	 * @return	string		Options for select-box
	 */
	function displayHelpSelectOptions($selectedId='') {
		global $LANG;

		$options = '';
		$help = array(
			0 => 'Disabled',
			1 => 'Layer',
			2 => 'Popup'
		);

		foreach ($help as $key => $value) {
			$selected = $key == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $key .'" '. $selected .'>'. stripslashes($LANG->getLang('prefsDisplayHelp'. $value)) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates compression select options.
	 * 0=none / 1=gzip / 2=bzip2
	 *
	 * @return	string		Options for select-box
	 */
	function compressionSelectOptions($selectedId='') {
		global $LANG;

		$options = '';
		$name = array(
			0 => 'None',
			1 => 'Gzip',
			2 => 'Bzip'
		);
		$functions = array(
			1 => 'gzencode',
			2 => 'bzcompress'
		);
		foreach ($name as $key => $value) {
			if ($key == 0 || function_exists($functions[$key])) {
				$selected = $key == $selectedId ? 'selected' : '';
				$options .= '<option value="'. $key .'" '. $selected .'>'. stripslashes($LANG->getLang('backupCompression'. $value)) .'</option>';
			}
		}
		return $options;
	}


	/**
	 * Generates save-path select options.
	 * 0=disabled / 1=local / 2=remote
	 *
	 * @return	string		Options for select-box
	 */
	function savebackupSelectOptions($selectedId='') {
		global $LANG;

		$options = '';
		$name = array(
			0 => 'Disabled',
			1 => 'Local',
			2 => 'Remote'
		);

		foreach ($name as $key => $value) {
			$selected = $key == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $key .'" '. $selected .'>'. stripslashes($LANG->getLang('backupSaveBackup'. $value)) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates size select options.
	 *
	 * @return	string		Options for select-box
	 */
	function sizeSelectOptions($selectedId='') {

		$options = '';
		$name = array(
			'b' => 'b',
			'kb' => 'kb',
			'Mb' => 'Mb',
			'Gb' => 'Gb'
		);

		foreach ($name as $key => $value) {
			$selected = $key == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $key .'" '. $selected .'>'. stripslashes($value) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates protocol select options.
	 * 0=FTP / 1=SFTP
	 *
	 * @return	string		Options for select-box
	 */
	function protocolSelectOptions($selectedId='') {
		global $TUPA_CONF_VARS, $LANG;

		$options = '';
		$name = array();
		if (function_exists('ftp_connect')) $name[0] = 'FTP';
		if (function_exists('ssh2_connect')) $name[1] = 'SSH';

		foreach ($name as $key => $value) {
			$selected = $key == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $key .'" '. $selected .'>'. stripslashes($value) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for local backups to restore
	 *
	 * @param 	object		Backup object
	 * @param 	string		Where to get list from (local or remote)
	 * @return	string		Options for select-box
	 */
	function restoreSelectOptions($BACKUP, $where) {
		global $LANG;
		$options = '<option value="">'. $LANG->getLang('backupSelectRestoreFile') .'</option>';

		if ($where == 'local') {
			$fileArr = $BACKUP->getLocalFileListArray();
		} elseif ($where == 'remote') {
			$fileArr = $BACKUP->getRemoteFileListArray();
		} else {
			return $options;
		}

		if ($fileArr) {
			foreach ($fileArr as $value) {
				$options .= '<option value="'. $value['name'] .'">'. stripslashes($value['name'] .' ['. lib_div::formatSize($value['size'])) .']</option>';
			}
		}
		return $options;
	}


	/**
	 * Generates options backup frequency
	 *
	 * @param 	string		Selected id
	 * @return	string		Options for select-box
	 */
	function frequencySelectOptions($selectedId='') {
		global $LANG;
		$options = '<option value="">'. stripslashes($LANG->getLang('backupExecuteNever')) .'</option>';

		$values = array(
			1 => $LANG->getLang('daily'),
			2 => $LANG->getLang('weekly'),
			3 => $LANG->getLang('monthly')
		);

		foreach ($values as $key => $value) {
			$selected = $key == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $key .'" '. $selected .'>'. stripslashes($value) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates weekday options
	 *
	 * @param 	string		Selected id
	 * @return	string		Options for select-box
	 */
	function weekdaysSelectOptions($selectedId='') {
		global $LANG;
		$options = '<option value="">&nbsp;</option>';

		for ($i = 0; $i <= 6; $i++) {
			$selected = $i == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $i .'" '. $selected .'>'. stripslashes($LANG->getLang('weekday'. $i)) .'</option>';
		}
		return $options;
	}


	/**
	 * Generates day options
	 *
	 * @param 	string		Selected id
	 * @return	string		Options for select-box
	 */
	function daysSelectOptions($selectedId='') {
		$options = '<option value="">&nbsp;</option>';

		for ($i = 1; $i <= 31; $i++) {
			$selected = $i == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $i .'" '. $selected .'>'. $i .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options of all IP's in the database
	 *
	 * @param 	string		Type op IP to change (IPv4 / IPv6)
	 * @param 	integer		Selected id
	 * @return	string		Options for select-box
	 */
	function ipSelectOptions($ipType='ipv4', $selectedId='') {
		global $TUPA_CONF_VARS, $LANG;

		$options = '';

		// Get IP's  in content field
		$conf['data']['type'] = $ipType;
		$values = tupa_general::getIpsOfType($conf);

//		$options .= '<optgroup label="'. $LANG->getLang('toolsIpChangeOptGroupIpV4') .'">';
		foreach ($values as $value) {
			$selected = $value == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $value .'" '. $selected .'>'. stripslashes($value) .'</option>';
		}
//		$options .= '</optgroup>';

/*
		// Get in-addr.arpa's in name field
		$res = $GLOBALS['TUPA_DB']->exec_SELECTgetRows('name', 'records', 'type=\'PTR\' AND name LIKE \'%.in-addr.arpa\'', 'name', '', '');

		$values = array();
		foreach ($res as $value) {
			$values[] = $value['name'];
		}
		// Sort the values natural
		natsort($values);

		$options .= '<optgroup label="'. $LANG->getLang('toolsIpChangeOptGroupPtrArpa') .'">';
		foreach ($values as $value) {
			$selected = $value == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $value .'" '. $selected .'>'. stripslashes($value) .'</option>';
		}
		$options .= '</optgroup>';
*/
		return $options;
	}


	/**
	 * Generates options of all IP's in the database
	 *
	 * @param 	integer		Selected id
	 * @return	string		Options for select-box
	 */
	function ipTypeSelectOptions($selectedId='') {
		$options = '';
		$name = array(
			'IPv4' => 'IPv4',
			'IPv6' => 'IPv6'
		);

		foreach ($name as $key => $value) {
			$selected = $key == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $key .'" '. $selected .'>'. $value .'</option>';
		}
		return $options;
	}


	/**
	 * Generates options for group/user selection (owner) on domain/template records.
	 *
	 * @param 	string		Part for permissions (domains or templates)
	 * @param 	string		Selected user id of boxes. If no one set it is set to own user ID.
	 * @return	string		Options for select-box
	 */
	function ownerSelectBoxes($part, $selectedUserId='') {
		global $USER, $LANG;
		$selectedUserId = $selectedUserId ? $selectedUserId : $_SESSION['uid'];
		$selectBoxes = '';
		$groupOptions = '';

		$userOptionsGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($selectedUserId);
		if ($USER->hasPerm($part .'_admin')) {
			$groupOptions = $this->groupSelectOptions($userOptionsGroup);
			$selectBoxes .= '<select name="owner_group" onchange="getUsersOfGroup(\'formdata\', \'owner\', this.value, true)" class="field">'. $groupOptions .'</select>';
		}
		$userOptions = $this->userSelectOptions($userOptionsGroup, $selectedUserId);
		$selectBoxes .= '<select name="owner" alt="select" emsg="'. $LANG->getLang('ownerSelectError') .'" class="field">'. $userOptions .'</select>';

		return $selectBoxes;
	}


	/**
	 * Generates an iframe with file upload field.
	 *
	 * @param 	string		Code to get target directory
	 * @param 	string		Translated help text (Normally from $LANG->getHelp function)
	 * @param 	string		Type of upload: 1=>single file / 2=> multi file
	 * @return	string		div wrapped iframe
	 */
	function addFileUploadField($uniqueId, $targetCode, $help='', $type=1) {
		//global $LANG;
		//$this->uploadFieldCount++;

		return '
			<div id="'. $uniqueId .'"><iframe name="ifupload" src="'. $this->fileUploadSrc($uniqueId, $targetCode, $type) .'" width="400" height="17" frameborder="0" scrolling="no"></iframe>'. $help .'</div><input type="text" name="'. $uniqueId .'" style="display: none;" />';
	}


	/**
	 * Generates upload source query
	 *
	 * @param 	string		Unique upload ID
	 * @param 	string		Code of targer
	 * @param 	string		Type of upload: 1=>single file / 2=> multi file
	 * @return	string		File source with parameters
	 */
	function fileUploadSrc($uniqueId, $targetCode, $type=1) {
		return 'fileupload.php?did='. $uniqueId .'&target='. $targetCode .'&type='. $type;
	}


}


$TBE_TEMPLATE = lib_div::makeInstance('template');
?>
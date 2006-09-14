<?php

/**
 *  File: calendar.php | (c) dynarch.com 2004
 *  Distributed as part of "The Coolest DHTML Calendar"
 *  under the same terms.
 *  -----------------------------------------------------------------
 *  This file implements a simple PHP wrapper for the calendar.  It
 *  allows you to easily include all the calendar files and setup the
 *  calendar by instantiating and calling a PHP object.
 */

/**
 * Class to include the JavaScript popup calendar
 *
 * @package 	TUPA
 * @author  	dynarch.com <dontknow@dynarch.com>
 * @author	Urs Weiss <urs(AT)tupa-dns.org>
 */
define('NEWLINE', "\n");

class DHTML_Calendar {
    var $calendar_lib_path;

    var $calendar_file;
    var $calendar_lang_file;
    var $calendar_setup_file;
    var $calendar_theme_file;
    var $calendar_options;

    function DHTML_Calendar($calendar_lib_path = '/calendar/',
                            $lang              = 'en',
                            $theme             = 'calendar-win2k-1',
                            $stripped          = true) {
        if ($stripped) {
            $this->calendar_file = 'calendar_stripped.js';
            $this->calendar_setup_file = 'calendar-setup_stripped.js';
        } else {
            $this->calendar_file = 'calendar.js';
            $this->calendar_setup_file = 'calendar-setup.js';
        }
        $this->calendar_lang_file = 'lang/calendar-' . $lang . '.js';
        $this->calendar_theme_file = $theme.'.css';
        $this->calendar_lib_path = preg_replace('/\/+$/', '/', $calendar_lib_path);
        $this->calendar_options = array('ifFormat' => '%Y/%m/%d',
                                        'daFormat' => '%Y/%m/%d');
    }

    function set_option($name, $value) {
        $this->calendar_options[$name] = $value;
    }

    function load_files() {
        return $this->get_load_files_code();
    }

    function get_load_files_code() {
        $code  = ( '<link rel="stylesheet" type="text/css" media="all" href="' .
                   $this->calendar_lib_path . $this->calendar_theme_file .
                   '" />' . NEWLINE );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_file .
                   '"></script>' . NEWLINE );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_lang_file .
                   '"></script>' . NEWLINE );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_setup_file .
                   '"></script>' );
        return $code;
    }

    // Changed for TUPA
    function _make_calendar($other_options = array()) {
        $js_options = htmlspecialchars($this->_make_js_hash(array_merge($this->calendar_options, $other_options)));
        $code  = ( 'Calendar.setup({' .
                   $js_options .
                   '});' );
        return $code;
    }

    // Changed for TUPA
    function make_input_field($cal_options = array(), $field_attributes = array()) {
        $id = $this->_gen_id();
        $attrstr = $this->_make_html_attr(array_merge($field_attributes,
                                                      array('id'   => $this->_field_id($id),
                                                            'type' => 'text')));
        $options = array_merge($cal_options,
                               array('inputField' => $this->_field_id($id),
                                     'button'     => $this->_trigger_id($id)));
        $onClickEvent = $this->_make_calendar($options);

        $output = '<input ' . $attrstr .' readonly />';
        $output .= '<a href="javascript:void(0);" id="'. $this->_trigger_id($id) . '">' .
            '<img align="middle" border="0" src="' . $this->calendar_lib_path . 'img.gif" alt="" onclick="'. $onClickEvent .'" /></a>
            <a href="javascript:void(0);" onclick="clearFieldValues(document.forms[0].name, \''. $this->_field_id($id) .'\')"><img align="middle" border="0" src="'. PATH_images .'icons/garbage.png"  width="11" height="12" /></a>';

        return $output;
    }

    /// PRIVATE SECTION

    function _field_id($id) { return 'f_calendar_field_' . $id; }
    function _trigger_id($id) { return 'f_calendar_trigger_' . $id; }
    function _gen_id() { static $id = 0; return ++$id; }

    // Changed for TUPA
    function _make_js_hash($array) {
        $jstr = '';
        reset($array);
        while (list($key, $val) = each($array)) {
            if (is_bool($val))
                $val = $val ? 'true' : 'false';
            elseif (is_array($val)) {
            	   $val = '['. implode(',', $val) .']';
            } else if (!is_numeric($val))
                $val = '"'.$val.'"';
            if ($jstr) $jstr .= ',';
            $jstr .= '"' . $key . '":' . $val;
        }
        return $jstr;
    }

    function _make_html_attr($array) {
        $attrstr = '';
        reset($array);
        while (list($key, $val) = each($array)) {
            $attrstr .= $key . '="' . $val . '" ';
        }
        return $attrstr;
    }
};

?>
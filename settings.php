<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * mod_embed module admin settings and defaults
 *
 * @package mod_embed
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {


    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('embedmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

	//The yes no option		
	$appcondition_options= array('none'=>get_string('appcondition_none', 'embed'),'grade'=>get_string('appcondition_grade', 'embed'),'custom'=>get_string('appcondition_custom', 'embed'));
	$settings->add(new admin_setting_configselect('embed/appcondition',
						new lang_string('appcondition', 'embed'),'', 'none', $appcondition_options));

	$timeoptions= array('none'=>get_string('time_none', 'embed'),'enabled'=>get_string('time_enabled', 'embed'));
    $settings->add(new admin_setting_configselect('embed/timecondition', 
						new lang_string('timecondition', 'embed'),'', 0, $timeoptions));
						
	$settings->add(new admin_setting_configtext('embed/mintime', 
			get_string('mintime', 'embed') , '', 0, PARAM_INT));
	//The yes no option		
	$options = array(0 => new lang_string('no'),
						   1 => new lang_string('yes'));
					
	$settings->add(new admin_setting_configselect('embed/showcompletion', 
						new lang_string('showcompletion', 'embed'),'', 1, $options));
						

}

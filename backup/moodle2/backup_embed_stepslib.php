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
 * @package   mod_embed
 * @category  backup
 * @copyright 2023 onwards Justin Hunt (@poodllguy) {@link http://poodll.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \mod_embed\constants;

/**
 * Define all the backup steps that will be used by the backup_embed_activity_task
 */

/**
 * Define the complete mod_embed structure for backup, with file and id annotations
 */
class backup_embed_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $embed = new backup_nested_element('embed', array('id'), array(
            'name', 'intro', 'introformat', 'content', 'contentformat',
            'embedtype','embeddata','grade','gradeoptions','maxattempts','mingrade',
            'timecondition','mintime','appcondition','showcompletion',
             'timemodified','timecreated'));
        $embedattempts = new backup_nested_element('attempts');
        $embedattempt =  new backup_nested_element('attempt', array('id'), array(
            'embed','time', 'userid', 'course', 'grade',
            'customdata1','customdata2','customdata3','customdata4','customdata5',
            'customint1','customint2','customint3','customint4','customint5',
            'timecreated','timemodified'
            ));

        // Build the tree
        $embed->add_child($embedattempts);
        $embedattempts->add_child($embedattempt);

        // Define sources
        $embed->set_source_table(constants::M_TABLE, array('id' => backup::VAR_ACTIVITYID));
        $embedattempt->set_source_table(constants::M_ATTEMPTSTABLE, array('embed' => backup::VAR_PARENTID));


        // Define id annotations
        $embedattempt->annotate_ids('user', 'userid');
		
        // Define file annotations
        $embed->annotate_files(constants::M_COMPONENT, 'intro', null); // This file areas haven't itemid
        $embed->annotate_files(constants::M_COMPONENT, 'content', null); // This file areas haven't itemid

        // Return the root element (embed), wrapped into standard activity structure
        return $this->prepare_activity_structure($embed);
    }
}

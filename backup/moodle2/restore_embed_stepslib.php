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
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use \mod_embed\constants;

/**
 * Define all the restore steps that will be used by the restore_embed_activity_task
 */

/**
 * Structure step to restore one mod_embed activity
 */
class restore_embed_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
		
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');
        
        $paths[] = new restore_path_element(constants::M_TABLE, '/activity/embed');
        if ($userinfo) {
            $paths[] = new restore_path_element(constants::M_ATTEMPTSTABLE, '/activity/embed/embedattempts/embedattempt');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
 
        
    }

    protected function process_embed($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the mod_embed record
        $newitemid = $DB->insert_record(constants::M_TABLE, $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }
    
     protected function process_embed_attempt($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->embed= $this->get_new_parentid('embed');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record(constants::M_ATTEMPTSTABLE, $data);

    }

    protected function after_execute() {
        // Add mod_embed related files, no need to match by itemname (just internally handled context)
        $this->add_related_files(constants::M_COMPONENT, 'intro', null);
        $this->add_related_files(constants::M_COMPONENT, 'content', null);
    }
}

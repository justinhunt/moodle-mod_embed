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
* Sets up the tabs at the top of the module view page　for teachers.
*
* This file was adapted from the mod/lesson/tabs.php
*
 * @package mod_embed
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
*/

defined('MOODLE_INTERNAL') || die();

use \mod_embed\constants;

/// This file to be included so we can assume config.php has already been included.
global $DB,$CFG;
if (empty($moduleinstance)) {
    print_error('cannotcallscript');
}
if (!isset($currenttab)) {
    $currenttab = '';
}
if (!isset($cm)) {
    $cm = get_coursemodule_from_instance(constants::M_MODNAME, $moduleinstance->id);
    $context = context_module::instance($cm->id);
}
if (!isset($course)) {
    $course = $DB->get_record('course', array('id' => $moduleinstance->course));
}
$attempt = optional_param('attempt', 0, PARAM_INT);

$tabs = $row = $inactive = $activated = array();
$config = get_config(constants::M_COMPONENT);

$row[] = new tabobject('attempts', "$CFG->wwwroot/mod/embed/view.php?id=$cm->id", get_string('attempts', constants::M_COMPONENT), get_string('manageattempts', constants::M_COMPONENT));
if(has_capability('mod/embed:manageapps',$context) ) {
    $row[] = new tabobject('apps', "$CFG->wwwroot/mod/embed/apps/apps.php?id=$cm->id",
            get_string('apps', constants::M_COMPONENT), get_string('manageapps', constants::M_COMPONENT));
}

if(has_capability('mod/embed:grades',$context)) {
    $row[] = new tabobject('grades', "$CFG->wwwroot/mod/embed/grades.php?id=$cm->id",
        get_string('grades', constants::M_COMPONENT),
        get_string('managegrades', constants::M_COMPONENT));
}

if(has_capability('mod/embed:viewreports',$context)) {
    $row[] = new tabobject('reports', "$CFG->wwwroot/mod/embed/reports.php?id=$cm->id",
            get_string('reports', constants::M_COMPONENT), get_string('viewreports', constants::M_COMPONENT));
}

$tabs[] = $row;

print_tabs($tabs, $currenttab, $inactive, $activated);

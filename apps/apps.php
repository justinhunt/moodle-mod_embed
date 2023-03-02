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
 * Provides the interface for overall managing of apps
 *
 * @package mod_embed
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/embed/lib.php');

use mod_embed\constants;

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id(constants::M_MODNAME, $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$embed = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);

$apphelper = new \mod_embed\apphelper($cm);
$apps = $apphelper->fetch_apps();
$selectedapps = $apphelper->fetch_selected_apps();

//mode is necessary for tabs
$mode='apps';
//Set page url before require login, so post login will return here
$PAGE->set_url('/mod/embed/app/apps.php', array('id'=>$cm->id,'mode'=>$mode));

//require login for this page
require_login($course, false, $cm);
$context = context_module::instance($cm->id);

$renderer = $PAGE->get_renderer(constants::M_COMPONENT);
$app_renderer = $PAGE->get_renderer(constants::M_COMPONENT,'app');

//prepare datatable(before header printed)
$apptableid = '' . constants::M_APP_TABLE . '_' . '_opts_9999';
$app_renderer->setup_datatables($apptableid);

$PAGE->navbar->add(get_string('apps', constants::M_COMPONENT));
echo $renderer->header($embed, $cm, $mode, null, get_string('apps', constants::M_COMPONENT));


// We need view permission to be here
require_capability('mod/embed:selectapps', $context);
if (has_capability('mod/embed:manageapps', $context)){
    echo $app_renderer->add_edit_page_links($embed);
}


//if we have apps, show em
if($apps){
	echo $app_renderer->show_apps_list($apps,$apptableid,$cm, $selectedapps);
}
echo $renderer->footer();

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
 * Action for adding/editing a app.
 *
 * @package mod_embed
 * @copyright  2019 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

use \mod_embed\constants;
use \mod_embed\utils;

require_once("../../../config.php");
require_once($CFG->dirroot.'/mod/embed/lib.php');


global $USER,$DB;

// first get the nfo passed in to set up the page
$moduleid= required_param('moduleid',PARAM_INT);
$id     = optional_param('id',0, PARAM_INT);         // App ID
$action = optional_param('action','edit',PARAM_TEXT);

// get the objects we need
$cm = get_coursemodule_from_instance(constants::M_MODNAME, $moduleid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record(constants::M_MODNAME, array('id' => $moduleid), '*', MUST_EXIST);

//make sure we are logged in and can see this form
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/embed:manageapps', $context);

//set up the page object
$PAGE->set_url('/mod/embed/apps/manageapps.php', array('moduleid'=>$moduleid, 'id'=>$id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');

//are we in new or edit mode?
if ($id) {
    $app = $DB->get_record(constants::M_APP_TABLE, array('id'=>$id), '*', MUST_EXIST);
    if(!$app){
        print_error('could not find app of id:' . $id);
    }
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the mod_embed apps page
$redirecturl = new moodle_url('/mod/embed/apps/apps.php', array('id'=>$cm->id));

//handle delete actions
if($action == 'confirmdelete'){
    $renderer = $PAGE->get_renderer(constants::M_COMPONENT);
    $app_renderer = $PAGE->get_renderer(constants::M_COMPONENT,'app');
    echo $renderer->header($moduleinstance, $cm, 'apps', null, get_string('confirmappdeletetitle', constants::M_COMPONENT));
    echo $app_renderer->confirm(get_string("confirmappdelete",constants::M_COMPONENT,$app->name),
            new moodle_url('/mod/embed/apps/manageapps.php', array('action'=>'delete','moduleid'=>$moduleid,'id'=>$id)),
            $redirecturl);
    echo $renderer->footer();
    return;

    /////// Delete app NOW////////
}elseif ($action == 'delete'){
    require_sesskey();
    $success = \mod_embed\app\helper::delete_app($moduleinstance,$id,$context);
    redirect($redirecturl);
}

$siteconfig = get_config(constants::M_COMPONENT);

//get the mform for our app
$mform = new \mod_embed\app\appform(null, array('moduleinstance'=>$moduleinstance));

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the quiz edit page
if ($data = $mform->get_data()) {
    require_sesskey();

    $theapp = $data;

    //$theapp->moduleid = $moduleinstance->id;
    //$theapp->applevel = $data->applevel;
    //$theapp->targetwords=  $data->targetwords;
    //$theapp->fonticon=  $data->fonticon;
    $theapp->timemodified=time();

    //first insert a new app if we need to
    //that will give us a appid, we need that for saving files
    if(!$edit){
        $theapp->id = null;
        $theapp->timecreated=time();

        //try to insert it
        if (!$theapp->id = $DB->insert_record(constants::M_APP_TABLE,$theapp)){
            print_error("Could not insert mod_embed app!");
            redirect($redirecturl);
        }
    }else{
        //now update the db once we have saved files and stuff
        if (!$DB->update_record(constants::M_APP_TABLE,$theapp)){
            print_error("Could not update mod_embed app!");
            redirect($redirecturl);
        }
    }


    //if we got here we did achieve some update
    redirect($redirecturl);

}


//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the app into a data object
if ($edit) {
    $data = $app;
    $data->courseid=$course->id;
    $data->moduleid = $moduleid;


}else{
    $data=new stdClass;
    $data->id = null;
    $data->courseid=$course->id;
    $data->moduleid = $moduleid;
}


//Set up the app type specific parts of the form data
$apprenderer = $PAGE->get_renderer('mod_embed','app');
$mform->set_data($data);
$PAGE->navbar->add(get_string('edit'), new moodle_url('/mod/embed/apps/apps.php', array('id'=>$moduleid)));
$PAGE->navbar->add(get_string('editingapp', constants::M_COMPONENT));
$renderer = $PAGE->get_renderer('mod_embed');
$mode='apps';
echo $renderer->header($moduleinstance, $cm,$mode, null, get_string('edit', constants::M_COMPONENT));
$mform->display();
echo $renderer->footer();
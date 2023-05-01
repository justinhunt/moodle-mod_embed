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
 * mod_embed module version information
 *
 * @package mod_embed
 * @copyright  2014 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_embed\utils;

require('../../config.php');
require_once($CFG->dirroot.'/mod/embed/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course Module ID
$p       = optional_param('p', 0, PARAM_INT);  // mod_embed instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$embed = $DB->get_record('embed', array('id'=>$p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('embed', $embed->id, $embed->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('embed', $id)) {
        print_error('invalidcoursemodule');
    }
    $embed = $DB->get_record('embed', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/embed:view', $context);

//this WAS important cos we used this to figure out how long student was on page
if($CFG->version<2014051200){
	add_to_log($course->id, 'embed', 'view', "view.php?id={$cm->id}", $embed->name, $cm->id);
}else{
	// Trigger module viewed event.
	$event = \mod_embed\event\course_module_viewed::create(array(
	   'objectid' => $embed->id,
	   'context' => $context
	));
	$event->add_record_snapshot('course_modules', $cm);
	$event->add_record_snapshot('course', $course);
	$event->add_record_snapshot('embed', $embed);
	$event->trigger();
}

// Update 'viewed' state if required by completion system
require_once($CFG->libdir . '/completionlib.php');

//comment these two lines .... possibly
$completion = new completion_info($course);
$completion->set_module_viewed($cm);


$PAGE->set_url('/mod/embed/view.php', array('id' => $cm->id));


$PAGE->set_title($course->shortname.': '.$embed->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($embed);


$renderer = $PAGE->get_renderer('mod_embed');
//mode is necessary for tabs
$mode='attempts';

echo $renderer->header($embed, $cm, $mode, null, get_string('attempts', 'mod_embed'));


if (trim(strip_tags($embed->intro))) {
    echo $renderer->box_start('mod_introbox', 'embedintro');
    echo format_module_intro('embed', $embed, $cm->id);
    echo $renderer->box_end();
}


//init our mod_embed timing helper
$tph = new embed_helper($embed,$course,$cm);
//create a completion log, and initialise our timer and js conditions, but only if we are not already complete
$iscomplete = utils::embed_is_complete($course,$cm,$USER->id,COMPLETION_AND);


	$tph->embed_initialise_timer($PAGE, $embed,$iscomplete);
	$tph->embed_initialise_jscomplete($PAGE, $embed,$iscomplete);

$itemid=0;
$content = file_rewrite_pluginfile_urls($embed->content, 'pluginfile.php', $context->id, 'mod_embed', 'content',$itemid);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $embed->contentformat, $formatoptions);
echo $renderer->box($content, "generalbox center clearfix");

$apptype='youtube';
$apptype='wordwall';
$apptype='poodll';

switch($apptype){
    case 'youtube':
        $tdata = ['contextid'=>$context->id,'ytv_id'=>'lXgkuM2NhYI','ytv_start'=>0,'ytv_end'=>0];
        echo $renderer->render_from_template('mod_embed/app_youtube', $tdata);
        break;
    case 'wordwall':
        ////var wwurl="https://wordwall.net/resource/12672847/game-past-simple-tense";
        $tdata = ['contextid'=>$context->id,'wordwallurl'=>'https://wordwall.net/resource/12672847/game-past-simple-tense'];
        echo $renderer->render_from_template('mod_embed/app_wordwall', $tdata);
        break;
    case 'poodll':
        //https://russell.poodll.com/mod/readaloud/view.php?id=120&embed=1
        $tdata = ['contextid'=>$context->id,'poodllurl'=>'https://russell.poodll.com/mod/readaloud/view.php?id=120&embed=1', 'allowurl'=>'https://russell.poodll.com'];
        echo $renderer->render_from_template('mod_embed/app_poodll', $tdata);
        break;
    default:
        //iFrame Embed Code
        echo $renderer->do_iframe_embed($embed);

}



//output completed tag
$completed = $tph->embed_fetch_completed_tag();
echo $completed;
$timer=$tph->embed_fetch_countdown_timer();
echo $timer;


$strlastmodified = get_string("lastmodified");
echo "<div class=\"modified\">$strlastmodified: ".userdate($embed->timemodified)."</div>";
echo $renderer->footer();

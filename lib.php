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
 * @package mod_embed
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use \mod_embed\constants;

/**
 * List of features supported in mod_embed module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function embed_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_HAS_RULES: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

function embed_get_completion_state($course,$cm,$userid,$type) {
	$updatetime = false;
	return embed_is_complete($course,$cm,$userid,$type,$updatetime);
}

/**
 * Returns all other caps used in module
 * @return array
 */
function embed_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the chat.
 *
 * @param object $mform form passed by reference
 */
function embed_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'embedheader', get_string('modulenameplural', 'embed'));
    $mform->addElement('advcheckbox', 'reset_embed', get_string('removeembeddata','embed'));
}

/**
 * Course reset form defaults.
 *
 * @param object $course
 * @return array
 */
function embed_reset_course_form_defaults($course) {
    return array('reset_embed'=>1);
}


/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function embed_reset_userdata($data) {
global $CFG, $DB;
	$componentstr = get_string('modulenameplural', 'embed');
    $status = array();
    if (!empty($data->reset_embed)) {
		$DB->delete_records(constants::M_ATTEMPTSTABLE,array('course'=>$data->courseid));
		$status[] = array('component'=>$componentstr, 'item'=>get_string('removeembeddata', 'embed'), 'error'=>false);
    }
    return $status;
}




/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function embed_get_view_actions() {
    return array('view','view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function embed_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add mod_embed instance.
 * @param stdClass $data
 * @param mod_embed_mod_form $mform
 * @return int new mod_embed instance id
 */
function embed_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    
    if ($mform) {
        $data->content       = $data->embed['text'];
        $data->contentformat = $data->embed['format'];
    }

    $data->id = $DB->insert_record('embed', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    $context = context_module::instance($cmid);

    if ($mform and !empty($data->embed['itemid'])) {
        $draftitemid = $data->embed['itemid'];
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_embed', 'content', 0, embed_get_editor_options($context), $data->content);
        $DB->update_record('embed', $data);
    }

    return $data->id;
}

/**
 * Update mod_embed instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function embed_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $cmid        = $data->coursemodule;
    $draftitemid = $data->embed['itemid'];

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->content       = $data->embed['text'];
    $data->contentformat = $data->embed['format'];

    $DB->update_record('embed', $data);

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_embed', 'content', 0, embed_get_editor_options($context), $data->content);
        $DB->update_record('embed', $data);
    }

    return true;
}

/**
 * Delete mod_embed instance.
 * @param int $id
 * @return bool true
 */
function embed_delete_instance($id) {
    global $DB;

    if (!$embed = $DB->get_record('embed', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('embed', array('id'=>$embed->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info Info to customise main mod_embed display
 */
function embed_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if (!$embed = $DB->get_record('embed', array('id'=>$coursemodule->instance),
            'id, name, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $embed->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('embed', $embed, $coursemodule->id, false);
    }

    return $info;
}


/**
 * Lists all browsable file areas
 *
 * @package  mod_embed
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array
 */
function embed_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('content', 'embed');
    return $areas;
}

/**
 * File browsing support for mod_embed module content area.
 *
 * @package  mod_embed
 * @category files
 * @param stdClass $browser file browser instance
 * @param stdClass $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function embed_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_embed', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_embed', 'content', 0);
            } else {
                // not found
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/embed/locallib.php");
        return new embed_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
    }

    // note: embed_intro handled in file_browser automatically

    return null;
}

/**
 * Serves the mod_embed files.
 *
 * @package  mod_embed
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function embed_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/embed:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
        // intro is handled automatically in pluginfile.php
        return false;
    }

    // $arg could be revision number or index.html
    $arg = array_shift($args);
    if ($arg == 'index.html' || $arg == 'index.htm') {
        // serve mod_embed content
        $filename = $arg;

        if (!$embed = $DB->get_record('embed', array('id'=>$cm->instance), '*', MUST_EXIST)) {
            return false;
        }

        // remove @@PLUGINFILE@@/
        $content = str_replace('@@PLUGINFILE@@/', '', $embed->content);

        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->overflowdiv = true;
        $formatoptions->context = $context;
        $content = format_text($content, $embed->contentformat, $formatoptions);

        send_file($content, $filename, 0, 0, true, true);
    } else {
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_embed/$filearea/0/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            $embed = $DB->get_record('embed', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($embed->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_embed', 'content', 0)) {
                return false;
            }
            //file migrate - update flag
            $embed->legacyfileslast = time();
            $DB->update_record('embed', $embed);
        }

        // finally send the file
        send_stored_file($file, null, 0, $forcedownload, $options);
    }
}

/**
 * Return a list of mod_embed types
 * @param string $embedtype current mod_embed type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function embed_embed_type_list($embedtype, $parentcontext, $currentcontext) {
    $module_embedtype = array('mod-embed-*'=>get_string('embed-mod-embed-x', 'embed'));
    return $module_embedtype;
}

/**
 * Export mod_embed resource contents
 *
 * @return array of file content
 */
function embed_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    $contents = array();
    $context = context_module::instance($cm->id);

    $embed = $DB->get_record('embed', array('id'=>$cm->instance), '*', MUST_EXIST);

    // mod_embed contents
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_embed', 'content', 0, 'sortorder DESC, id ASC', false);
    foreach ($files as $fileinfo) {
        $file = array();
        $file['type']         = 'file';
        $file['filename']     = $fileinfo->get_filename();
        $file['filepath']     = $fileinfo->get_filepath();
        $file['filesize']     = $fileinfo->get_filesize();
        $file['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_embed/content/'.$fileinfo->get_filepath().$fileinfo->get_filename(), true);
        $file['timecreated']  = $fileinfo->get_timecreated();
        $file['timemodified'] = $fileinfo->get_timemodified();
        $file['sortorder']    = $fileinfo->get_sortorder();
        $file['userid']       = $fileinfo->get_userid();
        $file['author']       = $fileinfo->get_author();
        $file['license']      = $fileinfo->get_license();
        $contents[] = $file;
    }

    // mod_embed html conent
    $filename = 'index.html';
    $embedfile = array();
    $embedfile['type']         = 'file';
    $embedfile['filename']     = $filename;
    $embedfile['filepath']     = '/';
    $embedfile['filesize']     = 0;
    $embedfile['fileurl']      = file_encode_url("$CFG->wwwroot/" . $baseurl, '/'.$context->id.'/mod_embed/content/' . $filename, true);
    $embedfile['timecreated']  = null;
    $embedfile['timemodified'] = $embed->timemodified;
    // make this file as main file
    $embedfile['sortorder']    = 1;
    $embedfile['userid']       = null;
    $embedfile['author']       = null;
    $embedfile['license']      = null;
    $contents[] = $embedfile;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function embed_dndupload_register() {
    return array('types' => array(
                     array('identifier' => 'text/html', 'message' => get_string('createembed', 'embed')),
                     array('identifier' => 'text', 'message' => get_string('createembed', 'embed'))
                 ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function embed_dndupload_handle($uploadinfo) {
    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    if ($uploadinfo->type == 'text/html') {
        $data->contentformat = FORMAT_HTML;
        $data->content = clean_param($uploadinfo->content, PARAM_CLEANHTML);
    } else {
        $data->contentformat = FORMAT_PLAIN;
        $data->content = clean_param($uploadinfo->content, PARAM_TEXT);
    }
    $data->coursemodule = $uploadinfo->coursemodule;

    // Set the display options to the site defaults.
    $config = get_config('embed');
    
    //conditional settings
    $data->timecondition = $config->timecondition;
    $data->mintime= $config->mintime;
    $data->showcompletion= $config->showcompletion;
    $data->appcondition = $config->appcondition;


    return embed_add_instance($data, null);
}

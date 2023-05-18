<?php
/**
 * External.
 *
 * @package mod_embed
 * @author  Justin Hunt - Poodll.com
 */


namespace mod_embed;


use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use mod_embed\utils;

/**
 * External class.
 *
 * @package mod_embed
 * @author  Justin Hunt - Poodll.com
 */
class external extends external_api {

    /**
     * Describes the parameters for do_complete_form webservice.
     * @return external_function_parameters
     */
    public static function do_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the activity'),
                'itemdata' => new external_value(PARAM_RAW, 'The itemdata'),
            )
        );
    }

    /**
     * Submit the completion request
     *
     * @param int $contextid The context id for the course.
     * @return int new grade id.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     */
    public static function do($contextid, $itemdata) {
        global $CFG, $DB, $USER;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::do_parameters(),
            ['contextid' => $contextid, 'itemdata'=>$itemdata]);

        $context = \context_module::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);
        require_capability('mod/embed:submit', $context);

        $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);

        //do a completion check
        $updatetime = time();
        $iscomplete = utils::embed_is_complete($course,$cm,$USER->id,COMPLETION_AND, $updatetime);


        //check completion reqs against satisfied conditions
        if($iscomplete==true){
            $completion = new \completion_info($course);
            //$completion->set_module_viewed($cm);
            $completion->update_state($cm,COMPLETION_COMPLETE);
            $return =array('success'=>true);
            echo json_encode($return);
        }else{
            $return =array('success'=>false);
            echo json_encode($return);
        }


        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);
        $modulecontext = context_module::instance($cmid);
        $cm = get_coursemodule_from_id(constants::M_MODNAME, $cmid, 0, false, MUST_EXIST);
        $attempthelper = new \mod_embed\attempthelper($cm);
        $attempt= $attempthelper->fetch_latest_complete_attempt($studentid);

        if (!$attempt) { return 0; }

        $moduleinstance = $DB->get_record(constants::M_TABLE, array('id'=>$attempt->embed));
        $gradingdisabled=false;
        $gradinginstance = utils::get_grading_instance($attempt->attemptid, $gradingdisabled,$moduleinstance, $modulecontext);

        $mform = new \rubric_grade_form(null, array('gradinginstance' => $gradinginstance), 'post', '', null, true, $data);

        $validateddata = $mform->get_data();

        if ($validateddata) {
            // Insert rubric
            if (!empty($validateddata->advancedgrading['criteria'])) {
                $thegrade=null;
                if (!$gradingdisabled) {
                    if ($gradinginstance) {
                        $thegrade = $gradinginstance->submit_and_get_grade($validateddata->advancedgrading,
                            $attempt->id);
                    }
                }
            }
            $feedbackobject = new \stdClass();
            $feedbackobject->id = $attempt->id;
            $feedbackobject->feedback = $validateddata->feedback;
            $feedbackobject->manualgraded = 1;
            $feedbackobject->grade = $thegrade;
            $DB->update_record(constants::M_ATTEMPTSTABLE, $feedbackobject);
            $grade = new \stdClass();
            $grade->userid = $studentid;
            $grade->rawgrade = $thegrade;
            \embed_grade_item_update($moduleinstance,$grade);
        } else {
            // Generate a warning.
            throw new \moodle_exception('erroreditgroup', 'group');
        }

        return 1;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     * @since Moodle 3.0
     */
    public static function do_returns() {
        return new external_value(PARAM_INT, 'grade id');
    }


}

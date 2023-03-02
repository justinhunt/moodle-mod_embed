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
 * mod_embed configuration form
 *
 * @package mod_embed
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


use \mod_embed\constants;
use \mod_embed\utils;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/embed/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_embed_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('embed');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        
        // Adding the standard "intro" and "introformat" fields
        $this->standard_intro_elements();

        // Adding the content section
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'embed'));
        $mform->addElement('editor', 'embed', get_string('content', 'embed'), null, embed_get_editor_options($this->context));
        $mform->addRule('embed', get_string('required'), 'required', null, 'client');

        //embed
        $embed_options = \mod_embed\utils::get_embed_options();
        $mform->addElement('select', 'embedtype', get_string('embedtype', constants::M_COMPONENT),$embed_options);
        $mform->setDefault('embedtype','free');

        //details  - stub for doing greater things TODO do greater things 20230112
       // $this->add_details_fields($mform);

        //iframe ( embeddata ) code thing
        $mform->addElement('textarea','embeddata',get_string('iframecode', constants::M_COMPONENT),'wrap="virtual" rows="5" cols="50"');
        $mform->setType('embeddata',PARAM_RAW);
        $mform->addElement('static','iframecode_explanation','',
            get_string('iframecode_explanation',constants::M_COMPONENT));



        /// Grade.
        $this->standard_grading_coursemodule_elements();

        //grade options for how to grade with multiple attempts.
        $gradeoptions = \mod_embed\utils::get_grade_options();
        $mform->addElement('select', 'gradeoptions', get_string('gradeoptions', constants::M_COMPONENT), $gradeoptions);
        $mform->setDefault('gradeoptions', constants::M_GRADECOMPLETION);
        $mform->addHelpButton('gradeoptions', 'gradeoptions', constants::M_COMPONENT);
        $mform->addElement('static', 'gradeoptions_details', '',
            get_string('gradeoptions_details', constants::M_COMPONENT));

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('embed');
            $default_values['embed']['format'] = $default_values['contentformat'];
            $default_values['embed']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_embed', 'content', 0, embed_get_editor_options($this->context), $default_values['content']);
            $default_values['embed']['itemid'] = $draftitemid;
        }

    }

    function add_details_fields(&$mform) {
        global $PAGE;
        //where the details are stored and saved in DB from
        $mform->addElement('hidden','embedabledetails');
        $mform->setType('embedabledetails',PARAM_TEXT);

        $mform->addElement('static','embedabledetails','','<div id="mod_embed_embedable_details"></div>');

        //what we show the user
        /*
        $mform->addElement('textarea','mywords',get_string('mywords', constants::M_COMPONENT),'wrap="virtual" rows="5" cols="50"');
        $mform->setType('mywords',PARAM_TEXT);
        $mform->addElement('static','targetwordsexplanation','',
            get_string('targetwordsexplanation',constants::M_COMPONENT));
*/

        $opts =Array();
        $opts['embedables']=[];
        $opts['triggercontrol']='topicid';
        $opts['updatecontrol']='embedabledetails';
        //convert opts to json
        $jsonstring = json_encode($opts);

        $controlid = 'mod_embed_opts_embedselector';
        $mform->addElement('hidden','selectoropts',$jsonstring,
            array('id' => 'amdopts_' . $controlid, 'type' => 'hidden'));
        $mform->setType('selectoropts',PARAM_RAW);


        $basicopts=array('controlid'=>$controlid);
        $PAGE->requires->js_call_amd("mod_embed/embedselector", 'init', array($basicopts));
    }


    function add_completion_rules() {
    $mform =& $this->_form;
    
    $config = get_config('embed');
    
     //timer options
        //Add a place to set a mimumum time after which the activity is recorded complete
       $mform->addElement('static', 'mintimedetails', '',get_string('mintimedetails', 'embed'));
        $options= array('none'=>get_string('time_none', 'embed'),'enabled'=>get_string('time_enabled', 'embed'));
        $mform->addElement('select', 'timecondition', get_string('timecondition', 'embed'), $options);
	   $mform->addElement('duration', 'mintime', get_string('mintime', 'embed'));    
	 	   $mform->setDefault('timecondition',$config->timecondition);
       $mform->setDefault('mintime',$config->mintime);

       
       //app condition options
       $options= array('none'=>get_string('appcondition_none', 'embed'),'grade'=>get_string('appcondition_grade', 'embed'),
           'custom'=>get_string('appcondition_custom', 'embed'));
       $mform->addElement('select', 'appcondition', get_string('appcondition', 'embed'), $options);

        


      //show completion tag
       $mform->addElement('static', 'showcompletiondetails', '',get_string('showcompletiondetails', 'embed')); 
	   $mform->addElement('selectyesno', 'showcompletion', get_string('showcompletion', 'embed'));  
       $mform->setDefault('showcompletion',$config->showcompletion);
       $mform->addElement('static', 'showcompletiondivider', '','<hr />');


		return array('timecondition','mintime','mintimedetails','appcondition',
		'showcompletion','showcompletiondetails');
	}
	
	function completion_rule_enabled($data) {
		return (($data['timecondition']!='none' && $data['mintime']>0) || ($data['appcondition']!='none'));
	}
	
}


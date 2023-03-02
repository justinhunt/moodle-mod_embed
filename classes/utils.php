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
 * Utils for mod_embed plugin
 *
 * @package    mod_embed
 * @copyright  2022 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace mod_embed;
defined('MOODLE_INTERNAL') || die();

use \mod_embed\constants;


/**
 * Functions used generally across this mod
 *
 * @package    mod_embed
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils{

    //const CLOUDPOODLL = 'http://localhost/moodle';
    //const CLOUDPOODLL = 'https://vbox.poodll.com/cphost';
    const CLOUDPOODLL = 'https://cloud.poodll.com';



    //What to show students after an attempt
    public static function get_embed_options(){
        return array(
            constants::EMBED_NONE => get_string("embed_none",constants::M_COMPONENT),
            constants::EMBED_FLIP  => get_string("embed_flip",constants::M_COMPONENT),
            constants::EMBED_BUNCEE  => get_string("embed_buncee",constants::M_COMPONENT),
            constants::EMBED_VOICEBOOK => get_string("embed_voicebook",constants::M_COMPONENT),
            constants::EMBED_ZENGENGO => get_string("embed_zengengo",constants::M_COMPONENT),
            constants::EMBED_ENGLISHCENTRAL => get_string("embed_englishcentral",constants::M_COMPONENT),
            constants::EMBED_YOUTUBE => get_string("embed_youtube",constants::M_COMPONENT),
        );
    }

    //What multi-attempt grading approach
    public static function get_grade_options() {
        return array(
            constants::M_GRADENONE => get_string("gradenone", constants::M_COMPONENT),
            constants::M_GRADECOMPLETION => get_string("gradecompletion", constants::M_COMPONENT),
            constants::M_GRADETOOL => get_string("gradetool", constants::M_COMPONENT)
        );
    }


    public static function get_timelimit_options(){
        return array(
            0 => get_string("notimelimit",constants::M_COMPONENT),
            30 => get_string("xsecs",constants::M_COMPONENT,'30'),
            45 => get_string("xsecs",constants::M_COMPONENT,'45'),
            60 => get_string("onemin",constants::M_COMPONENT),
            90 => get_string("oneminxsecs",constants::M_COMPONENT,'30'),
            120 => get_string("xmins",constants::M_COMPONENT,'2'),
            150 => get_string("xminsecs",constants::M_COMPONENT,array('minutes'=>2,'seconds'=>30)),
            180 => get_string("xmins",constants::M_COMPONENT,'3')
        );
    }


//this is called from ajaxcomplete and embed_get_completion_state
    public static function embed_is_complete($course,$cm,$userid,$type,$updatetime=false) {
        global $CFG,$DB;

        // Get mod_embed object
        if(!($embed=$DB->get_record('embed',array('id'=>$cm->instance)))) {
            throw new \Exception("Can't find Poodll Embed {$cm->instance}");
        }

        //if condition completion is not enabled , just get out of here
        if($embed->timecondition=='none' && $embed->appcondition=='none'){
            return $type;
        }


        $records = $DB->get_records(constants::M_ATTEMPTSTABLE,
            array('userid'=>$userid, 'course'=>$course->id,'embed'=>$embed->id),
            'time DESC','*',0,1);

        if(!$records) {return false;}
        $record = array_shift($records);
        $starttime = $record->time;


        //check for mintime completion
        if($embed->timecondition=='none'){
            $mintime_completed=true;
        }else{
            $mintime_completed=false;
            //we remove a second as a margin of error, occasionally get weirdness
            if($starttime && ($updatetime- $starttime) > ($embed->mintime - 1000)){
                $mintime_completed=true;
            }
        }



        //check for app completion
        $app_completed = false;
        switch($embed->appcondition){

            case 'grade':
                $app_completed = false;
                break;

            case 'custom':
                $app_completed = false;
                break;

            case 'none':
            default:
                $app_completed = true;
                break;

        }



        //check completion reqs against satisfied conditions
        switch ($type){
            case COMPLETION_AND:
                $success = ($mintime_completed && ($embed->appcondition!='none' || $app_completed)) ;
                break;
            case COMPLETION_OR:
                $success = ($embed->appcondition!='none' && $app_completed) ||
                    ($embed->timecondition!='none' && $mintime_completed);
        }
        //return our success flag
        return $success;
    }


    /* a php function to extract the src attribute from an html string containing an iframe */
    public static function get_src_from_iframe($iframe) {
        preg_match('/src="(.+?)"/', $iframe, $matches);
        if($matches && count($matches) > 0) {
            return $matches[1];
        }else{
            return '';
        }
    }


}

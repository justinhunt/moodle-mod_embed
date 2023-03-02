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
 * Private mod_embed module utility functions
 *
 * @package mod_embed
 * @copyright  2014 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/embed/lib.php");


/**
 * File browsing support class
 */
class embed_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}

function embed_get_editor_options($context) {
	global $CFG;
	return array('subdirs'=>1, 'maxbytes'=>$CFG->maxbytes, 'maxfiles'=>-1, 'changeformat'=>1, 'context'=>$context, 'noclean'=>1, 'trusttext'=>0);
}
	
/**
 * File browsing support class
 */
class embed_helper {

    /**
     * Constructor of quizlet plugin
     *
     * @param array $options
     */
    public function __construct($embed,$course,$cm) {

        $this->course = $course;
        $this->cm = $cm;
        $this->options = []; //defunct
    }

	 /**
	 * Output the JavaScript required to initialise the countdown timer.
	 * @param int $timerstartvalue time remaining, in seconds.
	 */
	public function embed_initialise_timer($page, $embed, $iscomplete) {
		if($embed->mintime==0){
			$config = get_config('embed');		
			$embed->mintime = $config->mintime;
		}
		$params = array( $embed->mintime,$this->cm->id,$embed->showcompletion>0,$iscomplete);
		$page->requires->js_init_call('M.mod_embed.timer.init', $params, false);
	}
	
	 /**
	 * Output the JavaScript required to initialise the countdown timer.
	 * @param int $timerstartvalue time remaining, in seconds.
	 */
	public function embed_initialise_jscomplete($page, $embed,  $iscomplete) {
		if($embed->mintime==0){
			$config = get_config('embed');		
			$embed->mintime= $config->mintime;
		}
		$params = array($this->cm->id,$embed->showcompletion>0,$iscomplete);
		$page->requires->js_init_call('M.mod_embed.jscomplete.init', $params, false);
	}

   /**
	 * Return the HTML of the quiz timer.
	 * @return string HTML content.
	 */
	public function embed_fetch_completed_tag() {
		return html_writer::tag('div',  get_string('completed', 'embed'),array('id' => 'embed-completed'));

	}


		   /**
	 * Return the HTML of the quiz timer.
	 * @return string HTML content.
	 */
	public function embed_fetch_countdown_timer() {
		return html_writer::tag('div', get_string('timeleft', 'embed') . ' ' .
			html_writer::tag('span', '', array('id' => 'embed-time-left')),
			array('id' => 'embed-timer', 'role' => 'timer',
				'aria-atomic' => 'true', 'aria-relevant' => 'text'));
	}

}
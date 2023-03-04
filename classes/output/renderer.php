<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace mod_embed\output;

use \mod_embed\constants;
use \mod_embed\utils;

class renderer extends \plugin_renderer_base {

    /**
     * Returns the header for the module
     *
     * @param mod $instance
     * @param string $currenttab current tab that is shown.
     * @param int    $item id of the anything that needs to be displayed.
     * @param string $extrapagetitle String to append to the page title.
     * @return string
     */
    public function header($moduleinstance, $cm, $currenttab = '', $itemid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($moduleinstance->name, true, $moduleinstance->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

        // Build the buttons
        $context = \context_module::instance($cm->id);

        /// Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        $output = $this->output->header();

        if (has_capability('mod/embed:manageapps', $context) || has_capability('mod/embed:viewreports', $context)) {


            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/mod/embed/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output .= $this->output->heading($activityname);
        }


        return $output;
    }

    /**
     * Return HTML to display limited header
     */
    public function notabsheader(){
        return $this->output->header();
    }

    public function do_iframe_embed($embed){
        $the_url = utils::get_src_from_iframe($embed->embeddata);
        if(!empty($the_url)) {
            $tdata = [];
            $tdata['iframe_src_url'] = $the_url;
            //finally render template and return
            return $this->render_from_template('mod_embed/embed_free', $tdata);
        }else{
            return '';
        }
    }

}

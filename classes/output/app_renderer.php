<?php

namespace mod_embed\output;

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


defined('MOODLE_INTERNAL') || die();

use \mod_embed\constants;
use \mod_embed\utils;

/**
 * A custom renderer class that extends the plugin_renderer_base.
 *
 * @package mod_embed
 * @copyright COPYRIGHTNOTICE
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class app_renderer extends \plugin_renderer_base {

 /**
 * Return HTML to display add first page links
 * @param lesson $lesson
 * @return string
 */
 public function add_edit_page_links($embed) {
		global $CFG;
        $appid = 0;

        $output = $this->output->heading(get_string("manageapps", "embed"), 3);
        $output .=  \html_writer::div(get_string('manageappinstructions',constants::M_COMPONENT ),constants::M_COMPONENT .'_instructions');
        $links = array();

		$addurl = new \moodle_url(constants::M_URL . '/apps/manageapps.php',
			array('moduleid'=>$this->page->cm->instance, 'id'=>$appid));
        $links[] = \html_writer::link($addurl,  get_string('addapp', constants::M_COMPONENT),
            array('class'=>'btn ' . constants::M_COMPONENT .'_menubutton ' . constants::M_COMPONENT .'_activemenubutton'));



    $buttonsdiv = \html_writer::div(implode('', $links),constants::M_COMPONENT .'_mbuttons');
     return $this->output->box($output . $buttonsdiv, 'generalbox firstpageoptions');
    }
	
	/**
	 * Return the html table of apps
	 * @param array homework objects
	 * @param integer $courseid
	 * @return string html of table
	 */
	function show_apps_list($apps,$tableid,$cm){
	
		if(!$apps){
			return $this->output->heading(get_string('noapps',constants::M_COMPONENT), 3, 'main');
		}

        //prepare table with data
		$table = new \html_table();
		$table->id = $tableid;
		$table->attributes =array('class'=>constants::M_CLASS_APPSCONTAINER);


		$table->head = array(
			get_string('appname', constants::M_COMPONENT),
			get_string('applevel', constants::M_COMPONENT),
            get_string('timemodified', constants::M_COMPONENT),
			get_string('actions', constants::M_COMPONENT),
            ''
		);
		$table->headspan = array(1,1,1,2);
		$table->colclasses = array(
			'appname', 'applevel', 'timemodified', 'edit','delete'
		);


		//loop through the apps and add to table
        $currentapp=0;
		foreach ($apps as $app) {
            $currentapp++;
            $row = new \html_table_row();

            //app name
            $appnamecell = new \html_table_cell($app->name);

            //app level
            switch($app->applevel) {
                case constants::M_APPLEVEL_SITE:
                    $applevel = get_string('applevelsite',constants::M_COMPONENT);
                    break;
                case constants::M_APPLEVEL_COURSE:
                default:
                    $applevel = get_string('applevelcourse',constants::M_COMPONENT);
                    break;
            }
            $applevelcell = new \html_table_cell($applevel);



            //modify date
            $datecell_content = date("Y-m-d H:i:s",$app->timemodified);
            $appdatecell = new \html_table_cell($datecell_content);

            //app edit

            $actionurl = '/mod/embed/apps/manageapps.php';
            $editurl = new \moodle_url($actionurl, array('moduleid' => $cm->instance,'id' => $app->id));
            $editlink = \html_writer::link($editurl, get_string('editapp', constants::M_COMPONENT));
            $editcell = new \html_table_cell($editlink);

		    //app delete
            $deleteurl = new \moodle_url($actionurl,
                    array('moduleid' => $cm->instance, 'id' => $app->id, 'action' => 'confirmdelete'));
            $deletelink = \html_writer::link($deleteurl, get_string('deleteapp', constants::M_COMPONENT));

			$deletecell = new \html_table_cell($deletelink);

			$row->cells = array(
                   $appnamecell, $applevelcell,$appdatecell, $editcell, $deletecell
			);
			$table->data[] = $row;
		}
		return \html_writer::table($table);

	}

    function setup_datatables($tableid){
        global $USER;

        $tableprops = array();
        $notorderable = array('orderable'=>false);
        $columns = [$notorderable,null,null,$notorderable,$notorderable,null,$notorderable,$notorderable];
        $tableprops['columns']=$columns;

        //default ordering
        $order = array();
        $order[0] =array(1, "asc");
        $tableprops['order']=$order;

        //here we set up any info we need to pass into javascript
        $opts =Array();
        $opts['tableid']=$tableid;
        $opts['tableprops']=$tableprops;
        $this->page->requires->js_call_amd("mod_embed/datatables", 'init', array($opts));
        $this->page->requires->css( new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));
    }
}
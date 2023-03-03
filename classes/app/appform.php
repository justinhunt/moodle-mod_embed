<?php

namespace mod_embed\app;

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Forms for mod_embed Activity
 *
 * @package    mod_embed
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

//why do we need to include this?
require_once($CFG->libdir . '/formslib.php');

use \mod_embed\constants;
use \mod_embed\utils;

/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appform extends \moodleform {



    /**
     * The module instance
     * @var array
     */
    protected $moduleinstance = null;

	
    /**
     * True if this is a standard item of false if it does something special.
     * items are standard items
     * @var bool
     */
    protected $standard = true;

    /**
     * Each item type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}

    /**
     * Item types can override this to add any custom elements to
     * the basic form that they want
     */
   public function custom_definition_after_data() {}

    /**
     * Used to determine if this is a standard item or a special item
     * @return bool
     */
    public final function is_standard() {
        return (bool)$this->standard;
    }

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        global $CFG;


        $mform = $this->_form;
	
        $mform->addElement('header', 'appheading', get_string('editingapp', constants::M_COMPONENT, get_string('appformtitle', constants::M_COMPONENT)));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'moduleid');
        $mform->setType('moduleid', PARAM_INT);

        //name
        $mform->addElement('text', 'name', get_string('appname', constants::M_COMPONENT), array('size'=>70));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        //level
        $applevels = [constants::M_APPLEVEL_SITE=>get_string('applevelsite',constants::M_COMPONENT),
            constants::M_APPLEVEL_COURSE=>get_string('applevelcourse',constants::M_COMPONENT)];
        $mform->addElement('select', 'applevel', get_string('applevel', constants::M_COMPONENT),$applevels,constants::M_APPLEVEL_SITE);
        $mform->setType('applevel', PARAM_INT);
        $mform->addRule('applevel', get_string('required'), 'required', null, 'client');

        $fields=['instructions','appinputs','appoutputs','embedtemplate','secretcode'];

        foreach($fields as $field){
            //instructions
            $mform->addElement('text', $field, get_string($field, constants::M_COMPONENT), array('size'=>70));
            $mform->setType($field, PARAM_RAW);
            $mform->addRule($field, get_string('required'), 'required', null, 'client');
        }


        $this->custom_definition();

		//add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('saveapp', constants::M_COMPONENT));

    }

    public final function definition_after_data() {
        parent::definition_after_data();
        $this->custom_definition_after_data();
    }

    /**
     * A function that gets called upon init of this object by the calling script.
     *
     * This can be used to process an immediate action if required. Currently it
     * is only used in special cases by non-standard item types.
     *
     * @return bool
     */
    public function construction_override($itemid,  $embed) {
        return true;
    }
}
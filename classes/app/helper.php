<?php

namespace mod_embed\app;

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
 * Internal library of functions for module mod_embed
 *
 * All the mod_embed specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_embed
 * @copyright  COPYRIGHTNOTICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_embed\constants;

class helper
{

    public static function delete_app($moduleid, $appid, $context)
    {
        global $DB;
        $ret = false;

        //remove records
        if (!$DB->delete_records(constants::M_APP_TABLE, array('id' => $appid))) {
            print_error("Could not delete app");
            return $ret;
        }

        $ret = true;
        return $ret;
    }

}

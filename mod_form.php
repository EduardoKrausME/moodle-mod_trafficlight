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
 * Traffic light activity plugin.
 *
 * @package    mod_trafficlight
 * @copyright  2026 Eduardo Kraus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/course/moodleform_mod.php");

/**
 * Activity settings form.
 */
class mod_trafficlight_mod_form extends moodleform_mod {
    /**
     * Defines the activity settings form.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement("text", "name", get_string("trafficlightname", "mod_trafficlight"), ["size" => "64"]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");

        $this->standard_intro_elements(get_string("prompt", "mod_trafficlight"));
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}

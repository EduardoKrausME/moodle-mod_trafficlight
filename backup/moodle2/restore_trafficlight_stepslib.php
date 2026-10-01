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

/**
 * Restores the traffic light activity structure.
 */
class restore_trafficlight_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines restore paths.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = [];
        $paths[] = new restore_path_element("trafficlight", "/activity/trafficlight");

        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("trafficlight_response", "/activity/trafficlight/responses/response");
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the main activity record.
     *
     * @param array $data
     * @return void
     */
    protected function process_trafficlight($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record("trafficlight", $data);
        $this->apply_activity_instance($newitemid);
        $this->set_mapping("trafficlight", $oldid, $newitemid, true);
    }

    /**
     * Restores one user response.
     *
     * @param array $data
     * @return void
     */
    protected function process_trafficlight_response($data) {
        global $DB;

        $data = (object)$data;
        $data->trafficlightid = $this->get_new_parentid("trafficlight");
        $data->userid = $this->get_mappingid("user", $data->userid);

        if (!$data->userid) {
            return;
        }

        $DB->insert_record("trafficlight_responses", $data);
    }

    /**
     * after_execute
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files("mod_trafficlight", "intro", null);
    }
}

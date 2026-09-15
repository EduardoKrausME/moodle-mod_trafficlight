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
 * Defines the traffic light backup structure.
 */
class backup_trafficlight_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines backup structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $trafficlight = new backup_nested_element("trafficlight", ["id"], [
            "name",
            "intro",
            "introformat",
            "timecreated",
            "timemodified",
        ]);

        $responses = new backup_nested_element("responses");
        $response = new backup_nested_element("response", ["id"], [
            "userid",
            "status",
            "timecreated",
            "timemodified",
        ]);

        $trafficlight->add_child($responses);
        $responses->add_child($response);

        $trafficlight->set_source_table("trafficlight", ["id" => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $response->set_source_table("trafficlight_responses", ["trafficlightid" => backup::VAR_PARENTID]);
            $response->annotate_ids("user", "userid");
        }

        $trafficlight->annotate_files("mod_trafficlight", "intro", null);

        return $this->prepare_activity_structure($trafficlight);
    }
}

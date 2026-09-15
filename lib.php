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
 * Returns the features supported by the module.
 *
 * @param string $feature
 * @return mixed
 */
function trafficlight_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COMMUNICATION;
        default:
            return null;
    }
}

/**
 * Adds a traffic light instance.
 *
 * @param stdClass $data
 * @param mod_trafficlight_mod_form|null $mform
 * @return int
 */
function trafficlight_add_instance($data, $mform = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    return $DB->insert_record("trafficlight", $data);
}

/**
 * Updates a traffic light instance.
 *
 * @param stdClass $data
 * @param mod_trafficlight_mod_form|null $mform
 * @return bool
 */
function trafficlight_update_instance($data, $mform = null) {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();

    return $DB->update_record("trafficlight", $data);
}

/**
 * Deletes a traffic light instance.
 *
 * @param int $id
 * @return bool
 */
function trafficlight_delete_instance($id) {
    global $DB;

    if (!$trafficlight = $DB->get_record("trafficlight", ["id" => $id])) {
        return false;
    }

    $DB->delete_records("trafficlight_responses", ["trafficlightid" => $trafficlight->id]);
    $DB->delete_records("trafficlight", ["id" => $trafficlight->id]);

    return true;
}

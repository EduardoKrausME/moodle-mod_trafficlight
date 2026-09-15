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

require_once(__DIR__ . "/../../config.php");

use mod_trafficlight\dashboard;
use mod_trafficlight\response_manager;

$id = required_param("id", PARAM_INT);

$cm = get_coursemodule_from_id("trafficlight", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$trafficlight = $DB->get_record("trafficlight", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability("mod/trafficlight:view", $context);

$PAGE->set_url("/mod/trafficlight/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($trafficlight->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class("mod-trafficlight-view");

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$manager = new response_manager($trafficlight, $context);
$canviewreport = has_capability("mod/trafficlight:viewreport", $context);

if (!$canviewreport) {
    require_capability("mod/trafficlight:submit", $context);

    if (optional_param("submitresponse", 0, PARAM_BOOL)) {
        require_sesskey();
        $status = required_param("status", PARAM_ALPHA);
        $manager->save_response($USER->id, $status);
        redirect($PAGE->url, get_string("responsesaved", "mod_trafficlight"), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($trafficlight->name));

if (!empty($trafficlight->intro)) {
    echo $OUTPUT->box(format_module_intro("trafficlight", $trafficlight, $cm->id), "generalbox mod_introbox");
}

if ($canviewreport) {
    $dashboard = new dashboard($trafficlight, $context);
    echo $OUTPUT->render_from_template("mod_trafficlight/dashboard", $dashboard->get_template_data());
} else {
    $response = $manager->get_user_response($USER->id);
    $currentstatus = $response ? $response->status : "";

    $data = [
        "action" => $PAGE->url->out(false),
        "sesskey" => sesskey(),
        "currentstatus" => $currentstatus,
        "hasresponse" => !empty($currentstatus),
        "green" => $currentstatus === response_manager::STATUS_GREEN,
        "yellow" => $currentstatus === response_manager::STATUS_YELLOW,
        "red" => $currentstatus === response_manager::STATUS_RED,
    ];

    echo $OUTPUT->render_from_template("mod_trafficlight/student_form", $data);
}

echo $OUTPUT->footer();

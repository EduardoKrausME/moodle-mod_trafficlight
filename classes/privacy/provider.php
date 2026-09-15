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
namespace mod_trafficlight\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for traffic light responses.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes stored personal data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("trafficlight_responses", [
            "trafficlightid" => "privacy:metadata:trafficlight_responses:trafficlightid",
            "userid" => "privacy:metadata:trafficlight_responses:userid",
            "status" => "privacy:metadata:trafficlight_responses:status",
            "timecreated" => "privacy:metadata:trafficlight_responses:timecreated",
            "timemodified" => "privacy:metadata:trafficlight_responses:timemodified",
        ], "privacy:metadata:trafficlight_responses");

        return $collection;
    }

    /**
     * Gets contexts containing data for a user.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {trafficlight} t ON t.id = cm.instance
                  JOIN {trafficlight_responses} r ON r.trafficlightid = t.id
                 WHERE r.userid = :userid";

        $params = [
            "contextlevel" => CONTEXT_MODULE,
            "modname" => "trafficlight",
            "userid" => $userid,
        ];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Adds users with data in a context.
     *
     * @param userlist $userlist
     * @return void
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $sql = "SELECT r.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {trafficlight_responses} r ON r.trafficlightid = cm.instance
                 WHERE cm.id = :cmid";

        $userlist->add_from_sql("userid", $sql, [
            "modname" => "trafficlight",
            "cmid" => $context->instanceid,
        ]);
    }

    /**
     * Exports user data.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id("trafficlight", $context->instanceid, 0, false, IGNORE_MISSING);
            if (!$cm) {
                continue;
            }

            $trafficlight = $DB->get_record("trafficlight", ["id" => $cm->instance]);
            $response = $DB->get_record("trafficlight_responses", [
                "trafficlightid" => $cm->instance,
                "userid" => $userid,
            ]);

            if (!$trafficlight || !$response) {
                continue;
            }

            $data = (object) [
                get_string("privacy:export:status", "mod_trafficlight") =>
                    get_string("status" . $response->status, "mod_trafficlight"),
                get_string("privacy:export:timecreated", "mod_trafficlight") => transform::datetime($response->timecreated),
                get_string("privacy:export:timemodified", "mod_trafficlight") => transform::datetime($response->timemodified),
            ];

            writer::with_context($context)->export_data([format_string($trafficlight->name)], $data);
        }
    }

    /**
     * Deletes all user data in a context.
     *
     * @param context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id("trafficlight", $context->instanceid, 0, false, IGNORE_MISSING);
        if ($cm) {
            $DB->delete_records("trafficlight_responses", ["trafficlightid" => $cm->instance]);
        }
    }

    /**
     * Deletes data for one user in approved contexts.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id("trafficlight", $context->instanceid, 0, false, IGNORE_MISSING);
            if ($cm) {
                $DB->delete_records("trafficlight_responses", [
                    "trafficlightid" => $cm->instance,
                    "userid" => $userid,
                ]);
            }
        }
    }

    /**
     * Deletes data for approved users in one context.
     *
     * @param approved_userlist $userlist
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id("trafficlight", $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params["trafficlightid"] = $cm->instance;
        $DB->delete_records_select("trafficlight_responses", "trafficlightid = :trafficlightid AND userid {$insql}", $params);
    }
}

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

namespace mod_trafficlight;

use context_module;
use moodle_url;
use stdClass;

/**
 * Builds the teacher dashboard data.
 */
class dashboard {
    /** @var stdClass */
    private $trafficlight;

    /** @var context_module */
    private $context;

    /**
     * Constructor.
     *
     * @param stdClass $trafficlight
     * @param context_module $context
     */
    public function __construct(stdClass $trafficlight, context_module $context) {
        $this->trafficlight = $trafficlight;
        $this->context = $context;
    }

    /**
     * Builds template data for the teacher dashboard.
     *
     * @return array
     */
    public function get_template_data(): array {
        $manager = new response_manager($this->trafficlight, $this->context);
        $responses = $manager->get_responses_by_user();
        $users = get_enrolled_users(
            $this->context,
            "mod/trafficlight:submit",
            0,
            "u.id,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.email",
            "u.lastname ASC,u.firstname ASC"
        );

        $groups = [
            response_manager::STATUS_GREEN => [],
            response_manager::STATUS_YELLOW => [],
            response_manager::STATUS_RED => [],
            "unanswered" => [],
        ];

        foreach ($users as $user) {
            $status = isset($responses[$user->id]) ? $responses[$user->id]->status : "unanswered";
            if (!array_key_exists($status, $groups)) {
                $status = "unanswered";
            }

            $groups[$status][] = [
                "name" => fullname($user),
                "profileurl" => (new moodle_url("/user/view.php", [
                    "id" => $user->id,
                    "course" => $this->trafficlight->course,
                ]))->out(false),
            ];
        }

        $total = count($users);
        $responded = $total - count($groups["unanswered"]);

        return [
            "total" => $total,
            "responded" => $responded,
            "responsepercentage" => $this->percentage($responded, $total),
            "green" => $this->group_data("green", $groups["green"], $total),
            "yellow" => $this->group_data("yellow", $groups["yellow"], $total),
            "red" => $this->group_data("red", $groups["red"], $total),
            "unanswered" => $this->group_data("unanswered", $groups["unanswered"], $total),
        ];
    }

    /**
     * Formats one dashboard group.
     *
     * @param string $status
     * @param array $users
     * @param int $total
     * @return array
     */
    private function group_data(string $status, array $users, int $total): array {
        return [
            "status" => $status,
            "label" => get_string("status" . $status, "mod_trafficlight"),
            "description" => get_string("status" . $status . "description", "mod_trafficlight"),
            "count" => count($users),
            "percentage" => $this->percentage(count($users), $total),
            "users" => $users,
            "hasusers" => !empty($users),
        ];
    }

    /**
     * Calculates a rounded percentage.
     *
     * @param int $value
     * @param int $total
     * @return int
     */
    private function percentage(int $value, int $total): int {
        if ($total === 0) {
            return 0;
        }

        return (int)round(($value / $total) * 100);
    }
}

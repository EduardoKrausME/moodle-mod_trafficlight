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
use invalid_parameter_exception;
use stdClass;

/**
 * Manages student traffic light responses.
 */
class response_manager {

    /** @var string */
    public const STATUS_GREEN = "green";

    /** @var string */
    public const STATUS_YELLOW = "yellow";

    /** @var string */
    public const STATUS_RED = "red";

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
     * Returns all valid statuses.
     *
     * @return array
     */
    public static function get_valid_statuses(): array {
        return [self::STATUS_GREEN, self::STATUS_YELLOW, self::STATUS_RED];
    }

    /**
     * Saves or updates one user's response.
     *
     * @param int $userid
     * @param string $status
     * @return stdClass
     */
    public function save_response(int $userid, string $status): stdClass {
        global $DB;

        if (!in_array($status, self::get_valid_statuses(), true)) {
            throw new invalid_parameter_exception("Invalid traffic light status.");
        }

        $now = time();
        $params = [
            "trafficlightid" => $this->trafficlight->id,
            "userid" => $userid,
        ];

        $record = $DB->get_record("trafficlight_responses", $params);

        if ($record) {
            $record->status = $status;
            $record->timemodified = $now;
            $DB->update_record("trafficlight_responses", $record);
        } else {
            $record = (object) [
                "trafficlightid" => $this->trafficlight->id,
                "userid" => $userid,
                "status" => $status,
                "timecreated" => $now,
                "timemodified" => $now,
            ];
            $record->id = $DB->insert_record("trafficlight_responses", $record);
        }

        return $record;
    }

    /**
     * Gets one user's current response.
     *
     * @param int $userid
     * @return stdClass|false
     */
    public function get_user_response(int $userid) {
        global $DB;

        return $DB->get_record("trafficlight_responses", [
            "trafficlightid" => $this->trafficlight->id,
            "userid" => $userid,
        ]);
    }

    /**
     * Gets all responses keyed by user id.
     *
     * @return array
     */
    public function get_responses_by_user(): array {
        global $DB;

        return $DB->get_records(
            "trafficlight_responses",
            ["trafficlightid" => $this->trafficlight->id],
            "",
            "userid,status,timemodified"
        );
    }
}

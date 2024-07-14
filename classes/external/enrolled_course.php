<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Core course external api
 *
 * @package   theme_boost
 * @copyright 2024 Lucas Mendes {@link https://www.lucasmendesdev.com.br}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_boost\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_multiple_structure;
use core_external\external_single_structure;


class enrolled_course extends external_api {

    public static function get_enrollments_user_by_course($params){
        global $USER, $DB;

        $params = explode(',', $params);
        $params = array_map(function ($param) {
            return intval($param);
        }, $params);

        list ($courseIdIn, $query) = $DB->get_in_or_equal($params, SQL_PARAMS_NAMED);
        list ($userIdEqual, $query2) = $DB->get_in_or_equal([$USER->id], SQL_PARAMS_NAMED);
        $query += $query2;

        $sql = "SELECT ue.id, ue.timeend, e.courseid
              FROM {user_enrolments} ue
              JOIN {enrol} e ON e.id = ue.enrolid
              JOIN {user} u ON u.id = ue.userid
             WHERE e.courseid {$courseIdIn} AND ue.userid {$userIdEqual}";

        $enrollments = $DB->get_records_sql($sql, $query);

        return array_map(function ($enrollment) {
            return [
                'courseId' => $enrollment->courseid,
                'timeEnd' => $enrollment->timeend
            ];
        }, $enrollments);
    }


    public static function get_enrollments_user_by_course_parameters() {
        return new external_function_parameters([
            'courseIds' => new external_value(PARAM_SEQUENCE, 'Ids of courses')
        ]);
    }

    public static function get_enrollments_user_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'courseId' => new external_value(PARAM_INT, 'Id of course'),
                'timeEnd' => new external_value(PARAM_INT, 'Enrollment expiration time in seconds'),
            ])
        );
    }
}
<?php

use theme_boost\external\enrolled_course;

$functions = [
    'core_course_boost_get_enrollments_user_by_course' => [
        'classname' => enrolled_course::class,
        'methodname' => 'get_enrollments_user_by_course',
        'description' => 'Returns user enrollments for each course',
        'type' => 'read',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE]
    ]
];
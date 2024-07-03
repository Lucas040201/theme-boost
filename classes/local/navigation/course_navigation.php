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
 * Version metadata for the theme_boost plugin.
 *
 * @package   theme_boost
 * @copyright 2024 Lucas Mendes {@link https://www.lucasmendesdev.com.br}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost\local\navigation;

use Exception;

defined('MOODLE_INTERNAL') || die;

include_once($CFG->libdir . '/modinfolib.php');



class course_navigation {

    private static function get_sibling_modules(int $sectionnum, int $activitynum) {
        global $COURSE;
        $modInfo = get_fast_modinfo($COURSE);
        $modules = $modInfo->get_cms();

        $prevModule = null;
        $nextModule = null;
        $currentModule = null;
        foreach($modules as $mod) {
            if (!$mod->uservisible || (!$mod->has_view() && strcmp($mod->modname, 'folder') !== 0)) {
                continue;
            }

            if(!empty($currentModule)) {
                $nextModule = $mod;
                break;
            }


            if((int)$activitynum === (int)$mod->id) {
                $currentModule = $mod;
                continue;
            }

            $prevModule = $mod;
        }

        $data = [];
        if(!empty($prevModule)) {
            $data['prev'] = $prevModule->get_url()->out(false);
        }

        if(!empty($nextModule)) {
            $data['next'] = $nextModule->get_url()->out(false);
        }
        
        return $data;
      }



    public static function get_course_navigation()
    {
        global $DB, $PAGE, $COURSE;
        $_ccnCourseSectionNavigator = '';
        if($PAGE->context instanceof \context_module){
            $sectionnum = $PAGE->cm->sectionnum;
            $activitynum = $PAGE->cm->__get('id');
        } elseif($PAGE->context instanceof \context_course){
            $sectionnum = $PAGE->url->get_param('section');
            $activitynum = 0;
        } else {
            return '';
        }
        $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        if (isset($sectionnum) && $DB->record_exists('course', array('id' => $COURSE->id)) && strpos($url,'grade/edit/') == false){
            $siblingmodules = self::get_sibling_modules($sectionnum,$activitynum);
            if(!empty($siblingmodules['next'])) {
                $nextActivity = $siblingmodules['next'];
            }

            if(!empty($siblingmodules['prev'])) {
                $prevActivity = $siblingmodules['prev'];
            }

            $baseElement = '<a href="%ACTIVITY_URL%" class="navbar_moduleforward ml-3 %ACTIVITY_DISABLE_CLASS%" aria-label="%ACTIVITY_TOOLTIP%" title="%ACTIVITY_TOOLTIP%"> %PREV_ICON% %ACTIVITY_TEXT% %NEXT_ICON%</a>';

            $disableClass = 'disabled';
            $_ccnCourseSectionNavigator .= str_replace(
                [
                    '%ACTIVITY_URL%',
                    '%ACTIVITY_TOOLTIP%',
                    '%ACTIVITY_TEXT%',
                    '%ACTIVITY_DISABLE_CLASS%',
                    '%PREV_ICON%',
                    '%NEXT_ICON%',
                ],
                [
                    !empty($prevActivity) ? $prevActivity : '#',
                    get_string('prev_activity_tooltip', 'theme_boost'),
                    get_string('prev_activity', 'theme_boost'),
                    empty($prevActivity) ? $disableClass : '',
                    '<i class="icon fa fa-chevron-left fa-fw " aria-hidden="true"></i>',
                    '',
                ],
                $baseElement
            );

            $_ccnCourseSectionNavigator .= str_replace(
                [
                    '%ACTIVITY_URL%',
                    '%ACTIVITY_TOOLTIP%',
                    '%ACTIVITY_TEXT%',
                    '%ACTIVITY_DISABLE_CLASS%',
                    '%PREV_ICON%',
                    '%NEXT_ICON%',
                ],
                [
                    !empty($nextActivity) ? $nextActivity : '#',
                    get_string('next_activity_tooltip', 'theme_boost'),
                    get_string('next_activity', 'theme_boost'),
                    empty($nextActivity) ? $disableClass : '',
                    '',
                    '<i class="icon fa fa-chevron-right fa-fw " aria-hidden="true"></i>',
                ],
                $baseElement
            );
        }
        return $_ccnCourseSectionNavigator;
    }
}
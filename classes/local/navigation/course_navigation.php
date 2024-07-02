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
 * Version metadata for the block_class_material plugin.
 *
 * @package   block_class_material
 * @copyright 2024 Lucas Mendes {@link https://www.lucasmendesdev.com.br}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost\local\navigation;

use Exception;

defined('MOODLE_INTERNAL') || die;

include_once($CFG->libdir . '/modinfolib.php');



class course_navigation {

    private static function get_sibling_modules(int $sectionnum, int $activitynum){
        global $COURSE;
        $currentsection = get_fast_modinfo($COURSE)->get_section_info($sectionnum);
        $modules = get_fast_modinfo($COURSE)->get_cms();
        
        if(empty($currentsection->modinfo->sections[$sectionnum])) {
            throw new Exception('Section number error');
        }

        $activitiesId = array_values($currentsection->modinfo->sections[$sectionnum]);

        $currentActivityKey = array_search($activitynum, $activitiesId);
        $firstItemId = (int)$activitiesId[0];
        if($currentActivityKey === 0 && count($activitiesId) > 1) {
            $nextModuleUrl = array_values(array_filter($modules, function ($mod) use($activitiesId){
                return $mod->id === $activitiesId[1];
            }));
            return [
                'prev' => '',
                'next' => $nextModuleUrl[0]->get_url()->out(false),
            ];
        }

        $lastItemId = (int)end($activitiesId);
        if($lastItemId === $activitynum && count($activitiesId) > 1) {
            $prevModuleUrl = array_values(array_filter($modules, function ($mod) use($activitiesId){
                return $mod->id === $activitiesId[count($activitiesId) - 2];
            }));
            return [
                'prev' => $prevModuleUrl[0]->get_url()->out(false),
                'next' => ''
            ];
        }

        if($lastItemId !== $activitynum && $firstItemId !== $activitynum) {
            $currentElementKey = array_search($activitynum, $activitiesId);

            $prevModuleId = $activitiesId[$currentElementKey - 1];
            $nextModuleId = $activitiesId[$currentElementKey + 1];

            $nextModuleUrl = array_values(array_filter($modules, function ($mod) use($nextModuleId){
                return $mod->id === $nextModuleId;
            }));

            $prevModuleUrl = array_values(array_filter($modules, function ($mod) use($prevModuleId){
                return $mod->id === $prevModuleId;
            }));

            return [
                'prev' => $prevModuleUrl[0]->get_url()->out(false),
                'next' => $nextModuleUrl[0]->get_url()->out(false),
            ];
        }

        return [];
      }

    private static function getNextSectionFirstActivityUrl(int $sectionnum, int $currentActivitId) {
        global $COURSE;
        $modules = get_fast_modinfo($COURSE)->get_cms();
        $currentsection = get_fast_modinfo($COURSE)->get_section_info($sectionnum);
        $sectionsWithActivitiesIds = $currentsection->modinfo->sections;

        if(empty($sectionsWithActivitiesIds[$sectionnum + 1])) {
            return;
        }

        $nextSection = $sectionsWithActivitiesIds[$sectionnum + 1];
        $firstActivityId = $nextSection[0];

        $firstModFromNextSection = array_filter($modules, function ($mod) use ($firstActivityId) {
            return $mod->id === $firstActivityId;
        });

        return end($firstModFromNextSection)->get_url()->out(false);
    }
    
    private static function getPrevSectionLastActivityUrl(int $sectionnum, int $currentActivitId) {
        global $COURSE;
        $modules = get_fast_modinfo($COURSE)->get_cms();

        if($sectionnum === 0) {
            return;
        }
        $currentsection = get_fast_modinfo($COURSE)->get_section_info($sectionnum);
        $sectionsWithActivitiesIds = $currentsection->modinfo->sections;
        
        $prevSection = $sectionsWithActivitiesIds[$sectionnum - 1];
        $lastActivityId = end($prevSection);

        $lastModFromPrevSection = array_filter($modules, function ($mod) use ($lastActivityId) {
            return $mod->id === $lastActivityId;
        });

        return end($lastModFromPrevSection)->get_url()->out(false);
    }


    private static function get_sibling_subsections(int $sectionnum, int $currentActivitId){
        $prevsubsectionurl = self::getPrevSectionLastActivityUrl($sectionnum, $currentActivitId);
        $nextsubsectionurl = self::getNextSectionFirstActivityUrl($sectionnum, $currentActivitId);
        return ['prev'=>$prevsubsectionurl, 'next'=>$nextsubsectionurl];
      }


    public static function get_course_navigation()
    {
        global $DB, $PAGE, $COURSE;
        $_ccnCourseSectionNavigator = '';
        if($PAGE->context instanceof \context_module){
            $sectionnum = $PAGE->cm->sectionnum;
            $activitynum = $PAGE->cm->__get('id');
        }
        elseif($PAGE->context instanceof \context_course){
            $sectionnum = $PAGE->url->get_param('section');
            $activitynum = 0;
        }
        $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        if (isset($sectionnum) && $DB->record_exists('course', array('id' => $COURSE->id)) && strpos($url,'grade/edit/') == false){
            $siblingmodules = self::get_sibling_modules($sectionnum,$activitynum);
            $siblingsubsections = self::get_sibling_subsections($sectionnum, $activitynum);
            $nextActivity = $siblingsubsections['next'];
            $prevActivity = $siblingsubsections['prev'];

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
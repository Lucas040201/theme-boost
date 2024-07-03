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

namespace theme_boost\local\header;

use Exception;

defined('MOODLE_INTERNAL') || die;

include_once($CFG->libdir . '/modinfolib.php');



class contact_info {

    public static function getContactVars()
    {
        $links = self::links();
        $contact = self::contact();
        $hasContact = !empty($contact);
        $hasSocial = !empty($links);

        $hasTopNavBar = $hasContact || $hasSocial;
        return [
            'socialMedia' => $links,
            'contactInfo' => $contact,
            'hasContact' => $hasContact,
            'hasSocial' => $hasSocial,
            'hasTopNavBar' => $hasTopNavBar,
        ];
    }

    private static function contact()
    {
        $contact = [];

        $phone = get_config('theme_boost', 'phone');
        $cellphone = get_config('theme_boost', 'cellphone');
        $contactEmail = get_config('theme_boost', 'contact_email');

        if(!empty($phone)) {
            $contact[] = "<a href='tel:$phone'>$phone</a>";
        }

        if(!empty($cellphone)) {
            $contact[] = "<a href='tel:$cellphone'>$cellphone</a>";
        }

        if(!empty($contactEmail)) {
            $contact[] = "<a href='mailto:$contactEmail'>$contactEmail</a>";;
        }

        return implode(' / ', $contact);
    }

    private static function links()
    {
        $links = [];

        $facebookLink = get_config('theme_boost', 'facebook_link');
        $instagramLink = get_config('theme_boost', 'instagram_link');
        $youtubeLink = get_config('theme_boost', 'youtube_link');
        $linkedinLink = get_config('theme_boost', 'linkedin_link');

        if(!empty($facebookLink)) {
            $links[] = "<a href='$facebookLink' target='_blank'><i class='fa-brands fa-facebook-f'></i></a>";
        }

        if(!empty($instagramLink)) {
            $links[] = "<a href='$instagramLink' target='_blank'><i class='fa-brands fa-instagram'></i></a>";
        }

        if(!empty($youtubeLink)) {
            $links[] = "<a href='$youtubeLink' target='_blank'><i class='fa-brands fa-youtube'></i></a>";;
        }

        if(!empty($linkedinLink)) {
            $links[] = "<a href='$linkedinLink' target='_blank'><i class='fa-brands fa-linkedin-in'></i></a>";;
        }

        return implode('', $links);
    }
}
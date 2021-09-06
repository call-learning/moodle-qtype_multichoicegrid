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
 * The editing form for toeicexam question type is defined here.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_toeicexam;
defined('MOODLE_INTERNAL') || die();

class utils {
    /**
     * Option count for each answer.
     */
    const OPTION_COUNT = 4;

    /**
     * Number of answer by default.
     */
    const BASE_ANSWER_COUNT = 25;


    const FILEPICKER_OPTIONS = [
        'audiofiles' => array('accepted_types' => 'web_audio', 'subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
        'documents' => array('accepted_types' => 'pdf', 'subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0)
    ];

    const FILE_AREAS = [
        'audiofiles',
        'documents'
    ];
    /**
     * @param $type
     * @return string[]
     */
    public static function file_manager_options($type) {
        return self::FILEPICKER_OPTIONS[$type];

    }
}
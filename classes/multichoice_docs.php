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
 * The multichoicegrid question renderer class is defined here.
 *
 * @package     qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_multichoicegrid;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

class multichoice_docs extends persistent {
    const TABLE = 'qtype_multichoicegrid_docs';
    const AUDIO_FILE_TYPE = 1;
    const DOCUMENT_FILE_TYPE = 2;

    const DOCUMENT_TYPE_SHORTNAMES = [
        self::AUDIO_FILE_TYPE => 'audio',
        self::DOCUMENT_FILE_TYPE => 'document'
    ];

    public static function add_document_data($question) {
        $docsforthisquestion = \qtype_multichoicegrid\multichoice_docs::get_records(
            array('questionid' => $question->id)
        );
        foreach ($docsforthisquestion as $doc) {
            $type = $doc->get('type');
            $index = $doc->get('sortorder');
            $area = multichoice_docs::DOCUMENT_TYPE_SHORTNAMES[$type];
            if (empty($question->{$area . 'name'})) {
                $question->{$area . 'name'} = [];
            }
            $question->{$area . 'name'}[$index] = $doc->get('name');
        }
    }

    protected static function define_properties() {
        return array(
            'type' => [
                'default' => self::AUDIO_FILE_TYPE,
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
                'choices' => [self::AUDIO_FILE_TYPE, self::DOCUMENT_FILE_TYPE]
            ],
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'sortorder' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
            ],
            'questionid' => [
                'null' => NULL_NOT_ALLOWED,
                'type' => PARAM_INT,
            ]
        );
    }

}
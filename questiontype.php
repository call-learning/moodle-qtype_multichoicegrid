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
 * Question type class for toeicexam is defined here.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

/**
 * Class that represents a toeicexam question type.
 *
 * The class loads, saves and deletes questions of the type toeicexam
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 */
class qtype_toeicexam extends question_type {

    // Override functions as necessary from the parent class located at
    // /question/type/questiontype.php.
    public function save_question_options($question) {
        parent::save_question_options($question);
        $this->save_question_answers($question);
        $this->save_hints($question);
        file_save_draft_area_files($question->audiofiles, $question->context->id,
            'qtype_toeicexam', 'audiofiles', $question->id,
            \qtype_toeicexam\utils::file_manager_options('audiofiles'));
        file_save_draft_area_files($question->documents, $question->context->id,
            'qtype_toeicexam', 'documents', $question->id,
            \qtype_toeicexam\utils::file_manager_options('documents'));
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata, false);
        $this->initialise_question_hints($question, $questiondata);
    }

}

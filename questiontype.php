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
 * Question type class for multichoicegrid is defined here.
 *
 * @package     qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qtype_multichoicegrid\utils;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

/**
 * Class that represents a multichoicegrid question type.
 *
 * The class loads, saves and deletes questions of the type multichoicegrid
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 */
class qtype_multichoicegrid extends question_type {

    // Override functions as necessary from the parent class located at
    // /question/type/questiontype.php.
    public function save_question_options($question) {
        // This will flattern the structure regarding the combined feedback.
        $this->get_question_options($question);
        $options = $this->save_combined_feedback_helper($question->options, $question, $question->context, true);
        foreach ((array) $options as $itemname => $value) {
            $question->$itemname = $value;
        }
        parent::save_question_options($question);
        $this->save_question_answers($question);
        $this->save_hints($question);
        file_save_draft_area_files($question->audiofiles, $question->context->id,
            'qtype_multichoicegrid', 'audiofiles', $question->id,
            utils::file_manager_options('audiofiles'));
        file_save_draft_area_files($question->documents, $question->context->id,
            'qtype_multichoicegrid', 'documents', $question->id,
            utils::file_manager_options('documents'));
    }

    /**
     * Defines the table which extends the question table. This allows the base questiontype
     * to automatically save, backup and restore the extra fields.
     *
     * @return an array with the table name (first) and then the column names (apart from id and questionid)
     */
    public function extra_question_fields() {
        return array('qtype_multichoicegrid',
            'correctfeedback',
            'correctfeedbackformat',
            'partiallycorrectfeedback',
            'partiallycorrectfeedbackformat',
            'incorrectfeedback',
            'incorrectfeedbackformat',
            'shownumcorrect',
        );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid, true);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);

        foreach (utils::FILE_AREAS as $area) {
            $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_multichoicegrid', $area, $questionid);
        }
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata, false);
        $this->initialise_question_hints($question, $questiondata);
        $this->initialise_combined_feedback($question, $questiondata);
    }

    protected function delete_files($questionid, $contextid) {
        $fs = get_file_storage();

        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid, true);
        $this->delete_files_in_hints($questionid, $contextid);

        foreach (utils::FILE_AREAS as $area) {
            $fs->delete_area_files($contextid, 'qtype_multichoicegrid',
                'area', $questionid);
        }
        $fs->delete_area_files($contextid, 'qtype_calculatedmulti',
            'correctfeedback', $questionid);
        $fs->delete_area_files($contextid, 'qtype_calculatedmulti',
            'partiallycorrectfeedback', $questionid);
        $fs->delete_area_files($contextid, 'qtype_calculatedmulti',
            'incorrectfeedback', $questionid);
    }

}

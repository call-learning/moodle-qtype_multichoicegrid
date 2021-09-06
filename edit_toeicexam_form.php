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

use qtype_toeicexam\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * toeicexam question editing form defition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_toeicexam_edit_form extends question_edit_form {

    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {
        $this->add_interactive_settings(true, true);

        foreach (utils::FILE_AREAS as $item) {
            $mform->addElement('filemanager', $item,
                get_string($item, 'qtype_toeicexam'),
                null,
                utils::file_manager_options($item)
            );
            $mform->setType($item, PARAM_RAW);
        }
        raise_memory_limit(MEMORY_EXTRA);
        $this->add_per_answer_fields($mform, get_string('answer', 'qtype_toeicexam', '{no}'),
            question_bank::fraction_options_full(), utils::BASE_ANSWER_COUNT, utils::BASE_ANSWER_COUNT);
        raise_memory_limit(MEMORY_STANDARD);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, false);
        $question = $this->data_preprocessing_hints($question, true, true);

        foreach (utils::FILE_AREAS as $item) {
            $draftitemid = file_get_submitted_draft_itemid($item);
            file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_toeicexam',
                $item, !empty($question->id) ? (int) $question->id : null,
                utils::file_manager_options($item));
            $question->$item = $draftitemid;
        }

        return $question;
    }



    /**
     * Get a single row of answers
     *
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param $repeatedoptions
     * @param $answersoption
     * @return array
     * @throws coding_exception
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions,
        &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $radioarray = array();
        // Answer 'answer' is a key in saving the question (see {@link save_question_answers()}).
        // Same for feedback.
        for ($i = 1; $i <= utils::OPTION_COUNT; $i++) {
            $radioarray[] = $mform->createElement('radio', 'answer', '', get_string('option:' . $i, 'qtype_toeicexam'), $i);
        }
        $repeated[] =
            $mform->createElement('group', 'answergroup', get_string('answer', 'qtype_toeicexam'), $radioarray, array(' '), false);
        $repeated[] = $mform->createElement('hidden', 'fraction');
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['feedback']['type'] = PARAM_TEXT;
        $repeatedoptions['fraction']['default'] = 0;
        $repeatedoptions['fraction']['type'] = PARAM_INT;
        $answersoption = 'answers';
        return $repeated;
    }

    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype() {
        return 'toeicexam';
    }
}

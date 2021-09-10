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
 * The editing form for multichoicegrid question type is defined here.
 *
 * @package     qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qtype_multichoicegrid\multichoice_docs;
use qtype_multichoicegrid\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * multichoicegrid question editing form defition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_multichoicegrid_edit_form extends question_edit_form {

    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype() {
        return 'multichoicegrid';
    }

    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {
        foreach (multichoice_docs::DOCUMENT_TYPE_SHORTNAMES as $item) {
            $mform->addElement('header', $item . 'header',
                get_string($item. ':title', 'qtype_multichoicegrid'));
            $repeated = [];
            $repeated[] =
                $mform->createElement('filemanager',
                    $item,
                    get_string($item, 'qtype_multichoicegrid'),
                    null,
                    utils::file_manager_options($item));
            $repeated[] =
                $mform->createElement('text',
                    $item . 'name',
                    get_string($item . 'name', 'qtype_multichoicegrid'));

            if (isset($this->question->options->$item)) {
                $repeatcount = count($this->question->options->$item);
            } else {
                $repeatcount = 1;
            }
            $repeatedoptions = [];
            $repeatedoptions[$item]['type'] = PARAM_RAW;
            $repeatedoptions[$item . 'name']['type'] = PARAM_RAW;
            $this->repeat_elements(
                $repeated,
                $repeatcount,
                $repeatedoptions,
                $item . 'item',
                'add' . $item,
                1
            );
        }
        raise_memory_limit(MEMORY_EXTRA);
        $this->add_per_answer_fields($mform, get_string('answer', 'qtype_multichoicegrid', '{no}'),
            question_bank::fraction_options_full(), utils::BASE_ANSWER_COUNT, utils::BASE_ANSWER_COUNT);
        raise_memory_limit(MEMORY_STANDARD);

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        $this->add_interactive_settings(true, true);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, false);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);
        $question = $this->data_preprocessing_documents($question);
        return $question;
    }

    /**
     * Perform the necessary preprocessing for audio and document fields
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing_documents($question) {
        foreach (multichoice_docs::DOCUMENT_TYPE_SHORTNAMES as $type => $area) {
            $draftitemid = file_get_submitted_draft_itemid($area);
            $docsforthisquestion = \qtype_multichoicegrid\multichoice_docs::get_records(
                array('questionid' => $question->id, 'type' => $type)
            );
            foreach($docsforthisquestion as $doc) {
                file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_multichoicegrid',
                    $area, $doc->get('id'),
                    utils::file_manager_options($area));
                $question->{$area}[] = $draftitemid;
            }
        }
        multichoice_docs::add_document_data($question);

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
            $radioarray[] = $mform->createElement('radio', 'answer', '', get_string('option:' . $i, 'qtype_multichoicegrid'), $i);
        }
        $repeated[] =
            $mform->createElement('group', 'answergroup', get_string('answer', 'qtype_multichoicegrid'), $radioarray, array(' '),
                false);
        $repeated[] = $mform->createElement('hidden', 'fraction');
        $repeated[] = $mform->createElement('text', 'feedback',
            get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('hidden', 'feedbackformat');
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['feedback']['type'] = PARAM_TEXT;
        $repeatedoptions['fraction']['default'] = 0;
        $repeatedoptions['fraction']['type'] = PARAM_INT;
        $repeatedoptions['feedbackformat']['type'] = PARAM_INT;
        $repeatedoptions['feedbackformat']['default'] = FORMAT_PLAIN;
        $answersoption = 'answers';
        return $repeated;
    }
}

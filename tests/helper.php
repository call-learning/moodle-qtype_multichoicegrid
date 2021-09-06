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
 * Test helpers for the toeicexam onto image question type.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Test helper class for the toeicexam onto image question type.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_toeicexam_test_helper extends question_test_helper {
    public function get_test_questions() {
        return array('ten');
    }

    /**
     * Question text
     */
    const QUESTION_TEXT = 'The quick brown fox jumped over the lazy dog.';

    const TEN_QUESTIONS = [
        1234 => [
            'rightanswer' => '1',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1235 => [
            'rightanswer' => '2',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1236 => [
            'rightanswer' => '3',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1237 => [
            'rightanswer' => '4',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1238 => [
            'rightanswer' => '1',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1239 => [
            'rightanswer' => '1',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1240 => [
            'rightanswer' => '1',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1241 => [
            'rightanswer' => '1',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1242 => [
            'rightanswer' => '1',
            'feedback' => 'feedback',
            'fraction' => 0,
        ],
        1243 => [
            'rightanswer' => '1',
            'feedback' => 'feedback',
            'fraction' => 0
        ]
    ];

    /**
     * @return qtype_toeicexam_question
     */
    public function make_toeicexam_question_ten() {
        question_bank::load_question_definition_classes('toeicexam');
        $dd = new qtype_toeicexam_question();

        test_question_maker::initialise_a_question($dd);

        $dd->name = 'Test TOEICEXAM';
        $dd->questiontext = self::QUESTION_TEXT;
        $dd->generalfeedback = 'This sentence uses each letter of the alphabet.';
        $dd->partiallycorrectfeedback =
            test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK;
        $dd->qtype = question_bank::get_qtype('toeicexam');
        $dd->penalty = 0.5; // For interactive behaviour.
        $dd->options = new stdClass();
        foreach (self::TEN_QUESTIONS as $key => $answer) {
            $dd->answers[$key] =
                new question_answer($key, $answer['rightanswer'], $answer['fraction'], $answer['feedback'], FORMAT_HTML);
        }
        return $dd;
    }

    /**
     * @return object
     */
    public function make_toeicexam_question_form_ten() {
        global $CFG;
        $form = new stdClass();
        $form->name = 'Test TOEICEXAM';
        $form->questiontext = array('text' => self::QUESTION_TEXT,
            'format' => FORMAT_HTML);
        $form->partiallycorrectfeedback = array('text' => test_question_maker::STANDARD_OVERALL_PARTIALLYCORRECT_FEEDBACK,
            'format' => FORMAT_HTML);
        $form->defaultmark = 1.0;
        $form->generalfeedback = array('text' => test_question_maker::STANDARD_OVERALL_CORRECT_FEEDBACK,
            'format' => FORMAT_HTML);
        $form->audiofiles =
            self::create_fixture_draft_file($CFG->dirroot . '/question/type/toeicexam/tests/fixtures/bensound-littleplanet.mp3');
        $form->documents = self::create_fixture_draft_file($CFG->dirroot . '/question/type/toeicexam/tests/fixtures/document.pdf');
        $form->noanswers = count(self::TEN_QUESTIONS);
        $form->answers = [];
        $form->feedback = [];
        $form->fraction = [];
        foreach (self::TEN_QUESTIONS as $key => $answer) {
            $form->answer[$key] = $answer['rightanswer'];
            $form->feedback[$key] = $answer['feedback'];
            $form->fraction[$key] = $answer['fraction'];
        }

        $form->qtype = question_bank::get_qtype('toeicexam');
        return $form;
    }

    public static function create_fixture_draft_file($originalfilepath) {
        global $USER;
        $drafitemid = 0;
        file_prepare_draft_area($drafitemid, null, null, null, null);
        $fs = get_file_storage();
        $filerecord = new stdClass();
        $filerecord->contextid = context_user::instance($USER->id)->id;
        $filerecord->component = 'user';
        $filerecord->filearea = 'draft';
        $filerecord->itemid = $drafitemid;
        $filerecord->filepath = '/';
        $filerecord->filename = 'mkmap.png';
        $fs->create_file_from_pathname($filerecord, $originalfilepath);
        return $drafitemid;
    }

    public static function get_questions($qtype, $which = null) {
        return self::TEN_QUESTIONS;
    }

    /**
     * Get answer field name by its parameter/order
     *
     * @param $dd
     * @param $desiredindex
     * @return string
     * @throws coding_exception
     */
    public static function get_fieldname_from_definition($dd, $desiredindex) {
        $index = 0;
        foreach (qtype_toeicexam_test_helper::get_questions('toeicexam') as $key => $val) {
            if ($index == $desiredindex) {
                return $dd->field($key);
            }
            $index++;
        }
        return '';
    }

    /**
     * Helper to create a question which is fully right
     *
     * @param question_definition $dd
     * @return array
     * @throws coding_exception
     */
    public static function create_full_right_response(question_definition $dd) {
        foreach (qtype_toeicexam_test_helper::get_questions('toeicexam') as $key => $val) {
            $fullresponse[$dd->field($key)] = $val['rightanswer'];
        }
        return $fullresponse;
    }

    /**
     * Helper to create a question which is fully right
     *
     * @param question_definition $dd
     * @return array
     * @throws coding_exception
     */
    public static function create_full_wrong_response(question_definition $dd) {
        foreach (qtype_toeicexam_test_helper::get_questions('toeicexam') as $key => $val) {
            $fullresponse[$dd->field($key)] = (intval($val['rightanswer']) + 1) % 4 + 1;
        }
        return $fullresponse;
    }

    /**
     * Helper to create a question which contains the value given in parameter
     *
     * @param question_definition $dd
     * @param $value
     * @return array
     * @throws coding_exception
     */
    public static function create_full_response_with_value(question_definition $dd, $value) {
        foreach (qtype_toeicexam_test_helper::get_questions('toeicexam') as $key => $val) {
            $fullresponse[$dd->field($key)] = $value;

        }
        return $fullresponse;
    }
}
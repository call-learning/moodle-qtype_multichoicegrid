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
 * Unit tests for the multichoicegrid question type.
 *
 * @package     qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/multichoicegrid/tests/helper.php');

/**
 * Unit tests for the drag-and-drop onto image question type.
 *
 * @package     qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoicegrid_walkthrough_test extends qbehaviour_walkthrough_test_base {

    public function test_interactive_behaviour() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(13, 'This is the first hint.', FORMAT_HTML, false, false),
            new question_hint_with_parts(14, 'This is the second hint.', FORMAT_HTML, true, true),
        );
        $this->start_attempt_at_question($dd, 'interactive', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 0, 1),
            $this->get_contains_answer_expectation($dd, 0, 2),
            $this->get_contains_answer_expectation($dd, 0, 3),
            $this->get_contains_answer_expectation($dd, 0, 4),
            $this->get_contains_answer_expectation($dd, 1, 1),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(3),
            $this->get_no_hint_visible_expectation());

        // Save the wrong answer.
        $fullwrong = qtype_multichoicegrid_test_helper::create_full_wrong_response($dd);
        $this->process_submission($fullwrong);
        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 1, 1, $fullwrong),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(3),
            $this->get_no_hint_visible_expectation());
        // Submit the wrong answer.
        $this->process_submission(
            array_merge($fullwrong, ['-submit' => 1])
        );

        // Verify that the current mark is not set and we can submit again.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 1, 1, $fullwrong),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_contains_try_again_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_contains_hint_expectation('This is the first hint'));

        // Do try again.
        $this->process_submission(array('-tryagain' => 1));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 1, 1, $fullwrong),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(2),
            $this->get_no_hint_visible_expectation());

        // Submit the right answer.
        $fullright = qtype_multichoicegrid_test_helper::create_full_right_response($dd);
        $this->process_submission(
            array_merge($fullright, ['-submit' => 1]));

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(5); // Penalty of 50%, 1 tries done, see adjust_fraction.
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 1, 1, $fullright),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_contains_correct_expectation(),
            $this->get_no_hint_visible_expectation());

        // Check regrading does not mess anything up.
        $this->quba->regrade_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(5);
    }

    /**
     * Get answer expectation
     *
     * @param question_definition $dd
     * @param int $answerindex
     * @param mixed $value
     * @param bool $enabled
     * @param false $checked
     * @return question_contains_tag_with_attributes
     */
    public function get_contains_answer_expectation($dd, $answerindex, $value, $enabled = true, $checked = false) {
        return $this->get_contains_radio_expectation(
            array(
                'name' => $this->quba->get_field_prefix($this->slot)
                    . qtype_multichoicegrid_test_helper::get_fieldname_from_definition($dd, $answerindex),
                'value' => $value
            ), $enabled, $checked);
    }

    /**
     * Get answer expectation for current answer
     *
     * @param question_definition $dd
     * @param int $answerindex
     * @param mixed $value
     * @param mixed $currentanswer
     * @param bool $enabled
     * @return question_contains_tag_with_attributes
     */
    public function get_contains_answer_expectation_currentanswer($dd, $answerindex, $value, $currentanswer, $enabled = true) {
        $fieldname = qtype_multichoicegrid_test_helper::get_fieldname_from_definition($dd, $answerindex);
        return $this->get_contains_radio_expectation(
            array(
                'name' => $this->quba->get_field_prefix($this->slot)
                    . qtype_multichoicegrid_test_helper::get_fieldname_from_definition($dd, $answerindex),
                'value' => $value
            ), $enabled, $currentanswer[$fieldname] == $value);
    }

    /**
     * Test deferred feedback
     */
    public function test_deferred_feedback() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(13, 'This is the first hint.', FORMAT_HTML, false, false),
            new question_hint_with_parts(14, 'This is the second hint.', FORMAT_HTML, true, true),
        );
        $this->start_attempt_at_question($dd, 'deferredfeedback', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 0, 1),
            $this->get_contains_answer_expectation($dd, 0, 2),
            $this->get_contains_answer_expectation($dd, 0, 3),
            $this->get_contains_answer_expectation($dd, 0, 4),
            $this->get_contains_answer_expectation($dd, 1, 1),
            $this->get_does_not_contain_feedback_expectation());

        // Save a partial answer.
        $fullright = qtype_multichoicegrid_test_helper::create_full_right_response($dd);
        $partialanswer = array_slice($fullright, 0, 3);
        $this->process_submission($partialanswer);
        // Verify.
        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);

        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 1, 1, $fullright),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation()
        );
        // Save the right answer.
        $this->process_submission($fullright);

        // Verify.
        $this->check_current_state(question_state::$complete);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 1, 1, $fullright),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation());

        // Finish the attempt.
        $this->quba->finish_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(10);

        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 1, 1, $fullright),
            $this->get_contains_correct_expectation());

        // Change the right answer a bit.
        $indexedanswers = array_values($dd->answers);
        $dd->answers[$indexedanswers[0]->id]->answer = 4;

        // Check regrading does not mess anything up.
        $this->quba->regrade_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(9);
    }

    /**
     * Test deferred feedback unanswered
     */
    public function test_deferred_feedback_unanswered() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(13, 'This is the first hint.', FORMAT_HTML, false, false),
            new question_hint_with_parts(14, 'This is the second hint.', FORMAT_HTML, true, true),
        );
        $this->start_attempt_at_question($dd, 'deferredfeedback', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 0, 1),
            $this->get_contains_answer_expectation($dd, 0, 2),
            $this->get_contains_answer_expectation($dd, 0, 3),
            $this->get_contains_answer_expectation($dd, 0, 4),
            $this->get_contains_answer_expectation($dd, 1, 1),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation());
        $this->check_step_count(1);

        // Save a blank response.
        $this->process_submission(qtype_multichoicegrid_test_helper::create_full_response_with_value($dd, ''));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 0, 1),
            $this->get_contains_answer_expectation($dd, 0, 2),
            $this->get_contains_answer_expectation($dd, 0, 3),
            $this->get_contains_answer_expectation($dd, 0, 4),
            $this->get_contains_answer_expectation($dd, 1, 1),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation());
        $this->check_step_count(1);

        // Finish the attempt.
        $this->quba->finish_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gaveup);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 0, 1),
            $this->get_contains_answer_expectation($dd, 0, 2),
            $this->get_contains_answer_expectation($dd, 0, 3),
            $this->get_contains_answer_expectation($dd, 0, 4),
            $this->get_contains_answer_expectation($dd, 1, 1));
    }

    /**
     * Test deferred feedback partial unanswered
     */
    public function test_deferred_feedback_partial_answer() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(13, 'This is the first hint.', FORMAT_HTML, false, false),
            new question_hint_with_parts(14, 'This is the second hint.', FORMAT_HTML, true, true),
        );
        $this->start_attempt_at_question($dd, 'deferredfeedback', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 0, 1),
            $this->get_contains_answer_expectation($dd, 0, 2),
            $this->get_contains_answer_expectation($dd, 0, 3),
            $this->get_contains_answer_expectation($dd, 0, 4),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation());

        // Save a partial answer.
        $fullright = qtype_multichoicegrid_test_helper::create_full_right_response($dd);
        $partialanswer = array_slice($fullright, 0, 3);
        $this->process_submission($partialanswer);

        // Verify.
        $this->check_current_state(question_state::$invalid);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation());

        // Finish the attempt.
        $this->quba->finish_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(3);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_partcorrect_expectation());
    }

    /**
     * Test interactive grading
     */
    public function test_interactive_grading() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(13, 'This is the first hint.', FORMAT_HTML, true, true),
            new question_hint_with_parts(14, 'This is the second hint.', FORMAT_HTML, true, true),
        );
        $dd->penalty = 0.3;
        $this->start_attempt_at_question($dd, 'interactive', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->assertEquals('interactive',
            $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(3),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_no_hint_visible_expectation());

        // Submit an response with the first two parts right.
        $fullright = qtype_multichoicegrid_test_helper::create_full_right_response($dd);
        $fullwrong = qtype_multichoicegrid_test_helper::create_full_wrong_response($dd);
        $partialright = array_merge(array_slice($fullright, 0, 3),
            array_slice($fullwrong, 3, 7), ['-submit' => 1]);
        $this->process_submission($partialright);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 4, 1, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 4, 2, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 4, 3, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 4, 4, $fullwrong),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_contains_try_again_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_contains_hint_expectation('This is the first hint'),
            $this->get_contains_num_parts_correct(3),
            $this->get_contains_standard_partiallycorrect_combined_feedback_expectation()
        );

        // Check that extract responses will return the reset data.
        $prefix = $this->quba->get_field_prefix($this->slot);
        $this->assertEquals(array_slice($fullright, 0, 3),
            $this->quba->extract_responses($this->slot,
                array($prefix . qtype_multichoicegrid_test_helper::get_fieldname_from_definition($dd, 0) => '1',
                    $prefix . qtype_multichoicegrid_test_helper::get_fieldname_from_definition($dd, 1) => '2',
                    $prefix . qtype_multichoicegrid_test_helper::get_fieldname_from_definition($dd, 2) => '3',
                    '-tryagain' => 1)));

        // Do try again.
        // keys p3 and p4 are extra hidden fields to clear data.
        $fullblank = qtype_multichoicegrid_test_helper::create_full_response_with_value($dd, '');

        $partialblanktryagain = array_merge(array_slice($fullright, 0, 3),
            array_slice($fullblank, 3, 7), ['-tryagain' => 1]);
        $this->process_submission($partialblanktryagain);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(2),
            $this->get_no_hint_visible_expectation());

        // Submit an response with the first and last parts right.
        $partialright = array_merge(array_slice($fullright, 0, 1),
            array_slice($fullblank, 1, 5),
            array_slice($fullright, 6, 4),
            ['-submit' => 1]);
        $this->process_submission($partialright);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_contains_try_again_button_expectation(true),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_contains_hint_expectation('This is the second hint'),
            $this->get_contains_num_parts_correct(5),
            $this->get_contains_standard_partiallycorrect_combined_feedback_expectation());

        // Do try again.
        $this->process_submission($partialblanktryagain);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 1, 1),
            $this->get_contains_answer_expectation($dd, 2, 2),
            $this->get_contains_answer_expectation($dd, 3, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_does_not_contain_correctness_expectation(),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(1),
            $this->get_no_hint_visible_expectation());

        // Submit the right answer.
        $this->process_submission(array_merge($fullright, ['-submit' => 1]));

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(4);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_does_not_contain_try_again_button_expectation(),
            $this->get_contains_correct_expectation(),
            $this->get_no_hint_visible_expectation(),
            $this->get_does_not_contain_num_parts_correct(),
            $this->get_contains_standard_correct_combined_feedback_expectation());
    }

    /**
     * Test interactive correct no submit
     */
    public function test_interactive_correct_no_submit() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(13, 'This is the first hint.', FORMAT_HTML, false, false),
            new question_hint_with_parts(14, 'This is the second hint.', FORMAT_HTML, true, true),
        );

        $this->start_attempt_at_question($dd, 'interactive', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(3),
            $this->get_no_hint_visible_expectation());

        // Save the right answer.
        $fullright = qtype_multichoicegrid_test_helper::create_full_right_response($dd);
        $this->process_submission($fullright);

        // Finish the attempt without clicking check.
        $this->quba->finish_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(10);
        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_contains_correct_expectation(),
            $this->get_no_hint_visible_expectation());

        // Check regrading does not mess anything up.
        $this->quba->regrade_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(10);
    }

    /**
     * Test interactive partial no submit
     */
    public function test_interactive_partial_no_submit() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(13, 'This is the first hint.', FORMAT_HTML, false, false),
            new question_hint_with_parts(14, 'This is the second hint.', FORMAT_HTML, true, true),
        );

        $this->start_attempt_at_question($dd, 'interactive', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(3),
            $this->get_no_hint_visible_expectation());

        // Save the a partially right answer.
        $fullright = qtype_multichoicegrid_test_helper::create_full_right_response($dd);
        $fullwrong = qtype_multichoicegrid_test_helper::create_full_wrong_response($dd);
        $partialright = array_slice($fullright, 0, 8) + array_slice($fullwrong, 8, 10);
        $this->process_submission($partialright);

        // Finish the attempt without clicking check.
        $this->quba->finish_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(8);

        $this->check_current_output(
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullright),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullright),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_contains_partcorrect_expectation(),
            $this->get_no_hint_visible_expectation());

        // Check regrading does not mess anything up.
        $this->quba->regrade_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(8);
    }

    /**
     * Test interactive no right clears
     */
    public function test_interactive_no_right_clears() {

        // Create a multichoicegrid question.
        $dd = test_question_maker::make_question('multichoicegrid');
        $dd->hints = array(
            new question_hint_with_parts(23, 'This is the first hint.', FORMAT_MOODLE, false, true),
            new question_hint_with_parts(24, 'This is the second hint.', FORMAT_MOODLE, true, true),
        );

        $this->start_attempt_at_question($dd, 'interactive', 10);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        $this->check_current_output(
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(3),
            $this->get_no_hint_visible_expectation());

        // Save the a completely wrong answer.
        $fullwrong = qtype_multichoicegrid_test_helper::create_full_wrong_response($dd);
        $this->process_submission($fullwrong + ['-submit' => 1]);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 1, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 2, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 3, $fullwrong),
            $this->get_contains_answer_expectation_currentanswer($dd, 0, 4, $fullwrong),
            $this->get_does_not_contain_submit_button_expectation(),
            $this->get_contains_hint_expectation('This is the first hint'));

        // Do try again.
        $fullempty = qtype_multichoicegrid_test_helper::create_full_response_with_value($dd, '');
        $this->process_submission($fullempty + ['-tryagain' => 1]);

        // Check that all the wrong answers have been cleared.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
            $this->get_contains_marked_out_of_summary(),
            $this->get_contains_answer_expectation($dd, 4, 1),
            $this->get_contains_answer_expectation($dd, 4, 2),
            $this->get_contains_answer_expectation($dd, 4, 3),
            $this->get_contains_answer_expectation($dd, 4, 4),
            $this->get_contains_submit_button_expectation(true),
            $this->get_does_not_contain_feedback_expectation(),
            $this->get_tries_remaining_expectation(2),
            $this->get_no_hint_visible_expectation()
        );
    }

}

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
 * Unit tests for the toeicexam onto image question definition class.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/toeicexam/tests/helper.php');

/**
 * Unit tests for the matching question definition class.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_toeicexam_question_test extends basic_testcase {

    public function test_get_question_summary() {
        $testhelper = test_question_maker::get_test_helper('toeicexam');
        $dd = test_question_maker::make_question('toeicexam');
        $this->assertEquals($testhelper::QUESTION_TEXT,
            $dd->get_question_summary());
    }

    public function test_summarise_response() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);

        $response = qtype_toeicexam_test_helper::create_full_right_response($dd);

        $this->assertEquals('1 -> A, 2 -> B, 3 -> C, 4 -> D, 5 -> A, 6 -> A, 7 -> A, 8 -> A, 9 -> A, 10 -> A',
            $dd->summarise_response($response));
    }

    public function test_clear_wrong_from_response() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);

        $response = qtype_toeicexam_test_helper::create_full_right_response($dd);
        $response[qtype_toeicexam_test_helper::get_fieldname_from_definition($dd, 0)] = 4;
        // The first 1 is wrong.
        $this->assertCount(9,
            $dd->clear_wrong_from_response($response));
    }

    public function test_get_num_parts_right() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);

        $response = qtype_toeicexam_test_helper::create_full_right_response($dd);
        $response[qtype_toeicexam_test_helper::get_fieldname_from_definition($dd, 0)] = 4;
        $this->assertEquals(array(9, 10),
            $dd->get_num_parts_right($response));

        $response = qtype_toeicexam_test_helper::create_full_right_response($dd);
        $this->assertEquals(array(10, 10), $dd->get_num_parts_right($response));
    }

    public function test_get_expected_data() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);
        $expecteddata = qtype_toeicexam_test_helper::create_full_response_with_value($dd, PARAM_INT);
        $this->assertEquals(
            $expecteddata,
            $dd->get_expected_data()
        );
    }

    public function test_get_correct_response() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);
        $rightresponse = qtype_toeicexam_test_helper::create_full_right_response($dd);
        $this->assertEquals($rightresponse,
            $dd->get_correct_response());
    }

    public function test_is_same_response() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);

        $response = qtype_toeicexam_test_helper::create_full_right_response($dd);

        $emptyresponse = qtype_toeicexam_test_helper::create_full_response_with_value($dd, '');
        // Is an empty response same ''.
        $this->assertTrue($dd->is_same_response(
            array(),
            $emptyresponse));

        $emptybutone = $emptyresponse;
        $firstfieldname = qtype_toeicexam_test_helper::get_fieldname_from_definition($dd, 0);
        $emptybutone[$firstfieldname] = $response[$firstfieldname];
        $this->assertFalse($dd->is_same_response(
            array(),
            $emptybutone));

        $this->assertTrue($dd->is_same_response(
            $response,
            $response));

        $differentresponse = $response;
        $differentresponse[$firstfieldname] = '4';
        $this->assertFalse($dd->is_same_response(
            $response,
            $differentresponse));
    }

    public function test_is_complete_response() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);
        $fullresponse = qtype_toeicexam_test_helper::create_full_right_response($dd);
        $firstfieldname = qtype_toeicexam_test_helper::get_fieldname_from_definition($dd, 0);

        $firstoneempty = $fullresponse;
        $firstoneempty[$firstfieldname] = '';
        $this->assertFalse($dd->is_complete_response(array()));
        $this->assertFalse($dd->is_complete_response(
            $firstoneempty));
        $oneresponse = [];
        $oneresponse[$firstfieldname] = '4';
        $this->assertFalse($dd->is_complete_response($oneresponse));
        $this->assertTrue($dd->is_complete_response(
            $fullresponse));
    }

    public function test_is_gradable_response() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);

        $fullresponse = qtype_toeicexam_test_helper::create_full_right_response($dd);
        $firstfieldname = qtype_toeicexam_test_helper::get_fieldname_from_definition($dd, 0);

        $firstoneempty = $fullresponse;
        $firstoneempty[$firstfieldname] = '';

        $emptyresponse = qtype_toeicexam_test_helper::create_full_response_with_value($dd, '');

        $this->assertFalse($dd->is_gradable_response(array()));
        $this->assertFalse($dd->is_gradable_response(
            $emptyresponse));
        $this->assertTrue($dd->is_gradable_response(
            $firstoneempty));
        $oneresponse = [];
        $oneresponse[$firstfieldname] = '4';
        $this->assertTrue($dd->is_gradable_response($oneresponse));
        $this->assertTrue($dd->is_gradable_response(
            $fullresponse));
    }

    public function test_grading() {
        $dd = test_question_maker::make_question('toeicexam');
        $dd->start_attempt(new question_attempt_step(), 1);

        $fullresponse = qtype_toeicexam_test_helper::create_full_right_response($dd);
        $this->assertEquals(array(1, question_state::$gradedright),
            $dd->grade_response($fullresponse));
        $oneresponse = [];
        $firstfieldname = qtype_toeicexam_test_helper::get_fieldname_from_definition($dd, 0);
        $oneresponse[$firstfieldname] = $fullresponse[$firstfieldname];
        $this->assertEquals(array(0.1, question_state::$gradedpartial),
            $dd->grade_response($oneresponse));
        $allwrongresponse = qtype_toeicexam_test_helper::create_full_wrong_response($dd);
        $this->assertEquals(array(0, question_state::$gradedwrong),
            $dd->grade_response($allwrongresponse));
    }
}

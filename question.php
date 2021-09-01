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
 * Question definition class for toeicexam.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.

/**
 * Class that represents a toeicexam question.
 */
class qtype_toeicexam_question extends question_graded_automatically {

    public $answers = null;

    /**
     * returns string of place key value prepended with p, i.e. p0 or p1 etc
     *
     * @param int $key stem number
     * @return string the question-type variable name.
     */
    public function field($key) {
        return 'answer' . $key;
    }

    /**
     * Returns data to be included in the form submission.
     *
     * @return array|string.
     */
    public function get_expected_data() {
        $data = [];
        foreach (array_keys($this->answers) as $key) {
            $data['answer' . $key] = PARAM_INT;
        }
        return $data;
    }

    /**
     * Returns the data that would need to be submitted to get a correct answer.
     *
     * @return array|null Null if it is not possible to compute a correct response.
     */
    public function get_correct_response() {
        $response = array();
        foreach ($this->answers as $key => $answer) {
            $response[$this->field($key)] = $answer->answer;
        }
        return $response;
    }

    /**
     * Checks whether the user is allowed to be served a particular file.
     *
     * @param question_attempt $qa The question attempt being displayed.
     * @param question_display_options $options The options that control display of the question.
     * @param string $component The name of the component we are serving files for.
     * @param string $filearea The name of the file area.
     * @param array $args the Remaining bits of the file path.
     * @param bool $forcedownload Whether the user must be forced to download the file.
     * @return bool True if the user can access this file.
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        $isdocument = $component == 'qtype_toeicexam' && $filearea == 'audiofiles' && $args[0] == $this->id;
        $isaudio = $component == 'qtype_toeicexam' && $filearea == 'documents' && $args[0] == $this->id;
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) || $isdocument || $isaudio;
    }

    public function is_complete_response(array $responses) {
        $iscomplete = true;
        foreach (array_keys($this->answers) as $answerkey) {
            $fieldname = $this->field($answerkey);
            $iscomplete = $iscomplete && !empty($responses[$fieldname]);
        }
        return $iscomplete;
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        // TODO: Implement is_same_response() method.
    }

    public function summarise_response(array $response) {
        // TODO: Implement summarise_response() method.
    }


    /**
     * A question is gradable if at least one gap response is not blank
     *
     * @param array $response
     * @return boolean
     */
    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }

    public function get_validation_error(array $response) {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleasedraganimagetoeachdropregion', 'qtype_ddimageortext');
    }

    public function grade_response(array $responses) {
        $totalscore = 0;
        foreach (array_keys($this->answers) as $answerkey) {
            $fieldname = $this->field($answerkey);
            if (empty($responses[$fieldname])) {
                continue;
            }
            $rightanswer = $this->answers[$answerkey]->answer;
            $isrightvalue = ($responses[$fieldname] == $rightanswer) ? 1 : 0;
            $totalscore += $isrightvalue;
        }
        $fraction = $totalscore / count(array_keys($this->answers));
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }
}

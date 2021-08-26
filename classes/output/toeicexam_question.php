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
 * The toeicexam question renderer class is defined here.
 *
 * @package     qtype_toeicexam
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_toeicexam\output;
use moodle_url;
use qtype_toeicexam\utils;
use question_attempt;
use question_display_options;
use renderable;
use stdClass;
use templatable;

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for toeicexam questions.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/rendererbase.php.
 */
class toeicexam_question implements renderable, templatable {

    /**
     * @var question_attempt
     */
    private $qa;

    /**
     * @var question_display_options
     */
    private question_display_options $options;

    public function __construct(question_attempt $qa, question_display_options $options) {
        $this->qa = $qa;
        $this->options = $options;
    }
    public function export_for_template(\renderer_base $output) {
        $data = new stdClass();
        $data->pdffileurl = self::get_url_for_document($this->qa, 'document');
        $data->audiofileurl = self::get_url_for_document($this->qa, 'audiofile');
        $question = $this->qa->get_question();
        $responses = $this->qa->get_last_qt_var('answer', '');
        $data->questions = [];
        foreach ($question->answers as $value => $answer) {
            $aquestion = new stdClass();
            $aquestion->answers = [];
            $aquestion->feedback = $answer->feedback;
            for ($i = 1; $i <= utils::OPTION_COUNT; $i++) {
                $ananswer = new stdClass();
                $ananswer->label = get_string('option:' . $i, 'qtype_toeicexam');
                $ananswer->value = $i;
                $aquestion->answers[] = $ananswer;

                $aquestion->id =  $this->qa->get_qt_field_name('choice' . $value);

            }
            $aquestion->id =  $this->qa->get_qt_field_name($value);
            //$isselected = $question->is_choice_selected($response, $value);
            $data->questions[] = $aquestion;
        }
        return $data;
    }

    /**
     * Returns the URL for an image or document
     *
     * @param object $qa Question attempt object
     * @param string $filearea File area descriptor
     * @param int $itemid Item id to get
     * @return string Output url, or null if not found
     */
    protected static function get_url_for_document(question_attempt $qa, $filearea, $itemid = 0) {
        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $fs = get_file_storage();
        $itemid = $question->id;
        $componentname = $question->qtype->plugin_name();
        $draftfiles = $fs->get_area_files($question->contextid, $componentname,
            $filearea, $itemid, 'id');
        if ($draftfiles) {
            foreach ($draftfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }
                $url = moodle_url::make_pluginfile_url($question->contextid, $componentname,
                    $filearea, "$qubaid/$slot/{$itemid}", '/',
                    $file->get_filename());
                return $url->out();
            }
        }
        return null;
    }
}
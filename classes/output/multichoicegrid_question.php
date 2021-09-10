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

namespace qtype_multichoicegrid\output;

use moodle_url;
use qtype_multichoicegrid\utils;
use question_attempt;
use question_display_options;
use renderable;
use renderer_base;
use stdClass;
use templatable;

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for multichoicegrid questions.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/rendererbase.php.
 */
class multichoicegrid_question implements renderable, templatable {

    /**
     * @var question_attempt
     */
    private $qa;

    /**
     * @var question_display_options
     */
    private question_display_options $options;

    private array $truefaldisplayoptions;

    public function __construct(question_attempt $qa, question_display_options $options, array $truefaldisplayoptions) {
        $this->qa = $qa;
        $this->options = $options;
        $this->truefaldisplayoptions = $truefaldisplayoptions;
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $pdffilesurls = iterator_to_array(self::get_url_for_document($this->qa, 'document'));
        $audiofilesurl = iterator_to_array(self::get_url_for_document($this->qa, 'audio'));
        $tofileurlobjects = function($fileurl) {
            return (object) ['url' => $fileurl ];
        };
        $data->possibleanswers = [];
        for ($i = 1; $i <= utils::OPTION_COUNT; $i++) {
            $data->possibleanswers[] = get_string('option:' . $i, 'qtype_multichoicegrid');
        }
        $data->pdffiles = array_map($tofileurlobjects, $pdffilesurls);
        $data->audiofiles = array_map($tofileurlobjects, $audiofilesurl);
        $question = $this->qa->get_question();
        $data->questions = [];
        $index = 1;
        foreach ($question->answers as $answerid => $answer) {
            $aquestion = new stdClass();
            $aquestion->answers = [];
            $aquestion->feedback = $answer->feedback;
            $aquestion->index = $index++;
            $answerkey = 'answer' . $answerid;
            $aquestion->id = $this->qa->get_qt_field_name($answerkey);
            $response = $this->qa->get_last_qt_var($answerkey, '');
            $iscorrect = false;
            for ($i = 1; $i <= utils::OPTION_COUNT; $i++) {
                $ananswer = new stdClass();
                $ananswer->label = get_string('option:' . $i, 'qtype_multichoicegrid');
                $ananswer->value = $i;
                if ($response == $i) {
                    $ananswer->selected = true;
                    $iscorrect = ($response == $answer->answer) ? 1 : 0;
                }
                if ($this->options->correctness) {
                    $isrightvalue = ($response == $answer->answer) ? 1 : 0;
                    $ananswer->additionalclass = $this->truefaldisplayoptions[$isrightvalue]->additionalclass;

                }
                $aquestion->answers[] = $ananswer;

            }
            if ($this->options->correctness) {
                $ananswer->feedbackimage = $this->truefaldisplayoptions[$iscorrect]->image;
            }

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
                yield $url->out();
            }
        }
    }
}

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
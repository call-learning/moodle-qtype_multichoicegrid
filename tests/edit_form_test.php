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
 * Unit tests for the multichoicegrid edit form.
 *
 * @package   qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/multichoicegrid/edit_multichoicegrid_form.php');

/**
 * Unit tests for the multichoicegrid edit form.
 *
 * @package     qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoicegrid_edit_form_test extends advanced_testcase {
    /**
     * Test the form correctly validates the HTML allowed in items.
     */
    public function test_item_validation() {
        list($form, $category) = $this->get_form();

        $generator = $this->getDataGenerator();
        $submitteddata = test_question_maker::get_question_form_data('multichoicegrid');
        $overrides = [
            'category' => $category->id,
            'defaultmark' => -1 // Will raise an error.
        ];

        $fromform = $generator->combine_defaults_and_record((array) $submitteddata, $overrides);
        $errors = $form->validation($fromform, []);

        // For now we don't do much validation but this will need to be covered in detail if it does.
        $this->assertEquals(get_string('defaultmarkmustbepositive', 'question'),
            $errors['defaultmark']);
    }

    /**
     * Helper method.
     *
     * @return array with two elements:
     *      question_edit_form
     *      stdClass the question category.
     */
    protected function get_form() {
        $this->setAdminUser();
        $this->resetAfterTest();

        $syscontext = context_system::instance();
        $category = question_make_default_categories(array($syscontext));
        $fakequestion = new stdClass();
        $fakequestion->qtype = 'multichoicegrid';
        $fakequestion->contextid = $syscontext->id;
        $fakequestion->createdby = 2;
        $fakequestion->category = $category->id;
        $fakequestion->questiontext = 'Test question';
        $fakequestion->options = new stdClass();
        $fakequestion->options->answers = array();
        $fakequestion->formoptions = new stdClass();
        $fakequestion->formoptions->movecontext = null;
        $fakequestion->formoptions->repeatelements = true;
        $fakequestion->inputs = null;

        $form = new qtype_multichoicegrid_edit_form(new moodle_url('/'), $fakequestion, $category,
            new question_edit_contexts($syscontext));

        return [$form, $category];
    }
}

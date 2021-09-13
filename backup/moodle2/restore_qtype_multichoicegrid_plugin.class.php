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

defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information needed to restore one multichoicegrid question type plugin
 *
 * @package    qtype_multichoicegrid
 * @subpackage backup-moodle2
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_multichoicegrid_plugin extends restore_qtype_extrafields_plugin {
    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure() {
        $paths = parent::define_question_plugin_structure();
        // Add own qtype stuff.
        $elename = 'part';
        $elepath = $this->get_pathfor('/parts/part'); // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);
        $elename = 'doc';
        $elepath = $this->get_pathfor('/docs/doc'); // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);
        return $paths;
    }
    /**
     * Process the qtype/multichoicegrid element
     *
     * @param mixed $data
     */
    public function process_multichoicegrid($data) {
        $this->really_process_extra_question_fields($data);
    }
    /**
     * Process the parts element.
     *
     * @param array|object $data Drag and drop drops data to work with.
     */
    public function process_part($data) {
        $this->do_process_element('parts', $data);
    }

    /**
     * Process the docs element.
     *
     * @param array|object $data Drag and drop drops data to work with.
     */
    public function process_doc($data) {
        $this->do_process_element('docs', $data);
    }

    /**
     * Do the processing of docs and parts
     *
     * @param string $subelementname
     * @param object $data
     * @throws coding_exception
     */
    protected function do_process_element($subelementname, $data) {
        $qtypeobj = question_bank::get_qtype($this->pluginname);
        $qtypename = $qtypeobj->name();

        $prefix = 'qtype_'.$qtypename.'_'.$subelementname;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        if ($questioncreated) {
            global $DB;
            $data->questionid = $newquestionid;
            // Insert record.
            // TODO: use the persistent.
            $newitemid = $DB->insert_record($prefix, $data);
            // Create mapping (there are files and states based on this).
            $this->set_mapping("{$prefix}", $oldid, $newitemid);
        }
    }
}

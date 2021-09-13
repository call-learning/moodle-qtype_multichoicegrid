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
 * Multiple choice grid
 *
 * @package     qtype_multichoicegrid
 * @copyright   2021 Laurent David <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the multiple choice grid type.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_multichoicegrid_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2021082401) {

        // Define table qtype_multichoicegrid to be renamed to NEWNAMEGOESHERE.
        $table = new xmldb_table('qtype_mcgrid_options');

        // Launch rename table for qtype_multichoicegrid.
        $dbman->rename_table($table, 'qtype_multichoicegrid');

        // Multichoicegrid savepoint reached.
        upgrade_plugin_savepoint(true, 2021082401, 'qtype', 'multichoicegrid');
    }

    if ($oldversion < 2021082402) {

        // Define table qtype_multichoicegrid_docs to be created.
        $table = new xmldb_table('qtype_multichoicegrid_docs');

        // Adding fields to table qtype_multichoicegrid_docs.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table qtype_multichoicegrid_docs.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('questionid', XMLDB_KEY_FOREIGN, ['questionid'], 'question', ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for qtype_multichoicegrid_docs.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Multichoicegrid savepoint reached.
        upgrade_plugin_savepoint(true, 2021082402, 'qtype', 'multichoicegrid');
    }

    if ($oldversion < 2021082403) {

        // Define field startnumbering to be added to qtype_multichoicegrid.
        $table = new xmldb_table('qtype_multichoicegrid');

        $field = new xmldb_field('startnumbering', XMLDB_TYPE_INTEGER, '10', null, null, null, '1', 'shownumcorrect');
        // Conditionally launch add field startnumbering.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Multichoicegrid savepoint reached.
        upgrade_plugin_savepoint(true, 2021082403, 'qtype', 'multichoicegrid');
    }

    if ($oldversion < 2021082404) {

        // Define table qtype_multichoicegrid_parts to be created.
        $table = new xmldb_table('qtype_multichoicegrid_parts');

        // Adding fields to table qtype_multichoicegrid_parts.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('start', XMLDB_TYPE_INTEGER, '10', null, null, null, '1');
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table qtype_multichoicegrid_parts.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('questionid', XMLDB_KEY_FOREIGN, ['questionid'], 'question', ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for qtype_multichoicegrid_parts.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Multichoicegrid savepoint reached.
        upgrade_plugin_savepoint(true, 2021082404, 'qtype', 'multichoicegrid');
    }

    return true;
}

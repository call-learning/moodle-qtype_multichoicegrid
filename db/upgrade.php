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

    return true;
}

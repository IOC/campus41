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
 * @package    local_materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_materials_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    $result = true;

    // Add a new column newcol to the mdl_myqtype_options
    if ($result && $oldversion < 2013100801) {
         // Rename field path on table local_materials to sources.
        $table = new xmldb_table('local_materials');
        $field = new xmldb_field('path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'courseid');

        // Launch rename field path.
        $dbman->rename_field($table, $field, 'sources');

        // Local savepoint reached.
        upgrade_plugin_savepoint(true, 2013100801, '', 'local');
    }

    return $result;
}


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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/mod/fct/lib.php');


function fct_get_sql_quaderns($orderby = '') {
    $sql = "SELECT q.id AS id,"
        . " CONCAT(ua.firstname, ' ', ua.lastname) AS alumne,"
        . " q.nom_empresa AS empresa,"
        . " c.nom AS cicle_formatiu,"
        . " CONCAT(uc.firstname, ' ', uc.lastname) AS tutor_centre,"
        . " CONCAT(ue.firstname, ' ', ue.lastname) AS tutor_empresa,"
        . " q.estat AS estat,"
        . " q.data_final AS data_final"
        . " FROM {fct_quadern} q"
        . " JOIN {fct_cicle} c ON q.cicle = c.id"
        . " JOIN {user} ua ON q.alumne = ua.id"
        . " LEFT JOIN {user} uc ON q.tutor_centre = uc.id"
        . " LEFT JOIN {user} ue ON q.tutor_empresa = ue.id"
        . " WHERE q.cicle = :cicle"
        . " AND q.alumne = :alumne";

    if (!empty($orderby)) {
        $sql .= " ORDER BY " . $orderby;
    }
    return $sql;
}

function fct_ultim_quadern($alumne, $cicle) {
    global $DB;

    $sql = fct_get_sql_quaderns('q.data_final');
    $params = array(
        'cicle' => $cicle,
        'alumne' => $alumne,
    );

    $quaderns = array();
    $records = $DB->get_records_sql($sql, $params);
    foreach ($records as $record) {
        $quaderns[] = new fct_quadern_base($record->id);
    }

    return array_pop($quaderns);
}

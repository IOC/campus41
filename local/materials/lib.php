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
 * Materials lib .
 *
 * @package    local_materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function search_courses($searchquery = '') {
    if (!empty($searchquery)) {
        $searchcoursesparams = array();
        $searchcoursesparams['search'] = $searchquery;
        $courses = core_course_category::search_courses($searchcoursesparams);
        return $courses;
    } else {
        return false;
    }
}

function get_materials($searchquery, $page) {

    global $DB;

    $materials = array();
    $params = array();

    if (empty($searchquery)) {
        $materials = $DB->get_records('local_materials', array(), '', '*', $page * PAGENUM, PAGENUM);
        $total = $DB->count_records('local_materials');
        return array('records' => $materials, 'total' => $total);
    }

    if ($courses = search_courses($searchquery)) {
        $in = '(';
        foreach ($courses as $course) {
            $in .= $course->id.',';
        }
        $in = rtrim($in, ',').')';
        $wherecondition = "courseid IN $in";
    } else {
        return array('records' => array(), 'total' => 0);
    }

    $countfields = "SELECT COUNT(1)";
    $fields = 'SELECT *';
    $sql = " FROM {local_materials}";

    if (!empty($wherecondition)) {
        $sql .= " WHERE $wherecondition";
    }

    $materials = $DB->get_records_sql($fields . $sql, $params, $page * PAGENUM, PAGENUM);
    $total = $DB->count_records_sql($countfields . $sql, $params);

    return array('records' => $materials, 'total' => $total);
}

function create_category_list($categoryid) {
    $list = core_course_category::make_categories_list();
    $select = new single_select(new moodle_url('./edit.php', array()), 'categoryid', $list, $categoryid, null, 0);
    $select->nothing = array();
    $select->set_label(get_string('isactive', 'filters'), array('class' => 'accesshide'));
    return $select;
}

function save_serialized_sources($context, $material) {
    global $DB;

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_materials', 'attachment', $material->id, "timemodified", false);
    $sources = array();
    foreach ($files as $file) {
        $sources[] = $file->get_source();
    }
    $material->sources = serialize($sources);

    $DB->update_record('local_materials', $material);
}

function make_secret_url($path) {
    global $CFG;

    $time = sprintf("%08x", time());
    $token = md5($CFG->local_materials_secret_token.'/'.$path.$time);
    $url = $CFG->local_materials_secret_url.'/'.$token.'/'.$time.'/'.$path;
    return $url;
}
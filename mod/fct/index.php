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
 * This file contains the forms to create and edit an instance of fct module
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);           // Course ID

$PAGE->set_url('/mod/fct/index.php', array('id' => $id));

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('Course ID is incorrect');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');
add_to_log($course->id, 'fct', 'view all', "index.php?id=$course->id", '');


// Get all required stringsfct

$strfcts = get_string('modulenameplural', 'fct');
$strfct  = get_string('modulename', 'fct');

// Print the header
$PAGE->set_title($strfcts);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strfcts);
echo $OUTPUT->header();


// Get all the appropriate data

if (! $fcts = get_all_instances_in_course('fct', $course)) {
    notice(get_string('thereareno', 'moodle', $strfcts), "../../course/view.php?id=$course->id");
}


$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_fast_modinfo($course->id)->get_section_info_all();
}

// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic = get_string('topic');

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strname);
    $table->align = array ('left', 'left', 'left');
}

foreach ($fcts as $fct) {
    if (!$fct->visible) {
        // Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id='.$fct->coursemodule.'">'.format_string($fct->name).'</a>';
    } else {
        // Show normal if the mod is visible
        $link = '<a href="view.php?id='.$fct->coursemodule.'">'.format_string($fct->name).'</a>';
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($fct->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo $OUTPUT->heading($strfcts);
echo "<br />";
echo html_writer::table($table);

echo $OUTPUT->footer();

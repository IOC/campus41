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
 * @package mod_fpdquadern
 * @copyright 2013 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

namespace mod_fpdquadern;

require_once('../../config.php');

$id = required_param('id', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course);

$context = \context_course::instance($course->id);
require_capability('mod/fpdquadern:view', $context);

$event = \mod_fpdquadern\event\course_module_instance_list_viewed::create(array(
    'context' => \context_course::instance($course->id)
));
$event->trigger();

$PAGE->set_url('/mod/fpdquadern/index.php', array('id' => $id));
$PAGE->set_pagelayout('incourse');
$strplural = get_string("modulenameplural", "fpdquadern");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

$table = new \html_table();
$table->head  = array('Nom', 'Descripció');
$table->align = array('left', 'left');

$quaderns = $DB->get_records('fpdquadern', array('course' => $course->id));
$modinfo = get_fast_modinfo($course);
$capabilities = array(
    'mod/fpdquadern:alumne',
    'mod/fpdquadern:professor',
    'mod/fpdquadern:admin',
    'mod/fpdquadern:tutor',
);

foreach ($modinfo->instances['fpdquadern'] as $id => $cm) {
    if (!$cm->uservisible or !isset($quaderns[$id])) {
        continue;
    }
    $context = \context_module::instance($cm->id);
    if (!has_any_capability($capabilities, $context)) {
       continue;
    }
    $name = format_string($quaderns[$id]->name);
    $style = $cm->visible ? '': 'class="dimmed"';
    $link = "<a href=\"view.php?id=$cm->id\" $style>$name</a>";
    $intro = format_module_intro('fpdquadern', $quaderns[$id], $cm->id);
    $table->data[] = array($link, $intro);
}

echo \html_writer::table($table);

echo $OUTPUT->footer();

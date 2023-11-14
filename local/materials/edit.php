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
 * Materials related managament.
 *
 * @package    local
 * @subpackage materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require($CFG->dirroot.'/local/materials/edit_form.php');
require_once('lib.php');

require_login();

$id        = optional_param('id', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 1, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);

if ($id) {
    $material = $DB->get_record('local_materials', array('id' => $id));
} else {
    $material = new stdClass();
    $material->id = null;
}

$returnurl = new moodle_url('/local/materials/index.php');

$context = context_system::instance();
require_capability('local/materials:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/materials/edit.php', array('id' => $id, 'delete' => $delete, 'confirm' => $confirm));
$PAGE->set_context($context);

if ($delete and $material->id) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $DB->delete_records('local_materials', array('id' => $material->id));
        redirect($returnurl);
    }
    $strheading = get_string('delmaterial', 'local_materials');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('./edit.php', array('id' => $material->id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    if ($course = $DB->get_record('course', array('id' => $material->courseid))) {
        $messageparams = new stdClass;
        $messageparams->sources = implode(',', unserialize($material->sources));
        $messageparams->sources = str_replace('/', '', $messageparams->sources);
        $messageparams->course = $course->fullname;
    }
    $message = get_string('delconfirm', 'local_materials', $messageparams);
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$maxfiles = 50;
$maxbytes = 0;
$attachmentoptions = array('subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes);
$material = file_prepare_standard_filemanager($material, 'attachment', $attachmentoptions, $context, 'local_materials', 'attachment', $material->id);

if (isset($material->id)) {
    $strheading = get_string('edit');
} else {
    $strheading = get_string('add');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('plugin_pluginname', 'local_materials'));
$PAGE->navbar->add($strheading, new moodle_url('/local/materials/edit.php',
    array('id' => $id, 'delete' => $delete, 'confirm' => $confirm)));

if ($categoryid) {
    require_once($CFG->dirroot . '/course/classes/category.php');
    $category = core_course_category::get($categoryid);
    // Subcategories courses must be showed
    $courses = $category->get_courses(array('recursive' => true));
} else {
    $courses = $DB->get_records('course', array());
}

$courseselect = array();
foreach ($courses as $course) {
    $courseselect[$course->id] = $course->fullname;
}

$editform = new material_edit_form(null, array('data' => $material,
                                               'categoryid' => $categoryid,
                                               'courses' => $courseselect,
                                               'attachmentoptions' => $attachmentoptions));

if ($editform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $editform->get_data()) {

    if ($data->id) {
        $material->courseid = $data->courseid;
        $DB->update_record('local_materials', $material);
    } else {
        $material->courseid = $data->courseid;
        $material->id = $DB->insert_record('local_materials', $material);
    }

    file_postupdate_standard_filemanager($material, 'attachment', $attachmentoptions, $context, 'local_materials', 'attachment', $material->id);
    save_serialized_sources($context, $material);

    redirect(new moodle_url('/local/materials/index.php', array()));
}
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);

if (!isset($material->id)) {
    echo html_writer::start_tag('div',  array('style' => 'text-align:center'));
    echo $OUTPUT->render(create_category_list($categoryid));
    echo html_writer::end_tag('div');
}

echo $editform->display();
echo $OUTPUT->footer();


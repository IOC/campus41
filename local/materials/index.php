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
 * Materials main page.
 *
 * @package    local_materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define("PAGENUM", "20");

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');

require_login();

$categoryid = optional_param('categoryid', 1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);

$context = context_system::instance();
require_capability('local/materials:manage', $context);

$strheading = get_string('plugin_pluginname', 'local_materials');

$params = array('page' => $page);
if (!empty($search)) {
    $params['search'] = $search;
}
$baseurl = new moodle_url('/local/materials/index.php', $params);

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url(new moodle_url('/local/materials/index.php'));
$PAGE->set_title($strheading);
$PAGE->add_body_class('path-admin');
$PAGE->set_heading($COURSE->fullname);
$PAGE->requires->css('/local/materials/styles.css');
$PAGE->navbar->add(get_string('plugin_pluginname', 'local_materials'));
$PAGE->navbar->add($strheading, new moodle_url('/local/materials/index.php'));

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);

$materials = get_materials($search, $page);

$ouput = $PAGE->get_renderer('local_materials');
echo $ouput->search_form($search);

echo html_writer::start_div('local_materials');
echo $OUTPUT->paging_bar($materials['total'], $page, PAGENUM, $baseurl);
echo html_writer::end_div();

echo $ouput->materials_table($materials);

echo html_writer::start_div('local_materials');
echo $OUTPUT->paging_bar($materials['total'], $page, PAGENUM, $baseurl);
echo html_writer::end_div();

echo $OUTPUT->single_button(new moodle_url('./edit.php', array('categoryid' => $categoryid)), get_string('add'));
echo $OUTPUT->footer();


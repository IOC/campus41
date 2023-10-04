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
 * This file is the entry point to the fct module. All pages are rendered from here
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once('lib.php');
require_once($CFG->dirroot . '/mod/fct/lib.php');
require_once($CFG->dirroot . '/mod/fct/locallib.php');
require_once('classes/fct_base.php');

$cmid = required_param('cmid', PARAM_INT);    // Course Module ID
$id = optional_param('id', false, PARAM_INT);    // Object ID
$page = optional_param('page', 'quadern', PARAM_ALPHAEXT);
$subpage = optional_param('subpage', false, PARAM_ALPHAEXT);
$delete = optional_param('delete', false, PARAM_BOOL);
$deleteall = optional_param('deleteall', false, PARAM_BOOL);
$confirm   = optional_param('confirm', 0, PARAM_BOOL);
$quadern = optional_param('quadern', false, PARAM_INT);
$valoracio = optional_param('valoracio', false, PARAM_ALPHAEXT);
$qualificaciotype = optional_param('qualificaciotype', 'parcial', PARAM_ALPHAEXT);
$uuid = optional_param('uuid', false, PARAM_RAW);
$dia = optional_param('dia', false, PARAM_ALPHAEXT);
$hora_inici = optional_param('hora_inici', false, PARAM_RAW);
$hora_final = optional_param('hora_final', false, PARAM_RAW);
$returnpage = optional_param('returnpage', false, PARAM_ALPHAEXT);

if (!$cm = get_coursemodule_from_id('fct', $cmid)) {
    print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('course is misconfigured');
}

require_course_login($course, false, $cm);

if (!$fct = $DB->get_record('fct', array('id' => $cm->instance))) {
    print_error('course module is incorrect');
}

if ($subpage) {
    $class = 'fct_'.$subpage;
} else {
    $class = 'fct_'.$page;
}

require_once('classes/'.$class.'.php');

if (!$id && $quadern && ($page != 'quadern_activitat' && $page != 'quadern_quinzena')) {
    $id = $quadern;
}

$record = new stdClass;
$record->fct = $fct->id;

if ($quadern) {
    $record->quadern = $quadern;
}

if ($qualificaciotype) {
    $record->qualificaciotype = $qualificaciotype;
}

if ($valoracio) {
    $record->valoracio = $valoracio;
}

if ($id) {
    $class = new $class($id);
    $class->qualificaciotype = $qualificaciotype;
    $class->quadern = $quadern;
    $class->valoracio = $valoracio;
    $class->fct = isset($class->fct) ? $class->fct : $fct->id;
} else {
    $class = new $class($record);
}

if ($delete) {
    $class->checkpermissions('delete');
} else if ($page == 'quadern_quinzena' and !$id) {
      $class->checkpermissions('add');
} else {
    $class->checkpermissions('edit');
}

if ($page == 'quadern_valoracio' and $subpage == 'quadern_qualificacio' and $qualificaciotype == 'global') {
    if ($lastquadern = fct_ultim_quadern($class->alumne, $class->cicle)) {
        if ($class->id != $lastquadern->id) {
            $params = array(
                'id' => $cm->id,
                'quadern' => $class->id,
                'page' => 'quadern_qualificacio',
                'qualificaciotype' => 'global',
            );
            $url = new moodle_url('/mod/fct/view.php', $params);
            redirect($url);
        }
    }
}


$context = context_module::instance($cm->id);
$url = new moodle_url('/mod/fct/edit.php', array('id' => $cmid, 'id' => $id, 'page' => $page, 'subpage' => $subpage, 'quadern' => $quadern));
if (isset($class->returnurl)) {
    $returnurl = $class->returnurl;
} else {
    $returnurl = new moodle_url('view.php', array('id' => $cmid,
                                                'page' => $returnpage ? $returnpage : $page,
                                                'subpage' => $subpage,
                                                'quadern' => $quadern,
                                                'valoracio' => $valoracio,
                                                'qualificaciotype' => $qualificaciotype,
                                                'uuid' => $uuid,
                                                'dia' => $dia,
                                                'hora_inici' => $hora_inici,
                                                'hora_final' => $hora_final));
}

$PAGE->set_cm($cm, $course, $fct);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(format_string($fct->name));
$PAGE->set_heading(format_string($fct->name));
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/fct/client.js');
$PAGE->requires->css('/mod/fct/styles.css');

if ($quadern) {
    if ($alumne = $DB->get_record('user', array('id' => $class->alumne))) {
        $PAGE->navbar->add(fullname($alumne));
    }
}

if ($delete &&  ($id || $uuid || $deleteall)) {
    $PAGE->url->param('delete', 1);
    $class->checkpermissions('delete');

    if ($confirm and confirm_sesskey()) {
        if ($uuid) {
            $params = new stdClass;
            $params->uuid = $uuid;
            $params->dia = $dia;
            $params->hora_inici = $hora_inici;
            $params->hora_final = $hora_final;
        } else {
            $params = false;
        }
        if ($deleteall) {
            $class->deleteall($fct->id, $quadern);
        }

        if (!$class->delete($params)) {
              $nodeletemessage = $class->no_delete_message();
              echo $OUTPUT->header();
              echo $OUTPUT->heading($strheading);
              echo $OUTPUT->box($nodeletemessage, 'generalbox', 'notice');
              echo $OUTPUT->continue_button($returnurl);
              echo $OUTPUT->footer();
              die;
        }
        redirect($returnurl);
    }

    echo $OUTPUT->header();
    $yesurl = new moodle_url('./edit.php', array('cmid' => $cmid,
                                                 'id' => $id,
                                                 'page' => $page,
                                                 'subpage' => $subpage,
                                                 'quadern' => $quadern,
                                                 'valoracio' => $valoracio,
                                                 'uuid' => $uuid,
                                                 'dia' => $dia,
                                                 'hora_inici' => $hora_inici,
                                                 'hora_final' => $hora_final,
                                                 'delete' => 1,
                                                 'deleteall' => $deleteall,
                                                 'confirm' => 1,
                                                 'sesskey' => sesskey()));

    $message = $class->delete_message($deleteall);
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$data = new stdClass;
$data->cmid = $cmid;
$data->page = $page;
$data->fct = $fct->id;
$data->qualificaciotype = $qualificaciotype;
$data->valoracio = $valoracio;

if ($quadern) {
    $data->quadern = $quadern;
}

$data->subpage = $subpage;
$data->returnpage = $returnpage;

$objectdata = $class->get_object_data();

$data = (object)array_merge((array)$objectdata, (array)$data);

$class->prepare_form_data($data);

$editform = $class->get_edit_form(array('data' => $data));

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($formdata = $editform->get_data()) {
    if (!$formdata->id) {
        $class->insert($formdata);
    } else {
        $class->set_data($formdata);
        $class->update();
    }
    redirect($returnurl);
}

echo $OUTPUT->header();

$tab = $class->tabs($cmid, 'edit');

$output = $PAGE->get_renderer('mod_fct');

$output->print_tabs($tab);

$fctclass = '';
if ($page == 'quadern_valoracio_activitat' or ($page == 'quadern_valoracio' and $subpage != 'quadern_qualificacio')) {
    $fctclass = ' fct_' . $page;
}

echo $OUTPUT->box_start('boxaligncenter boxwidthwide' . $fctclass);
$editform->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
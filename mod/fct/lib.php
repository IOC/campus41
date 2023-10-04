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

require_once(dirname(__FILE__) . '/../../config.php');
require_once('classes/fct_instance.php');
require_once('classes/fct_quadern_base.php');

function fct_add_instance($data) {
    $fct = new stdClass;
    $fct->course = $data->course;
    $fct->name = $data->name;
    $fct->intro = $data->intro;
    $fct->timecreated = time();
    $fct->timemodified = time();
    $fct->objecte = '';

    $fctinstance = new fct_instance($fct);
    $fctinstance->add();
    return $fctinstance->id;
}

function fct_update_instance($data) {
    global $DB;

    $fctrecord = $DB->get_record('fct', array('id' => $data->instance));

    $fctinstance = new fct_instance($fctrecord);
    $fctinstance->name = $data->name;
    $fctinstance->intro = $data->intro;
    $fctinstance->timemodified = time();

    $fctinstance->add();

    return true;
}

function fct_delete_instance($id) {global $CFG;

    global $DB;

    $fctrecord = $DB->get_record('fct', array('id' => $id));
    $fctinstance = new fct_instance($fctrecord);
    $fctinstance->delete();

    return true;
}

function fct_string($identifier, $a=null) {
    if (is_array($a)) {
        $a = (object) $a;
    }
    return get_string($identifier, 'fct', $a);
}

function fct_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    if ($filearea !== 'inssimage' && $filearea !== 'targetaimage') {
        return false;
    }

    require_login($course, true, $cm);

    $itemid = array_shift($args); // The first item in the $args array.

    $quadern = new fct_quadern_base($itemid);

    if (!$quadern->checkpermissions('image')) {
        return false;
    }

    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_fct', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

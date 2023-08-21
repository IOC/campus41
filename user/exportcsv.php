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
 * This file is part of the User section Moodle
 *
 * @author Marc Català mcatala@ioc.cat
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */
// @PATCH IOC016: new action expert CSV
require_once('../config.php');

$id = required_param('id', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_login($course);

$context = context_course::instance($course->id);

if (!has_capability('moodle/grade:manage', $context) or !has_capability('moodle/user:viewhiddendetails', $context)) {
    return false;
}

require_sesskey();

if ($post = data_submitted()) {
    foreach ($post as $k => $v) {
        if (preg_match('/^user(\d+)$/', $k, $m)) {
            $users[] = $m[1];
        }
    }
}

if (empty($users)) {
    return false;
}

$exportdata = array(
        get_string('firstname'),
        get_string('lastname'),
        get_string('username'),
        get_string('roles'),
        get_string('groups'),
        get_string('email')
);

$downloadfilename = clean_filename($course->shortname . '-' . $course->fullname . '-' . get_string('users'));
$csvexport = new csv_export_writer();
$csvexport->set_filename($downloadfilename);
$csvexport->add_data($exportdata);

list($sqlusers, $params) = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED);

$sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.username, u.email
          FROM {user} u
          JOIN {role_assignments} ra ON ra.userid = u.id
          JOIN {role} r ON r.id = ra.roleid
          JOIN {context} ctx on ctx.id = ra.contextid
         WHERE ctx.instanceid = :courseid
           AND ctx.contextlevel = 50
           AND u.id $sqlusers
      ORDER BY u.lastname";

$params['courseid'] = $course->id;
$rs = $DB->get_recordset_sql($sql, $params);

$getnames = function($var) {
    $data = array();
    foreach ($var as $value) {
        array_push($data, $value->name);
    }
    return implode(' ', $data);
};

foreach ($rs as $user) {
    $exportdata = array();
    $groups = groups_get_all_groups($course->id, $user->id);
    $roles = get_user_roles($context, $user->id);
    $exportdata[] = $user->firstname;
    $exportdata[] = $user->lastname;
    $exportdata[] = $user->username;
    $exportdata[] = $getnames($roles);
    $exportdata[] = $getnames($groups);
    $exportdata[] = $user->email;
    $csvexport->add_data($exportdata);
}
$rs->close();

$csvexport->download_file();
// Fi

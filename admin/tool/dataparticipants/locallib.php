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
 * @package    tool
 * @subpackage dataparticipants
 * @copyright  2019 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('PAGENUM', 20);
define('WEEKLY', 1);
define('QUARTERLY', 2);

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/course/classes/category.php');
require_once($CFG->libdir . '/xtecmail/lib.php');

class tool_dataparticipants_utils {
    /**
     * Return all corses from site
     * @return array courses from site
     */
    public static function get_courses() {

        $coursenames = array();

        if ($courses = core_course_category::get(0)->get_courses(array('recursive' => true, 'sort' => array('shortname' => 1)))) {
            foreach ($courses as $course) {
                if ($course->id == SITEID) {
                    continue;
                }
                if (!empty($course->visible) || has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {
                    $coursenames[$course->id] = $course->shortname . ' - ' . $course->fullname;
                }
            }
        }
        return $coursenames;
    }

    /**
     * Generate one zip file with all participants data from task
     * @param  object $task
     * @return string path to zip file
     */
    public function generate_zip($task) {
        global $DB;

        list($sqlid, $params) = $DB->get_in_or_equal(explode(',', $task->courses));
        $courses = $DB->get_records_select('course', "id {$sqlid}", $params, '', 'id, shortname, fullname');
        if (count($courses) < 1) {
            return false;
        }

        $roles = explode(',', $task->roles);
        if (count($roles) < 1) {
            return false;
        }

        foreach ($courses as $course) {
            if ($pathname = self::generate_csv($course, $roles)) {
                $filesforzipping[basename($pathname)] = $pathname;
            }
        }

        if ($zipfile = $this->pack_files($filesforzipping)) {
            // Delete tempfiles.
            foreach ($filesforzipping as $fileforzipping) {
                unlink($fileforzipping);
            }
            return $zipfile;
        }
        return false;
    }

    /**
     * Send an email with one attachment
     * @param  object $task
     * @param  string $zipfile
     */
    public function send_email($task, $zipfile) {
        global $CFG;

        $xm = new xtecmail($CFG->local_xtecmail_app,
                            $CFG->local_xtecmail_sender,
                            $CFG->local_xtecmail_env);
                            
        $stringcourses = $this->get_course_names($task->courses);
       
        $mail = array(
            'to' => array($task->email),
            'from' => $CFG->noreplyaddress,
            'subject' => get_string('emailsubject', 'tool_dataparticipants'),
            'body' => get_string('emailcontent', 'tool_dataparticipants', $stringcourses),
            'contenttype' => 'text/plain',
        );
        $filename = 'tool_dataparticipants_' . str_replace(',', '_', $task->courses);
        if (strlen($filename) > 50) {
            $filename = core_text::substr($filename, 0, 50);
        }
        $filename .= '.zip';
        $attachments = array();
        $attachments[] = array(
            'filename' => $filename,
            'content' => file_get_contents($zipfile),
            'mimetype' => mimeinfo('type', '.zip')
        );
        try {
            $xm->send($mail['to'], array(), array(), $mail['from'], $mail['subject'],
                      $mail['body'], $mail['contenttype'], $attachments);
            $this->update_timesend($task, time());
        } catch (xtecmailerror $e) {
            mtrace($e->getMessage());
        }
        @unlink($zipfile);
    }

    /**
     * Update timesend field
     * @param  object $task
     * @param  int $now
     */
    public function update_timesend($task, $now) {
        global $DB;

        $DB->set_field('tool_dataparticipants', 'timesend', $now, array('id' => $task->id));
    }

    /**
     * Generate csv files with participants data
     * @param  object $course
     * @param  array $roles
     * @return string path to csv file
     */
    private static function generate_csv($course, $roles) {
        global $CFG, $DB;

        $context = context_course::instance($course->id);

        $exportdata = array(
            get_string('firstname'),
            get_string('lastname'),
            get_string('username'),
            get_string('roles'),
            get_string('groups'),
            get_string('email')
        );

        $customfilename = clean_filename($course->shortname . '-' . $course->fullname . '-' . get_string('users'));
        $csvexport = new csv_export_writer();
        $csvexport->set_filename($customfilename);
        $csvexport->add_data($exportdata);

        list($sqlroles, $params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED);

        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.username, u.email
                  FROM {user} u
                  JOIN {role_assignments} ra ON ra.userid = u.id
                  JOIN {role} r ON r.id = ra.roleid
                  JOIN {context} ctx on ctx.id = ra.contextid
                 WHERE ctx.instanceid = :courseid
                   AND ctx.contextlevel = 50
                   AND r.id $sqlroles
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

        make_temp_directory('dataparticipants');
        $tempfile = $CFG->tempdir . '/dataparticipants/' . $customfilename . '.csv';
        if (!$fp = fopen($tempfile, 'w+b')) {
            mtrace(get_string('cannotsavedata', 'error'));
            @unlink($tempfile);
            return false;
        }
        $content = $csvexport->print_csv_data(true);
        fwrite($fp, $content);
        fclose($fp);

        return $tempfile;
    }

    /**
     * Create a zip file
     * @param  array $filesforzipping [description]
     * @return string path to zip file
     */
    private function pack_files($filesforzipping) {
        global $CFG;

        $tempzip = tempnam($CFG->tempdir . '/', 'dataparticipants_');
        $zipper = new zip_packer();
        if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
            return $tempzip;
        }
        return false;
    }

    /**
     * Return a string with shortname and fullname from all courses.
     * @param  string $courses
     * @return string comma separated with shortname - fullname from all courses.
     */
    public function get_course_names($courses) {
        global $DB;

        $coursenames = array();

        list($sqlid, $params) = $DB->get_in_or_equal(explode(',', $courses));
        $courses = $DB->get_records_select('course', "id {$sqlid}", $params, '', 'shortname, fullname');

        foreach ($courses as $course) {
            $coursenames[] = $course->shortname . ' - ' . $course->fullname;
        }

        $content = implode("\n", $coursenames);

        return $content;
    }
}

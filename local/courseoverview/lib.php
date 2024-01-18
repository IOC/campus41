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
 * Library functions for local_courseview.
 *
 * @package     local_courseoverview
 * @author      Toni Ginard <toni.ginard@ticxcat.cat>
 * @copyright   2022 Departament d'Educació - Generalitat de Catalunya
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Calculates the number of unread messages, pending tasks and pending
 * quizzes in all user courses.
 *
 * @throws moodle_exception
 * @throws coding_exception
 * @copyright   2022 Departament d'Educació - Generalitat de Catalunya
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package     local_courseoverview
 * @author      Toni Ginard <toni.ginard@ticxcat.cat>
 */
function local_courseoverview_before_footer() {

    global $PAGE, $USER, $CFG, $OUTPUT;

    // Check if the current page is the dashboard page.
    if ($PAGE->pagetype === 'my-index') {

        $coursesenrolled = enrol_get_all_users_courses($USER->id, true);

        $data = [];

        foreach ($coursesenrolled as $course) {
            $coursedata = [
                'course_id' => $course->id,
            ];

            // Generate the HTML code for the course. This code includes img tags and numerical information and
            // will be the data in the javascript script. The preg statement is used to remove tabs, new lines and
            // repeated spaces from the HTML code, so the result is one line of code.
            $data[$course->id] = preg_replace(
                '/\s+/',
                ' ',
                $OUTPUT->render_from_template('local_courseoverview/courseoverview', ['data' => $coursedata])
            );
        }

        // Build an array of courses to be passed to the javascript script. Each item contains the data of a course.
        $jscourses = [];
        foreach ($data as $courseid => $coursedata) {
            $jscourses[] = ['course_id' => $courseid, 'data' => $coursedata];
        }

        // Combine the data of the courses with the javascript code to generate the final script.
        $javascript = $OUTPUT->render_from_template('local_courseoverview/js', ['courses' => $jscourses, 'ajax_url' => $CFG->wwwroot.'/local/courseoverview/ajax.php?courseid=', 'loading_src' => $CFG->wwwroot.'/local/courseoverview/loading.gif']);

        // Add the javascript script to the page.ajax_url
        $PAGE->requires->js_init_code($javascript, true);
    }
}

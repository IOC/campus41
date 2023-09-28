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
 * Name of module forum.
 */
const MODULE_FORUM_NAME = 'forum';

/**
 * Name of module assign.
 */
const MODULE_ASSIGN_NAME = 'assign';

/**
 * Name of module quiz.
 */
const MODULE_QUIZ_NAME = 'quiz';

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

        // Load classes.
        include_once($CFG->dirroot . '/mod/forum/lib.php');
        include_once($CFG->dirroot . '/mod/assign/locallib.php');
        include_once($CFG->dirroot . '/mod/quiz/accessmanager.php');
        include_once($CFG->dirroot . '/mod/quiz/attemptlib.php');

        foreach ($coursesenrolled as $course) {

            $unreadforums = get_pending_forums($USER->id, $course);

            if ($unreadforums['totalunread'] > 0) {
                $forumsdetails = format_unreadforums($unreadforums);
            }

            $coursecontext = \context_course::instance($course->id);

            $isstudent = check_role($USER->id, $coursecontext, 'student');
            $isteacher = check_role($USER->id, $coursecontext, 'teacher') ||
                check_role($USER->id, $coursecontext, 'editingteacher');

            if ($isstudent || $isteacher) {
                // Get all assignments and quizzes here to avoid calling this function twice
                //  in case of users with multiple roles.
                $assignments = get_all_instances_in_course(MODULE_ASSIGN_NAME, $course, $USER->id);
                $quizzes = get_all_instances_in_course(MODULE_QUIZ_NAME, $course, $USER->id);
            }

            if ($isstudent) {
                $studentpendingassign = count_student_pending_assign($course, $assignments);
                $studentpendingquiz = count_student_pending_quiz($USER->id, $quizzes);
            }

            if ($isteacher) {
                $teacherpendingassign = count_teacher_pending_assign($course, $assignments);
                $teacherpendingquiz = count_teacher_pending_quiz($course, $USER->id, $quizzes);
            }

            $coursedata = [
                'course_id' => $course->id,
                'is_student' => $isstudent,
                'is_teacher' => $isteacher,
                'unread_forums' => $unreadforums['totalunread'],
                'is_unread_forums' => (bool)$unreadforums['totalunread'],
                'forums_details' => $forumsdetails ?? '',
                'student_pending_assign' => $studentpendingassign ?? 0,
                'is_student_pending_assign' => (bool)($studentpendingassign ?? 0),
                'student_pending_quiz' => $studentpendingquiz ?? 0,
                'is_student_pending_quiz' => (bool)($studentpendingquiz ?? 0),
                'teacher_pending_assign' => $teacherpendingassign ?? 0,
                'is_teacher_pending_assign' => (bool)($teacherpendingassign ?? 0),
                'teacher_pending_quiz' => $teacherpendingquiz ?? 0,
                'is_teacher_pending_quiz' => (bool)($teacherpendingquiz ?? 0),
                'url_assign' => new \moodle_url('/mod/assign/index.php', ['id' => $course->id]),
                'url_quiz' => new \moodle_url('/mod/quiz/index.php', ['id' => $course->id]),
            ];

            // Generate the HTML code for the course. This code includes img tags and numerical information and
            // will be the data in the javascript script. The preg statement is used to remove tabs, new lines and
            // repeated spaces from the HTML code, so the result is one line of code.
            $data[$course->id] = preg_replace(
                '/\s+/',
                ' ',
                $OUTPUT->render_from_template('local_courseoverview/courseoverview', ['data' => $coursedata])
            );

            // Delete the course information before the next iteration.
            unset($isstudent, $isteacher, $unreadforums, $forumsdetails, $studentpendingassign,
                $studentpendingquiz, $teacherpendingassign, $teacherpendingquiz);

        }

        // Build an array of courses to be passed to the javascript script. Each item contains the data of a course.
        $jscourses = [];
        foreach ($data as $courseid => $coursedata) {
            $jscourses[] = ['course_id' => $courseid, 'data' => $coursedata];
        }

        // Combine the data of the courses with the javascript code to generate the final script.
        $javascript = $OUTPUT->render_from_template('local_courseoverview/js', ['courses' => $jscourses]);

        // Add the javascript script to the page.
        $PAGE->requires->js_init_code($javascript, true);

    }
}

/**
 * Get information regarding unread forum posts in a course. Is aware of the user groups.
 *
 * @param int $userid
 * @param stdClass $course
 * @param bool $resetreadcache
 * @return array The information about pending forum posts.
 * @throws coding_exception
 */
function get_pending_forums(int $userid, stdClass $course, bool $resetreadcache = false): array {

    $unreadforums = [];
    $totalunread = 0;

    // Get all the forums in the course.
    $forums = get_all_instances_in_course(MODULE_FORUM_NAME, $course, $userid);

    // Count the number of unread forum posts in each forum, being aware of the user groups.
    foreach ($forums as $forum) {

        $cm = get_coursemodule_from_instance(MODULE_FORUM_NAME, $forum->id, $course->id);
        $forumunread = forum_tp_count_forum_unread_posts($cm, $course, $resetreadcache);
        $totalunread += $forumunread;

        if (!empty($forumunread)) {
            $unreadforums[$forum->id] = [
                'id' => $forum->coursemodule,
                'name' => $forum->name,
                'count' => $forumunread,
            ];
        }
    }

    return $unreadforums + ['totalunread' => $totalunread];

}

/**
 * Count the number of tasks in a course where the user is enrolled as
 * a student and has not submitted the answers yet.
 *
 * @param stdClass $course
 * @param array $assignments
 * @return int
 * @throws coding_exception
 */
function count_student_pending_assign(stdClass $course, array $assignments): int {

    $sum = 0;

    foreach ($assignments as $assignment) {

        // Create an assignment object in order to call its member functions.
        $cm = get_coursemodule_from_instance(MODULE_ASSIGN_NAME, $assignment->id);
        $context = \context_module::instance($assignment->coursemodule);
        $assign = new \assign($context, $cm, $course);

        if (!$assign->count_submissions_with_status('submitted')) {
            // The user has not submitted the assignment.
            $sum++;
        }

    }

    return $sum;

}

/**
 * Count the number of tasks in a course where the user is enrolled as
 * a teacher and has not been graded yet.
 *
 * @param stdClass $course
 * @param array $assignments
 * @return int
 * @throws coding_exception
 */
function count_teacher_pending_assign(stdClass $course, array $assignments): int {

    $sum = 0;

    foreach ($assignments as $assignment) {

        // Create an assignment object in order to call its member functions.
        $cm = get_coursemodule_from_instance(MODULE_ASSIGN_NAME, $assignment->id);
        $context = \context_module::instance($assignment->coursemodule);
        $assign = new \assign($context, $cm, $course);

        $sum += $assign->count_submissions_need_grading();

    }

    return $sum;

}

/**
 * Count the number of quizzes in a course where the user is enrolled as
 * a student and whose answers have not been submitted yet.
 *
 * @param int $userid
 * @param array $quizzes
 * @return int
 * @throws coding_exception
 * @throws moodle_exception
 */
function count_student_pending_quiz(int $userid, array $quizzes): int {

    $sum = 0;

    foreach ($quizzes as $quiz) {

        if (!is_quiz_available($quiz, $userid)) {
            continue;
        }

        $context = \context_module::instance($quiz->coursemodule);

        if (has_capability('mod/quiz:viewreports', $context, $userid)) {
            continue;
        }

        // Student: Count the attempts they have made.
        $attempts = quiz_get_user_attempts($quiz->id, $userid);
        if (count($attempts) === 0) {
            $sum++;
        }
    }

    return $sum;

}

/**
 * Count the number of quizzes in a course where the user is enrolled as
 * a teacher and there are submitted answers not reviewed yet.
 *
 * @param stdClass $course
 * @param int $userid
 * @param array $quizzes
 * @return int
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function count_teacher_pending_quiz(stdClass $course, int $userid, array $quizzes): int {

    global $DB;

    $sum = 0;

    foreach ($quizzes as $quiz) {

        if (!is_quiz_available($quiz, $userid)) {
            continue;
        }

        // Check that quiz has any question of type essay.
        $queryessay = "
                SELECT COUNT({question}.id) AS total
                FROM {question}
                INNER JOIN {quiz_slots} ON ({quiz_slots}.questionid = {question}.id)
                INNER JOIN {quiz} ON ({quiz}.id = {quiz_slots}.quizid)
                WHERE {quiz}.course = " . $course->id . "
                AND {quiz}.id = " . $quiz->id . "
                AND {question}.qtype = 'essay'
            ";

        $resultessay = $DB->get_record_sql($queryessay);

        if ((int)$resultessay->total <= 0) {
            continue;
        }

        $context = \context_module::instance($quiz->coursemodule);

        if (!has_capability('mod/quiz:viewreports', $context, $userid)) {
            continue;
        }

        $quizobj = \quiz::create($quiz->id, $userid);

        // Get enrolled students.
        $coursecontext = \context_course::instance($course->id);

        $users = get_enrolled_students($quizobj, $coursecontext);

        foreach ($users as $user) {
            if (is_quiz_pending($quizobj, $user)) {
                $sum++;
                continue 2;
            }
        }
    }

    return $sum;

}

/**
 * Generate a piece of HTML code that formats the information about unread forum posts.
 *
 * @param array $unreadforums
 * @return string
 * @throws coding_exception
 */
function format_unreadforums(array $unreadforums): string {

    global $CFG;

    $content = '<ul>';

    foreach ($unreadforums as $key => $value) {
        if (is_numeric($key)) {
            $unreadtext = ($value['count'] === 1) ? get_string('onepostunread', 'local_courseoverview')
                : get_string('manypostsunread', 'local_courseoverview');

            $content .= '<li><strong>' . $value['count'] . '</strong>'
                . ' ' . $unreadtext . ' '
                . '<a href="' . $CFG->wwwroot . '/mod/forum/view.php?id=' . $value['id'] . '" target="_blank">'
                . addslashes($value['name'])
                . '</a></li>';
        }
    }

    return $content . '</ul>';

}

/**
 * Check if quiz is visible and available.
 *
 * @param stdClass $quiz
 * @param int $userid
 * @return bool
 * @throws coding_exception
 * @throws moodle_exception
 */
function is_quiz_available(stdClass $quiz, int $userid): bool {

    $cm = get_coursemodule_from_id(MODULE_QUIZ_NAME, $quiz->coursemodule);

    // Check visibility.
    if (!$quiz->visible) {
        return false;
    }

    // Check if quiz is open.
    $now = time();
    if (!(($quiz->timeclose >= $now && $quiz->timeopen < $now) ||
        ((int)$quiz->timeclose === 0 && $quiz->timeopen < $now) ||
        ((int)$quiz->timeclose === 0 && (int)$quiz->timeopen === 0))) {
        return false;
    }

    // Check availability.
    if (!\core_availability\info_module::is_user_visible($cm, $userid)) {
        return false;
    }

    return true;

}

/**
 * Check if a user has submitted a quiz. Returns true if there is a submission that needs
 * grading, false otherwise.
 *
 * @param quiz $quizobj
 * @param string $user
 * @return bool
 * @throws coding_exception
 */
function is_quiz_pending(quiz $quizobj, string $user): bool {

    // Get attempts.
    $attempts = quiz_get_user_attempts($quizobj->get_quizid(), $user);

    foreach ($attempts as $attempt) {
        // Create attempt object.
        $attemptobject = $quizobj->create_attempt_object($attempt);

        // Get questions.
        $slots = $attemptobject->get_slots();
        foreach ($slots as $slot) {
            if (!$attemptobject->is_real_question($slot)) {
                return true;
            }

            // Check if the status is "pending of grading".
            if ($attemptobject->get_question_status($slot, true) === get_string('requiresgrading', 'question')) {
                return true;
            }
        }
    }

    return false;

}

/**
 * Get list of unique users enrolled as students in the course. The group mode is supported.
 *
 * @param quiz $quizobj
 * @param context_course $coursecontext
 * @return array
 * @throws dml_exception
 */
function get_enrolled_students(quiz $quizobj, context_course $coursecontext): array {

    // If group mode is on, find out the list of the group ids the current user belongs to.
    $groupids = [];
    if ($quizobj->get_course()->groupmode) {
        $groups = groups_get_my_groups();
        foreach ($groups as $group) {
            $groupids[] = $group->id;
        }
    }

    $users = [];

    if (!empty($groupids)) {
        foreach ($groupids as $groupid) {
            $userscontext = get_enrolled_users($coursecontext, '', $groupid, 'u.id');
            foreach ($userscontext as $user) {
                $users[] = $user->id;
            }
        }
    } else {
        $userscontext = get_enrolled_users($coursecontext, '', 0, 'u.id');
        foreach ($userscontext as $user) {
            $users[] = $user->id;
        }
    }

    $users = array_unique($users);

    $filteredusers = [];

    foreach ($users as $user) {
        if (check_role($user, $coursecontext, 'student')) {
            $filteredusers[] = $user;
        }
    }

    return $filteredusers;

}

/**
 * Helper function to check the role of a user in a context.
 *
 * @param int $userid
 * @param context|null $context
 * @param string $archetype
 * @return bool
 * @throws dml_exception
 */
function check_role(int $userid = 0, context $context = null, string $archetype = ''): bool {

    global $DB;

    $roles = get_user_roles($context, $userid);

    foreach ($roles as $role) {
        $roledb = $DB->get_record('role', ['id' => $role->roleid]);
        if ($roledb->archetype === $archetype) {
            return true;
        }
    }

    return false;

}

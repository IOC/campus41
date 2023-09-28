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

defined('MOODLE_INTERNAL') || die();

/**
 * Constant to avoid repeated use of 'userid' literal.
 */
const USERID = 'userid';

// Load helper functions.
require_once(dirname(__DIR__, 3) . '/mod/forum/tests/generator_trait.php');
require_once(dirname(__DIR__, 3) . '/mod/assign/tests/generator.php');

/**
 * Test cases for the course overview generator.
 *
 * @package     local_courseoverview
 * @author      Toni Ginard <toni.ginard@ticxcat.cat>
 * @copyright   2022 Departament d'Educació - Generalitat de Catalunya
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_courseoverview_testcase extends advanced_testcase {

    use mod_forum_tests_generator_trait;
    use mod_assign_test_generator;

    /**
     * Set up function. In this instance we are setting up database
     * records to be used in the unit tests.
     *
     * @throws coding_exception
     */
    protected function setUp(): void {
        parent::setUp();

        set_config('defaultpreference_trackforums', 1);

        $this->student = $this->getDataGenerator()->create_user(['username' => 'student']);
        $this->teacher = $this->getDataGenerator()->create_user(['username' => 'teacher']);

        $this->course = $this->getDataGenerator()->create_course();

        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, 'student');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, 'editingteacher');

        // Create a group and add both users to it.
        $group = $this->getDataGenerator()->create_group(['courseid' => $this->course->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group->id, USERID => $this->student->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group->id, USERID => $this->student->id]);

    }

    /**
     * Test the function to get the information about the number of unread posts in the
     * forums of a course.
     *
     * @return void
     * @throws dml_exception
     * @throws coding_exception
     */
    public function test_forum_unread_messages(): void {
        $this->resetAfterTest();

        // Create activity forum.
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $this->course->id]);

        // Check initial state.
        $this->assertEquals(0, get_pending_forums($this->teacher->id, $this->course, true)['totalunread']);
        $this->assertEquals(0, get_pending_forums($this->student->id, $this->course, true)['totalunread']);

        // Add a discussion to the activity forum, done by the student.
        [, $poststudent] = $this->helper_post_to_forum($forum, $this->student);

        // Check that the teacher has 1 unread message. Student will also have 1 unread message because
        // when a new discussion is created in the web, it is automatically added to table 'forum_read'
        // for the creator. But in this case, it is not added to the table 'forum_read'.
        $this->setUser($this->teacher);
        $this->assertEquals(1, get_pending_forums($this->teacher->id, $this->course, true)['totalunread']);
        $this->setUser($this->student);
        $this->assertEquals(1, get_pending_forums($this->student->id, $this->course, true)['totalunread']);

        // Add a post to the discussion.
        $this->helper_reply_to_post($poststudent, $this->student);

        // Now the teacher and the student should have 2 unread messages.
        $this->setUser($this->teacher);
        $this->assertEquals(2, get_pending_forums($this->teacher->id, $this->course, true)['totalunread']);
        $this->setUser($this->student);
        $this->assertEquals(2, get_pending_forums($this->student->id, $this->course, true)['totalunread']);

        // Add a second discussion to the activity forum, done by the teacher.
        [$discussionteacher, $postteacher] = $this->helper_post_to_forum($forum, $this->teacher);

        // Now the teacher should have 2 unread messages and the student should have 3.
        $this->setUser($this->teacher);
        $this->assertEquals(2, get_pending_forums($this->teacher->id, $this->course, true)['totalunread']);
        $this->setUser($this->student);
        $this->assertEquals(3, get_pending_forums($this->student->id, $this->course, true)['totalunread']);

        global $DB;

        // Simulate user student reading teacher's post.
        $DB->insert_record('forum_read', [
            USERID => $this->student->id,
            'forumid' => $forum->id,
            'discussionid' => $discussionteacher->id,
            'postid' => $postteacher->id,
            'firstread' => time(),
            'lastread' => time(),
        ]);

        // Now the student should have 2 unread messages.
        $this->assertEquals(2, get_pending_forums($this->student->id, $this->course, true)['totalunread']);

    }

    /**
     * Test the function to get the information about the number of tasks in a course with
     * submitted answers that have not been graded yet.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_assign_pending_teacher(): void {
        $this->resetAfterTest();

        // Create an assignment.
        $assign = $this->create_instance($this->course);

        // The student adds a submission and submits it for grading.
        $this->add_submission($this->student, $assign);
        $this->submit_for_grading($this->student, $assign);

        global $DB;

        // Check the number of assignments submitted by user student.
        $submits = $DB->count_records('assign_submission', [
            USERID => $this->student->id,
            'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
        ]);

        $this->assertEquals(1, $submits);

        // Check the number of assignments pending grading by user teacher.
        $assignments = get_all_instances_in_course(MODULE_ASSIGN_NAME, $this->course, $this->teacher->id);
        $this->assertEquals(1, count_teacher_pending_assign($this->course, $assignments));

        // Grade the submission.
        $this->mark_submission($this->teacher, $assign, $this->student);

        // Check the assignment has been graded.
        $this->assertEquals(0, count_teacher_pending_assign($this->course, $assignments));

    }

    /**
     * Test the function to get the information about the number of tasks in a course that
     * don't have a submitted response.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_assign_pending_student(): void {
        $this->resetAfterTest();

        $params = [
            'allowsubmissionsfromdate' => time() - DAYSECS,
            'duedate' => time() + DAYSECS,
            'cutoffdate' => time() + 2 * DAYSECS,
        ];

        // Create 2 tasks and check there are 2 pending tasks.
        $assign1 = $this->create_instance($this->course, $params);
        $assign2 = $this->create_instance($this->course, $params);

        $assignments = get_all_instances_in_course(MODULE_ASSIGN_NAME, $this->course, $this->teacher->id);

        $this->assertEquals(2, count_student_pending_assign($this->course, $assignments));

        // Add a submission to both tasks.
        $this->add_submission($this->student, $assign1);
        $this->add_submission($this->student, $assign2);

        global $DB;

        // Check that there are 2 tasks submitted.
        $assigntotal = $DB->count_records_select('assign',
            'allowsubmissionsfromdate < :allowsubmissionsfromdate AND duedate > :duedate',
            [
                'allowsubmissionsfromdate' => time(),
                'duedate' => time(),
            ]
        );

        $this->assertEquals(2, $assigntotal);

        // Submit one assignment and check that there is only 1 pending task.
        $this->submit_for_grading($this->student, $assign1);
        $this->assertEquals(1, count_student_pending_assign($this->course, $assignments));

        // Submit the other assignment and check that there are none pending tasks.
        $this->submit_for_grading($this->student, $assign2);
        $this->assertEquals(0, count_student_pending_assign($this->course, $assignments));

    }

    /**
     * Test the functions to get information about the number of quizzes waiting for a student
     * to be done and the number of quizzes for a teacher with questions of type 'essay'
     * waiting to be graded.
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function test_quizz_pending_tasks(): void {
        $this->resetAfterTest();

        $quizzes = get_all_instances_in_course(MODULE_QUIZ_NAME, $this->course, $this->student->id);

        // In the beginning, there are no quizzes ready to be responded to.
        $this->assertEquals(0, count_student_pending_quiz($this->student->id, $quizzes));

        // Create the quiz.
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance([
            'course' => $this->course->id,
            'grade' => 100.0,
            'sumgrades' => 2,
            'layout' => '1,0'
        ]);

        // Create a question and add it to the quiz.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('essay', 'plain', ['category' => $cat->id]);
        quiz_add_quiz_question($question->id, $quiz, 0, 10);

        // After the creation of the quiz, the list of quizzes must be updated.
        $quizzes = get_all_instances_in_course(MODULE_QUIZ_NAME, $this->course, $this->student->id);

        // At this point, there should be a quiz with an essay question in, which can be responded to.
        $this->assertEquals(1, count_student_pending_quiz($this->student->id, $quizzes));
        $this->assertEquals(0, count_teacher_pending_quiz($this->course, $this->teacher->id, $quizzes));

        // Create and start the attempt.
        $quizobj = quiz::create($quiz->id);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time();

        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $this->student->id);
        $attempt = quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        $attempt = quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Generate and process a response from the student.
        $attemptobj = quiz_attempt::create($attempt->id);

        $attemptobj->process_submitted_actions(
            $timenow + 300,
            false,
            [
                1 => [
                    'answer' => 'This is an essay by ' . $this->student->firstname,
                    'answerformat' => FORMAT_PLAIN,
                ]
            ]
        );

        $attemptobj->process_finish($timenow + 600, false);

        // Now, there should be a quiz with an essay question in, which has been responded to,
        // so the teacher can grade it.
        $this->assertEquals(1, count_teacher_pending_quiz($this->course, $this->teacher->id, $quizzes));

    }

}

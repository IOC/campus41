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

defined('MOODLE_INTERNAL') || die;

use theme_cre\mod_quiz;

class theme_cre_mod_quiz_renderer extends \mod_quiz_renderer {


    /**
     * Display the prev/next buttons that go at the bottom of each page of the attempt.
     *
     * @param int $page the page number. Starts at 0 for the first page.
     * @param bool $lastpage is this the last page in the quiz?
     * @param string $navmethod Optional quiz attribute, 'free' (default) or 'sequential'
     * @return string HTML fragment.
     */
    protected function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free') {
        $output = '';
        $customclass = 'mod_quiz-next-nav';

        $output .= html_writer::start_tag('div', array('class' => 'submitbtns'));
        if ($page > 0 && $navmethod == 'free') {
            $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'previous',
                    'value' => get_string('navigateprevious', 'quiz'), 'class' => 'mod_quiz-prev-nav btn btn-secondary'));
        }
        if ($lastpage) {
            $nextlabel = get_string('endtest', 'quiz');
            $customclass = 'mod_quiz-end-nav';
        } else {
            $nextlabel = get_string('navigatenext', 'quiz');
        }
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next', 'aria-label' => $nextlabel,
                'value' => '_', 'class' => 'btn btn-primary ' . $customclass));
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /*
     * Summary Page
     */
    /**
     * Create the summary page
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_page($attemptobj, $displayoptions) {
        $output = '';
        $output .= $this->header();
        $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        $output .= html_writer::start_tag('div', array('class' => 'mod_quiz-summary'));
        $output .= $this->heading(get_string('summaryofattempt', 'quiz'), 3);
        $output .= $this->summary_table($attemptobj, $displayoptions);
        $output .= html_writer::end_tag('div');
        $output .= $this->summary_page_controls($attemptobj);
        $output .= $this->footer();
        return $output;
    }

    /**
     * Creates any controls a the page should have.
     *
     * @param quiz_attempt $attemptobj
     */
    public function summary_page_controls($attemptobj) {
        $output = html_writer::start_tag('div', array('class' => 'mod_quiz-summary-page-controls'));

        // Return to place button.
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $icon = new \pix_icon('images/button-reply', get_string('returnattempt', 'quiz'), 'theme');
            $html = $this->render($icon);
            $html .= html_writer::tag('span', get_string('returnattempt', 'quiz'),
                    array('class' => 'btn-text'));
            $button = new single_button(
                    new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                    $html);
            $button->class = 'btn-back';
            $output .= $this->container($this->container($this->render($button),
                    'controls'), 'submitbtns mod_quiz-returnattempt');
        }

        // Finish attempt button.
        $options = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'cmid' => $attemptobj->get_cmid(),
            'sesskey' => sesskey(),
        );

        $html = get_string('submitallandfinish', 'quiz');
        $button = new single_button(
                new moodle_url($attemptobj->processattempt_url(), $options),
                $html);
        $button->class = 'btn-next';
        $button->id = 'responseform';
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
             $button->add_action(new confirm_action(get_string('confirmclose', 'quiz'), null,
                     get_string('submitallandfinish', 'quiz')));
        }

        $duedate = $attemptobj->get_due_date();
        $message = '';
        if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
            $message = get_string('overduemustbesubmittedby', 'quiz', userdate($duedate));

        } else if ($duedate) {
            $message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
        }

        $output .= $this->countdown_timer($attemptobj, time());
        $output .= $this->container($message . $this->container(
                $this->render($button), 'controls'), 'submitbtns mod_quiz-submitattempt');

        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function navigation_panel(quiz_nav_panel_base $panel) {

        $output = '';
        $output .= $panel->render_before_button_bits($this);

        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        foreach ($panel->get_question_buttons() as $button) {
            $output .= $this->render($button);
        }
        $output .= html_writer::end_tag('div');

        $output .= html_writer::tag('div', $panel->render_end_bits($this),
                array('class' => 'othernav'));

        $this->page->requires->js_init_call('M.mod_quiz.nav.init', null, false,
                quiz_get_js_module());

        return $output;
    }

    /**
     * Generates the table of data
     *
     * @param array $quiz Array contining quiz data
     * @param int $context The page context ID
     * @param mod_quiz_view_object $viewobj
     */
    public function view_table($quiz, $context, $viewobj) {
        if (!$viewobj->attempts) {
            return '';
        }

        // Prepare table header.
        $table = new html_table();
        $table->attributes['class'] = 'generaltable quizattemptsummary';
        $table->head = array();
        $table->align = array();
        $table->size = array();

        // One row for each attempt.
        foreach ($viewobj->attemptobjs as $attemptobj) {
            $attemptoptions = $attemptobj->get_display_options(true);
            // $row = array();
            $row1 = new \html_table_row();
            $row2 = new \html_table_row();

            $row1->attributes['class'] = 'attemptsummaryr0';
            $row2->attributes['class'] = 'attemptsummaryr1';

            // Add the attempt number.
            if ($viewobj->attemptcolumn) {
                $cell1 = new \html_table_cell();
                if ($attemptobj->is_preview()) {
                    //$row[] = get_string('preview', 'quiz');
                    $text = get_string('preview', 'quiz');
                    $cell1->text = $this->output->pix_icon('images/quiz-preview', $text, 'theme', array('title' => $text));
                } else {
                    //$row[] = $attemptobj->get_attempt_number();
                    $cell1->text = $attemptobj->get_attempt_number();
                }
                $cell1->rowspan = 2;
                $row1->cells[] = $cell1;
            }

            //$row[] = $this->attempt_state($attemptobj);
            $cell2 = new \html_table_cell();
            $cell2->text = $this->attempt_state($attemptobj);
            if ($attemptobj->get_state() != quiz_attempt::IN_PROGRESS) {
                $cell2->attributes['class'] = 'content';
            } else {
                $cell2->attributes['class'] = 'attempt-not-finished';
            }
            $row1->cells[] = $cell2;

            if ($viewobj->markcolumn) {
                $cell3 = new \html_table_cell();
                if ($attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                        $attemptobj->is_finished()) {
                    //$row[] = quiz_format_grade($quiz, $attemptobj->get_sum_marks());
                    if ($attemptobj->get_state() == quiz_attempt::ABANDONED) {
                        $cell3->attributes['class'] = 'abandoned';
                        $customclass = 'quiz-attempt-text';
                    } else {
                        $cell3->attributes['class'] = 'content';
                        $customclass = '';
                    }
                    $cell3->text = html_writer::tag('span', quiz_format_grade($quiz, $attemptobj->get_sum_marks()),
                            array('class' => $customclass));
                    if ($attemptobj->get_state() != quiz_attempt::ABANDONED) {
                        $cell3->text .= html_writer::tag('span', '/' . quiz_format_grade($quiz, $quiz->sumgrades), array('class' => 'quiz-attempt-total'));
                    }
                } else {
                    //$row[] = '';
                    $cell3->text = '';
                }
                $row1->cells[] = $cell3;
            }

            // Ouside the if because we may be showing feedback but not grades.
            $attemptgrade = quiz_rescale_grade($attemptobj->get_sum_marks(), $quiz, false);

            if ($viewobj->gradecolumn) {
                $cell4 = new \html_table_cell();
                if ($attemptoptions->marks >= question_display_options::MARK_AND_MAX &&
                        $attemptobj->is_finished()) {

                    // Highlight the highest grade if appropriate.
                    if ($viewobj->overallstats && !$attemptobj->is_preview()
                            && $viewobj->numattempts > 1 && !is_null($viewobj->mygrade)
                            && $attemptobj->get_state() == quiz_attempt::FINISHED
                            && $attemptgrade == $viewobj->mygrade
                            && $quiz->grademethod == QUIZ_GRADEHIGHEST) {
                        $table->rowclasses[$attemptobj->get_attempt_number()] = 'bestrow';
                    }
                    //$row[] = quiz_format_grade($quiz, $attemptgrade);
                    if ($attemptobj->get_state() == quiz_attempt::ABANDONED) {
                        $cell4->attributes['class'] = 'abandoned';
                        $customclass = 'quiz-attempt-text';
                    } else {
                        $cell4->attributes['class'] = 'content';
                        $customclass = '';
                    }
                    $cell4->text = html_writer::tag('span', quiz_format_grade($quiz, $attemptgrade),
                            array('class' => $customclass));
                    if ($attemptobj->get_state() != quiz_attempt::ABANDONED) {
                        $cell4->text .= html_writer::tag('span', '/' . quiz_format_grade($quiz, $quiz->grade), array('class' => 'quiz-attempt-total'));
                    }
                } else {
                    //$row[] = '';
                    $cell4->text = '';
                }
                $row1->cells[] = $cell4;
            }

            $cell6 = new \html_table_cell();
            if ($viewobj->canreviewmine) {
                /*$row[] = $viewobj->accessmanager->make_review_link($attemptobj->get_attempt(),
                        $attemptoptions, $this);*/
                $cell6->text = $viewobj->accessmanager->make_review_link($attemptobj->get_attempt(),
                        $attemptoptions, $this);
                if (!empty($cell6->text)) {
                    $cell6->attributes['class'] = 'content';
                }
            }

            $cell5 = new \html_table_cell();
            $cell5->colspan = 2;
            if ($viewobj->feedbackcolumn && $attemptobj->is_finished()) {
                if ($attemptoptions->overallfeedback) {
                    //$row[] = quiz_feedback_for_grade($attemptgrade, $quiz, $context);
                    $cell5->attributes['class'] = 'content';
                    $cell5->text = quiz_feedback_for_grade($attemptgrade, $quiz, $context);
                } else {
                    //$row[] = '';
                    $cell5->text = '';
                }
            }
            //$cell5->attributes['class'] = 'content';
            //$cell5->text = "Bona feina, tot i que convé que consultis  el qüestionari novament, si trobes dificultats a l’hora de crear el teu nom de campus.";

            $row2->cells[] = $cell5;
            $row2->cells[] = $cell6;

            if ($attemptobj->is_preview()) {
                //$table->data['preview'] = $row;
                $table->data[] = $row1;
                $table->data[] = $row2;
            } else {
                //$table->data[$attemptobj->get_attempt_number()] = $row;
                $table->data[] = $row1;
                $table->data[] = $row2;
            }
        } // End of loop over attempts.

        $output = '';
        $output .= $this->view_table_heading();
        $output .= html_writer::table($table);
        return $output;
    }

    /**
     * Outputs the table containing data from summary data array
     *
     * @param array $summarydata contains row data for table
     * @param int $page contains the current page number
     */
    public function review_summary_table($summarydata, $page) {
        $summarydata = $this->filter_review_summary_table($summarydata, $page);
        if (empty($summarydata)) {
            return '';
        }

        //print_object($summarydata);

        $output = html_writer::start_tag('div', array('class' => 'quizreviewsummary'));

        if (isset($summarydata['attemptlist'])) {
            $content = $this->render_review_summary_table($summarydata['attemptlist']['content']);
            $content = str_replace(',', '  - ', $content);
            $output .= html_writer::tag('div', $content, array('class' => 'attemptlist'));
        }

        $statedate = html_writer::start_tag('div', array('class' => 'quizreviewstatedate'));
        if (isset($summarydata['state'])) {
            $content = $this->render_review_summary_table($summarydata['state']['content']);
            $statedate .= html_writer::tag('div', $content, array('class' => 'state'));
        }
        $date = html_writer::start_tag('div', array('class' => 'quizreviewdate'));
        if (isset($summarydata['startedon'])) {
            $content = $summarydata['startedon']['title']
                    . ' ' . $this->render_review_summary_table($summarydata['startedon']['content']);
            $date .= html_writer::tag('div', $content, array('class' => 'startedon'));
        }
        if (isset($summarydata['completedon'])) {
            $content = $summarydata['completedon']['title']
                    . ' ' . $this->render_review_summary_table($summarydata['completedon']['content']);
            $date .= html_writer::tag('div', $content, array('class' => 'completedon'));
        }
        $date .= html_writer::end_tag('div');
        $statedate .= $date;
        $statedate .= html_writer::end_tag('div');
        $output .= $statedate;
        $grade = html_writer::start_tag('div', array('class' => 'quizreviewgrade'));
        if (isset($summarydata['marks'])) {
            $text = get_string('marks', 'quiz');
            $image = $this->output->pix_icon('images/quiz-points', $content, 'theme', array('title' => $text));
            $content = $this->render_review_summary_table($summarydata['marks']['content']);
            preg_match('/^([^\/]*)(.*)$/', $content, $matches);
            if (isset($matches[2])) {
                $content = html_writer::start_tag('span')
                    . html_writer::tag('span', $matches[1])
                    . html_writer::tag('span', $matches[2], array('class' => 'marktotal'))
                    . html_writer::end_tag('span');
            }
            $grade .= html_writer::tag('div', $image . $content, array('class' => 'marks'));
        }
        if (isset($summarydata['grade'])) {
            $text = get_string('grade', 'quiz');
            $image = $this->output->pix_icon('images/quiz-grade', $content, 'theme', array('title' => $text));
            $content = html_writer::tag('span', $this->render_review_summary_table($summarydata['grade']['content']), array('class' => 'quizgrade'));
            $grade .= html_writer::tag('div', $image . $content, array('class' => 'grade'));
        }
        if (isset($summarydata['timetaken'])) {
            $text = get_string('timetaken', 'quiz');
            $image = $this->output->pix_icon('images/quiz-clock', $content, 'theme', array('title' => $text));
            $content = $this->render_review_summary_table($summarydata['timetaken']['content']);
            $grade .= html_writer::tag('div', $image . $content, array('class' => 'timetaken'));
        }
        $grade .= html_writer::end_tag('div');
        $output .= $grade;
        if (isset($summarydata['feedback'])) {
            $content = $this->render_review_summary_table($summarydata['feedback']['content']);
            $output .= html_writer::tag('div', $content, array('class' => 'feedback'));
        }

        $output .= html_writer::end_tag('div');
        return $output;
    }

    protected function render_review_summary_table($content) {
        if ($content instanceof renderable) {
            $content = $this->render($content);
        } else {
            $content = $content;
        }
        return $content;
    }

    /**
     * Renders each question
     *
     * @param quiz_attempt $attemptobj instance of quiz_attempt
     * @param bool $reviewing
     * @param array $slots array of intgers relating to questions
     * @param int $page current page number
     * @param bool $showall if true shows attempt on single page
     * @param mod_quiz_display_options $displayoptions instance of mod_quiz_display_options
     */
    public function questions(quiz_attempt $attemptobj, $reviewing, $slots, $page, $showall,
                              mod_quiz_display_options $displayoptions) {
        $output = '';
        foreach ($slots as $slot) {
            $output .= html_writer::start_tag('div', array('class' => 'que_number'));
            $output .= html_writer::tag('div', $attemptobj->get_question_number($slot));
            $output .= html_writer::end_tag('div');
            $output .= $attemptobj->render_question($slot, $reviewing, $this,
                    $attemptobj->review_url($slot, $page, $showall));
        }
        return $output;
    }

    /**
     * Generates the table heading.
     */
    public function view_table_heading() {
        return $this->heading(get_string('summaryofattempts', 'quiz'), 3, 'quiz-attempt-summary-header');
    }
}
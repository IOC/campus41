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

use theme_cre\mod_lesson;

class theme_cre_mod_lesson_renderer extends \mod_lesson_renderer {
    /**
     * Returns the header for the lesson module
     *
     * @param lesson $lesson a lesson object.
     * @param string $currenttab current tab that is shown.
     * @param bool   $extraeditbuttons if extra edit buttons should be displayed.
     * @param int    $lessonpageid id of the lesson page that needs to be displayed.
     * @param string $extrapagetitle String to appent to the page title.
     * @return string
     */
    public function header($lesson, $cm, $currenttab = '', $extraeditbuttons = false, $lessonpageid = null, $extrapagetitle = null) {
        global $CFG;

        $activityname = format_string($lesson->name, true, $lesson->course);
        if (empty($extrapagetitle)) {
            $title = $this->page->course->shortname.": ".$activityname;
        } else {
            $title = $this->page->course->shortname.": ".$activityname.": ".$extrapagetitle;
        }

        // Build the buttons
        $context = context_module::instance($cm->id);

        // Header setup
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);
        lesson_add_header_buttons($cm, $context, $extraeditbuttons, $lessonpageid);
        $output = $this->output->header();

        if (has_capability('mod/lesson:manage', $context)) {
            $output .= $this->output->heading_with_help($activityname, 'overview', 'lesson');

            if (!empty($currenttab)) {
                ob_start();
                include($CFG->dirroot.'/mod/lesson/tabs.php');
                $output .= ob_get_contents();
                ob_end_clean();
            }
        } else {
            $output .= '';
        }

        foreach ($lesson->messages as $message) {
            $output .= $this->output->notification($message[0], $message[1], $message[2]);
        }

        return $output;
    }

    /**
     * Returns HTML to display a page to the user
     * @param lesson $lesson
     * @param lesson_page $page
     * @param object $attempt
     * @return string
     */
    public function display_page(lesson $lesson, lesson_page $page, $attempt) {
        // We need to buffer here as there is an mforms display call
        ob_start();
        echo $page->display($this, $attempt);
        echo html_writer::end_div();
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}
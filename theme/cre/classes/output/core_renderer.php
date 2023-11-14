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

namespace theme_cre\output;

use coding_exception;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use paging_bar;
use context_course;
use pix_icon;
use availability_completion;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/theme/cre/classes/output/activity_navigation.php');

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_cre
 * @copyright  2018 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_boost\output\core_renderer {

    private $cremodule;
    private $creindexpage;
    private $modulesbackto;
    private static $creindexword = '[index]';

    /**
     * Constructor
     *
     * The constructor takes two arguments. The first is the page that the renderer
     * has been created to assist with, and the second is the target.
     * The target is an additional identifier that can be used to load different
     * renderers for different options.
     *
     * @param moodle_page $page the page we are doing output for.
     * @param string $target one of rendering target constants
     */
    public function __construct($page, $target) {
        global $FULLME;

        parent::__construct($page, $target);
        if (!$page->has_set_url()) {
            $page->set_url(new moodle_url($FULLME));
        }
        $this->cremodule = $this->can_apply_cre();
        $this->creindexpage = $this->cre_index_page();
        $this->modulesbackto = array(
            'forum',
            'workshop'
        );
    }

    public function can_apply_cre() {
        global $COURSE;

        $capability = has_capability('moodle/course:update', context_course::instance($COURSE->id));
        $ismodule = $this->page->context->contextlevel == CONTEXT_MODULE;
        // Participants.
        $matchurl = $this->page->url->compare(new \moodle_url('/user/index.php'), URL_MATCH_BASE);
        // User.
        $matchurl = $matchurl || $this->page->url->compare(new \moodle_url('/user/view.php'), URL_MATCH_BASE);
        $matchurl = $matchurl || $this->page->url->compare(new \moodle_url('/user/edit.php'), URL_MATCH_BASE);
        // Grades.
        $params = array(
            'mode' => 'grade'
        );
        $url = new \moodle_url('/course/user.php', $params);
        $matchurl = $matchurl || $url->compare($this->page->url, URL_MATCH_PARAMS);
        // Calendar.
        $matchurl = $matchurl || $this->in_calendar_page();
        // Badges.
        $matchurl = $matchurl || $this->page->url->compare(new \moodle_url('/badges/view.php'), URL_MATCH_BASE);
        // Mail.
        $matchurl = $matchurl || $this->page->url->compare(new \moodle_url('/local/mail/compose.php'), URL_MATCH_BASE);

        return !$capability && ($ismodule || $matchurl);
    }

    public function in_calendar_page() {
        return $this->page->pagetype == 'calendar-view';
    }

    public function in_quiz_module() {
        $pagetypes = array (
            'mod-quiz-attempt',
            'mod-quiz-review'
        );
        return in_array($this->page->pagetype, $pagetypes);
    }

    public function cre_index_page() {
        $ismodule = $this->page->context->contextlevel == CONTEXT_MODULE;
        $hasindexclass = strpos($this->page->bodyclasses, 'cre-index') !== false;

        return $ismodule && $hasindexclass;
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE;

        $header = new stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($PAGE->layout_options['nonavbar']);
        $header->navbar = $this->navbar(true);
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->cremodule = $this->cremodule;
        $header->creindexpage = $this->creindexpage;
        if ($PAGE->pagetype == 'mod-quiz-review') {
            $header->quizreview = get_string('quizreview', 'theme_cre');
        }
        if (!$header->cremodule || ($header->cremodule && $PAGE->pagetype == 'mod-forum-discuss')) {
            return $this->render_from_template('theme_cre/header', $header);
        }
        return '';
    }

    /**
     * Get the logo URL.
     *
     * @return string
     */
    public function get_logo_url($maxwidth = 100, $maxheight = 100) {
        $image = 'ioc-logo';
        if ($this->cremodule) {
            $image .= '-cre';
        }
        return $this->image_url($image, 'theme');;
    }

    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        $image = 'ioc-logo-sm';
        if ($this->cremodule) {
            $image .= '-cre';
        }
        return $this->image_url($image, 'theme');;
    }

    /**
     * The standard tags (typically script tags that are not needed earlier) that
     * should be output after everything else. Designed to be called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_end_of_body_html() {
        global $PAGE;

        $output = parent::standard_end_of_body_html();

        $PAGE->requires->js_call_amd('theme_cre/script', 'init');

        return $output;
    }

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar($top = false) {
        if ($this->cremodule) {
            $this->page->navbar->cremodule = true;
            $this->page->navbar->backtop = $top;
            foreach ($this->page->navbar->get_items() as $item) {
                if (!$item->is_last() && in_array($item->icon->component, $this->modulesbackto)) {
                    $item->backto_module = true;
                    $image = $top ? 'images/arrow-backto' : 'images/button-reply';
                    $modulename = get_string('modulename', $item->icon->component);
                    $item->text = get_string('backtomodule', 'theme_cre', $modulename);
                    $item->icon->attributes['name'] = 'src';
                    $item->icon->attributes['value'] = $this->image_url($image, 'theme_cre');
                }
            }
        }
        return $this->render_from_template('core/navbar', $this->page->navbar);
    }

    /**
     * Allow plugins to provide some content to be rendered in the navbar.
     * The plugin must define a PLUGIN_render_navbar_output function that returns
     * the HTML they wish to add to the navbar.
     *
     * @return string HTML for the navbar
     */
    public function navbar_plugin_output() {
        $output = '';

        if ($pluginsfunction = get_plugins_with_function('render_navbar_output')) {
            foreach ($pluginsfunction as $plugintype => $plugins) {
                if ($plugintype == 'message') {
                    continue;
                }
                foreach ($plugins as $pluginfunction) {
                    $output .= $pluginfunction($this);
                }
            }
        }

        return $output;
    }

    /**
     * Returns standard navigation between activities in a course.
     *
     * @return string the navigation HTML.
     */
    public function activity_navigation() {
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        // Get a list of all the activities in the course.
        $course = $this->page->cm->get_course();
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }

            // Skip activity if student and no visible
            if ($this->cremodule && !$module->is_visible_on_course_page()) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // Module URL.
            $linkurl = new moodle_url($module->url, array('forceview' => 1));
            if (!$this->cremodule) {
                // Add module URL (as key) and name (as value) to the activity list array.
                $activitylist[$linkurl->out(false)] = $modname;
            }
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a previous mod to show.
        if ($position > 0) {
            $prevmod = $mods[$modids[$position - 1]];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }

        $activitynav = new \theme_cre\output\activity_navigation($prevmod, $nextmod, $activitylist, $this->cremodule);
        $renderer = $this->page->get_renderer('core', 'course');

        $output = $this->cremodule ? $this->navbar() : '';

        return $output . $renderer->render($activitynav);
    }

    public function activity_index_button_go() {
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        // Get a list of all the activities in the course.
        $course = $this->page->cm->get_course();
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }

            // Skip activity if student and no visible
            if ($this->cremodule && !$module->is_visible_on_course_page()) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // Module URL.
            $linkurl = new moodle_url($module->url, array('forceview' => 1));
            if (!$this->cremodule) {
                // Add module URL (as key) and name (as value) to the activity list array.
                $activitylist[$linkurl->out(false)] = $modname;
            }
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }

        $activitynav = new \theme_cre\output\activity_navigation($prevmod, $nextmod, $activitylist, $this->cremodule, $this->creindexpage);
        $renderer = $this->page->get_renderer('core', 'course');
        return $renderer->render($activitynav);
    }

    public function flatnavigation() {
        global $USER, $DB;

        $inmodule = $this->page->context->contextlevel == CONTEXT_MODULE;
        $modinfo = get_fast_modinfo($this->page->course->id);
        $modinfosections = $modinfo->get_sections();
        $cinfo = new \completion_info($this->page->course);
        //$sections = $modinfo->get_section_info_all();
        $coursenumsections = course_get_format($this->page->course->id)->get_last_section_number();
        //$modules = $modinfo->get_cms();
        $courseformat = course_get_format($this->page->course->id);
        $activitylist = array();

        $currentsection = $inmodule ? $modinfo->cms[$this->page->cm->id]->sectionnum : 0;
        //foreach ($modinfo->sections[0] as $cmid) {
        //foreach ($modules as $module) {
        $section = 0;
        $completiontotals = $this->activity_completion($modinfo);
        while ($section <= $coursenumsections) {
            $thissection = $modinfo->get_section_info($section);
            if ($thissection->uservisible) {
                if ($section == 0 || $section == $currentsection) {
                    if ($section > 0) {
                        $node = \navigation_node::create(
                                       $section . '. ' . $courseformat->get_section_name($section),
                                        null,
                                        \navigation_node::TYPE_SECTION
                                    );
                        $node->myindent = 0;
                        $node->showdivider = true;
                        // Completion totals.
                        if (isset($completiontotals[$section])) {
                            $node->completiontotals = $completiontotals[$section]->complete . '/' . $completiontotals[$section]->totals;
                            if ($completiontotals[$section]->complete == $completiontotals[$section]->totals) {
                                $node->completiontotalscomplete = true;
                            }
                        }
                        array_push($activitylist, $node);
                    }
                    foreach ($modinfosections[$section] as $cmid) {
                        $module = $modinfo->cms[$cmid];
                        $creindexpage = false;
                        $custommodulename = '';
                        if (!$module->uservisible || $module->is_stealth() || empty($module->url) || !$module->is_visible_on_course_page()) {
                            continue;
                        }

                        // Page index customizations
                        if ($inmodule && $module->modname == 'page') {
                            $indexpage = $DB->get_record($module->modname, array('id' => $module->instance), 'intro, content');
                            if (strpos($indexpage->intro, self::$creindexword) !== false) {
                                $indexpage->content = preg_replace('/\s*<\/?ol>\s*/', '##', $indexpage->content);
                                $indexpage->content = preg_replace('/\s*<\/?li>\s*/', '@@', $indexpage->content);
                                $indexpage->content = strip_tags($indexpage->content);
                                if (preg_match('/@@([^@@]*)@@##/', $indexpage->content, $matches)) {
                                    if (isset($matches[1])) {
                                        $custommodulename = $matches[1];
                                    }
                                }
                                $creindexpage = true;
                            }
                        }
                        $node = \navigation_node::create(
                                    !empty($custommodulename) ? $custommodulename : $module->get_formatted_name(),
                                    new \moodle_url($module->url),
                                    \navigation_node::TYPE_ACTIVITY,
                                    null,
                                    null,
                                    new \pix_icon('icon-light', $module->name, 'mod_' . $module->modname)
                                );
                        if ($creindexpage) {
                            $node->icon = new \pix_icon('images/index-light', $module->name, 'theme');
                        }
                        if ($inmodule && $module->id == $this->page->cm->id) {
                            if (!$creindexpage) {
                                $node->icon = new \pix_icon('icon', $module->name, 'mod_' . $module->modname);
                            } else {
                                $node->icon = new \pix_icon('images/index', $module->name, 'theme');
                            }
                            $node->make_active();
                        }

                        // Completion info.
                        if ($module->completion != COMPLETION_TRACKING_NONE) {
                            $current = $cinfo->get_data($module, false, $USER->id);
                            if ($current->completionstate >= COMPLETION_COMPLETE) {
                                $node->completion = 'completion-complete';
                            } else {
                                $node->completion = 'completion-incomplete';
                            }
                        }
                        array_push($activitylist, $node);

                        // Participants.
                        if ($section === 0 && count($activitylist) == 1) {
                            $participants = get_string('participants');
                            $icon = 'images/participants-light-icon';
                            $node = \navigation_node::create(
                                    $participants,
                                    new \moodle_url('/user/index.php?id=' . $this->page->course->id),
                                    \navigation_node::TYPE_ACTIVITY
                                );
                            if (strpos($this->page->bodyclasses, 'path-user')) {
                                $icon = 'images/participants-icon';
                                $node->make_active();
                            }
                            $node->icon = new \pix_icon($icon, $participants, 'theme');
                            array_push($activitylist, $node);
                        }
                    }
                    $activitylist[count($activitylist) - 1]->endgrouplist = true;
                } else {
                    if (isset($completiontotals[$section]) && $completiontotals[$section]->visiblemods === 0) {
                        $node = \navigation_node::create(
                                       $section . '. ' . $courseformat->get_section_name($section),
                                        null,
                                        \navigation_node::TYPE_SECTION
                                    );
                        $node->myindent = 0;
                        $node->showdivider = true;
                        $node->padlock = true;
                        // Completion totals.
                        if (isset($completiontotals[$section])) {
                            $node->completiontotals = $completiontotals[$section]->complete . '/' . $completiontotals[$section]->totals;
                            if ($completiontotals[$section]->complete == $completiontotals[$section]->totals) {
                                $node->completiontotalscomplete = true;
                            }
                        }
                        array_push($activitylist, $node);
                    } else {
                        foreach ($modinfosections[$section] as $cmid) {
                            $module = $modinfo->cms[$cmid];
                            if (!$module->uservisible || $module->is_stealth() || empty($module->url) || !$module->is_visible_on_course_page()) {
                                continue;
                            }
                            $node = \navigation_node::create(
                                       $section . '. ' . $courseformat->get_section_name($section),
                                        new \moodle_url($module->url),
                                        \navigation_node::TYPE_SECTION
                                    );
                            $node->myindent = 0;
                            $node->showdivider = true;
                            // Completion totals.
                            if (isset($completiontotals[$section])) {
                                $node->completiontotals = $completiontotals[$section]->complete . '/' . $completiontotals[$section]->totals;
                                if ($completiontotals[$section]->complete == $completiontotals[$section]->totals) {
                                    $node->completiontotalscomplete = true;
                                }
                            }
                            array_push($activitylist, $node);
                            break;
                        }
                    }
                }
            }
            $section++;
        }

        return $activitylist;
    }

    /**
     * Returns for each section how many completed activities are and the total
     * number of completion activities.
     *
     * @return array.
     */
    private function activity_completion($modinfo) {
        $cinfo = new \completion_info($this->page->course);
        $totals = array();

        if ($cinfo->is_enabled()) {
            if ($modules = $cinfo->get_activities()) {
                foreach ($modules as $mod) {
                    $completiondata = $cinfo->get_data($mod, true);
                    $sectionnum = $modinfo->cms[$completiondata->coursemoduleid]->sectionnum;
                    if (!isset($totals[$sectionnum])) {
                        $totals[$sectionnum] = new stdClass();
                        $totals[$sectionnum]->complete = 0;
                        $totals[$sectionnum]->totals = 0;
                        $totals[$sectionnum]->visiblemods = 0;
                    }
                    if ($completiondata->completionstate >= COMPLETION_COMPLETE) {
                        $totals[$sectionnum]->complete += 1;
                    }
                    $totals[$sectionnum]->totals += 1;
                    if ($mod->uservisible && !$mod->is_stealth()) {
                        $totals[$sectionnum]->visiblemods += 1;
                    }
                }
            }
        }

        return $totals;
    }
}

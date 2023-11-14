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
 * A two column layout for the boost theme.
 *
 * @package   theme_cre
 * @copyright 2018 Institut Obert de Catalunya
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

$cremodule = $OUTPUT->can_apply_cre();
$creindexpage = $OUTPUT->cre_index_page();
$creblocks = $cremodule && ($OUTPUT->in_calendar_page() || $OUTPUT->in_quiz_module());

$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$footer = true;

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
if ($cremodule) {
    $extraclasses[] = 'cre-student-view';
    $regionmainsettingsmenu = false;
}
if (!$footer) {
    $extraclasses[] = 'no-footer';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = $creblocks && strpos($blockshtml, 'data-block=') !== false;
$customparams = array();

if ($PAGE->context->contextlevel == CONTEXT_MODULE) {
    $customparams[] = (object)[
            'name' => 'courseid',
            'value' => $PAGE->course->id
    ];
    $customparams[] = (object)[
            'name' => 'cmid',
            'value' => $PAGE->cm->id
    ];
}

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'cremodule' => $cremodule,
    'creindexpage' => $creindexpage,
    'creparams' => $customparams,
    'footer' => $footer
];

if ($cremodule) {
    $templatecontext['flatnavigation'] = $OUTPUT->flatnavigation();
} else {
    $templatecontext['flatnavigation'] = $PAGE->flatnav;
}
echo $OUTPUT->render_from_template('theme_cre/columns2', $templatecontext);

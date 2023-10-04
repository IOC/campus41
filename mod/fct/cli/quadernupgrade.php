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
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->dirroot . '/mod/fct/lib.php');
require_once($CFG->dirroot . '/mod/fct/classes/fct_quadern_base.php');

$quadernstemp = $DB->get_records('fct_quadern_temp', array());
$quadernscount = 0;

foreach ($quadernstemp as $quaderntemp) {
    $quadern = new fct_quadern_base((int)$quaderntemp->id);

    if (isset($quaderntemp->alumne)) {
        $recordalumne = $DB->get_record('user', array('username' => $quaderntemp->alumne, 'mnethostid' => 1));
        $quadern->alumne = $recordalumne->id;
    }

    if (isset($quaderntemp->tutor_centre) && !empty($quaderntemp->tutor_centre)) {
        $recordtutor_centre = $DB->get_record('user', array('username' => $quaderntemp->tutor_centre, 'mnethostid' => 1));
        $quadern->tutor_centre = ($recordtutor_centre ? $recordtutor_centre->id : '');
    }

    if (isset($quaderntemp->tutor_empresa) && !empty($quaderntemp->tutor_empresa)) {
        $recordtutor_empresa = $DB->get_record('user', array('username' => $quaderntemp->tutor_empresa, 'mnethostid' => 1));
        $quadern->tutor_empresa = ($recordtutor_empresa ? $recordtutor_empresa->id : '');
    }

    $quadern->create_object();
    $quadern->update();
    $quadernscount++;
    if ($quadernscount % 10 == 0) {
        echo "S'han actualizat " .  $quadernscount . " quaderns de " . count($quadernstemp) . "\n";
    }
}

 echo "S'han actualizat " .  $quadernscount . " quaderns";

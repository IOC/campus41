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
 * @package   local_userdebug
 * @author    Toni Ginard <toni.ginard@ticxcat.cat>
 * @copyright 2022 Departament d'Educació - Generalitat de Catalunya
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once $CFG->libdir . '/adminlib.php';

$context_system = context_system::instance();

$PAGE->set_context($context_system);
$PAGE->set_url('/local/userdebug/debug.php');

require_admin();

$debug = required_param('debug', PARAM_INT);

// Set the cookie
if (!isset($_COOKIE['user_debug']) || (int)$_COOKIE['user_debug'] !== $debug) {
    $_COOKIE['user_debug'] = $debug;

    if ($debug) {
        error_reporting(E_ALL | E_STRICT);
    }
  
    setcookie(
        'user_debug',
        $debug,
        time() + 1800,
        $CFG->sessioncookiepath,
        $CFG->sessioncookiedomain,
        $CFG->cookiesecure,
        $CFG->cookiehttponly
    );
}

echo $OUTPUT->header();
echo '<h2>' . get_string('debug', 'admin') . '</h2>';

if ($debug) {
    echo $OUTPUT->notification(
        get_string('debugactivated', 'local_userdebug', $USER->username),
        'notifysuccess'
    );
} else {
    echo $OUTPUT->notification(
        get_string('debugdeactivated', 'local_userdebug', $USER->username)
    );
}

echo $OUTPUT->footer();

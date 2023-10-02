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

function userdebug_get_debug(): void {

    global $CFG;

    if (isset($_COOKIE['user_debug']) && (int)$_COOKIE['user_debug'] === 1) {

        $CFG->debug = E_ALL | E_STRICT;
        $CFG->debugdisplay = 1;
        $CFG->showcrondebugging = true;

        error_reporting($CFG->debug);
        @ini_set('display_errors', '1');
        @ini_set('log_errors', '0');

    }

}

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
 * Materials secure download.
 *
 * @package    local_materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');

require_login();

$path = required_param('path', PARAM_PATH);
$originalpath = trim($path, '/');

$materials = $DB->get_records('local_materials');
foreach ($materials as $material) {
    $parts = explode('/', $path);
    $sources = unserialize($material->sources);
    $context = context_course::instance($material->courseid);
    while (count($parts) > 0) {
        foreach ($sources as $source) {
            if (implode('/', $parts) === trim($source, '/')) {
                if (has_capability('moodle/course:viewparticipants', $context)) {
                    $url = make_secret_url($originalpath);
                    redirect($url);
                    exit;
                }
            }
        }
        array_pop($parts);
    }

}

print_error('materialnotaccesible', 'local_materials');

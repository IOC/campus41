<?php
//  Error log plugin for Moodle
//  Copyright © 2012  Institut Obert de Catalunya
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');


admin_externalpage_setup('local_errorlog');

require_capability('moodle/site:config', context_system::instance());

$PAGE->requires->js('/local/errorlog/index.js');
$PAGE->requires->css('/local/errorlog/index.css');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('errorlog', 'local_errorlog'));

$filter = optional_param('filter', '', PARAM_TEXT);

$lines = array();
$error = false;
$file = false;

if (!empty($CFG->local_errorlog_path)) {
    $file = fopen($CFG->local_errorlog_path, 'r');
}

if ($file) {
    fseek($file, -64*1024, SEEK_END);
    fgets($file);
    while (!feof($file)) {
        $line = fgets($file);
        if (trim($line)) {
            $lines[] = $line;
        }
    }
    fclose($file);
    if ($filter) {
        $patt = preg_quote($filter, '/');
        $lines = preg_grep("/$patt/", $lines);
    }
} else {
    $error = get_string('errorlog_notfound', 'local_errorlog');
}

$content = s(implode('', $lines));

include 'index.html';

echo $OUTPUT->footer();

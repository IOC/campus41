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
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/mod/fct/classes/fct_quadern_base.php');
require_once($CFG->libdir . '/filelib.php');

$dirimages = $CFG->dataroot . '/temp/fct/fct';
$slash = "/";

$quadernsid =  $DB->get_records_menu('fct_quadern', null, 'id', 'id');

if ($directories = opendir($dirimages)) {
    while (false !== ($entry = readdir($directories))) {

        if (is_dir($dirimages. $slash .$entry) && $entry != ".." && $entry != ".") {

            if (preg_match('/^quadern-(\d+)$/', $entry, $matches)) {

                if (array_key_exists($matches[1], $quadernsid)) {
                    $quadern = new fct_quadern_base($matches[1]);
                    $quadernimages = opendir($dirimages . $slash . $entry);

                    while (false !== ($image = readdir($quadernimages))) {

                        if (!is_dir($dirimages.$slash.$entry.$slash.$image) && strpos($image, '.') !== 0) {
                            $cm = get_coursemodule_from_instance('fct', $quadern->fct);
                            $context = context_module::instance($cm->id);
                            $author = $DB->get_record('user', array('id' => $quadern->alumne));
                            preg_match('/^(.*?)~.*?$/', $image, $filename);
                            $filearea = (strpos($image, 'catsalut') !== false ? 'targetaimage' : 'inssimage');

                            $filerecord = array(
                                'contextid'    => $context->id,
                                'component'    => 'mod_fct',
                                'filearea'     => $filearea,
                                'itemid'       => $quadern->id,
                                'filepath'     => '/',
                                'filename'     => $filename[1],
                                'timecreated'  => time(),
                                'timemodified' => time(),
                                'userid'       => $quadern->alumne,
                                'author'       => fullname($author),
                                'license'      => 'allrightsreserved',
                                'sortorder'    => 0,
                            );
                            $fs = get_file_storage();

                            if (!$fs->file_exists($context->id, 'mod_fct', $filearea, $quadern->id, "/", $filename[1])) {
                                $fs->create_file_from_pathname($filerecord, $dirimages.$slash.$entry.$slash.$image);
                            }
                        }
                    }
                    echo "S'han migrat les imatges del quadern  " .  $quadern->id . "\n";
                }
            }
        }
    }
}

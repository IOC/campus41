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
 * Quadern main page FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_edit_form.php');
require_once('fct_quadern_base.php');

class fct_quadern_main extends fct_quadern_base {

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);
        $tab['currentab'] = 'quadern_main';

        return $tab;
    }

    public function view() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'quadern_main');
        $output->view($this);

        return true;

    }

    public function prepare_form_data($data) {
    }

    public function export_pdf() {
        global $CFG, $USER;

        require_once("$CFG->dirroot/mod/fct/export/lib.php");
        require_once("$CFG->dirroot/lib/filelib.php");

        $export = new fct_export($this->id);
        $doc = $export->quadern_latex();

        $tmpdir = "$CFG->dataroot/temp/fct/$USER->id";

        remove_dir($tmpdir);
        mkdir($tmpdir, $CFG->directorypermissions, true);
        file_put_contents("$tmpdir/quadern.ltx", $doc);
        copy("$CFG->dirroot/mod/fct/export/logo.pdf", "$tmpdir/logo.pdf");
        chdir($tmpdir);

        $args = '--interaction=nonstopmode --fmt=pdflatex quadern.ltx';
        exec("$CFG->filter_tex_pathlatex $args");
        exec("$CFG->filter_tex_pathlatex $args");

        if (!file_exists("$tmpdir/quadern.pdf")) {
            print_error('exportacio');
        }

        send_file("$tmpdir/quadern.pdf", 'quadern.pdf', 0, 0, false, true, 'application/pdf');

        remove_dir($tmpdir);
    }

    public function export_html() {
        global $CFG, $USER;

        require_once("$CFG->dirroot/mod/fct/export/lib.php");

        $export = new fct_export($this->id);
        echo $export->dades_generals_html();
        die;
    }
}

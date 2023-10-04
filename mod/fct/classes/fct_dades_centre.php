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
 * Fct mod dades centre class.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/centre_edit_form.php');

class fct_dades_centre extends fct_base {

    public $id;
    public $course;
    public $name;
    public $intro;
    public $timecreated;
    public $timemodified;
    public $objecte;
    public $fct;

    public $frases_centre;
    public $frases_empresa;
    public $centre;

    protected $record_keys = array('id', 'course', 'name', 'intro', 'timecreated', 'timemodified', 'objecte');
    protected $objecte_keys = array('id', 'course', 'name', 'intro', 'timecreated', 'timemodified', 'centre', 'frases_centre', 'frases_empresa');
    protected static $table = 'fct';
    protected $editform = 'fct_centre_edit_form';

    public function __construct($record=null) {

        if (isset($record->fct)) {
            $this->fct = $record->fct;
            parent::__construct((int)$record->fct);
        } else {
            if (is_int($record)) {
                $this->fct = $record;
            }
            parent::__construct($record);
        }
    }

    public function tabs($id, $type = 'view') {
        $tab = parent::tabs_general($id);
        $tab['currentab'] = 'dades_centre';

        return $tab;
    }

    public function view() {
        global $PAGE, $OUTPUT;

        $output = $PAGE->get_renderer('mod_fct', 'centre');

        $centre = $output->centre($this->centre);

        echo $centre;
    }

    public function set_data($data) {
        $this->centre = $data;
        parent::set_data($data);
    }

    public function checkpermissions($type = 'view') {

        if (!$this->usuari->es_administrador) {
            print_error('nopermissions', 'fct');
        }
    }

    public function prepare_form_data($data) {
    }
}
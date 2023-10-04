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
 * Fct instance class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class fct_instance {

    public $id;
    public $course;
    public $name;
    public $intro;
    public $timecreated;
    public $timemodified;
    public $frases_centre = array();
    public $frases_empresa = array();
    public $centre;
    public $instance;
    public $objecte = '';

    public function __construct($fctrecord) {
        foreach ((array)$fctrecord as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->decode();
    }

    public function record() {
        $record = new stdClass;
        $record->id = $this->id;
        $record->name = $this->name;
        $record->course = $this->course;
        $record->intro = $this->intro;
        $record->timecreated = $this->timecreated;
        $record->timemodified = $this->timemodified;
        $record->frases_empresa = $this->frases_empresa;
        $record->frases_centre = $this->frases_centre;
        $record->objecte = '';

        return $record;
    }

    private function encode() {
        $record = new stdClass;
        $record->id = $this->id;
        $record->name = $this->name;
        $record->intro = $this->intro;
        $record->timecreated = $this->timecreated;
        $record->timemodified = $this->timemodified;
        $record->frases_empresa = $this->frases_empresa;
        $record->frases_centre = $this->frases_centre;
        $record->centre = $this->centre;
        $this->objecte = json_encode($record);

        return $this->objecte;
    }

    public function decode() {
        if (isset($this->objecte)) {
            $objectedata = json_decode($this->objecte);
            foreach ((array)$objectedata as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function add() {
        global $DB;

        $record = $this->record();

        if (!isset($this->id) || !$this->id) {
            if ($record->id = $DB->insert_record('fct', $record)) {
                $this->create_centre();
                $this->id = $record->id;
            }
        }

        $record->objecte = $this->encode();

        $DB->update_record('fct', $record);
    }

    public function delete() {
        global $DB, $CFG;

        require_once('fct_quadern.php');
        require_once('fct_cicle.php');

        if ($cicles = $DB->get_records('fct_cicle', array('fct' => $this->id))) {
            foreach ($cicles as $cicle) {
                if ($quaderns = $DB->get_records('fct_quadern', array('cicle' => $cicle->id))) {
                    foreach ($quaderns as $record) {
                        $quadern = new fct_quadern((int)$record->id);
                        $quadern->delete();
                    }
                }

                $cicle = new fct_cicle((int)$cicle->id);
                $cicle->delete();
            }
        }
        $DB->delete_records('fct', array('id' => $this->id));
        $this->fct = false;
    }

    public function create_centre() {
        $this->centre = new stdClass;
        $this->centre->nom = '';
        $this->centre->adreca = '';
        $this->centre->codi_postal = '';
        $this->centre->poblacio = '';
        $this->centre->telefon = '';
        $this->centre->fax = '';
        $this->centre->email = '';

    }
}
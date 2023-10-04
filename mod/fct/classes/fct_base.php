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
 * Fct mod main abstract class.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('OBERT', 'obert');
define('TANCAT', 'tancat');
define('PROPOSAT', 'proposat');

require_once('fct_usuari.php');
require_once('fct_quadern_base.php');

abstract class fct_base {

     /**
      * Dabatase columns array.
      * @var array
      */
    protected $record_keys = array();
     /**
      * Keys of serialized data.
      * @var array
      */
    protected $objecte_keys = array();

     /**
      * Associated database table.
      * @var string
      */
     protected static $table = '';

     /**
      * Class asociated editform. Called from edit.php
      * @var string
      */
     protected $editform = '';

     protected $objecte = '';

     public $usuari;
     protected $fct;

      /**
       * Fct base class construct method. If $record param is int, get's associated record from database decoding database object.
       * If $record is an array, fct_base class instantiates properties acording with $record keys.
       * @param int | array
       * @return bool true
       */
    public function __construct($record = null) {
        global $USER;

        if (is_numeric($record)) {
            global $DB;

            if ($data = $DB->get_record(static::$table, array('id' => $record))) {

                foreach ($data as $key => $value) {
                    if (property_exists(get_class($this), $key)) {
                        $this->$key = $value;
                    }
                }

                if (isset($data->objecte) && !empty($data->objecte)) {
                    $this->objecte = $data->objecte;
                    $objectdata = json_decode($data->objecte);
                }
                if (isset($objectdata)) {
                    foreach ($objectdata as $key => $value) {

                        if (property_exists(get_class($this), $key)) {
                            $this->$key = $value;
                        }

                        if (is_object($value)) {
                            $valuearray = (array)$value;

                            foreach ($valuearray as $key => $value) {
                                if (property_exists(get_class($this), $key)) {
                                    $this->$key = $value;
                                }
                            }
                        }
                    }
                }
            }

            if (isset($this->fct)) {
                $this->usuari = new fct_usuari($this->fct, $USER->id);
            }

            return true;
        }

        if (isset($record)) {

            if (isset($record->objecte) && !empty($record->objecte)) {
                $record = json_decode($record->objecte);
            }

            foreach ($record as $key => $value) {
                if (property_exists(get_class($this), $key)) {
                    $this->$key = $value;
                }
            }

        }

        if (isset($this->fct)) {
              $this->usuari = new fct_usuari($this->fct, $USER->id);
        }

        return true;
    }

    public function __get($name) {

        if (property_exists(get_class($this), $name)) {
            return $this->$name;
        }
        if ($name == 'alumne') {
            if (isset($this->quadern)) {
                if ($quadern = new fct_quadern_base((int)$this->quadern)) {
                    return $quadern->alumne;
                }
            }
        }
        return false;
    }

    /**
     * Get all records from class table associated to a fct instance.
     *
     * @param  int $fctid fct instance id
     * @return array An array of records associated to the fct instance.
     */

    public static function get_records($fctid, $usuari = false, $searchparams = false, $pagenumber = false) {
        global $DB;

        $records = array();
        $records = $DB->get_records(static::$table, array('fct' => $fctid));

        return $records;
    }

    /**
     * Instantiate class properties with class form data.
     * @param stcClass $data class forrm data.
     */
    public function set_data($data) {

        foreach ((array)$data as $key => $value) {
            if (property_exists(get_class($this), $key)) {
                $this->$key = $value;
            }
        }

        $this->create_object();

    }

    /**
     * Create serialized class object serializing properties indexed in $objectekey array.
     * @return bool true
     */
    public function create_object() {

        $object = array();

        foreach ($this->objecte_keys as $objecte_key) {
            $object[$objecte_key] = $this->$objecte_key;
        }

        $this->objecte = json_encode($object);

        return true;
    }

    /**
     * Returns an object with class properties indexed in $objectekey array.
     *
     * @return stdClass
     */
    public function get_object_data () {
        global $DB;

        $objectdata = new stdClass;

        foreach ($this->objecte_keys as $objecte_key) {
            $objectdata->$objecte_key = $this->$objecte_key;
        }

        return $objectdata;
    }


    /**
     * Inserts a new record in to class associated table.
     *
     * @param  stdClass $data data to be inserted.
     */
    public function insert($data) {
        global $DB;

        if (empty(static::$table)) {
            throw new coding_exception('notableobject');
        }

        $record = array_intersect_key((array)$data, array_flip($this->record_keys));

        if ($data->id = $DB->insert_record(static::$table, $record)) {
            self::__construct($data);
            if (!isset($this->objecte_keys) || empty($this->objecte_keys)) {
                return true;
            }
            $this->create_object();
            $this->update();
            return true;
        }

        return false;
    }

    public function update() {
        global $DB;

        $record = array();

        foreach ($this->record_keys as $key) {
            if (isset($this->$key)) {
                $record[$key] = $this->$key;
            }
        }

        $DB->update_record(static::$table, $record);
    }

    public function deleteall($fctid, $quadernid = false) {
        global $DB;

        if ($quadernid) {
            $DB->delete_records(static::$table, array('quadern' => $quadernid));
        } else {
            if (!in_array('fct', $this->record_keys)) {
                $DB->delete_records(static::$table, array('fct' => $fctid));
            } else {
                print_error('novalidparamdelete');
            }
        }
    }

    public function no_delete_message() {
        return '';
    }

    public function get_edit_form($data = array()) {
        $editform = new $this->editform(null, $data);
        return $editform;
    }

    public function checkpermissions($type = 'view') {
        return true;
    }

    protected function tabs_general($id) {
        $tab = array();

        $row = array();
        $row['quaderns'] = new tabobject('quaderns',
                                     new moodle_url('view.php', array('id' => $id, 'page' => 'quadern')),
                                     get_string('quaderns', 'mod_fct'));

        if ($this->usuari->es_administrador || $this->usuari->es_tutor_centre) {

            $row['aviso'] = new tabobject('avisos',
                                         new moodle_url('view.php', array('id' => $id, 'page' => 'avisos')),
                                         get_string('avisos', 'mod_fct'));

        }

        if ($this->usuari->es_administrador || $this->usuari->es_tutor_centre) {

            $row_admin['cicle'] = new tabobject('cicles',
                                        new moodle_url('view.php', array('id' => $id, 'page' => 'cicle')),
                                        get_string('cicles_formatius', 'fct'));
            $row = array_merge($row, $row_admin);
        }

        if ($this->usuari->es_administrador) {

            $row_admin['dades_centre'] = new tabobject('dades_centre',
                                            new moodle_url('view.php', array('id' => $id, 'page' => 'dades_centre')),
                                            get_string('dades_centre', 'fct'));

            $row_admin['frases_retroaccio'] = new tabobject('frases_retroaccio',
                                  new moodle_url('view.php', array('id' => $id, 'page' => 'frases_retroaccio')),
                                  get_string('frases_retroaccio', 'fct'));

            $row_admin['llista_empreses'] = new tabobject('llista_empreses',
                                  new moodle_url('edit.php', array('cmid' => $id, 'page' => 'llista_empreses')),
                                  get_string('llista_empreses', 'fct'));

            $row_admin['tutor'] = new tabobject('tutor',
                                  new moodle_url('edit.php', array('cmid' => $id, 'page' => 'tutor')),
                                  get_string('afegeix_tutor_empresa', 'fct'));

            $row_admin['tancar_quaderns'] = new tabobject('tancar_quaderns',
                                  new moodle_url('edit.php', array('cmid' => $id, 'page' => 'tancar_quaderns')),
                                  get_string('tancar_quaderns', 'fct'));

            $row = array_merge($row, $row_admin);
        }

          $tab['row'] = $row;
          $tab['inactivetabs'] = array();

        return $tab;
    }

    protected function tabs_quadern($id, $quadernid) {

        $row = array();

        $row['quadern_main'] = new tabobject('quadern_main',
                                     new moodle_url('view.php', array('id' => $id, 'quadern' => $quadernid, 'page' => 'quadern_main')),
                                     get_string('quadern', 'mod_fct'));

        $row['quadern_dades'] = new tabobject('quadern_dades',
                                     new moodle_url('view.php', array('id' => $id, 'quadern' => $quadernid, 'page' => 'quadern_dades')),
                                     get_string('dades_generals', 'mod_fct'));

        $row['quadern_activitat'] = new tabobject('quadern_activitat',
                                    new moodle_url('view.php', array('id' => $id, 'quadern' => $quadernid, 'page' => 'quadern_activitat')),
                                    get_string('pla_activitats', 'fct'));

        $row['quadern_quinzena'] = new tabobject('quadern_quinzena',
                                        new moodle_url('view.php', array('id' => $id, 'quadern' => $quadernid, 'page' => 'quadern_quinzena')),
                                        get_string('seguiment_quinzenal', 'fct'));

        $row['quadern_valoracio'] = new tabobject('quadern_valoracio',
                              new moodle_url('view.php', array('id' => $id, 'quadern' => $quadernid, 'page' => 'quadern_valoracio', 'valoracio' => 'parcial')),
                              get_string('valoracio', 'fct'));

        $row['quadern_qualificacio'] = new tabobject('quadern_qualificacio',
                              new moodle_url('view.php', array('id' => $id, 'quadern' => $quadernid, 'page' => 'quadern_qualificacio', 'qualificaciotype' => 'global')),
                              get_string('qualificacio_global', 'fct'));

        $tab['row'] = $row;
        $tab['inactivetabs'] = $this->tabs_inactive_quadern($id, $quadernid);

        return $tab;
    }

    private function tabs_inactive_quadern($id, $quadernid) {
        $quadern = new fct_quadern_base((int)$quadernid);

        $inactivetabs = array();
        if ($quadern->estat == 'proposat' and !$this->usuari->es_administrador) {
            $inactivetabs = array('quadern_quinzena', 'quadern_valoracio', 'quadern_qualificacio');
        }

        return $inactivetabs;
    }

    public static function validation($data) {
        return array();
    }

    abstract public function prepare_form_data($data);

    protected static function comprovar_dni($document, $inputname, $required = false, $newuser = false) {
        global $CFG, $DB;

        $dni = strtolower(trim($document));

        if (!$required and empty($dni)) {
            return true;
        }

        $fchar = substr($dni, 0, 1);
        $letter = substr($dni, -1, 1);
        $number = substr($dni, 0, 8);
        // NIE
        if ($fchar == 'x' or $fchar == 'y' or $fchar == 'z') {
            if (!preg_match('/^[xyz][0-9]{7}[a-z]$/', $dni)) {
                return array($inputname => fct_string('nie_no_valid'));
            }
            $number = str_replace(array('x', 'y', 'z'), array(0, 1, 2), $number);

        } else { // DNI
            if (!preg_match('/^[0-9]{8}[a-z]$/', $dni)) {
                return array($inputname => fct_string('dni_no_valid'));
            }
        }

        if ($newuser and $DB->record_exists('user', array('username' => $dni, 'deleted' => 0,
                            'mnethostid' => $CFG->mnet_localhost_id))) {
              return array($inputname => fct_string('dni_existent'));
        }

        $mod = $number % 23;
        $validletters = strtolower("TRWAGMYFPDXBNJZSQVHLCKE");
        $correctletter = substr($validletters, $mod, 1);

        if ($correctletter != $letter) {
            return array($inputname => get_string('dni_lletra_incorrecta', 'fct'));
        }
        return true;
    }
}

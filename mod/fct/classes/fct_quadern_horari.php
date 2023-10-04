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
 * Quadern horari FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_horari_edit_form.php');
require_once('fct_quadern_base.php');
require_once('fct_base.php');
require_once('fct_cicle.php');

define('DILLUNS', 'Dilluns');
define('DIMARTS', 'Dimarts');
define('DIMECRES', 'Dimecres');
define('DIJOUS', 'Dijous');
define('DIVENDRES', 'Divendres');
define('DISSABTE', 'Dissabte');
define('DIUMENGE', 'Diumenge');

class fct_quadern_horari extends fct_quadern_base {


    protected static $dataobject = 'convenis';

    protected $editform = 'fct_quadern_horari_edit_form';

    public $convenis;

    protected static $dataobjectkeys = array();

    protected $dies = array(DILLUNS => 'dilluns',
                            DIMARTS => 'dimarts',
                            DIMECRES => 'dimecres',
                            DIJOUS => 'dijous',
                            DIVENDRES => 'divendres',
                            DISSABTE => 'dissabte',
                            DIUMENGE => 'diumenge');


    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);
        $subtree = parent::subtree($id, $this->id);

        $row = $tab['row'];
        $row['quadern_dades']->subtree = $subtree;
        $tab['row'] = $row;
        $tab['currentab'] = 'quadern_horari';

        return $tab;
    }

    public function view() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct', 'quadern_horari');
        $output->view($this);

        return true;

    }

    public function delete($params) {

        $uuid = $params->uuid;

        if (!$conveni = $this->convenis->$uuid) {
            print_error('notvaliduuid');
        }
        if (isset($conveni->horari)) {
            foreach ($conveni->horari as $key => $value) {
                $horari = $value;
                if ($horari->dia == $params->dia && $horari->hora_inici == $params->hora_inici && $horari->hora_final == $params->hora_final) {
                    unset($this->convenis->$uuid->horari[$key]);
                }
            }
        }
        $this->create_object();
        $this->update();
        return true;
    }

    public function delete_message() {
        return get_string('segur_suprimir_franja', 'fct');
    }

    public function get_convenis() {

        if (isset($this->convenis)) {
            $arrayconvenis = (array)$this->convenis;

            $convenis = array();

            foreach ($arrayconvenis as $arrayconveni) {
                $conveni = new stdClass;
                foreach ((array)$arrayconveni as $key => $value) {
                    $conveni->$key = $value;
                }
                $convenis[] = $conveni;
            }
            return $convenis;
        } else {
            return false;
        }
    }

    public static function validation($data) {
    }

    public function set_data($data) {

        $conveni = $data->conveni;

        $data->convenis = $this->convenis;

        $hora_inici = ((float) $data->hourfrom + (float) $data->minutfrom / 60);
        $hora_final = ((float) $data->hourto + (float) $data->minutto / 60);

        $data->convenis->$conveni->horari[] = array('dia' => $this->dies[$data->dies],
                                                  'hora_inici' => $hora_inici,
                                                  'hora_final' => $hora_final);

        self::$dataobjectkeys = $this->get_uuids();

        parent::set_data($data);
    }

    private function get_uuids() {

        if (isset($this->convenis)) {
            $uuids = array();
            foreach ((array)$this->convenis as $conveni) {
                $uuids[] = $conveni->uuid;
            }

            return $uuids;

        } else {
            return false;
        }
    }


    private function uuid() {

        $octets = array();

        for ($n = 0; $n < 16; $n++) {
            $octets[] = mt_rand(0, 255);
        }

        $octets[8] = ($octets[8] | 0x80) & 0xbf; // variant ISO/IEC 11578:1996
        $octets[6] = ($octets[6] & 0x0f) | 0x40; // version 4 (random)

        return sprintf('%02x%02x%02x%02x-%02x%02x-%02x%02x-%02x%02x'
                       .'-%02x%02x%02x%02x%02x%02x',
                       $octets[0], $octets[1], $octets[2], $octets[3],
                       $octets[4], $octets[5], $octets[6], $octets[7],
                       $octets[8], $octets[9], $octets[10], $octets[11],
                       $octets[12], $octets[13], $octets[14], $octets[15]);

    }

    public function prepare_form_data($data) {

        $convenisarray = (array)$data->convenis;

        foreach ($convenisarray as $conveni) {
            $convenis[$conveni->uuid] = $conveni->codi;
        }

        $data->convenis = $convenis;
        $data->dies = $this->dies;

        parent::prepare_form_data($data);

    }


    protected function prepare_form_select($objects, $selectkey, $selectvalue, $selected = false) {
        $select = array();

        if (!$selected) {
            $select[0] = '';
        }

        foreach ($objects as $object) {
            $select[$object->$selectkey] = $object->$selectvalue;
        }

        return $select;

    }

}

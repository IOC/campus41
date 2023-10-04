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
 * Quadern conveni FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_convenis_edit_form.php');
require_once('fct_quadern_base.php');
require_once('fct_base.php');
require_once('fct_cicle.php');
require_once('fct_conveni.php');

class fct_quadern_conveni extends fct_quadern_base {


    protected static $dataobject = 'convenis';

    protected $editform = 'fct_quadern_convenis_edit_form';

    public $convenis;

    protected static $dataobjectkeys = array();

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);
        $subtree = parent::subtree($id, $this->id);

        $row = $tab['row'];
        $row['quadern_dades']->subtree = $subtree;
        $tab['row'] = $row;
        $tab['currentab'] = 'quadern_conveni';

        return $tab;
    }

    public function view() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct',
            'quadern_convenis');

        $output->view($this);

        return true;

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

    public function set_data($data) {

        $convenis = $this->convenis;

        if ($uuids = $this->get_uuids()) {

            foreach ($uuids as $uuid) {
                $pregmatchexp = '"'.'/^'.$uuid.'_/'.'"';

                $arrayfiltered = array_filter(array_flip((array)$data), create_function('$a', 'return preg_match('.$pregmatchexp.', $a);'));

                $conveni = new stdClass;

                $uuidelete = $uuid.'_delete_conveni';

                if (isset($data->$uuidelete)) {
                    unset($convenis->$uuid);
                    continue;
                }

                foreach (array_flip($arrayfiltered) as $key => $value) {
                    $key = preg_replace('/^'.$uuid.'_/', '', $key);

                    $convenis->$uuid->$key = $value;
                }
            }
        }

        if (isset($data->new_codi) && !empty($data->new_codi)) {

            $record = new stdClass;

            $uuid = $this->uuid();
            $uuids[] = $uuid;

            $record->uuid = $uuid;
            $record->codi = $data->new_codi;
            $record->data_inici = $data->new_data_inici;
            $record->data_final = $data->new_data_final;
            $conveni = new fct_conveni($record);

            $uuid = $conveni->uuid();
            $conveni->uuid = $uuid;
            $convenis->$uuid = $conveni;

        }

        self::$dataobjectkeys = $uuids;

        $data->convenis = $convenis;

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


    public function uuid() {

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

}

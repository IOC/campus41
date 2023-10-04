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
 * Tancar quaderns FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2015 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('fct_base.php');
require_once('form/tancar_quaderns_form.php');

class fct_tancar_quaderns extends fct_quadern_base {

    public $editform = 'fct_tancar_quaderns_form';
    public $returnurl;

    public function __construct($record = null) {
        parent::__construct($record);
        $cm = get_coursemodule_from_instance('fct', $this->fct);
        $params = array(
            'cmid' => $cm->id,
            'page' => 'tancar_quaderns',
        );
        $this->returnurl = new moodle_url('/mod/fct/edit.php', $params);
    }

    public function tabs($id, $type = 'view') {
        $tab = parent::tabs_general($id);
        $tab['currentab'] = 'tancar_quaderns';

        return $tab;
    }

    public function insert($data) {
        global $DB;

        if ($data->tancaquaderns == 'oberts') {
            $where = 'estat=:estat1';
            $params = array(
                'estat1' => 'obert',
            );
        } else if ($data->tancaquaderns == 'proposats') {
            $where = 'estat=:estat1';
            $params = array(
                'estat1' => 'proposat',
            );
        } else {
            $where = 'estat=:estat1 OR estat=:estat2';
            $params = array(
                'estat1' => 'obert',
                'estat2' => 'proposat',
            );
        }

        $qids = $DB->get_records_select('fct_quadern', $where, $params, '', 'id');
        $obj = new stdClass;
        $obj->estat = 'tancat';
        foreach ($qids as $qid) {
            $quadern = new fct_quadern_base((int)$qid->id);
            $quadern->estat = 'tancat';
            $quadern->set_data($obj);
            $quadern->update();
        }
    }

    public function checkpermissions($type = 'view') {
        if (!$this->usuari->es_administrador) {
            print_error('nopermissions', 'fct');
        }
    }

    public function prepare_form_data($data) {
        global $DB;
        $data->quadernsoberts = $DB->count_records_select('fct_quadern', 'estat=:estat', array('estat' => 'obert'));
        $data->quadernsproposats = $DB->count_records_select('fct_quadern', 'estat=:estat', array('estat' => 'proposat'));
    }

}

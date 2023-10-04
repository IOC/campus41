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
 * Quadern empresas FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form/quadern_empresa_edit_form.php');
require_once('fct_quadern_base.php');
require_once('fct_base.php');
require_once('fct_cicle.php');

class fct_quadern_empresa extends fct_quadern_base {


    protected static $dataobject = 'empresa';

    protected $editform = 'fct_quadern_empresa_edit_form';

    protected static $dataobjectkeys = array('nom',
                                             'adreca',
                                             'poblacio',
                                             'codi_postal',
                                             'telefon',
                                             'fax',
                                             'email',
                                             'nif',
                                             'codi_agrupacio',
                                             'sic',
                                             'nom_responsable',
                                             'cognoms_responsable',
                                             'dni_responsable',
                                             'carrec_responsable',
                                             'nom_tutor',
                                             'cognoms_tutor',
                                             'dni_tutor',
                                             'email_tutor',
                                             'nom_lloc_practiques',
                                             'adreca_lloc_practiques',
                                             'poblacio_lloc_practiques',
                                             'codi_postal_lloc_practiques',
                                             'telefon_lloc_practiques');

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_quadern($id, $this->id);
        $subtree = parent::subtree($id, $this->id);

        $row = $tab['row'];
        $row['quadern_dades']->subtree = $subtree;
        $tab['row'] = $row;
        $tab['currentab'] = 'quadern_empresa';

        return $tab;
    }

    public function set_data($data) {
        if (!$this->checkpermissions('edit_company_name')) {
            $data->nom = $this->nom_empresa;
        }
        $this->nom_empresa = $data->nom;
        parent::set_data($data);
    }

    public function view() {
        global $PAGE;

        $output = $PAGE->get_renderer('mod_fct',
            'quadern_empresa');

        $output->view($this);

        return true;

    }

    public static function validation($data) {

        $error1 = parent::comprovar_dni($data['dni_responsable'], 'dni_responsable');
        $error2 = parent::comprovar_dni($data['dni_tutor'], 'dni_tutor');

        if (!is_array($error1)) {
            $error1 = array();
        }
        if (!is_array($error2)) {
            $error2 = array();
        }

        $error = array_merge($error1, $error2);
        return empty($error) ? true : $error;
    }
}

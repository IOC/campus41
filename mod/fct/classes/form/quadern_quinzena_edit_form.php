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
 * FCT quinzena related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class fct_quadern_quinzena_edit_form extends moodleform {

    public $class = 'fct_quadern_quinzena';

    public function definition() {

        global $DB, $OUTPUT;

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $mform->addElement('header', 'header_quinzena', get_string('nova_quinzena', 'fct'));

        $mform->addElement('select', 'any', get_string('any', 'fct'), $data->anyselect);

        $mform->addElement('select', 'periode', get_string('periode', 'fct'), $data->periodeselect);

        $mform->addElement('text', 'dies', get_string('dies', 'fct'). ':');
        $mform->setType('dies', PARAM_TEXT);
        $mform->addRule('dies', get_string('nombres_separats_comes', 'mod_fct'),
                               'regex', "/^\s*([0-9]+(\s*,\s*[0-9]+)*)?\s*$/",
                               'client');

        $elements = array();
        $elements[] =& $mform->createElement('text', 'hores', '',
                                                    array('size' => 4));

        $elements[] =& $mform->createElement('select', 'minuts', '',
                                                        $this->opcions());
        $mform->setType('minuts', PARAM_INT);

        $mform->addGroup($elements, 'grouphores', get_string('hores', 'mod_fct') . ':',
                                ' ' . get_string('hores_i', 'mod_fct') . ' ');

        $mform->setType('grouphores[hores]', PARAM_INT);

        $mform->addElement('header', 'header_activitats', get_string('activitats_realitzades', 'fct'));

        if (isset($data->activitatscicle) && !empty($data->activitatscicle)) {
            foreach ($data->activitatscicle as $key => $value) {
                $mform->addElement('checkbox', 'activity_'.$key, '', $value);
            }
        } else {
                $html = '<center><strong>' . get_string('cap_activitat', 'fct'). '</center></strong>';
                $mform->addElement('html', $html);
        }

        $mform->addElement('header', 'header_valoracions', get_string('valoracions_observacions', 'fct'));
        $attributes = array('cols' => 50, 'rows' => 10);
        $mform->addElement('textarea' , 'valoracions', get_string('valoracions', 'mod_fct'), $attributes);
        $mform->addElement('textarea' , 'observacions_alumne', get_string('observacions', 'mod_fct'), $attributes);

        if (!$data->usuari->es_alumne) {
            $mform->addElement('header', 'header_retroaccio', get_string('retroaccio', 'fct'));

            $params = new stdClass;
            if (!isset($params->cols)) {
                $params->cols = 50;
            }
            if (!isset($params->rows)) {
                $params->rows = 4;
            }

            $mform->addElement('textarea', 'observacions_centre', get_string('tutor_centre', 'fct'),
                                      array('cols' => $params->cols,
                                            'rows' => $params->rows));

            $mform->setType('observacions_centre', PARAM_TEXT);

            $iconminusurl = $OUTPUT->image_url('t/switch_minus');
            $iconplusurl = $OUTPUT->image_url('t/switch_plus');

            $html = array('<div id="id_', 'observacions_centre', '_frases"',
                              ' class="frases_areatext amagat">', '<h4>',
                              '<img src="',  $iconplusurl,
                              '" /> ',
                              '<img class="amagat" src="',
                              $iconminusurl, '" /> ',
                              get_string('frases_retroaccio', 'mod_fct'),
                              '</h4>', '<ul class="amagat">');
            if (!empty($data->frases_centre)) {
                foreach ($data->frases_centre as $frase) {
                    $html[] = '<li>' . trim($frase) . '</li>';
                }
            }
            $html[] = '</ul>';

            $mform->addElement('static', '', '', implode('', $html));

            $mform->addElement('textarea', 'observacions_empresa', get_string('tutor_empresa', 'fct'),
                                      array('cols' => $params->cols,
                                            'rows' => $params->rows));

            $mform->setType('observacions_empresa', PARAM_TEXT);

            $html = array('<div id="id_', 'observacions_empresa', '_frases"',
                              ' class="frases_areatext amagat">', '<h4>',
                              '<img src="', $iconplusurl,
                              '" /> ',
                              '<img class="amagat" src="',
                              $iconminusurl, '" /> ',
                               get_string('frases_retroaccio', 'mod_fct'),
                              '</h4>', '<ul class="amagat">');
            if (!empty($data->frases_empresa)) {
                foreach ($data->frases_empresa as $frase) {
                    $html[] = '<li>' . trim($frase) . '</li>';
                }
            }
            $html[] = '</ul>';

            $mform->addElement('static', '', '', implode('', $html));
        }

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'quadern');
        $mform->setType('quadern', PARAM_INT);
        $mform->addElement('hidden', 'fct');
        $mform->setType('fct', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'objecte');
        $mform->setType('objecte', PARAM_TEXT);
        $mform->setDefault('objecte', '');

        $this->add_action_buttons();
        $this->set_data($data);
    }

    public function validation($data, $files) {
        $errors = array();
        $class = $this->class;
        $errors = $class::validation($data);
        return $errors;
    }

    public function opcions() {
        $opcions = array();
        for ($minuts = 0; $minuts < 60; $minuts += 15) {
            $opcions[$minuts] = $minuts . ' ' . get_string('minuts', 'fct');
        }
        return $opcions;
    }

    public function get_data() {

        $data = parent::get_data();
        if ($data) {
            $valor = (float) $data->grouphores['hores'];
            if (!empty($data->grouphores['minuts'])) {
                $valor += $data->grouphores['minuts'] / 60;
            }
            $data->hores = $valor;

            $datakeys = array_keys((array)$data);

            $pregmatchexp = '"'.'/^'.'activity'.'_/'.'"';

            $arrayfiltered = array_filter($datakeys, create_function('$a', 'return preg_match('.$pregmatchexp.', $a);'));
            $activitieskeys = array_map(create_function('$a', 'return preg_replace('.$pregmatchexp.', '."''".', $a);'), $arrayfiltered);

            $data->activitats = array_keys(array_flip($activitieskeys));
            $data->dies = explode(',', $data->dies);

            return $data;
        }
    }

    public function set_data($data) {

        $hores = floor($data->hores);
        $minuts = round(($data->hores - $hores) * 60);

        $data->grouphores['hores'] = $hores;
        $data->grouphores['minuts'] = $minuts;

        if (isset($data->dies)) {
            $data->dies = implode(',', $data->dies);
        }

        if (isset($data->activitats)) {
            foreach ($data->activitats as $activitat) {
                $activity = 'activity_'.$activitat;
                $data->$activity = 1;
            }
        }
        parent::set_data($data);
    }
}
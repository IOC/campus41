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
 * Centre related management form.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/mod/fct/classes/fct_quadern_empresa.php');
require_once($CFG->dirroot . '/mod/fct/classes/fct_cicle.php');
require_once($CFG->dirroot . '/lib/excellib.class.php');
require_once($CFG->dirroot . '/lib/filelib.php');

class fct_llista_empreses_edit_form extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $data = $this->_customdata['data'];

        $formats = array('csv' => get_string('format_csv', 'fct'), 'excel' => get_string('format_excel', 'fct'));

        if (isset($data->cicles)) {
            foreach ($data->cicles as $cicle) {
                $mform->addElement('checkbox', 'cicle_'.$cicle->id, '', $cicle->nom);
            }
            $mform->addelement('select', 'format', get_string('format', 'fct'), $formats);
            $this->add_action_buttons(false, get_string('download'));
        } else {
            $html = '<center> <strong>'. get_string('cap_cicle_formatiu', 'fct'). '</strong> </center>';
            $mform->addElement('html', $html);
        }

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'fct');
        $mform->setType('fct', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->set_data($data);
    }

    public function get_data() {
        $data = parent::get_data();

        if (!$data) {
            return false;
        }

        $datakeys = array_keys((array)$data);

        $pregmatchexp = '"'.'/^'.'cicle'.'_/'.'"';

        $arrayfiltered = array_filter($datakeys, create_function('$a', 'return preg_match('.$pregmatchexp.', $a);'));

        $ciclesid = array_map(
                    create_function('$string', 'return substr($string,6,6);'),
                    $arrayfiltered);

        $files = fct_llista_empreses::create_file_data($ciclesid);

        switch ($data->format) {
            case 'excel':
                $workbook = new MoodleExcelWorkbook('-');
                $workbook->send(get_string('llista_empreses', 'fct') . '.xls');
                $worksheet = array();
                $worksheet = $workbook->add_worksheet(get_string('llista_empreses', 'fct'));
                foreach ($files as $fila => $columnes) {
                    foreach ($columnes as $columna => $camp) {
                         $worksheet->write_string($fila, $columna, $camp);
                    }
                }
                $workbook->close();
                die;
                break;
            case 'csv':
                $csv = array();
                foreach ($files as $columnes) {
                    foreach ($columnes as $camp) {
                        $csv[] = '"' . $camp .'",';
                    }
                    $csv[] = "\n";
                }
                $csv = implode($csv);
                send_file($csv, fct_string('llista_empreses') . '.csv', 'default', 0, true, true, '');
                die;
                break;

            default:
                print_error('invalidexportformat');
                break;
        }
    }
}
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
 * FCT quadern class.
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('fct_quadern_base.php');

require_once('form/quadern_edit_form.php');
require_once('form/quadern_search_form.php');

class fct_quadern extends fct_quadern_base {

    protected $editform = 'fct_quadern_edit_form';

    public function tabs($id, $type = 'view') {

        $tab = parent::tabs_general($id);

        $tab['currentab'] = 'quaderns';

        if ($this->usuari->es_administrador) {

            $row = $tab['row'];
            $activerow = $row['quaderns'];
            $activerow->subtree[] = new tabobject('quadernlist', new moodle_url('/mod/fct/view.php',
                                                                           array('id' => $id)),
                                                                           get_string('quaderns', 'fct'));

            $activerow->subtree[] = new tabobject('afegeix_quadern', new moodle_url('/mod/fct/edit.php',
                                                    array('cmid' => $id)),
                                                    get_string('afegeix_quadern', 'fct'));

            $row['quaderns'] = $activerow;
            $tab['row'] = $row;
            $tab['currentab'] = $type == 'edit' ? 'afegeix_quadern' : 'quadernlist';
        }
        return $tab;
    }

    public function view($id = false, $index, $searchparams = false) {
        global $PAGE, $USER, $OUTPUT;

        if (!$id) {

            $output = $PAGE->get_renderer('mod_fct', 'quaderns');

            $searchdata = false;
            if (!$this->usuari->es_alumne) {

                $cursos = $this->valors_curs();

                $searchdata = new stdClass;
                $searchdata->id = $PAGE->cm->id;
                $searchdata->cursos = $this->valors_curs();
                $searchdata->cicles = $this->valors_cicles();
                $searchdata->estats = $this->valors_estat();
                $searchdata->curs = $searchparams->searchcurs;
                $searchdata->cicle = $searchparams->searchcicle;
                $searchdata->estat = $searchparams->searchestat;
                $searchdata->cerca = $searchparams->cerca;

                if ($searchform = $this->search_form(array('searchdata' => $searchdata))) {
                    $searchform->display();
                }
            }

            if ($quaderns = self::get_records($this->fct, $this->usuari, $searchdata, $index)) {

                $urlparams = array();
                $urlparams['id'] = $PAGE->cm->id;
                $urlparams['searchcurs'] = $searchparams->searchcurs;
                $urlparams['searchestat'] = $searchparams->searchestat;
                $urlparams['cerca'] = $searchparams->cerca;
                $urlparams['searchcicle'] = $searchparams->searchcicle;

                $baseurl = new moodle_url('/mod/fct/view.php', $urlparams);

                echo $OUTPUT->paging_bar($quaderns['totalrecords'], $index, PAGENUM, $baseurl, 'index');

                $table = $output->quaderns_table($quaderns['records'], $urlparams);
                echo $table;
                echo $OUTPUT->paging_bar($quaderns['totalrecords'], $index, PAGENUM, $baseurl, 'index');
            } else {
                echo $OUTPUT->notification(get_string('cap_quadern', 'fct'));
            }

            $editlink = $output->editlink($this->fct, $USER->id);
            echo $editlink;

        }
    }

    public function insert($data) {
        if (!isset($this->id)) {
            $date = getdate();
            $data->data_final = mktime(0, 0, 0, (int) $date['mon'], (int) $date['mday'], $date['year'] + 1);

            $record = new stdClass;
            $record->data_inici = mktime(0, 0, 0, (int) $date['mon'], (int) $date['mday'], $date['year']);
            $record->data_final = mktime(0, 0, 0, (int) $date['mon'], (int) $date['mday'], $date['year'] + 1);
            $record->horari = array();

            $conveni = new fct_conveni($record);
            $uuid = $conveni->uuid();
            $conveni->uuid = $uuid;

            $this->convenis = new stdClass;
            $this->convenis->$uuid = $conveni;

        }

        parent::insert($data);
    }

    public static function get_records($fctid, $usuari = false, $searchparams = false, $index = null) {
        global $DB;

        $records = new stdClass;

        $tables = "{fct_quadern} q"
            . " JOIN {fct_cicle} c ON q.cicle = c.id"
            . " JOIN {user} ua ON q.alumne = ua.id"
            . " LEFT JOIN {user} uc ON q.tutor_centre = uc.id"
            . " LEFT JOIN {user} ue ON q.tutor_empresa = ue.id";

        $select = array();
        $select[] = "c.fct = $fctid";

        $selectuser = array();

        if (!$usuari->es_administrador) {

            if ($usuari->es_alumne) {
                $selectuser[] = "ua.id = $usuari->id";
            }

            if ($usuari->es_tutor_empresa) {
                $selectuser[] = "ue.id = $usuari->id";
            }

            if ($usuari->es_tutor_centre) {
                $selectuser[] = "uc.id = $usuari->id";
            }

            if (count($selectuser) > 0) {
                $select[] = '(' . implode(' OR ', $selectuser) . ')';
            }
        }

        if (isset($searchparams->cicle) && $searchparams->cicle) {
            $select[] = "cicle = $searchparams->cicle";
        }

        if (isset($searchparams->estat) && !empty($searchparams->estat)) {
            $select[] = "estat = '$searchparams->estat'";
        }

        if (isset($searchparams->curs) && $searchparams->curs) {
            $mindatafinal = mktime(0, 0, 0, 9, 1, $searchparams->curs);
            $maxdatafinal = mktime(0, 0, 0, 9, 1, $searchparams->curs + 1);

            $select[] = "q.data_final >= $mindatafinal";
            $select[] = "q.data_final <= $maxdatafinal";
        }

        if (isset($searchparams->cerca) && !empty($searchparams->cerca)) {
            $fields = array("CONCAT(ua.firstname, ' ', ua.lastname)",
                            "q.nom_empresa",
                            "CONCAT(uc.firstname, ' ', uc.lastname)",
                            "CONCAT(ue.firstname, ' ', ue.lastname)");
            $selectcerca = array();
            foreach ($fields as $field) {
                $selectcerca[] = "$field LIKE '%"
                    . $searchparams->cerca . "%'";
            }
            $select[] = '(' . implode(' OR ', $selectcerca) . ')';
        }

        $wherecondition = implode(' AND ', $select);

        $sql = "SELECT q.*"
             . ' FROM ' . $tables
             . ' WHERE ' . $wherecondition
             . ' ORDER by q.data_final DESC';

        $countsql = "SELECT count(1)"
                  . ' FROM ' . $tables
                  . ' WHERE ' . $wherecondition;

        if (!$records = $DB->get_records_sql($sql, null,  $index * PAGENUM, PAGENUM)) {
            return false;
        } else {
            foreach ($records as $record) {
                $quaderns[] = new fct_quadern($record->id);
            }
        }
        $totalrecords = $DB->count_records_sql($countsql);

        return array('records' => $quaderns, 'totalrecords' => $totalrecords);
    }

    public function delete_message() {
        global $DB;

        $user = $DB->get_record('user', array('id' => $this->alumne));
        $fullname = fullname($user);
        return get_string('segur_suprimir_quadern', 'fct', $fullname . ' (' . $this->nom_empresa . ')');
    }

    public function delete() {
        global $DB;

        if (!isset($this->id)) {
            print_error('noidgiven');
        }

        try {
            $transaction = $DB->start_delegated_transaction();

            $DB->delete_records('fct_activitat', array('quadern' => $this->id));
            $DB->delete_records('fct_quinzena', array('quadern' => $this->id));
            $DB->delete_records('fct_quadern', array('id' => $this->id));

            $transaction->allow_commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    public function get_object_data() {

        $objectdata = parent::get_object_data();
        $objectdata->nom_empresa = $this->nom_empresa ? $this->nom_empresa : '';

        return $objectdata;
    }


    public function prepare_form_data($data) {
        global $DB, $USER;

        if (!isset($this->fct)) {
            print_error('nofct');
        }

        $usuari = new fct_usuari($this->fct, $USER->id);
        $data->es_alumne = $usuari->es_alumne;
        $data->es_tutor_centre = $usuari->es_tutor_centre;
        $data->es_administrador = $usuari->es_administrador;

        $cm = get_coursemodule_from_instance('fct', $this->fct);
        $context = context_course::instance($cm->course);

        if ($data->es_alumne) {
            $data->alumne = $usuari->id;
            $data->estat = PROPOSAT;
        } else {
            $alumnes = get_role_users(5, $context);
            foreach ($alumnes as $alumne) {
                $alumne->fullname = fullname($alumne);
            }
            $data->alumnes = $this->prepare_form_select($alumnes, 'id', 'fullname');

            $roleid = $DB->get_field('role', 'id', array('shortname' => 'tutorempresa'));
            $tutorsempresa = get_role_users($roleid, $context);

            foreach ($tutorsempresa as $tutorempresa) {
                $tutorempresa->fullname = fullname($tutorempresa);
            }

            $data->tutors_empresa = array('' => '-') + $this->prepare_form_select($tutorsempresa, 'id', 'fullname');
            core_collator::asort($data->tutors_empresa);
            $data->estats = self::$estats;
            if (!$this->usuari->es_administrador && $this->usuari->es_tutor_centre) {
                unset($data->estats[TANCAT]);
            }
        }

        $cicles = fct_cicle::get_records($this->fct);
        $data->cicles = $this->prepare_form_select($cicles, 'id', 'nom', $data->cicle);

        // Remove LOGSE cicles.
        $filter = function($value) {
            return !preg_match('/(?<!\()logse(?!\))/i', $value);
        };
        $data->cicles = array_filter($data->cicles, $filter);

        $records = get_users_by_capability($context, 'mod/fct:tutor_centre');

        foreach ($records as $record) {
            $record->fullname = fullname($record);
        }

        $data->tutors_centre = array('' => '-') + $this->prepare_form_select($records, 'id', 'fullname');
        core_collator::asort($data->tutors_centre);
    }

    protected function prepare_form_select($objects, $selectkey, $selectvalue) {
        $select = array();

        foreach ($objects as $object) {
            $select[$object->$selectkey] = $object->$selectvalue;
        }

        return $select;
    }

    public function search_form($searchdata) {
        $searchform = new fct_quadern_search_form(null, $searchdata);
        return $searchform;
    }

    public function valors_curs() {
        $cursos = array(0 => get_string('tots', 'fct'));

        list($min, $max) = $this->min_max_data_final_quaderns();

        if (!$min or !$max) {
            $this->curs = false;
            return $cursos;
        }

        $min = getdate($min);
        $max = getdate($max);
        $any_min = ($min['mon'] >= 9 ? $min['year'] : $min['year'] - 1);
        $any_max = ($max['mon'] >= 9 ? $max['year'] : $max['year'] - 1);

        for ($curs = $any_max; $curs >= $any_min; $curs--) {
            $cursos[$curs] = $curs . '-' . ($curs + 1);
        }
        return $cursos;
    }

    public function valors_cicles() {
        global $DB;

        $valorscicle = array(0 => get_string('tots', 'fct'));

        if ($cicles = $DB->get_records('fct_cicle', array('fct' => $this->fct))) {
            foreach ($cicles as $cicle) {
                $valorscicle[$cicle->id] = $cicle->nom;
            }
        }

        return $valorscicle;

    }

    public function valors_estat() {
        $estats = array('' => get_string('tots', 'fct'),
                        'proposat' => get_string('estat_proposat', 'fct'),
                        'obert' => get_string('estat_obert', 'fct'),
                        'tancat' => get_string('estat_tancat', 'fct'));
        return $estats;
    }

    public function min_max_data_final_quaderns() {
        global $CFG, $DB;

        $fctid = $this->fct;
        $sql = "SELECT MIN(q.data_final) AS min_data_final,"
            . " MAX(q.data_final) AS max_data_final"
            . " FROM {fct_quadern} q"
            . " JOIN {fct_cicle} c ON c.id = q.cicle"
            . " WHERE c.fct = $fctid AND q.data_final > 0";

        $record = $DB->get_record_sql($sql);

        return array($record->min_data_final, $record->max_data_final);
    }

    public function checkpermissions($type = 'view') {

        if ($this->usuari->es_administrador) {
            return true;
        }

        switch ($type) {

            case 'edit':
                if (isset($this->id)) {
                    if (($this->usuari->es_tutor_centre && ($this->usuari->id != $this->tutor_centre)) ||
                        (!$this->usuari->es_tutor_centre && !$this->usuari->es_administrador)) {
                        print_error('nopermissions', 'fct');
                    }
                } else {
                    if ($this->usuari->es_tutor_empresa) {
                            print_error('nopermissions', 'fct');
                    }
                }
                return true;
                break;

            case 'editlink':
                if (isset($this->id)) {
                    if (($this->usuari->es_tutor_centre && ($this->usuari->id != $this->tutor_centre)) ||
                        (!$this->usuari->es_tutor_centre && !$this->usuari->es_administrador)) {
                        return false;
                    }
                } else {
                    if ($this->usuari->es_tutor_empresa) {
                        return false;
                    }
                }
                return true;
                break;

            case 'deletelink' :
                if (!$this->usuari->es_administrador) {
                    return false;
                }
                return true;
                break;

            case 'delete' :
                if ($this->usuari->es_tutor_centre and $this->estat == 'proposat'
                    and $this->usuari->id == $this->tutor_centre) {
                    return true;
                }
                if (!$this->usuari->es_administrador) {
                    print_error('nopermissions', 'fct');
                }
                return true;
                break;

            case 'default' :

                if (!$this->usuari->es_administrador and !$this->usuari->es_alumne
                and !$this->usuari->es_tutor_centre and !$this->usuari->es_tutor_empresa) {
                    print_error('permis_activitat');
                }
                return true;
                break;
        }

    }
}
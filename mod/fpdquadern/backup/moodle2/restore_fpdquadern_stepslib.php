<?php
/**
 * @package mod_fpdquadern
 * @copyright 2014 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

class restore_fpdquadern_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {
        $paths = array();

        $paths[] = new restore_path_element('quadern', '/activity/quadern');
        $paths[] = new restore_path_element(
            'activitat', '/activity/quadern/activitats/activitat');
        $paths[] = new restore_path_element(
            'competencia', '/activity/quadern/competencies/competencia');
        $paths[] = new restore_path_element(
            'element_llista', '/activity/quadern/llistes/element_llista');

        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element(
                'alumne', '/activity/quadern/alumnes/alumne');
            $paths[] = new restore_path_element(
                'fase', '/activity/quadern/fases/fase');
            $paths[] = new restore_path_element(
                'dia_seguiment', '/activity/quadern/seguiment/dia_seguiment');
            $paths[] = new restore_path_element(
                'valoracio', '/activity/quadern/valoracions/valoracio');
            $paths[] = new restore_path_element(
                'avaluacio', '/activity/quadern/avaluacions/avaluacio');
        }

        return $this->prepare_activity_structure($paths);
    }


    protected function process_quadern($data) {
        global $DB;

        $data = (object) $data;

        $data->course = $this->get_courseid();
        if ($data->grade < 0) {
            $data->grade = -$this->get_mappingid('scale', abs($data->grade));
        }
        $data->data_qualificacio_1 =
            $this->apply_date_offset($data->data_qualificacio_1);
        $data->data_qualificacio_2 =
            $this->apply_date_offset($data->data_qualificacio_2);
        $data->data_qualificacio_3 =
            $this->apply_date_offset($data->data_qualificacio_3);
        $data->data_qualificacio_final =
            $this->apply_date_offset($data->data_qualificacio_final);

        $newid = $DB->insert_record('fpdquadern', $data);
        $this->apply_activity_instance($newid);
    }

    protected function process_activitat($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        if ($data->alumne_id and !$this->get_setting_value('userinfo')) {
            return;
        }

        $data->quadern_id = $this->get_task()->get_activityid();
        $data->alumne_id = $this->get_mappingid('user', $data->alumne_id, 0);
        $data->data_valoracio_alumne =
            $this->apply_date_offset($data->data_valoracio_alumne);
        $data->data_valoracio_professor =
            $this->apply_date_offset($data->data_valoracio_professor);
        $data->data_valoracio_tutor =
            $this->apply_date_offset($data->data_valoracio_tutor);

        $newid = $DB->insert_record('fpdquadern_activitats', $data);
        $this->set_mapping('fpdquadern_activitats', $oldid, $newid, true);
    }

    protected function process_competencia($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->quadern_id = $this->get_task()->get_activityid();

        $newid = $DB->insert_record('fpdquadern_competencies', $data);
        $this->set_mapping('fpdquadern_competencies', $oldid, $newid);
    }

    protected function process_element_llista($data) {
        global $DB;

        $data = (object) $data;

        $data->quadern_id = $this->get_task()->get_activityid();

        $DB->insert_record('fpdquadern_llistes', $data);
    }

    protected function process_alumne($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->quadern_id = $this->get_task()->get_activityid();
        $data->alumne_id = $this->get_mappingid('user', $data->alumne_id);
        $data->professor_id = $this->get_mappingid('user', $data->professor_id, 0);
        $data->tutor_id = $this->get_mappingid('user', $data->tutor_id, 0);
        $data->acces_professor = $this->apply_date_offset($data->acces_professor);
        $data->avis_professor = $this->apply_date_offset($data->avis_professor);

        $newid = $DB->insert_record('fpdquadern_alumnes', $data);
        $this->set_mapping('fpdquadern_alumnes', $oldid, $newid, true);
    }

    protected function process_fase($data) {
        global $DB;

        $data = (object) $data;

        $data->quadern_id = $this->get_task()->get_activityid();
        $data->alumne_id = $this->get_mappingid('user', $data->alumne_id);
        $data->data_inici = $this->apply_date_offset($data->data_inici);
        $data->data_final = $this->apply_date_offset($data->data_final);

        $DB->insert_record('fpdquadern_fases', $data);
    }

    protected function process_dia_seguiment($data) {
        global $DB;

        $data = (object) $data;

        $data->quadern_id = $this->get_task()->get_activityid();
        $data->alumne_id = $this->get_mappingid('user', $data->alumne_id);
        $data->data = $this->apply_date_offset($data->data);

        $DB->insert_record('fpdquadern_seguiment', $data);
    }

    protected function process_valoracio($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->quadern_id = $this->get_task()->get_activityid();
        $data->alumne_id = $this->get_mappingid('user', $data->alumne_id);
        $data->activitat_id =
            $this->get_mappingid('fpdquadern_activitats', $data->activitat_id);
        $data->data_valoracio_professor =
            $this->apply_date_offset($data->data_valoracio_professor);
        $data->data_valoracio_tutor =
            $this->apply_date_offset($data->data_valoracio_tutor);

        $newid = $DB->insert_record('fpdquadern_valoracions', $data);
        $this->set_mapping('fpdquadern_valoracions', $oldid, $newid, true);
    }

    protected function process_avaluacio($data) {
        global $DB;

        $data = (object) $data;

        $data->quadern_id = $this->get_task()->get_activityid();
        $data->alumne_id = $this->get_mappingid('user', $data->alumne_id);
        $data->activitat_id = $this->get_mappingid(
            'fpdquadern_activitats', $data->activitat_id);
        $data->competencia_id = $this->get_mappingid(
            'fpdquadern_competencies', $data->competencia_id);

        $DB->insert_record('fpdquadern_avaluacions', $data);
    }

    protected function after_execute() {
        $this->add_related_files('mod_fpdquadern', 'intro', null);
        $this->add_related_files(
            'mod_fpdquadern', 'descripcio_activitat', 'fpdquadern_activitats');
        $this->add_related_files(
            'mod_fpdquadern', 'quadern_anterior', 'fpdquadern_alumnes');
        $this->add_related_files(
            'mod_fpdquadern', 'valoracio_activitat_professor', 'fpdquadern_valoracions');
        $this->add_related_files(
            'mod_fpdquadern', 'valoracio_activitat_tutor', 'fpdquadern_valoracions');
    }
}

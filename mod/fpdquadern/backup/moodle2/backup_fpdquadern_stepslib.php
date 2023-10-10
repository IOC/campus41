<?php
/**
 * @package mod_fpdquadern
 * @copyright 2014 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

class backup_fpdquadern_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated

        $quadern = new backup_nested_element('quadern', array('id'), array(
            'name', 'intro', 'introformat', 'grade',  'durada_fase_1', 'durada_fase_2',
            'durada_fase_3', 'data_dades_generals', 'data_qualificacio_1', 'data_qualificacio_2',
            'data_qualificacio_3', 'data_qualificacio_final', 'nom_centre_estudis',
            'codi_centre_estudis', 'adreca_centre_estudis',
        ));

        $activitats = new backup_nested_element('activitats');
        $activitat = new backup_nested_element('activitat', array('id'), array(
            'alumne_id', 'codi', 'fase', 'titol', 'descripcio', 'format_descripcio',
            'data_valoracio_alumne', 'data_valoracio_professor', 'data_valoracio_tutor',
            'acceptada', 'validada' ));

        $competencies = new backup_nested_element('competencies');
        $competencia = new backup_nested_element('competencia', array('id'), array(
            'codi', 'descripcio'));

        $llistes = new backup_nested_element('llistes');
        $element_llista = new backup_nested_element('element_llista', null, array(
            'llista', 'codi', 'nom', 'grup'));

        $alumnes = new backup_nested_element('alumnes');
        $alumne = new backup_nested_element('alumne', array('id'), array(
            'alumne_id', 'professor_id', 'tutor_id', 'alumne_dni', 'alumne_especialitat',
            'alumne_adreca', 'alumne_codi_postal', 'alumne_poblacio', 'alumne_telefon',
            'alumne_titol', 'alumne_validat', 'centre_nom', 'centre_codi', 'centre_tipus',
            'centre_adreca', 'centre_director', 'centre_coordinador', 'centre_validat',
            'tutor_telefon', 'tutor_horari', 'tutor_especialitat', 'tutor_cicles', 'tutor_credits',
            'tutor_validat', 'qualificacio', 'acces_professor', 'avis_professor'));

        $fases = new backup_nested_element('fases');
        $fase = new backup_nested_element('fase', null, array(
            'alumne_id', 'fase', 'data_inici', 'data_final', 'observacions_calendari',
            'calendari_validat', 'calendari_acceptat', 'qualificacio'));

        $seguiment = new backup_nested_element('seguiment');
        $dia_seguiment = new backup_nested_element('dia_seguiment', null, array(
            'alumne_id', 'fase', 'data', 'de1', 'a1', 'de2', 'a2', 'de3', 'a3', 'validat'));

        $valoracions = new backup_nested_element('valoracions');
        $valoracio = new backup_nested_element('valoracio', array('id'), array(
            'alumne_id', 'activitat_id', 'valoracio_tutor', 'format_valoracio_tutor',
            'valoracio_professor', 'format_valoracio_professor', 'data_valoracio_professor',
            'data_valoracio_tutor', 'valoracio_validada'));

        $avaluacions = new backup_nested_element('avaluacions');
        $avaluacio = new backup_nested_element('avaluacio', null, array(
            'alumne_id', 'activitat_id', 'competencia_id', 'grau_assoliment_professor',
            'grau_assoliment_tutor'));

        // Build the tree

        $quadern->add_child($activitats);
        $quadern->add_child($competencies);
        $quadern->add_child($llistes);
        $quadern->add_child($alumnes);
        $quadern->add_child($fases);
        $quadern->add_child($seguiment);
        $quadern->add_child($valoracions);
        $quadern->add_child($avaluacions);

        $activitats->add_child($activitat);
        $competencies->add_child($competencia);
        $llistes->add_child($element_llista);
        $alumnes->add_child($alumne);
        $fases->add_child($fase);
        $seguiment->add_child($dia_seguiment);
        $valoracions->add_child($valoracio);
        $avaluacions->add_child($avaluacio);

        // Define sources

        $quadern->set_source_table('fpdquadern', array('id' => backup::VAR_ACTIVITYID));

        $params = array('quadern_id' => backup::VAR_ACTIVITYID);
        $competencia->set_source_table('fpdquadern_competencies', $params);
        $element_llista->set_source_table('fpdquadern_llistes', $params);

        if ($this->get_setting_value('userinfo')) {
            $activitat->set_source_table('fpdquadern_activitats', $params);
            $alumne->set_source_table('fpdquadern_alumnes', $params);
            $fase->set_source_table('fpdquadern_fases', $params);
            $dia_seguiment->set_source_table('fpdquadern_seguiment', $params);
            $valoracio->set_source_table('fpdquadern_valoracions', $params);
            $avaluacio->set_source_table('fpdquadern_avaluacions', $params);
        } else {
            $activitat->set_source_table('fpdquadern_activitats', array(
                'quadern_id' => backup::VAR_ACTIVITYID,
                'alumne_id' =>  backup_helper::is_sqlparam(0),
            ));
        }

        // Define id annotations

        $activitat->annotate_ids('user', 'alumne_id');
        $alumne->annotate_ids('user', 'alumne_id');
        $alumne->annotate_ids('user', 'professor_id');
        $alumne->annotate_ids('user', 'tutor_id');
        $fase->annotate_ids('user', 'alumne_id');
        $dia_seguiment->annotate_ids('user', 'alumne_id');
        $valoracio->annotate_ids('user', 'alumne_id');
        $valoracio->annotate_ids('fpdquadern_activitats', 'activitat_id');
        $avaluacio->annotate_ids('user', 'alumne_id');
        $avaluacio->annotate_ids('fpdquadern_activitats', 'activitat_id');
        $avaluacio->annotate_ids('fpdquadern_competencies', 'competencia_id');

        // Define file annotations

        $quadern->annotate_files('mod_fpdquadern', 'intro', null);
        $activitat->annotate_files('mod_fpdquadern', 'descripcio_activitat', 'id');
        $alumne->annotate_files('mod_fpdquadern', 'quadern_anterior', 'id');
        $valoracio->annotate_files('mod_fpdquadern', 'valoracio_activitat_professor', 'id');
        $valoracio->annotate_files('mod_fpdquadern', 'valoracio_activitat_tutor', 'id');

        // Return the root element

        return $this->prepare_activity_structure($quadern);
    }
}

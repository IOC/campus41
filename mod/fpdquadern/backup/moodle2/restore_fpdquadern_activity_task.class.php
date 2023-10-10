<?php
/**
 * @package mod_fpdquadern
 * @copyright 2014 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

require_once(__DIR__ . '/restore_fpdquadern_stepslib.php');

class restore_fpdquadern_activity_task extends restore_activity_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(
            new restore_fpdquadern_activity_structure_step(
                'fpdquadern_structure', 'fpdquadern.xml'));
    }

    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('fpdquadern', array('intro'));
        $contents[] = new restore_decode_content(
            'fpdquadern_activitats', array('descripcio'));
        $contents[] = new restore_decode_content(
            'fpdquadern_valoracions', array('valoracio_professor', 'valoracio_tutor'));

        return $contents;
    }

    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule(
            'FPDQUADERNVIEWBYID', '/mod/fpdquadern/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule(
            'FPDQUADERNCHOICEINDEX', '/mod/fpdquadern/index.php?id=$1', 'course');

        return $rules;

    }
}

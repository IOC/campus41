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
 * Tutor FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('fct_base.php');
require_once('form/tutor_edit_form.php');

class fct_tutor extends fct_base {

    public $editform = 'fct_tutor_edit_form';

    public $id;

    protected static $table = 'user';
    protected $record_keys = array(
        'username',
        'firstname',
        'lastname',
        'email',
        'auth',
        'confirmed',
        'emailstop',
        'lang',
        'mnethostid',
        'secret',
        'timemodified',
        'city',
        'country',
    );
    protected $propierties_keys = array('id');
    public $returnurl;

    public function tabs($id, $type = 'view') {
        $tab = parent::tabs_general($id);
        $tab['currentab'] = 'tutor';

        return $tab;
    }

    public function __construct($record) {
        parent::__construct($record);
        if (!isset($this->fct)) {
            print_error('nofct');
        }
        $cm = get_coursemodule_from_instance('fct', $this->fct);
        $this->returnurl = new moodle_url('/mod/fct/view.php', array('id' => $cm->id));
    }

    private function prepare_data_after_update($data) {
        global $CFG;

        $data->username = strtolower($data->dni);
        $data->auth = 'manual';
        $data->confirmed = 1;
        $data->emailstop = 0;
        $data->lang = current_language();
        $data->mnethostid = $CFG->mnet_localhost_id;
        $data->secret = random_string(15);
        $data->timemodified = time();
        $data->city = '-';
        $data->country = 'ES';
    }

    private function prepare_data_before_update($data) {
        global $DB;
        $user = $DB->get_record('user', array('id' => $this->id));
        events_trigger('user_created', $user);
        setnew_password_and_mail($user);

        $cm = get_coursemodule_from_id('fct', $data->cmid);
        $roleid = $DB->get_field('role', 'id', array('shortname' => 'tutorempresa'));
        $context = context_course::instance($cm->course);
        $plugin = enrol_get_plugin('manual');
        $conditions = array('enrol' => 'manual', 'courseid' => $cm->course);
        $enrol = $DB->get_record('enrol', $conditions, '*', MUST_EXIST);
        $plugin->enrol_user($enrol, $user->id, $roleid, 0, 0, null, false);
    }

    public function insert($data) {
        global $DB;

        $this->prepare_data_after_update($data);

        parent::insert($data);

        $this->prepare_data_before_update($data);
    }

    public static function validation($data) {

        $errors = parent::comprovar_dni($data['dni'], 'dni', true, true);

        return $errors;
    }

    public function checkpermissions($type = 'view') {

        if (!$this->usuari->es_administrador) {
            print_error('nopermissions', 'fct');
        }
    }

    public function prepare_form_data($data) {
    }

}
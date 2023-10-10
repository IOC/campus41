<?php
/**
 * @package mod_fpdquadern
 * @copyright 2013 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

namespace mod_fpdquadern;

class database {

    private $moodledb;

    function __construct($moodledb=null) {
        $this->moodledb = $moodledb;
    }

    function create($model, array $properties=null) {
        $class = __NAMESPACE__ . '\\' . $model;
        $object = new $class($this, $class::$fields);
        if ($properties) {
            $object->update($properties);
        }
        return $object;
    }

    function delete(model $object) {
        if ($object->id) {
            $class = get_class($object);
            $conditions = array('id' => $object->id);
            $this->moodledb->delete_records($class::table(), $conditions);
        }
    }

    function delete_all($model, array $conditions) {
        $class = __NAMESPACE__ . '\\' . $model;
        $this->moodledb->delete_records($class::table(), $conditions);
    }

    function exists($model, array $conditions) {
        $class = __NAMESPACE__ . '\\' . $model;
        return $this->moodledb->record_exists($class::table(), $conditions);
    }

    function exists_other(model $object, array $conditions) {
        $class = get_class($object);

        $records = $this->moodledb->get_records(
            $class::table(), $conditions, '', 'id');

        if (!$records) {
            return false;
        } elseif (count($records) === 1) {
            return key($records) != $object->id;
        } else {
            return true;
        }
    }

    function fetch($model, array $conditions, $ignoremissing=false) {
        $class = __NAMESPACE__ . '\\' . $model;
        $fields = implode(',', $class::field_names());
        $strictness = $ignoremissing ? IGNORE_MISSING : MUST_EXIST;
        $record = $this->moodledb->get_record(
            $class::table(), $conditions, $fields, $strictness);
        if ($record) {
            $object = new $class($this);
            $object->update((array) $record);
            return $object;
        }
    }

    function fetch_all($model, array $conditions, $sort='', $key='id') {
        $class = __NAMESPACE__ . '\\' . $model;
        $fields = implode(',', $class::field_names());
        $records = $this->moodledb->get_records(
            $class::table(), $conditions, $sort, $fields);

        $objects = array();
        foreach ($records as $record) {
            $object = new $class($this);
            $object->update((array) $record);
            $objects[$object->$key] = $object;
        }
        return $objects;
    }

    function fetch_all_select($model, $select, array $params,
                              $sort='', $key='id') {
        $class = __NAMESPACE__ . '\\' . $model;
        $fields = implode(',', $class::field_names());
        $records = $this->moodledb->get_records_select(
            $class::table(), $select, $params, $sort, $fields);

        $objects = array();
        foreach ($records as $record) {
            $object = new $class($this);
            $object->update((array) $record);
            $objects[$object->$key] = $object;
        }
        return $objects;
    }

    function save(model $object) {
        $class = get_class($object);

        if ($object->id) {
            $this->moodledb->update_record($class::table(), $object->record());
        } else {
            $object->id = $this->moodledb->insert_record(
                $class::table(), $object->record());
        }
    }
}

abstract class model {

    static $table;
    static $fields;

    public $id;
    protected $database;

    static function field_names() {
        $names = array_keys(static::$fields);
        array_unshift($names, 'id');
        return $names;        
    }

    static function table() {
        return static::$table;
    }

    function __construct(database $database, array $properties=null) {
        $this->database = $database;
        if ($properties) {
            $this->update($properties);
        }
    }

    function delete() {
        $this->database->delete($this);
    }

    function record() {
        $class = get_class($this);
        $record = new \stdClass();
        foreach ($class::field_names() as $name) {
            $record->$name = $this->$name;
        }
        if (!$this->id) {
            unset($record->id);
        }
        return $record;
    }

    function save() {
        $this->database->save($this);
    }

    function update(array $properties) {
        foreach (self::field_names() as $name) {
            if (array_key_exists($name, $properties)) {
                $this->$name = $properties[$name];
            }
        }
    }
}

class user extends model {

    static $table = 'user';

    static $fields = array(
        'firstname' => null,
        'lastname' => null,
        'email' => null,
        'picture' => null,
        'imagealt' => null,
    );
}

if (class_exists('core_user\fields')) {
    foreach (\core_user\fields::for_name()->get_required_fields() as $field) {
        user::$fields[$field] = null;
    }
}

class quadern extends model {

    static $table = 'fpdquadern';

    static $fields = array(
        'course' => null,
        'name' => '',
        'intro' => '',
        'introformat' => FORMAT_HTML,
        'grade' => 0,
        'durada_fase_1' => 0,
        'durada_fase_2' => 0,
        'durada_fase_3' => 0,
        'data_dades_generals' => 0,
        'data_qualificacio_1' => 0,
        'data_qualificacio_2' => 0,
        'data_qualificacio_3' => 0,
        'data_qualificacio_final' => 0,
        'nom_centre_estudis' => '',
        'codi_centre_estudis' => '',
        'adreca_centre_estudis' => '',
    );

    function activitat($id) {
        $conditions = array(
            'id' => $id,
            'quadern_id' => $this->id,
        );
        return $this->database->fetch('activitat', $conditions);
    }

    function activitats($fase=null) {
        $conditions = array(
            'quadern_id' => $this->id,
            'alumne_id' => 0,
        );
        if ($fase) {
            $conditions['fase'] = $fase;
        }
        $sort = 'fase,codi';
        return $this->database->fetch_all('activitat', $conditions, $sort);
    }

    function alumne($alumne_id, $crear=false) {
        $conditions = array(
            'quadern_id' => $this->id,
            'alumne_id' => $alumne_id,
        );

        $alumne = $this->database->fetch('alumne', $conditions, true);

        if (!$alumne and $crear) {
            $alumne = $this->database->create('alumne', $conditions);
            $alumne->save();
        }

        return $alumne;
    }

    function alumnes() {
        $alumnes = array();
        $conditions = array('quadern_id' => $this->id);
        return $this->database->fetch_all(
            'alumne', $conditions, '' , 'alumne_id');
    }

    function competencia($id) {
        $conditions = array(
            'id' => $id,
            'quadern_id' => $this->id,
        );
        return $this->database->fetch('competencia', $conditions);
    }

    function competencies($fase=null) {
        $conditions = array('quadern_id' => $this->id);
        return $this->database->fetch_all('competencia', $conditions, 'codi');
    }

    function elements_llista($llista) {
        $conditions = array('quadern_id' => $this->id, 'llista' => $llista);
        return $this->database->fetch_all(
            'element_llista', $conditions, 'codi');
    }
}

class activitat extends model {

    static $table = 'fpdquadern_activitats';

    static $fields = array(
        'quadern_id' => null,
        'alumne_id' => null,
        'codi' => '',
        'fase' => null,
        'titol' => '',
        'descripcio' => '',
        'format_descripcio' => FORMAT_HTML,
        'data_valoracio_alumne' => 0,
        'data_valoracio_professor' => 0,
        'data_valoracio_tutor' => 0,
        'acceptada' => false,
        'validada' => false,
    );

    function assignada($alumne_id=null) {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'activitat_id' => $this->id,
        );
        if ($alumne_id) {
            $conditions['alumne_id'] = $alumne_id;
        }
        return $this->database->exists('valoracio', $conditions);
    }

    function complementaria() {
        return $this->alumne_id > 0;
    }

    function duplicada($codi) {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'alumne_id' => 0,
            'codi' => $codi,
        );

        if ($this->database->exists_other($this, $conditions)) {
            return true;
        }

        if ($this->alumne_id) {
            $conditions['alumne_id'] = $this->alumne_id;
            return $this->database->exists_other($this, $conditions);
        } else {
            return false;
        }
    }
}

class competencia extends model {

    static $table = 'fpdquadern_competencies';

    static $fields = array(
        'quadern_id' => null,
        'codi' => '',
        'descripcio' => '',
    );

    function avaluada() {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'competencia_id' => $this->id,
        );
        return $this->database->exists('avaluacio', $conditions);
    }

    function duplicada($codi) {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'codi' => $codi,
        );
        return $this->database->exists_other($this, $conditions);
    }
}

class element_llista extends model {

    static $table = 'fpdquadern_llistes';

    static $fields = array(
        'quadern_id' => null,
        'llista' => null,
        'codi' => null,
        'nom' => null,
        'grup' => '',
    );
}

class alumne extends model {

    static $table = 'fpdquadern_alumnes';

    static $fields = array(
        'quadern_id' => null,
        'alumne_id' => null,
        'professor_id' => 0,
        'tutor_id' => 0,
        'alumne_dni' => '',
        'alumne_especialitat' => 0,
        'alumne_adreca' => '',
        'alumne_codi_postal' => '',
        'alumne_poblacio' => '',
        'alumne_telefon' => '',
        'alumne_titol' => 0,
        'alumne_validat' => false,
        'centre_nom' => '',
        'centre_codi' => '',
        'centre_tipus' => 0,
        'centre_adreca' => '',
        'centre_director' => '',
        'centre_coordinador' => '',
        'centre_validat' => false,
        'tutor_telefon' => '',
        'tutor_horari' => '',
        'tutor_especialitat' => '',
        'tutor_cicles' => '',
        'tutor_credits' => '',
        'tutor_validat' => false,
        'qualificacio' => null,
        'acces_professor' => 0,
        'avis_professor' => 0,
    );

    function activitats($fase) {
        $select = 'quadern_id=? AND fase=? AND ' .
            '(alumne_id=? OR alumne_id=0 AND id IN ( ' .
            'SELECT activitat_id ' .
            'FROM {' . valoracio::$table . '} a ' .
            'WHERE quadern_id=? AND alumne_id=?))';
        $params = array(
            $this->quadern_id,
            $fase,
            $this->alumne_id,
            $this->quadern_id,
            $this->alumne_id,
        );
        return $this->database->fetch_all_select(
            'activitat', $select, $params, 'alumne_id, codi');
    }

    function alumne() {
        return $this->user($this->alumne_id);
    }

    function avis_professor() {
        return $this->avis_professor > $this->acces_professor;
    }

    function dades_alumne_introduides() {
        return (trim($this->alumne_dni) or
                (int) $this->alumne_especialitat or
                trim($this->alumne_adreca) or
                trim($this->alumne_codi_postal) or
                trim($this->alumne_poblacio) or
                trim($this->alumne_telefon) or
                (int) $this->alumne_titol);
    }

    function dades_centre_introduides() {
        return (trim($this->centre_nom) or
                trim($this->centre_codi) or
                (int) $this->centre_tipus or
                trim($this->centre_adreca) or
                trim($this->centre_director) or
                trim($this->centre_coordinador));
    }

    function dades_tutor_introduides() {
        return (trim($this->tutor_telefon) or
                trim($this->tutor_horari) or
                (int) $this->tutor_especialitat or
                trim($this->tutor_cicles) or
                trim($this->tutor_credits));
    }

    function dia_seguiment($id) {
        $conditions = array(
            'id' => $id,
            'quadern_id' => $this->quadern_id,
            'alumne_id' => $this->alumne_id,
        );
        return $this->database->fetch('seguiment', $conditions);
    }

    function dies_seguiment($fase, $novalidats=false) {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'alumne_id' => $this->alumne_id,
            'fase' => $fase,
        );
        if ($novalidats) {
            $conditions['validat'] = false;
        }
        return $this->database->fetch_all(
            'seguiment', $conditions, 'data DESC');
    }

    function fase($num) {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'alumne_id' => $this->alumne_id,
            'fase' => $num,
        );

        $fase = $this->database->fetch('fase', $conditions, true);

        if (!$fase) {
            $fase = $this->database->create('fase', $conditions);
            $fase->save();
        }

        return $fase;
    }

    function professor() {
        return $this->user($this->professor_id);
    }

    function tutor() {
        return $this->user($this->tutor_id);
    }

    function valoracio($activitat_id) {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'alumne_id' => $this->alumne_id,
            'activitat_id' => $activitat_id,
        );
        return $this->database->fetch('valoracio', $conditions);
    }

    private function user($id) {
        $conditions = array('id' => $id);
        return $this->database->fetch('user', $conditions, true);
    }
}

class fase extends model {

    static $table = 'fpdquadern_fases';

    static $fields = array(
        'quadern_id' => null,
        'alumne_id' => null,
        'fase' => null,
        'data_inici' => 0,
        'data_final' => 0,
        'observacions_calendari' => '',
        'calendari_validat' => false,
        'calendari_acceptat' => false,
        'qualificacio' => null,
    );

    function planificacio_introduida() {
        return ((int) $this->data_inici or
                (int) $this->data_final or
                trim($this->observacions_calendari));
    }
}

class valoracio extends model {

    static $table = 'fpdquadern_valoracions';

    static $fields = array(
        'quadern_id' => null,
        'alumne_id' => null,
        'activitat_id' => null,
        'valoracio_tutor' => '',
        'format_valoracio_tutor' => FORMAT_HTML,
        'valoracio_professor' => '',
        'format_valoracio_professor' => FORMAT_HTML,
        'data_valoracio_professor' => 0,
        'data_valoracio_tutor' => 0,
        'valoracio_validada' => false,
    );

    function avaluacions() {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'alumne_id' => $this->alumne_id,
            'activitat_id' => $this->activitat_id,
        );
        return $this->database->fetch_all(
            'avaluacio', $conditions, '', 'competencia_id');
    }

    function valorada() {
        return $this->valorada_professor() or $this->valorada_tutor();
    }

    function valorada_professor() {
        return (bool) trim($this->valoracio_professor);
    }

    function valorada_tutor() {
        return (bool) trim($this->valoracio_tutor);
    }
}

class avaluacio extends model {

    static $table = 'fpdquadern_avaluacions';

    static $fields = array(
        'quadern_id' => null,
        'alumne_id' => null,
        'activitat_id' => null,
        'competencia_id' => null,
        'grau_assoliment_professor' => 0,
        'grau_assoliment_tutor' => 0,
    );
}

class seguiment extends model {

    static $table = 'fpdquadern_seguiment';

    static $fields = array(
        'quadern_id' => null,
        'alumne_id' => null,
        'fase' => null,
        'data' => null,
        'de1' => null,
        'a1' => null,
        'de2' => null,
        'a2' => null,
        'de3' => null,
        'a3' => null,
        'validat' => false,
    );

    function duplicat($data) {
        $conditions = array(
            'quadern_id' => $this->quadern_id,
            'alumne_id' => $this->alumne_id,
            'fase' => $this->fase,
            'data' => $data,
        );
        return $this->database->exists_other($this, $conditions);
    }
}

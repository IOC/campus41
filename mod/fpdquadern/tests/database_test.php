<?php
/**
 * @package mod_fpdquadern
 * @copyright 2013 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

namespace mod_fpdquadern;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../database.php');

class test_model extends model {
    static $table = 'table';
    static $fields = array(
        'field1' => null,
        'field2' => 'default',
    );
}

class db_test extends \basic_testcase {

    private $database;
    private $moodledb;

    function setUp() {
        global $DB;

        parent::setUp();

        $this->moodledb = $this->getMock(get_class($DB));
        $this->database = new database($this->moodledb);
    }

    function test_create() {
        $properties = array('id' => 10, 'field1' => 123);

        $result = $this->database->create('test_model', $properties);

        $this->assertInstanceOf('mod_fpdquadern\test_model', $result);
        $this->assertEquals($properties['id'], $result->id);
        $this->assertEquals($properties['field1'], $result->field1);
        $this->assertEquals(test_model::$fields['field2'], $result->field2);
    }

    function test_delete() {
        $object = new test_model($this->database, array('id' => 10));
        $this->moodledb->expects($this->once())->method('delete_records')
                       ->with('table', array('id' => $object->id));

        $this->database->delete($object);
    }

    function test_delete_new_object() {
        $object = new test_model($this->database, array('id' => false));
        $this->moodledb->expects($this->never())->method('delete_records');
        $this->database->delete($object);
    }

    function test_delete_all() {
        $conditions = array('field1' => 123);
        $this->moodledb->expects($this->once())->method('delete_records')
                       ->with('table', $conditions);

        $this->database->delete_all('test_model', $conditions);
    }

    function test_exists() {
        $conditions = array('field1' => 123);
        $this->moodledb->expects($this->any())->method('record_exists')
                       ->with('table', $conditions)
                       ->will($this->returnValue(true));

        $result = $this->database->exists('test_model', $conditions);

        $this->assertTrue($result);
    }

    function test_exists_other() {
        $object = new test_model($this->database, array('id' => 10));

        $conditions = array('field1' => 123, 'field2' => 'text');
        $records = array(
            20 => (object) array(
                'id' => 20,
                'field1' => 123,
                'field2' => 'text',
            ),
        );

        $this->moodledb->expects($this->any())->method('get_records')
                       ->with('table', $conditions, '', 'id')
                       ->will($this->returnValue($records));

        $result = $this->database->exists_other($object, $conditions);

        $this->assertTrue($result);
    }

    function test_fetch() {
        $conditions = array('field1' => 123);
        $record = array('id' => 10, 'field1' => 123, 'field2' => 'text');
        $fields = 'id,field1,field2';
        $this->moodledb->expects($this->any())->method('get_record')
                       ->with('table', $conditions, $fields, MUST_EXIST)
                       ->will($this->returnValue((object) $record));

        $result = $this->database->fetch('test_model', $conditions);

        $this->assertInstanceOf('mod_fpdquadern\test_model', $result);
        $this->assertEquals($record, get_object_vars($result));
    }

    function test_fetch_ignore_missing() {
        $conditions = array('field1' => 123);
        $fields = 'id,field1,field2';
        $this->moodledb->expects($this->any())->method('get_record')
                       ->with('table', $conditions, $fields, IGNORE_MISSING)
                       ->will($this->returnValue(false));

        $result = $this->database->fetch('test_model', $conditions, true);

        $this->assertNull($result);
    }

    function test_fetch_all() {
        $conditions = array('field1' => 123);
        $sort = 'field2 ASC';
        $key = 'field2';
        $fields = 'id,field1,field2';
        $record1 = array('id' => 10, 'field1' => 123, 'field2' => 'text1');
        $record2 = array('id' => 20, 'field1' => 123, 'field2' => 'text2');
        $records = array(
            'text1' => (object) $record1,
            'text2' => (object) $record2,
        );

        $this->moodledb->expects($this->any())->method('get_records')
                       ->with('table', $conditions, $sort, $fields)
                       ->will($this->returnValue($records));

        $result = $this->database->fetch_all(
            'test_model', $conditions, $sort, $key);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(array_keys($records), array_keys($result));
        $this->assertContainsOnlyInstancesOf(
            'mod_fpdquadern\test_model', $result);
        $this->assertEquals($record1, get_object_vars($result['text1']));
        $this->assertEquals($record2, get_object_vars($result['text2']));
    }

    function test_fetch_all_select() {
        $select = 'field1 = ? OR field2 = ?';
        $params = array(123, 'text');
        $sort = 'field2 ASC';
        $key = 'field2';
        $fields = 'id,field1,field2';
        $record1 = array('id' => 10, 'field1' => 123, 'field2' => 'text1');
        $record2 = array('id' => 20, 'field1' => 123, 'field2' => 'text2');
        $records = array(
            'text1' => (object) $record1,
            'text2' => (object) $record2,
        );

        $this->moodledb->expects($this->any())->method('get_records_select')
                       ->with('table', $select, $params, $sort, $fields)
                       ->will($this->returnValue($records));

        $result = $this->database->fetch_all_select(
            'test_model', $select, $params, $sort, $key);

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertEquals(array_keys($records), array_keys($result));
        $this->assertContainsOnlyInstancesOf(
            'mod_fpdquadern\test_model', $result);
        $this->assertEquals($record1, get_object_vars($result['text1']));
        $this->assertEquals($record2, get_object_vars($result['text2']));
    }

    function test_save_existing() {
        $properties = array('id' => 10, 'field1' => 123, 'field2' => 'text');
        $object = new test_model($this->database, $properties);

        $this->moodledb->expects($this->once())->method('update_record')
                       ->with('table', (object) $properties);

        $this->database->save($object);
    }

    function test_save_new() {
        $id = 10;
        $properties = array('field1' => 123, 'field2' => 'text');
        $object = new test_model($this->database, $properties);

        $this->moodledb->expects($this->once())->method('insert_record')
                       ->with('table', (object) $properties)
                       ->will($this->returnValue($id));

        $this->database->save($object);

        $this->assertEquals($id, $object->id);
    }
}

abstract class base_model_test extends \basic_testcase {

    protected $db;

    function setUp() {
        parent::setUp();
        $this->db = $this->getMock('mod_fpdquadern\database');
    }
}

class model_test extends base_model_test {

    function test_delete() {
        $object = new test_model($this->db);

        $this->db->expects($this->once())->method('delete')
                 ->with($this->identicalTo($object));

        $object->delete();
    }

    function test_record() {
        $properties = array('field1' => 123, 'field2' => 'text');
        $object = new test_model($this->db, $properties);

        $result = $object->record();

        $this->assertEquals((object) $properties, $result);
    }

    function test_save() {
        $object = new test_model($this->db);

        $this->db->expects($this->once())->method('save')
                 ->with($this->identicalTo($object));

        $object->save();
    }

    function test_update() {
        $properties = array('field1' => 123, 'field2' => 'text');
        $object = new test_model($this->db);

        $object->update($properties);

        $this->assertEquals($properties['field1'], $object->field1);
        $this->assertEquals($properties['field2'], $object->field2);
    }
}


class quadern_test extends base_model_test {

    private $quadern;

    function setUp() {
        parent::setUp();
        $this->quadern = new quadern($this->db, array('id' => 10));
    }

    function test_activitat() {
        $activitat = new activitat($this->db, array('id' => 20));
        $conditions = array(
            'id' => $activitat->id,
            'quadern_id' => $this->quadern->id,
        );
        $this->db->expects($this->any())->method('fetch')
                 ->with('activitat', $conditions)
                 ->will($this->returnValue($activitat));

        $result = $this->quadern->activitat($activitat->id);

        $this->assertSame($activitat, $result);
    }

    function test_activitats() {
        $fase = 3;
        $conditions = array(
            'quadern_id' => $this->quadern->id,
            'alumne_id' => 0,
        );
        $activitats = array(
            20 => new activitat($this->db, array('id' => 20)),
            30 => new activitat($this->db, array('id' => 30)),
        );
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('activitat', $conditions, 'fase,codi')
                 ->will($this->returnValue($activitats));

        $result = $this->quadern->activitats();

        $this->assertSame($activitats, $result);
    }

    function test_activitats_fase() {
        $fase = 3;
        $conditions = array(
            'quadern_id' => $this->quadern->id,
            'alumne_id' => 0,
            'fase' => $fase,
        );
        $activitats = array(
            20 => new activitat($this->db, array('id' => 20)),
            30 => new activitat($this->db, array('id' => 30)),
        );
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('activitat', $conditions, 'fase,codi')
                 ->will($this->returnValue($activitats));

        $result = $this->quadern->activitats($fase);

        $this->assertSame($activitats, $result);
    }

    function test_alumne_existent() {
        $alumne = new alumne($this->db, array(
            'id' => 20,
            'quadern_id' => $this->quadern->id,
            'alumne_id' => 30,
        ));
        $conditions = array(
            'quadern_id' => $this->quadern->id,
            'alumne_id' => $alumne->alumne_id,
        );
        $this->db->expects($this->any())->method('fetch')
                 ->with('alumne', $conditions, true)
                 ->will($this->returnValue($alumne));

        $result = $this->quadern->alumne($alumne->alumne_id, true);

        $this->assertSame($alumne, $result);
    }

    function test_alumne_crear_no_existent() {
        $conditions = array(
            'quadern_id' => $this->quadern->id,
            'alumne_id' => 30,
        );
        $alumne = new alumne($this->db, $conditions);
        $this->db->expects($this->any())->method('fetch')
                 ->with('alumne', $conditions, true)
                 ->will($this->returnValue(null));
        $this->db->expects($this->once())->method('create')
                 ->with('alumne', $conditions)
                 ->will($this->returnValue($alumne));
        $this->db->expects($this->once())->method('save')->with($alumne);

        $result = $this->quadern->alumne($alumne->alumne_id, true);

        $this->assertEquals($alumne, $result);
    }

    function test_alumne_ignorar_no_existent() {
        $alumne_id = 30;
        $conditions = array(
            'quadern_id' => $this->quadern->id,
            'alumne_id' => $alumne_id,
        );
        $this->db->expects($this->any())->method('fetch')
                 ->with('alumne', $conditions, true)
                 ->will($this->returnValue(null));

        $result = $this->quadern->alumne($alumne_id, false);

        $this->assertNull($result);
    }

    function test_alumnes() {
        $alumnes = array(
            20 => new alumne($this->db, array('id' => 40, 'alumne_id' => 20)),
            30 => new alumne($this->db, array('id' => 50, 'alumne_id' => 30)),
        );
        $conditions = array('quadern_id' => $this->quadern->id);
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('alumne', $conditions, '', 'alumne_id')
                 ->will($this->returnValue($alumnes));

        $result = $this->quadern->alumnes();

        $this->assertEquals($alumnes, $result);
    }

   function test_competencia() {
        $competencia = new competencia($this->db, array('id' => 20));
        $conditions = array(
            'id' => $competencia->id,
            'quadern_id' => $this->quadern->id,
        );
        $this->db->expects($this->any())->method('fetch')
                 ->with('competencia', $conditions)
                 ->will($this->returnValue($competencia));

        $result = $this->quadern->competencia($competencia->id);

        $this->assertSame($competencia, $result);
    }

    function test_competencies() {
        $conditions = array('quadern_id' => $this->quadern->id);
        $competencies = array(
            20 => new competencia($this->db, array('id' => 20)),
            30 => new competencia($this->db, array('id' => 30)),
        );
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('competencia', $conditions, 'codi')
                 ->will($this->returnValue($competencies));

        $result = $this->quadern->competencies();

        $this->assertSame($competencies, $result);
    }

    function test_elements_llista() {
        $conditions = array(
            'quadern_id' => $this->quadern->id,
            'llista' => 'nom_llista',
        );
        $elements = array(
            20 => new element_llista($this->db, array('id' => 20)),
            30 => new element_llista($this->db, array('id' => 30)),
        );
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('element_llista', $conditions, 'codi')
                 ->will($this->returnValue($elements));

        $result = $this->quadern->elements_llista('nom_llista');

        $this->assertSame($elements, $result);
    }
}

class activitat_test extends base_model_test {

    private $activitat;

    function setUp() {
        parent::setUp();
        $this->activitat = new activitat($this->db, array(
            'id' => 10,
            'quadern_id' => 20,
            'alumne_id' => 30,
            'fase' => 3,
        ));
    }

    function test_assignada() {
        $conditions = array(
            'quadern_id' => $this->activitat->quadern_id,
            'activitat_id' => $this->activitat->id,
        );
        $this->db->expects($this->any())->method('exists')
                 ->with('valoracio', $conditions)
                 ->will($this->returnValue(true));

        $result = $this->activitat->assignada();

        $this->assertTrue($result);
    }

    function test_assignada_alumne() {
        $alumne_id = 40;
        $conditions = array(
            'quadern_id' => $this->activitat->quadern_id,
            'activitat_id' => $this->activitat->id,
            'alumne_id' => $alumne_id,
        );
        $this->db->expects($this->any())->method('exists')
                 ->with('valoracio', $conditions)
                 ->will($this->returnValue(true));

        $result = $this->activitat->assignada($alumne_id);

        $this->assertTrue($result);
    }

    function test_duplicada() {
        $codi = 'ABC';
        $conditions1 = array(
            'quadern_id' => $this->activitat->quadern_id,
            'alumne_id' => 0,
            'codi' => $codi,
        );
        $conditions2 = array(
            'quadern_id' => $this->activitat->quadern_id,
            'alumne_id' => $this->activitat->alumne_id,
            'codi' => $codi,
        );
        $map = array(
            array($this->activitat, $conditions1, false),
            array($this->activitat, $conditions2, true),
        );
        $this->db->expects($this->any())->method('exists_other')
                 ->will($this->returnValueMap($map));

        $result = $this->activitat->duplicada($codi);

        $this->assertTrue($result);
    }
}

class competencia_test extends base_model_test {

    private $competencia;

    function setUp() {
        parent::setUp();
        $this->competencia = new competencia($this->db, array(
            'id' => 10,
            'quadern_id' => 20,
            'codi' => 'C1',
            'descripcio' => 'Competència 1',
        ));
    }

    function test_avaluada() {
        $conditions = array(
            'quadern_id' => $this->competencia->quadern_id,
            'competencia_id' => $this->competencia->id,
        );
        $this->db->expects($this->any())->method('exists')
                 ->with('avaluacio', $conditions)
                 ->will($this->returnValue(true));

        $result = $this->competencia->avaluada();

        $this->assertTrue($result);
    }

    function test_duplicada() {
        $codi = 'C2';
        $conditions = array(
            'quadern_id' => $this->competencia->quadern_id,
            'codi' => $codi,
        );
        $this->db->expects($this->any())->method('exists_other')
                 ->with($this->identicalTo($this->competencia), $conditions)
                 ->will($this->returnValue(true));

        $result = $this->competencia->duplicada($codi);

        $this->assertTrue($result);
    }
}

class alumne_test extends base_model_test {

    private $alumne;

    function setUp() {
        parent::setUp();
        $this->alumne = new alumne($this->db, array(
            'id' => 10,
            'quadern_id' => 20,
            'alumne_id' => 30,
            'professor_id' => 40,
            'tutor_id' => 50,
        ));
    }

    function test_activitats() {
        $fase = 3;
        $select = 'quadern_id=? AND fase=? AND ' .
            '(alumne_id=? OR alumne_id=0 AND id IN ( ' .
            'SELECT activitat_id ' .
            'FROM {' . valoracio::$table . '} a ' .
            'WHERE quadern_id=? AND alumne_id=?))';
        $params = array(
             $this->alumne->quadern_id,
             $fase,
             $this->alumne->alumne_id,
             $this->alumne->quadern_id,
             $this->alumne->alumne_id,
        );
        $activitats = array(
            60 => new activitat($this->db, array('id' => 60)),
            70 => new activitat($this->db, array('id' => 70)),
        );

        $this->db->expects($this->any())->method('fetch_all_select')
                 ->with('activitat', $select, $params, 'alumne_id, codi')
                 ->will($this->returnValue($activitats));

        $result = $this->alumne->activitats($fase);

        $this->assertEquals($activitats, $result);
    }

    function test_alumne() {
        $this->_test_user('alumne', $this->alumne->alumne_id);
    }

    function test_dia_seguiment() {
        $conditions = array(
            'id' => 60,
            'quadern_id' => $this->alumne->quadern_id,
            'alumne_id' => $this->alumne->alumne_id,
        );
        $dia = new seguiment($this->db, $conditions);
        $this->db->expects($this->any())->method('fetch')
                 ->with('seguiment', $conditions)
                 ->will($this->returnValue($dia));

        $result = $this->alumne->dia_seguiment($dia->id);

        $this->assertSame($dia, $result);
    }

    function test_dies_seguiment() {
        $fase = 3;
        $conditions = array(
            'quadern_id' => $this->alumne->quadern_id,
            'alumne_id' => $this->alumne->alumne_id,
            'fase' => $fase,
        );
        $dies = array(
            60 => new seguiment($this->db, array('id' => 60)),
            70 => new seguiment($this->db, array('id' => 70)),
        );
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('seguiment', $conditions, 'data DESC')
                 ->will($this->returnValue($dies));

        $result = $this->alumne->dies_seguiment($fase);

        $this->assertSame($dies, $result);
    }

    function test_dies_seguiment_no_validats() {
        $fase = 3;
        $conditions = array(
            'quadern_id' => $this->alumne->quadern_id,
            'alumne_id' => $this->alumne->alumne_id,
            'fase' => $fase,
            'validat' => false,
        );
        $dies = array(
            60 => new seguiment($this->db, array('id' => 60)),
            70 => new seguiment($this->db, array('id' => 70)),
        );
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('seguiment', $conditions, 'data DESC')
                 ->will($this->returnValue($dies));

        $result = $this->alumne->dies_seguiment($fase, true);

        $this->assertSame($dies, $result);
    }

    function test_fase_existent() {
        $num = 3;
        $conditions = array(
            'quadern_id' => $this->alumne->quadern_id,
            'alumne_id' => $this->alumne->alumne_id,
            'fase' => $num,
        );
        $fase = new fase($this->db, array('id' => 60));
        $this->db->expects($this->any())->method('fetch')
                 ->with('fase', $conditions, true)
                 ->will($this->returnValue($fase));

        $result = $this->alumne->fase($num);

        $this->assertSame($fase, $result);
    }

    function test_fase_no_existent() {
        $num = 3;
        $conditions = array(
            'quadern_id' => $this->alumne->quadern_id,
            'alumne_id' => $this->alumne->alumne_id,
            'fase' => $num,
        );
        $fase = new fase($this->db, $conditions);
        $this->db->expects($this->any())->method('fetch')
                 ->with('fase', $conditions, true)
                 ->will($this->returnValue(null));
        $this->db->expects($this->any())->method('create')
                 ->with('fase', $conditions)
                 ->will($this->returnValue($fase));
        $this->db->expects($this->once())->method('save')->with($fase);

        $result = $this->alumne->fase($num);

        $this->assertEquals($fase, $result);
    }

    function test_professor() {
        $this->_test_user('professor', $this->alumne->professor_id);
    }

    function test_tutor() {
        $this->_test_user('tutor', $this->alumne->tutor_id);
    }

    function test_valoracio() {
        $activitat_id = 60;
        $conditions = array(
            'quadern_id' => $this->alumne->quadern_id,
            'alumne_id' => $this->alumne->alumne_id,
            'activitat_id' => $activitat_id,
        );
        $valoracio = new valoracio($this->db, $conditions);
        $this->db->expects($this->any())->method('fetch')
                 ->with('valoracio', $conditions)
                 ->will($this->returnValue($valoracio));

        $result = $this->alumne->valoracio($activitat_id);

        $this->assertSame($valoracio, $result);
    }

    private function _test_user($method, $id) {
        $user = new user($this->db, array('id' => $id));
        $this->db->expects($this->any())->method('fetch')
                 ->with('user', array('id' => $id), true)
                 ->will($this->returnValue($user));

        $result = $this->alumne->$method();

        $this->assertSame($user, $result);
    }
}

class valoracio_test extends base_model_test {

    private $valoracio;

    function setUp() {
        parent::setUp();
        $this->valoracio = new valoracio($this->db, array(
            'id' => 10,
            'quadern_id' => 20,
            'alumne_id' => 30,
            'activitat_id' => 40,
        ));
    }

    function test_avaluacions() {
        $conditions = array(
            'quadern_id' => $this->valoracio->quadern_id,
            'alumne_id' => $this->valoracio->alumne_id,
            'activitat_id' => $this->valoracio->activitat_id,
        );
        $avaluacions = array(
            60 => new avaluacio($this->db, array(
                'id' => 80, 'competencia_id' => 60)),
            70 => new avaluacio($this->db, array(
                'id' => 90, 'competencia_id' => 70)),
        );
        $this->db->expects($this->any())->method('fetch_all')
                 ->with('avaluacio', $conditions, '', 'competencia_id')
                 ->will($this->returnValue($avaluacions));

        $result = $this->valoracio->avaluacions();

        $this->assertSame($avaluacions, $result);
    }
}

class seguiment_test extends base_model_test {

    private $dia;

    function setUp() {
        parent::setUp();
        $this->dia = new seguiment($this->db, array(
            'id' => 10,
            'quadern_id' => 20,
            'alumne_id' => 30,
            'fase' => 3,
            'data' => 1000,
        ));
    }

    function test_duplicat() {
        $data = 2000;
        $conditions = array(
            'quadern_id' => $this->dia->quadern_id,
            'alumne_id' => $this->dia->alumne_id,
            'fase' => $this->dia->fase,
            'data' => $data,
        );
        $this->db->expects($this->any())->method('exists_other')
                 ->with($this->identicalTo($this->dia), $conditions)
                 ->will($this->returnValue(true));

        $result = $this->dia->duplicat($data);

        $this->assertTrue($result);
    }
}

<?php

/**
 * @package mod_fpdquadern
 * @copyright 2013 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

require_once(__DIR__ . '/../lib.php');

function xmldb_fpdquadern_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013120900) {
        $table = new xmldb_table('fpdquadern_alumne_activitats');
        $fieldnames = array(
            'valoracio_alumne',
            'format_valoracio_alumne',
            'data_valoracio_alumne',
        );

        foreach ($fieldnames as $fieldname) {
            $field = new xmldb_field($fieldname);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2013120900, 'fpdquadern');
    }

    if ($oldversion < 2013120901) {
        $table = new xmldb_table('fpdquadern_alumne_activitats');
        $field = new xmldb_field(
            'comentaris_professor', XMLDB_TYPE_TEXT, null, null,
            null, null, null, 'grau_assoliment');
        $dbman->rename_field($table, $field, 'valoracio_professor');
        upgrade_mod_savepoint(true, 2013120901, 'fpdquadern');
    }

    if ($oldversion < 2013120902) {
        $table = new xmldb_table('fpdquadern_alumne_activitats');
        $field = new xmldb_field(
            'format_comentaris_professor', XMLDB_TYPE_INTEGER, '4', null,
            XMLDB_NOTNULL, null, '0', 'valoracio_professor');
        $dbman->rename_field($table, $field, 'format_valoracio_professor');
        upgrade_mod_savepoint(true, 2013120902, 'fpdquadern');
    }

    if ($oldversion < 2013120904) {
        $table = new xmldb_table('fpdquadern_competencies');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null,
                          XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quadern_id', XMLDB_TYPE_INTEGER, '10', null,
                          XMLDB_NOTNULL, null, null);
        $table->add_field('codi', XMLDB_TYPE_CHAR, '20', null,
                          XMLDB_NOTNULL, null, null);
        $table->add_field('descripcio', XMLDB_TYPE_TEXT, null, null,
                          XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('quadern_id', XMLDB_INDEX_NOTUNIQUE,
                          array('quadern_id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_mod_savepoint(true, 2013120904, 'fpdquadern');
    }

    if ($oldversion < 2013120905) {
        $table = new xmldb_table('fpdquadern_alumne_competenci');
        $table->add_field('id', XMLDB_TYPE_INTEGER,
                          '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quadern_id', XMLDB_TYPE_INTEGER,
                          '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('alumne_id', XMLDB_TYPE_INTEGER,
                          '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('activitat_id', XMLDB_TYPE_INTEGER,
                          '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('competencia_id', XMLDB_TYPE_INTEGER,
                          '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('grau_assoliment_professor', XMLDB_TYPE_INTEGER,
                          '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('grau_assoliment_tutor', XMLDB_TYPE_INTEGER,
                          '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('quadern_alumne_activitat', XMLDB_INDEX_NOTUNIQUE,
                          array('quadern_id', 'alumne_id', 'activitat_id'));
        $table->add_index('quadern_competencia', XMLDB_INDEX_NOTUNIQUE,
                          array('quadern_id', 'competencia_id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_mod_savepoint(true, 2013120905, 'fpdquadern');
    }

    if ($oldversion < 2013120906) {
        $table = new xmldb_table('fpdquadern_alumne_activitats');
        $fieldnames = array('grau_assoliment', 'avaluacio_professor');
        foreach ($fieldnames as $fieldname) {
            $field = new xmldb_field($fieldname);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2013120906, 'fpdquadern');
    }

    if ($oldversion < 2014041800) {
        $table = new xmldb_table('fpdquadern');
        $fieldnames = array(
            'nom_centre_estudis',
            'codi_centre_estudis',
            'adreca_centre_estudis'
        );
        $previous = 'data_qualificacio_final';
        foreach ($fieldnames as $fieldname) {
            $field = new xmldb_field($fieldname, XMLDB_TYPE_CHAR, '255', null,
                                     XMLDB_NOTNULL, null, null, $previous);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $previous = $fieldname;
        }
        upgrade_mod_savepoint(true, 2014041800, 'fpdquadern');
    }

    if ($oldversion < 2014041801) {
        $rs = $DB->get_recordset('fpdquadern', null, '', 'id');
        foreach ($rs as $r) {
            $r->nom_centre_estudis = 'Institut Obert de Catalunya';
            $r->codi_centre_estudis = '08045203';
            $r->adreca_centre_estudis = 'Av. del Paral·lel, 71 08029 Barcelona';
            $DB->update_record('fpdquadern', $r);
        }
        $rs->close();
        upgrade_mod_savepoint(true, 2014041801, 'fpdquadern');
    }

    if ($oldversion < 2014041802) {
        $table = new xmldb_table('fpdquadern_llistes');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10',
                          null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('quadern_id', XMLDB_TYPE_INTEGER, '10',
                          null, XMLDB_NOTNULL, null, null);
        $table->add_field('llista', XMLDB_TYPE_CHAR, '255',
                          null, XMLDB_NOTNULL, null, null);
        $table->add_field('codi', XMLDB_TYPE_INTEGER, '10',
                          null, XMLDB_NOTNULL, null, null);
        $table->add_field('nom', XMLDB_TYPE_CHAR, '255',
                          null, XMLDB_NOTNULL, null, null);
        $table->add_field('grup', XMLDB_TYPE_CHAR, '255',
                          null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('quadern_llista_codi', XMLDB_INDEX_UNIQUE,
                          array('quadern_id', 'llista', 'codi'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_mod_savepoint(true, 2014041802, 'fpdquadern');
    }

    if ($oldversion < 2014041803) {
        $rs = $DB->get_recordset('fpdquadern', null, '', 'id');
        foreach ($rs as $r) {
            fpdquadern_crear_llistes_predeterminades($r->id);
        }
        $rs->close();
        upgrade_mod_savepoint(true, 2014041803, 'fpdquadern');
    }

    if ($oldversion < 2014042100) {
        $table = new xmldb_table('fpdquadern_alumne_fases');
        $dies = array('dilluns', 'dimarts', 'dimecres', 'dijous', 'divendres');
        foreach ($dies as $dia) {
            $field = new xmldb_field("hores_$dia");
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
            $field = new xmldb_field("franja_$dia");
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2014042100, 'fpdquadern');
    }

    if ($oldversion < 2014042101) {
        $renames = array(
            'fpdquadern_alumne' => 'fpdquadern_alumnes',
            'fpdquadern_alumne_activitats' => 'fpdquadern_valoracions',
            'fpdquadern_alumne_competenci' => 'fpdquadern_avaluacions',
            'fpdquadern_alumne_fases' => 'fpdquadern_fases',
            'fpdquadern_alumne_seguiment' => 'fpdquadern_seguiment',
        );
        foreach ($renames as $oldname => $newname) {
            $table = new xmldb_table($oldname);
            if ($dbman->table_exists($table)) {
                $dbman->rename_table($table, $newname);
            }
        }
        upgrade_mod_savepoint(true, 2014042101, 'fpdquadern');
    }

    return true;
}

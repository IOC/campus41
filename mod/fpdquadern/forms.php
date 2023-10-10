<?php
/**
 * @package mod_fpdquadern
 * @copyright 2013 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

namespace mod_fpdquadern;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

abstract class base_form extends \moodleform {

    protected $output;
    protected $controller;

    private $filters = array();
    private $editors = array();
    private $filemanagers = array();

    function __construct($controller, $editable=false) {
        global $PAGE;
        $this->controller = $controller;
        $this->output = $PAGE->get_renderer('mod_fpdquadern');
        $this->output->set_controller($controller);
        $this->editable = $editable;
        $class = 'mod-fpdquadern-form ' .
            str_replace(array('_', '\\'), '-', get_class($this)) .
            ($editable ? ' mod-fpdquadern-form-editable' : '');
        parent::__construct(
            $PAGE->url, null, 'post', '', array('class' => $class)
        );
    }

    function get_data() {
        $data = parent::get_data();
        if ($data) {
            foreach ($this->filters as $filter) {
                $filter($data);
            }
        }
        return $data;
    }

    function save_files($data, $id=null) {
        global $PAGE;
        foreach ($this->editors as $editor) {
            $data->{$editor['name']} = file_save_draft_area_files(
                $editor['draftitemid'], $PAGE->context->id,
                'mod_fpdquadern', $editor['filearea'], $id,
                null, $data->{$editor['name']}
            );
        }
        foreach ($this->filemanagers as $filemanager) {
            file_save_draft_area_files(
                $filemanager['draftitemid'], $PAGE->context->id,
                'mod_fpdquadern', $filemanager['filearea'], $id
            );
        }
    }

    protected function add_buttons() {
        if ($this->editable) {
            $this->add_action_buttons();
        }
    }

    protected function add_element_avaluacions (
        $name, $label, $value, array $competencies, $static=false
    ) {
        $elements = array();
        foreach ($competencies as $c) {
            $cname = "{$name}_{$c->id}";
            $cvalue = isset($value[$c->id]) ? $value[$c->id] : 0;
            $options = $this->options_llista('graus_assoliment', $cvalue);
            if ($cvalue > 0 or ($this->editable and !$static)) {
                $elements = array_merge($elements, array(
                    $this->create_element_static(
                        '<div class="mod-fpdquadern-avaluacio">' .
                        '<abbr title="' . s($c->descripcio) . '">' .
                        s($c->codi) . '</abbr> '),
                    $this->create_element_select($cname, $cvalue, $options, $static),
                    $this->create_element_static('</div>'),
                ));
                if ($this->editable and !$static) {
                    $this->filters[] = function($data) use ($cname) {
                        $data->$cname = (int) $data->$cname;
                    };
                }
            }
        }
        if ($elements) {
            $this->_form->addGroup($elements, $name, $label, '', false);
        } else {
            $this->add_element_static($name, $label, '');
        }
    }

    protected function add_element_checkbox(
        $name, $label, $value, $frozen=false
    ) {
        $this->_form->addElement('checkbox', $name, '', $label);
        $this->_form->setDefault($name, $value);
        if ($frozen) {
            $this->_form->hardFreeze($name);
        } else {
            $this->filters[] = function($data) use ($name) {
                $data->$name = !empty($data->$name);
            };
        }
    }

    protected function add_element_date($name, $label, $value, $optional=false, $static=false) {
        if ($this->editable and !$static) {
            $this->_form->addElement('date_selector', $name, $label,
                                     array('optional' => $optional));
            $this->_form->setType($name, PARAM_INT);
            $this->_form->setDefault($name, $value);
        } else {
            $html = $this->output->data($value);
            $this->add_element_static('', $label, $html);
        }
    }

    protected function add_element_editor(
        $name, $label, $value, $format, $filearea, $itemid,
        $static=false, $dataedicio=0, $limitedicio=0
    ) {
        global $PAGE;

        if ($this->editable and !$static) {
            $draftitemid = file_get_submitted_draft_itemid($name);
            $value = file_prepare_draft_area(
                $draftitemid, $PAGE->context->id, 'mod_fpdquadern',
                $filearea, $itemid, null, $value
            );
            $this->_form->addElement(
                'editor', $name, $label, null,
                array('maxfiles' => EDITOR_UNLIMITED_FILES)
            );
            $this->_form->setType($name, PARAM_RAW);

            $this->_form->setDefault($name, (object) array(
                'text' => $value,
                'format' => $value or $format ? $format : FORMAT_HTML,
                'itemid' => $draftitemid,
            ));

            $this->editors[] = array(
                'name' => $name,
                'draftitemid' => $draftitemid,
                'filearea' => $filearea
            );

            $this->filters[] = function($data) use ($name, $filearea, $itemid) {
                $data->{"format_$name"} = $data->{$name}['format'];
                $data->$name = $data->{$name}['text'];
            };

        } else {
            $value = file_rewrite_pluginfile_urls(
                $value, 'pluginfile.php', $PAGE->context->id,
                'mod_fpdquadern', $filearea, $itemid
            );
            $html = format_text($value, $format);
            if ($dataedicio) {
                $html .= \html_writer::div(
                    $this->output->data($dataedicio, 'datetime', $limitedicio));
            }
            $this->add_element_static('', $label, $html);
        }
    }

    protected function add_element_filemanager($name, $label, $filearea, $itemid, $static=false) {
        global $PAGE;

        if ($this->editable and !$static) {
            $draftitemid = file_get_submitted_draft_itemid($name);
            file_prepare_draft_area(
                $draftitemid, $PAGE->context->id, 'mod_fpdquadern',
                $filearea, $itemid, null);
            $this->_form->addElement(
                'filemanager', $name, $label, null,
                array('maxfiles' => 1, 'subdirs' => false));
            $this->_form->setDefault($name, $draftitemid);
            $this->filemanagers[] = array(
                'name' => $name,
                'draftitemid' => $draftitemid,
                'filearea' => $filearea,
            );

        } else {
            $html = '';
            $files = get_file_storage()->get_area_files(
                $PAGE->context->id, 'mod_fpdquadern', $filearea, $itemid);
            foreach ($files as $file) {
                if (!$file->is_directory()) {
                    $url = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(), $file->get_component(), $file->get_filearea(),
                        $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                    $html .= \html_writer::link($url, $file->get_filename()) . '<br/>';
                }
            }
            if ($html) {
                $this->add_element_static('', $label, $html);
            }
        }
    }

    protected function add_element_fullname($user, $label='Nom i cognoms') {
        global $COURSE, $OUTPUT;
        $link = '';
        if ($user) {
            $url = new \moodle_url('/user/view.php', array('id' => $user->id, 'course' => $COURSE->id));
            $link = $OUTPUT->action_link($url, fullname($user));
        }
        $this->add_element_static('', $label, $link);
    }

    protected function add_element_grade($name, $label, $value, $gradingtype) {
        $options = array(-1 => '');
        foreach (make_grades_menu($gradingtype) as $k => $v) {
            $options[$k] = $v;
        }
        $value = ($value == null ? -1 : (int) $value);
        $this->add_element_select($name, $label, $value, $options);
        $this->filters[] = function($data) use ($name) {
            if ($data->$name < 0) {
                $data->$name = null;
            }
        };
    }

    protected function add_element_franja_horaria(
        $label, $name_de, $value_de, $name_a, $value_a, $optional=false
    ) {
        $name = $this->unique_name();
        if ($this->editable) {
            $elements = array_merge(
                $this->create_elements_hora(
                    $name_de, $value_de, true, $optional
                ),
                array($this->create_element_static(' - ')),
                $this->create_elements_hora(
                    $name_a, $value_a, true, $optional
                )
            );
            $this->_form->addGroup($elements, $name, $label, '', false);
            $this->filters[] = function($data) use($name_de, $name_a) {
                if ($data->$name_de === null or $data->$name_a === null) {
                    $data->$name_de = $data->$name_a = null;
                }
            };
        }
    }

    protected function add_element_select($name, $label, $value, array $options, $static=false) {
        $hasgroups = is_array(reset($options));
        if ($this->editable and !$static) {
            $element = $hasgroups ? 'selectgroups' : 'select';
            $this->_form->addElement($element, $name, $label, $options);
            $this->_form->setType($name, PARAM_INT);
            $this->_form->setDefault($name, $value);
        } else {
            $text = '';
            if ($hasgroups) {
                foreach ($options as $group) {
                    if (isset($group[$value])) {
                        $text = $group[$value];
                        break;
                    }
                }
            } else {
                if (isset($options[$value])) {
                    $text = $options[$value];
                }
            }
            $this->add_element_static('', $label, s($text));
        }
    }

    protected function add_element_static($name, $label, $value) {
        $name = $name ?: $this->unique_name();
        $this->_form->addElement('static', $name, $label, trim($value));
    }

    protected function add_element_text(
        $name, $label, $value, $size=64, $maxlength=255, $type=PARAM_TEXT
    ) {
        if ($this->editable) {
            $this->_form->addElement('text', $name, $label, array('size' => $size));
            $this->_form->setType($name, $type);
            $this->_form->addRule(
                $name, get_string('maximumchars', '', $maxlength),
                'maxlength', $maxlength, 'client'
            );
            $this->_form->setDefault($name, $value);
        } else {
            $this->add_element_static('', $label, s($value));
        }
   }

    protected function add_element_textarea($name, $label, $value, $rows=8, $cols=64) {
        if ($this->editable) {
            $this->_form->addElement('textarea', $name, $label, "rows=\"$rows\" cols=\"$cols\"");
            $this->_form->setType($name, PARAM_TEXT);
            $this->_form->setDefault($name, $value);
        } else {
            $this->add_element_static('', $label, format_text($value));
        }
    }

    protected function add_element_user($name, $value, $users) {
        $options = array(0 => '');
        foreach ($users as $user) {
            $options[$user->id] = fullname($user);
        }
        $this->add_element_select($name, 'Usuari', $value, $options);
    }

    protected function add_element_validat($name, $value, $permis=false) {
        $label = 'Dades validades i no editables';
        if ($this->editable and $permis) {
            $this->add_element_checkbox($name, $label, $value);
        }
    }

    protected function add_rule_required($element) {
        if ($this->editable) {
            $this->_form->addRule($element, null, 'required', null, 'client');
        }
    }

    protected function create_element_select($name, $value, $options, $static=false) {
        if ($this->editable and !$static) {
            $element = $this->_form->createElement('select', $name, '', $options);
            $this->_form->setType($name, PARAM_INT);
            $this->_form->setDefault($name, $value);
            return $element;
        } else {
            return $this->create_element_static($options[$value]);
        }
    }

    protected function create_element_static($value) {
        return $this->_form->createElement(
            'static', $this->unique_name(), null, $value
        );
    }

    protected function create_elements_hora(
        $name, $value, $rellotge=false, $optional=false
    ) {
        $opcions_hora = array();
        $opcions_minut = array();
        if ($rellotge and $optional) {
            $opcions_hora[-1] = '';
        }
        for ($i = 0; $i <= ($rellotge ? 23 : 20); $i++) {
            $opcions_hora[$i] = ($rellotge ? "$i" :
                                 ($i == 1 ? "1 hora" : "$i hores"));
        }
        for ($i = 0; $i <= 55; $i += 5) {
            $opcions_minut[$i] = sprintf($rellotge ? '%02d' : '%d minuts', $i);
        }

        $hora = ($value === null ? -1 : (int) (round($value * 60) / 60));
        $minut = (int) (round($value * 60) % 60);

        if ($this->editable) {
            $this->filters[] = function($data) use($name) {
                $hora = $data->{$name}['hora'];
                $minut = $data->{$name}['minut'];
                $data->$name = $hora < 0 ? null : $hora + $minut / 60;
            };
            return array(
                $this->create_element_select(
                    "{$name}[hora]", $hora, $opcions_hora),
                $this->create_element_static($rellotge ? ':' : ' i '),
                $this->create_element_select(
                    "{$name}[minut]", $minut, $opcions_minut
                )
            );
        } else {
            $text = '';
            if ($rellotge or $hora > 0) {
                $text .= $opcions_hora[$hora];
            }
            if ($rellotge or $minut > 0) {
                $text .= $rellotge ? ':' : ($text ? ' i ' : '');
                $text .= $opcions_minut[$minut];
            }
            return array($this->create_element_static($text));
        }
    }

    protected function options_llista($llista, $value=false) {
        $options = array('' => array(0 => ''));
        $value_found = false;
        foreach ($this->controller->llista($llista) as $e) {
            $options[$e->grup][$e->codi] = $e->nom;
            if ($e->codi == $value) {
                $value_found = true;
            }
        }
        if ($value and !$value_found) {
            $options[''][(int) $value] = "$value";
        }
        if (count($options) == 1) {
            $options = $options[''];
        }
        return $options;
    }

    protected function unique_name() {
        static $index = 0;
        $index++;
        return "item_$index";
    }
}

class activitat_form extends base_form {

    private $activitat;

    function __construct($controller, $activitat) {
        $this->activitat = $activitat;
        parent::__construct($controller, true);
    }

    function definition() {
        $fases = array();
        foreach (range(1, N_FASES) as $num) {
            $fases[$num] = "Fase $num";
        }

        if ($this->activitat->assignada()) {
            $this->add_element_static(
                '', "Fase", $fases[$this->activitat->fase]
            );
            $this->add_element_static('', "Codi", $this->activitat->codi);
        } else {
            $this->add_element_select(
                'fase', "Fase", $this->activitat->fase, $fases
            );
            $this->add_rule_required('fase');
            $this->add_element_text(
                'codi', "Codi", $this->activitat->codi, 20, 20, PARAM_ALPHANUM
            );
            $this->add_rule_required('codi');
        }

        $this->add_element_text('titol',  'Títol', $this->activitat->titol);
        $this->add_rule_required('titol');
        $this->add_element_editor(
            'descripcio', 'Descripció',
            $this->activitat->descripcio, $this->activitat->format_descripcio,
            'descripcio_activitat', $this->activitat->id);
        $this->add_element_date(
            'data_valoracio_alumne', 'Data límit de lliurament de l\'alumne',
            $this->activitat->data_valoracio_alumne, true);
        $this->add_element_date(
            'data_valoracio_professor', 'Data d\'avaluació del professor de l\'IOC',
            $this->activitat->data_valoracio_professor, true);
        $this->add_element_date(
            'data_valoracio_tutor', 'Data límit d\'avaluació del tutor/mentor',
            $this->activitat->data_valoracio_tutor, true);

        $this->add_buttons();
    }

    function validation($data, $files) {
        $errors = array();

        if (isset($data['codi'])) {
            if ($this->activitat->duplicada($data['codi'])) {
                $errors['codi'] = "Ja existeix una activitat amb aquest codi";
            }
        }

        return $errors;
    }
}

class activitat_complementaria_form extends base_form {

    private $activitat;

    function __construct($controller, $activitat) {
        $this->activitat = $activitat;
        parent::__construct($controller, true);
    }

    function definition() {
        if ($this->controller->permis_validar_activitat_complementaria()) {
            $this->add_element_text(
                'codi', "Codi", $this->activitat->codi, 20, 20, PARAM_ALPHANUM);
            $this->add_rule_required('codi');
        } else {
            $this->add_element_static('', 'Codi', $this->activitat->codi);
        }

        $this->add_element_text('titol',  'Títol', $this->activitat->titol);
        $this->add_rule_required('titol');

        $this->add_element_editor(
            'descripcio', 'Descripció',
            $this->activitat->descripcio, $this->activitat->format_descripcio,
            'descripcio_activitat', $this->activitat->id);

        if ($this->controller->permis_validar_activitat_complementaria()) {
            $this->add_element_date(
                'data_valoracio_alumne', 'Data límit de lliurament de l\'alumne',
                $this->activitat->data_valoracio_alumne, true);
            $this->add_element_date(
                'data_valoracio_professor',
                'Data d\'avaluació del professor de l\'IOC',
                $this->activitat->data_valoracio_professor, true);
            $this->add_element_date(
                'data_valoracio_tutor', 'Data límit d\'avaluació del tutor/mentor',
                $this->activitat->data_valoracio_tutor, true);
        }

        $this->add_element_validat(
            'validada', $this->activitat->validada,
            $this->controller->permis_validar_activitat_complementaria());

        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = array();

        if ($this->controller->permis_validar_activitat_complementaria()) {
            if ($this->activitat->duplicada($data['codi'])) {
                $errors['codi'] = "Ja existeix una activitat amb aquest codi";
            }
        }

        return $errors;
    }
}

class competencia_form extends base_form {

    private $competencia;

    function __construct($controller, $competencia) {
        $this->competencia = $competencia;
        parent::__construct($controller, true);
    }

    function definition() {
        if ($this->competencia->avaluada()) {
            $this->add_element_static('', "Codi", $this->competencia->codi);
        } else {
            $this->add_element_text(
                'codi', "Codi", $this->competencia->codi, 20, 20, PARAM_ALPHANUM
            );
            $this->add_rule_required('codi');
        }

        $this->add_element_textarea(
            'descripcio', 'Descripció', $this->competencia->descripcio);

        $this->add_buttons();
    }

    function validation($data, $files) {
        $errors = array();

        if (isset($data['codi'])) {
            if ($this->competencia->duplicada($data['codi'])) {
                $errors['codi'] = "Ja existeix una competència amb aquest codi";
            }
        }

        return $errors;
    }
}

class importacio_llista_form extends base_form {

    function definition() {
        $this->_form->addElement(
            'filepicker', 'file', '', null, array('accepted_types' => 'text/csv'));
        $this->add_action_buttons(true, 'Importa');
    }
}

class dades_alumne_form extends base_form {

    function definition() {
        $alumne = $this->controller->alumne;
        $this->add_element_fullname($alumne->alumne());
        $this->add_element_text(
            'alumne_dni', 'DNI', $alumne->alumne_dni, 32);
        $this->add_element_select(
            'alumne_especialitat', 'Especialitat', $alumne->alumne_especialitat,
            $this->options_llista('especialitats_docents', $alumne->alumne_especialitat));
        $this->add_element_text(
            'alumne_adreca', 'Adreça', $alumne->alumne_adreca, 32);
        $this->add_element_text(
            'alumne_codi_postal', 'Codi postal',
            $alumne->alumne_codi_postal, 32);
        $this->add_element_text(
            'alumne_poblacio', 'Població', $alumne->alumne_poblacio, 32);
        $this->add_element_text(
            'alumne_telefon', 'Telèfon', $alumne->alumne_telefon, 32);
        $this->add_element_select(
            'alumne_titol', 'Títol equivalent', $alumne->alumne_titol,
            $this->options_llista('titols_equivalents', $alumne->alumne_titol));
        if ($this->controller->permis_veure_quadern_anterior()) {
            $this->add_element_filemanager(
                'quadern_anterior', 'Quadern anterior',
                'quadern_anterior', $alumne->id);
        }
        $this->add_element_validat(
            'alumne_validat', $alumne->alumne_validat,
            $this->controller->permis_validar_dades());
        $this->add_buttons();
    }
}

class dades_centre_estudis_form extends base_form {

    function definition() {
        $this->add_element_static(
            '', 'Nom', $this->controller->quadern->nom_centre_estudis);
        $this->add_element_static(
            '', 'Codi de centre', $this->controller->quadern->codi_centre_estudis);
        $this->add_element_static(
            '', 'Adreça', $this->controller->quadern->adreca_centre_estudis);
    }
}

class dades_professor_form extends base_form {

    function definition() {
        if ($this->editable and $this->controller->permis_editar_professor()) {
            $this->add_element_user(
                'professor_id', $this->controller->alumne->professor_id,
                $this->controller->professors_inscrits());
        } else {
            $this->add_element_fullname(
                $this->controller->alumne->professor(), 'Nom i cognoms');
        }
        $this->add_buttons();
    }
}

class dades_centre_practiques_form extends base_form {

    function definition() {
        $alumne = $this->controller->alumne;
        $this->add_element_text(
            'centre_nom', 'Nom', $alumne->centre_nom, 32);
        $this->add_element_text(
            'centre_codi', 'Codi de centre', $alumne->centre_codi, 32);
        $this->add_element_select(
            'centre_tipus', 'Tipus de centre', $alumne->centre_tipus,
            $this->options_llista('tipus_centre', $alumne->centre_tipus));
        $this->add_element_text(
            'centre_director', 'Nom del director',
            $alumne->centre_director, 32);
        $this->add_element_text(
            'centre_coordinador', 'Nom del coordinador de pràctiques',
            $alumne->centre_coordinador, 32);
        $this->add_element_validat(
            'centre_validat', $alumne->centre_validat,
            $this->controller->permis_validar_dades());
        $this->add_buttons();
    }
}

class dades_tutor_form extends base_form {

    function definition() {
        $alumne = $this->controller->alumne;

        if ($this->editable and $this->controller->permis_editar_tutor()) {
            $this->add_element_user(
                'tutor_id', $alumne->tutor_id,
                $this->controller->tutors_inscrits());
        } else {
            $this->add_element_fullname($alumne->tutor());
        }
        $this->add_element_text(
            'tutor_telefon', 'Telèfon de contacte', $alumne->tutor_telefon, 32);
        $this->add_element_text(
            'tutor_horari', 'Horari de contacte', $alumne->tutor_horari, 32);
        $this->add_element_text(
            'tutor_especialitat', 'Especialitat docent',
            $alumne->tutor_especialitat, 32);
        $this->add_element_textarea(
            'tutor_cicles', 'Cicles que imparteix',
            $alumne->tutor_cicles, 4, 32);
        $this->add_element_textarea(
            'tutor_credits', 'Crèdits/mòduls que imparteix',
            $alumne->tutor_credits, 4, 32);
        $this->add_element_validat(
            'tutor_validat', $alumne->tutor_validat,
            $this->controller->permis_validar_dades());

        $this->add_buttons();
    }
}

class calendari_form extends base_form {

    private $fase;

    function __construct($controller, $fase, $editable=false) {
        $this->fase = $fase;
        parent::__construct($controller, $editable);
    }

    function definition() {
        $fase = $this->fase->fase;
        $hores = $this->controller->quadern->{"durada_fase_$fase"};

        $this->add_element_static('', "Durada", $hores . ' hores');
        $this->add_element_date(
            'data_inici', "Data d'inici",
            $this->fase->data_inici, true);
        $this->add_element_date(
            'data_final', "Data de finalització",
            $this->fase->data_final, true);
        $this->add_element_textarea(
            'observacions_calendari', "Observacions",
            format_string($this->fase->observacions_calendari));

        $this->add_element_validat(
            'calendari_validat', $this->fase->calendari_validat,
            $this->controller->permis_validar_calendari());

        $text = "Estic assabentat/ada de l'horari i em comprometo a complir-lo";
        if ($this->fase->calendari_acceptat) {
            $this->add_element_static(
                '', '', "Validat i acceptat per l'alumne");
        } elseif ($this->controller->permis_acceptar_calendari($this->fase->fase)) {
            $params = array('sesskey' => sesskey());
            $url = $this->controller->url_fase('acceptar_calendari', $params);
            $text = "Estic assabentat/ada de l'horari i em comprometo a complir-lo";
            $html = '<a href="' . $url  . '">' . $text . '</a>';
            $this->add_element_static('', '', $html);
        }

        $this->add_buttons();
    }
}

class seguiment_form extends base_form {

    private $dia;

    function __construct($controller, $dia) {
        $this->dia = $dia;
        parent::__construct($controller, true);
    }

    function definition() {
        $this->add_element_date('data', "Data", $this->dia->data);

        for ($i = 1; $i <= 3; $i++) {
            $this->add_element_franja_horaria(
                $i == 1 ? 'Hores' : '',
                "de$i", $this->dia->{"de$i"},
                "a$i", $this->dia->{"a$i"},
                true // optional
            );
        }

        $this->add_element_validat(
            'validat', $this->dia->validat,
            $this->controller->permis_validar_seguiment());

        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = array();

        if ($this->dia->duplicat($data['data'])) {
            $errors['data'] = "Ja s'ha introduït el seguiment per aquesta data";
        }

        return $errors;
    }
}

class activitats_alumne_form extends base_form {

    private $activitats;

    function __construct($controller, $activitats) {
        $this->activitats = $activitats;
        parent::__construct($controller, true);
    }

    function definition() {
        foreach ($this->activitats as $a) {
            $descripcio = $this->output->descripcio_activitat($a);

            $icona = $this->output->icona_descripcio_activitat($a);
            $label = '<strong>' . s($a->codi) . '</strong> '
                . s($a->titol) . ($descripcio ? ' ' . $icona : '');

            $assignada = $a->assignada($this->controller->alumne->alumne_id);
            if ($assignada) {
                $valoracio = $this->controller->alumne->valoracio($a->id);
                $valorada = $valoracio->valorada();
            } else {
                $valorada = false;
            }

            $this->add_element_checkbox(
                "activitat_{$a->id}", $label, $assignada, $valorada);

            $this->add_element_static('', '', $descripcio);
        }

        $this->add_action_buttons();
    }
}

class valoracio_form extends base_form {

    private $activitat;
    private $valoracio;

    function __construct($controller, $activitat, $valoracio, $editable=false) {
        $this->activitat = $activitat;
        $this->valoracio = $valoracio;
        parent::__construct($controller, $editable);
    }

    function definition() {
        global $PAGE;

        if (!$this->editable) {
            $this->add_element_date(
                'data_valoracio_alumne',
                'Data límit de lliurament de l\'alumne',
                $this->activitat->data_valoracio_alumne, true, true);
            $this->add_element_date(
                'data_valoracio_professor',
                'Data d\'avaluació del professor de l\'IOC',
                $this->activitat->data_valoracio_professor, true, true);
            $this->add_element_date(
                'data_valoracio_tutor',
                'Data límit d\'avaluació del tutor/mentor',
                $this->activitat->data_valoracio_tutor, true, true);
        }

        $this->add_element_editor(
            'valoracio_tutor',
            "Observacions del tutor/mentor",
            $this->valoracio->valoracio_tutor,
            $this->valoracio->format_valoracio_tutor,
            'valoracio_activitat_tutor', $this->valoracio->id,
            !$this->controller->permis_editar_valoracio_tutor(),
            $this->valoracio->data_valoracio_tutor,
            $this->activitat->data_valoracio_tutor);

        $this->add_element_editor(
            'valoracio_professor', "Observacions del professor de l'IOC",
            $this->valoracio->valoracio_professor,
            $this->valoracio->format_valoracio_professor,
            'valoracio_activitat_professor', $this->valoracio->id,
            !$this->controller->permis_editar_valoracio_professor(),
            $this->valoracio->data_valoracio_professor,
            $this->activitat->data_valoracio_professor);

        $competencies = $this->controller->quadern->competencies();
        $avaluacions_professor = array();
        $avaluacions_tutor = array();
        foreach ($this->valoracio->avaluacions() as $id => $a) {
            $avaluacions_professor[$id] = $a->grau_assoliment_professor;
            $avaluacions_tutor[$id] = $a->grau_assoliment_tutor;
        }

        $this->add_element_avaluacions(
            'grau_assoliment_tutor', "Grau d'assoliment (tutor/mentor)",
            $avaluacions_tutor, $competencies,
            !$this->controller->permis_editar_valoracio_tutor()
        );

        $this->add_element_avaluacions(
            'grau_assoliment_professor', "Grau d'assoliment (professor de l'IOC)",
            $avaluacions_professor, $competencies,
            !$this->controller->permis_editar_valoracio_professor()
        );

        $this->add_element_validat(
            'valoracio_validada', $this->valoracio->valoracio_validada,
            $this->controller->permis_validar_valoracio()
        );

        $this->add_buttons();
    }
}

class qualificacio_form extends base_form {

    function definition() {
        $grade = $this->controller->quadern->grade;

        foreach (range(1, N_FASES) as $num) {
            $fase = $this->controller->alumne->fase($num);
            $this->add_element_grade(
                "qualificacio_$num", "Fase $num",
                $fase->qualificacio, $grade);
        }

        $this->add_element_grade(
            'qualificacio_final', "Qualificació final de les pràctiques",
            $this->controller->alumne->qualificacio, $grade);

        $this->add_buttons();
    }
}

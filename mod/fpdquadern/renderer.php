<?php
/**
 * @package mod_fpdquadern
 * @copyright 2013 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

defined('MOODLE_INTERNAL') || die();

class mod_fpdquadern_renderer extends plugin_renderer_base {

    private $controller;

    function set_controller($controller) {
        $this->controller = $controller;
    }

    function accions_pendents($rol, $accions) {
        $o = '';
        $url = $this->controller->url_alumne('veure_accions_pendents');
        $rols = array(
            'admin' => 'Adminitrador',
            'alumne' => 'Alumne',
            'professor' => 'Professor',
            'tutor' => 'Tutor',
        );

        if ($this->controller->permis_veure_totes_accions_pendents()) {
            $o .= $this->output->single_select(
                $url, 'rol', $rols, $rol, array('' => 'Tots')
            );
        } else {
            $rol = 'alumne';
        }

        $table = new html_table();

        $table->head = array("Usuari", "Fase", "Acció", "Data límit");
        $table->align = array('left', 'left', 'left', 'left');

        if ($rol) {
            array_shift($table->head);
            array_shift($table->align);
        }

        foreach ($accions as $accio) {
            if (!$rol or $accio[0] == $rol) {
                $row = array();
                if (!$rol) {
                    $row[] = $rols[$accio[0]];
                }
                $row[] = $accio[1] ? "Fase {$accio[1]}" : '';
                $row[] = $accio[2];
                if (!empty($accio[3])) {
                    $row[] = $this->data($accio[3], 'date', time());
                } else {
                    $row[] = '';
                }
                $table->data[] = $row;
            }
        }

        $o .= html_writer::table($table);

        return $o;
    }

    function activitats_alumne($fase) {
        $o = '';

        foreach ($this->controller->alumne->activitats($fase) as $a) {
            $o .= $this->activitat($a);
        }

        return $this->pagina_alumne('activitats', $fase, $o);
    }

    function calendari($fase) {

        $o = $this->output->heading(
            "Planificació" .
            $this->icona_validat($fase->calendari_validat) .
            $this->icona_editar(
                $this->controller->permis_editar_calendari($fase->fase),
                $this->controller->url_fase('editar_calendari')));

        $o .= $this->moodleform(
            new mod_fpdquadern\calendari_form($this->controller, $fase)
        );

        $url_afegir = $this->controller->url_fase('afegir_seguiment');
        $o .= $this->output->heading(
            "Seguiment" . $this->icona('t/add', 'Afegeix', $url_afegir));

        $o .= $this->output->box_start('seguiment');

        $table = new html_table();
        $table->head = array("Data", "Hores", "Validat", "");
        $table->align = array('left', 'center', 'center', 'left');

        $dies = $this->controller->alumne->dies_seguiment($fase->fase);

        foreach ($dies as $dia) {
            $validat = $this->icona('t/check', 'Sí', null, array(
                'id' => "mod-fpdquadern-dia-validat-{$dia->id}",
                'class' => ($dia->validat ? 'mod-fpdquadern-dia-validat'
                            : 'mod-fpdquadern-dia-validat hidden')
            ));
            if (!$dia->validat and $this->controller->permis_validar_seguiment()) {
                $params = array('dia_id' => $dia->id, 'sesskey' => sesskey());
                $url = $this->controller->url_alumne('validar_seguiment', $params);
                $validat .= $this->output->action_link(
                    $url, "Valida", null, array(
                        'class' => 'mod-fpdquadern-dia-validar',
                        'data-mod-fpdquadern-dia' => $dia->id
                    ));
            }

            $accions = array();
            if ($this->controller->permis_editar_seguiment($dia)) {
                $url_editar = $this->controller->url_alumne(
                    'editar_seguiment', array('dia_id' => $dia->id));
                $url_suprimir = $this->controller->url_alumne(
                    'suprimir_seguiment', array('dia_id' => $dia->id));
                $accions[] = $this->icona('t/editstring', 'Edita', $url_editar);
                $accions[] = $this->icona('t/delete', 'Suprimeix', $url_suprimir);
            }

            $table->data[] = array(
                $this->data($dia->data),
                $this->hores_dia_seguiment($dia),
                $validat,
                implode(' ', $accions),
            );
        }

        $o .= html_writer::table($table);
        $o .= $this->output->box_end();

        return $this->pagina_alumne('calendari', $fase->fase, $o);
    }

    function confirmacio_suprimir_activitat($activitat) {
        $message = ("Esteu segur que voleu suprimir l'activitat " .
                    "<em>{$activitat->codi}</em>?");
        $continue = $this->controller->url('suprimir_activitat', array(
            'activitat_id' => $activitat->id,
            'confirm' => true,
            'sesskey' => sesskey()
        ));
        $cancel = $this->controller->url('veure_activitats');
        $confirm = $this->output->confirm($message, $continue, $cancel);
        return $this->pagina($confirm);
    }

    function confirmacio_suprimir_activitat_complementaria($activitat) {
        $nom = $activitat->codi ? $activitat->codi : $activitat->titol;
        $message = "Esteu segur que voleu suprimir l'activitat " .
            "complementària <em>{$nom}</em>?";
        $params = array('confirm' => true, 'sesskey' => sesskey());
        $continue = $this->controller->url_activitat(
            'suprimir_activitat_complementaria', $params);
        $cancel = $this->controller->url_fase('veure_activitats_alumne');
        $confirm = $this->output->confirm($message, $continue, $cancel);

        return $this->pagina_alumne('activitats', $activitat->fase, $confirm);
    }

    function confirmacio_suprimir_competencia($competencia) {
        $message = ("Esteu segur que voleu suprimir la competència " .
                    "<em>{$competencia->codi}</em>?");
        $continue = $this->controller->url('suprimir_competencia', array(
            'competencia_id' => $competencia->id,
            'confirm' => true,
            'sesskey' => sesskey()
        ));
        $cancel = $this->controller->url('veure_competencies');
        $confirm = $this->output->confirm($message, $continue, $cancel);
        return $this->pagina($confirm);
    }

    function confirmacio_suprimir_seguiment($dia) {
        $data = $this->data($dia->data);
        $message = "Esteu segur que voleu suprimir el dia <em>$data</em>?";
        $continue = $this->controller->url_alumne('suprimir_seguiment', array(
            'dia_id' => $dia->id,
            'confirm' => true,
            'sesskey' => sesskey()
        ));
        $cancel = $this->controller->url_alumne('veure_calendari', array(
            'fase' => $dia->fase,
        ));
        $confirm = $this->output->confirm($message, $continue, $cancel);

        return $this->pagina_alumne('calendari', $dia->fase, $confirm);
    }

    function dades() {
        $alumne = $this->controller->alumne;

        $o = $this->output->heading(
            "Alumne" .
            $this->icona_validat($alumne->alumne_validat) .
            $this->icona_editar(
                $this->controller->permis_editar_dades_alumne(),
                $this->controller->url_alumne('editar_dades_alumne')
            )
        );
        $o .= $this->moodleform(
            new mod_fpdquadern\dades_alumne_form($this->controller)
        );
        $o .= $this->output->heading("Centre d'estudis");
        $o .= $this->moodleform(
            new mod_fpdquadern\dades_centre_estudis_form($this->controller)
        );
        $o .= $this->output->heading(
            "Professor de l'IOC" .
            $this->icona_editar(
                $this->controller->permis_editar_professor(),
                $this->controller->url_alumne('editar_dades_professor')
            )
        );
        $o .= $this->moodleform(
            new mod_fpdquadern\dades_professor_form($this->controller)
        );
        $o .= $this->output->heading(
            $heading = "Centre de pràctiques" .
            $this->icona_validat($alumne->centre_validat) .
            $this->icona_editar(
                $this->controller->permis_editar_dades_centre(),
                $this->controller->url_alumne('editar_dades_centre')
            )
        );
        $o .= $this->moodleform(
            new mod_fpdquadern\dades_centre_practiques_form($this->controller)
        );
        $o .= $this->output->heading(
            "Tutor del centre de pràctiques" .
            $this->icona_validat($alumne->tutor_validat) .
            $this->icona_editar(
                $this->controller->permis_editar_tutor(),
                $this->controller->url_alumne('editar_dades_tutor')
            )
        );
        $o .= $this->moodleform(
            new mod_fpdquadern\dades_tutor_form($this->controller)
        );

        return $this->pagina_alumne('dades', false, $o);
    }

    function data($data, $format='date', $limit=false) {
        if (!$data) {
            return '';
        }
        $fixday = (strpos($format, 'short') === false);
        $o = userdate($data, get_string('strftime' . $format), 99, $fixday);
        $classes = 'mod-fpdquadern-data';
        if ($limit and $data > $limit) {
            $classes .= ' mod-fpdquadern-data-retard';
        }
        return html_writer::span($o, $classes);
    }

    function descripcio_activitat($activitat) {
        $html = format_text(
            file_rewrite_pluginfile_urls(
                $activitat->descripcio, 'pluginfile.php',
                $this->controller->context->id, 'mod_fpdquadern',
                'descripcio_activitat', $activitat->id
            ),
            $activitat->format_descripcio
        );
        if (trim($html)) {
            $id = "mod-fpdquadern-activitat-descripcio-{$activitat->id}";
            $class = 'mod-fpdquadern-activitat-descripcio';
            return $this->output->box($html, $class, $id);
        } else {
            return '';
        }
    }

    function estat_activitat($activitat) {
        if (!$activitat->complementaria()) {
            return '';
        }

        if (!$activitat->validada and !$activitat->acceptada) {
            return "proposada";
        } else if (!$activitat->validada) {
            return "pendent de la validació del professor";
        } else if (!$activitat->acceptada) {
            return "pendent de l'acceptació del tutor";
        } else {
            return "complementària";
        }
    }

    function formulari_activitat($form) {
        return $this->pagina(
            $this->heading('Activitat') . $this->moodleform($form));
    }

    function formulari_activitat_complementaria($fase, $form) {
        $o = $this->moodleform($form);
        return $this->pagina_alumne('afegir-activitat', $fase, $o);
    }

    function formulari_activitats_alumne($fase, $form) {
        $o = $this->moodleform($form);
        return $this->pagina_alumne('seleccionar-activitats', $fase, $o);
    }

    function formulari_calendari($fase, $form) {
        $o = $this->heading("Planficació");
        $o .= $this->moodleform($form);
        return $this->pagina_alumne('calendari', $fase, $o);
    }

    function formulari_competencia($form) {
        return $this->pagina(
            $this->heading('Competència') . $this->moodleform($form));
    }

    function formulari_dades_alumne($form) {
        $o = $this->heading("Alumne");
        $o .= $this->moodleform($form);
        return $this->pagina_alumne('dades', false, $o);
    }

    function formulari_dades_professor($form) {
        $o = $this->heading("Professor de l'IOC");
        $o .= $this->moodleform($form);
        return $this->pagina_alumne('dades', false, $o);
    }

    function formulari_dades_centre($form) {
        $o = $this->heading("Centre de pràctiques");
        $o .= $this->moodleform($form);
        return $this->pagina_alumne('dades', false, $o);
    }

    function formulari_dades_tutor($form) {
        $o = $this->heading("Tutor del centre de pràctiques");
        $o .= $this->moodleform($form);
        return $this->pagina_alumne('dades', false, $o);
    }

    function formulari_qualificacio($form) {
        return $this->pagina_alumne('qualificacio', false, $this->moodleform($form));
    }

    function formulari_seguiment($fase, $form) {
        $o = $this->heading("Seguiment");
        $o .= $this->moodleform($form);
        return $this->pagina_alumne('calendari', $fase, $o);
    }

    function formulari_valoracio($activitat, $form) {
        $o = $this->heading(s($activitat->codi) . ' ' .  s($activitat->titol));
        $o .= $this->moodleform($form);
        return $this->pagina_alumne('activitats', $activitat->fase, $o);
    }

    function hores_dia_seguiment($dia) {
        $hores = array();
        for ($i = 1; $i <= 3; $i++) {
            if ($dia->{"de$i"} !== null and $dia->{"a$i"} !== null) {
                $hores[] = $this->hores($dia->{"de$i"}) .
                    ' - ' . $this->hores($dia->{"a$i"});
            }
        }
        return implode('<br/>', $hores);
    }

    function icona_descripcio_activitat($activitat) {
        return $this->icona(
            't/preview', 'Mostra la descripció', '#', array(
                'class' => 'mod-fpdquadern-activitat-mostrar',
                'data-mod-fpdquadern-activitat' => $activitat->id
            )
        );
    }

    function importacio_llista($llista, $grups, $form, array $errors) {
        $o = '';

        $text = ("El fixer ha de tenir format CSV, amb una coma (,) com a " .
                 "delimitador de camp i cometes (\") com a delimitador de " .
                 "text (les opcions predetermiades del LibreOffice Calc en " .
                 "desar en format CSV).");
        $o .= html_writer::tag('p', $text);

        if ($errors) {
            $o .= html_writer::tag(
                'h3', "S'han trobat errors en importar el fitxer");
            $o .= html_writer::start_tag('ul');
            foreach ($errors as $error) {
                $o .= html_writer::tag('li', s($error));
            }
            $o .= html_writer::end_tag('ul');
        }

        $o .= $this->moodleform($form);
        return $this->pagina_llista($llista, 'importar', $o);
    }

    function index_activitats() {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable mod-fpdquadern-activitats';
        $table->head = array('Fase', 'Codi', 'Títol', '');
        $table->colclasses = array('', '', 'mod-fpdquadern-columna-text');
        $table->align = array('center', 'left', 'left', 'left');

        foreach ($this->controller->quadern->activitats() as $a) {
            $params = array('activitat_id' => $a->id);
            $url_editar = $this->controller->url('editar_activitat', $params);
            $url_suprimir = $this->controller->url('suprimir_activitat', $params);

            $accions = array(
                $this->icona('t/edit', 'Actualitza', $url_editar)
            );
            if (!$a->assignada()) {
                $accions[] = $this->icona(
                    't/delete', 'Suprimeix', $url_suprimir
                );
            }

            $table->data[] = array(
                $a->fase,
                format_string($a->codi),
                format_string($a->titol),
                implode(' ', $accions),
            );
        }

        $o = $this->heading('Activitats ' . $this->icona(
            't/add', 'Afegeix', $this->controller->url('afegir_activitat')
        ));
        $o .= html_writer::table($table);

        return $this->pagina($o);
    }

    function index_alumnes($alumnes, $users, $groups, $groupid) {
        $o = '';

        if ($groups) {
            $menu = array(0 => get_string('allparticipants'));
            foreach ($groups as $group) {
                $menu[$group->id] = format_string($group->name);
            }
            $select = new single_select(
                $this->page->url, 'group', $menu,
                $groupid, null, 'selectgroup');
            $select->label = 'Grup';
            $o .= $this->render($select);
        }

        $table = new html_table();
        $table->head  = array(
            'Alumne',
            'Especialitat',
            'Centre',
            'Fase',
            "Data d'inici",
            "Data final"
        );
        $table->align = array('left', 'left', 'left', 'left', 'center', 'center');


        $date = usergetdate(time());
        $today = make_timestamp($date['year'], $date['mon'], $date['mday']);

        foreach ($users as $user) {
            $alumne = $alumnes[$user->id];
            $url = $this->controller->url(
                'veure_alumne', array('alumne_id' => $user->id));

            $avis = '';
            if ($this->controller->es_professor() and
                $alumne->avis_professor()) {
                $avis = ' ' . html_writer::tag('strong', '(!)');
            }

            $fase = false;
            $inici = false;
            $final = false;
            foreach (range(1, mod_fpdquadern\N_FASES) as $num) {
                $f = $alumne->fase($num);
                if ($f->data_inici and $today >= $f->data_inici) {
                    $fase = $num;
                    $inici = $f->data_inici;
                    $final = $f->data_final;
                }
            }

            $table->data[] = array(
                $this->output->action_link($url, fullname($user)) . $avis,
                $this->nom_element_llista(
                    'especialitats_docents', $alumne->alumne_especialitat),
                $alumne->centre_nom,
                $fase ? "Fase $fase" : '',
                $this->data($inici, 'datefullshort'),
                $this->data($final, 'datefullshort'),
            );
        }

        $o .= html_writer::table($table);

        return $o;
    }

    function index_competencies() {
        $table = new html_table();
        $table->attributes['class'] = 'generaltable mod-fpdquadern-competencies';
        $table->head = array('Codi', 'Descripció', '');
        $table->colclasses = array('', 'mod-fpdquadern-columna-text');
        $table->align = array('left', 'left', 'left');

        foreach ($this->controller->quadern->competencies() as $c) {
            $params = array('competencia_id' => $c->id);
            $url_editar = $this->controller->url('editar_competencia', $params);
            $url_suprimir = $this->controller->url('suprimir_competencia', $params);

            $accions = array(
                $this->icona('t/edit', 'Actualitza', $url_editar)
            );
            if (!$c->avaluada()) {
                $accions[] = $this->icona(
                    't/delete', 'Suprimeix', $url_suprimir
                );
            }

            $table->data[] = array(
                format_string($c->codi),
                format_string($c->descripcio),
                implode(' ', $accions),
            );
        }

        $o = $this->heading('Competències ' . $this->icona(
            't/add', 'Afegeix', $this->controller->url('afegir_competencia')
        ));
        $o .= html_writer::table($table);

        return $this->pagina($o);
    }

    function llista($llista, $grups) {
        $o = '';

        $table = new html_table();
        $table->attributes['class'] = 'generaltable mod-fpdquadern-llista';
        $table->head = array('Codi', 'Nom');
        $table->align = array('left', 'left', 'left');

        if ($grups) {
            $table->head[] = 'Grup';
        }

        foreach ($this->controller->quadern->elements_llista($llista) as $e) {
            $row = array($e->codi, $e->nom);
            if ($grups) {
                $row[] = $e->grup;
            }
            $table->data[] = $row;
        }

        $o .= html_writer::table($table);

        return $this->pagina_llista($llista, 'veure_llista', $o);
    }

    function nom_element_llista($llista, $codi) {
        $elements = $this->controller->llista($llista);
        if (!$codi) {
            return '';
        } else if (isset($elements[$codi])) {
            return s($elements[$codi]->nom);
        } else {
            return s("$codi");
        }
    }

    function pagina_accions_pendents($rol, $accions) {
        $o = $this->accions_pendents($rol, $accions);
        return $this->pagina_alumne('accions_pendents', false, $o);
    }

    function pagina_index_alumnes($alumnes, $users, $groups, $groupid) {
        $o = $this->index_alumnes($alumnes, $users, $groups, $groupid);
        return $this->pagina($o);
    }

    function qualificacions_finals() {
        $form = new mod_fpdquadern\qualificacio_form($this->controller);
        $o = $this->moodleform($form);
        if ($this->controller->permis_editar_qualificacio()) {
            $url = $this->controller->url_alumne('editar_qualificacio');
            $o .= $this->output->action_link($url, 'Edita');
        }
        return $this->pagina_alumne('qualificacio', false, $o);
    }

    private function activitat($activitat) {
        $valoracio = $this->controller->alumne->valoracio($activitat->id);

        $titol = s($activitat->codi) . ' ' . s($activitat->titol);
        $descripcio = $this->descripcio_activitat($activitat);

        $estat = $this->estat_activitat($activitat);
        if ($estat) {
            $class = 'mod-fpdquadern-activitat-estat';
            $titol .= ' ' . html_writer::span("($estat)", $class) . ' ';
        }

        $titol .= $this->icona_validat($valoracio->valoracio_validada);

        $titol .= $this->icona_editar(
            $this->controller->permis_editar_valoracio($valoracio, $activitat),
            $this->controller->url_alumne(
                'editar_valoracio', array('activitat_id' => $activitat->id)
            ),
            "Edita la valoració"
        );

        if ($descripcio) {
            $titol .= ' ' . $this->icona_descripcio_activitat($activitat);
        }

        if ($activitat->complementaria()) {
            $params = array('activitat_id' => $activitat->id);
            if ($this->controller->permis_editar_activitat_complementaria($activitat)) {
                $url_editar = $this->controller->url_alumne(
                    'editar_activitat_complementaria', $params);
                $url_suprimir = $this->controller->url_alumne(
                    'suprimir_activitat_complementaria', $params);
                $titol .= ' ' . $this->icona('t/edit', "Edita l'activitat", $url_editar);
                $titol .= ' ' . $this->icona('t/delete', 'Suprimeix', $url_suprimir);
            }
            if ($this->controller->permis_acceptar_activitat_complementaria($activitat)) {
                $params['sesskey'] = sesskey();
                $url = $this->controller->url_alumne(
                    'acceptar_activitat_complementaria', $params);
                $titol .= ' ' . $this->output->action_link($url, "Accepta");
            }
        }

        $o = $this->box_start('mod-fpdquadern-activitat');

        $o .= $this->output->heading(
            $titol, 2, 'mod-fpdquadern-activitat-titol'
        );

        $o .= $descripcio;

        if ($valoracio->valorada()) {
            $form = new mod_fpdquadern\valoracio_form(
                $this->controller, $activitat, $valoracio);
            $o .= $this->moodleform($form);
        }

        $o .= $this->output->box_end();

        return $o;
    }

    private function hores($hores) {
        return sprintf(
            '%d:%02d',
            (int) (round($hores * 60) / 60),
            (int) (round($hores * 60) % 60)
        );
    }

    private function icona(
        $pix, $alt, $url=null, array $attributes=null
    ) {
        $icon = new pix_icon($pix, $alt);

        $attributes = $attributes ?: array();
        if (!isset($attributes['class'])) {
            $attributes['class'] = '';
        }
        $attributes['class'] .= ' mod-fpdquadern-icona';

        if ($url) {
            $attributes['class'] .= ' action-icon';
            return $this->output->action_icon($url, $icon, null, $attributes);
        } else {
            return html_writer::tag('span', $this->render($icon), $attributes);
        }
    }

    private function icona_editar($permis, $url, $alt='Edita') {
        if ($permis) {
            return ' ' . $this->icona('t/editstring', $alt, $url);
        }
    }

    private function icona_validat($validat) {
        if ($validat) {
            return ' ' . $this->icona(
                't/check',  'Dades validades i no editables',
                null, array('class' => 'mod-fpdquadern-icona-validat')
            );
        }
    }

    private function moodleform(moodleform $mform) {
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }

    private function pagina($content, $alumne_id=false) {
        $this->page->requires->jquery();
        $this->page->requires->js('/mod/fpdquadern/client.js');
        $arguments = array(sesskey(), $this->page->cm->id, $alumne_id);
        $this->page->requires->js_init_call('mod_fpdquadern_init', $arguments);
        return $this->output->header() . $content . $this->footer();
    }

    private function pagina_alumne($tab, $fase, $content) {
        $alumne = $this->controller->alumne->alumne();
        $user = html_writer::div(
            $this->output->user_picture($alumne->record()) .
            $this->output->action_link(
                new moodle_url('/user/view.php', array(
                    'id' => $alumne->id,
                    'course' => $this->page->course->id,
                )),
                fullname($alumne)
            ) .
            html_writer::div(
                $this->output->action_link(
                    $this->controller->url_alumne('exportar_alumne'),
                    $this->pix_icon('f/pdf', '') . ' Exporta en PDF'
                ),
                'mod-fpdquadern-alumne-exportar'
            ),
            'mod-fpdquadern-alumne clearfix'
        );

        $tabs = array();

        $url = $this->controller->url_alumne('veure_dades');
        $tabs[] = new tabobject('dades', $url, 'Dades generals');

        foreach (range(1, mod_fpdquadern\N_FASES) as $num) {
            $params = array('fase' => $num);
            $url = $this->controller->url_alumne('veure_calendari', $params);
            $tabs[] = $t = new tabobject("fase-$num", $url, "Fase $num");
            if ($num == $fase) {
                $t->subtree[] = new tabobject(
                    "calendari-$num", $url, 'Calendari');
                $url = $this->controller->url_alumne(
                    'veure_activitats_alumne', $params);
                $t->subtree[] = new tabobject(
                    "activitats-$num", $url, 'Activitats');
                if ($this->controller->permis_seleccionar_activitats()) {
                    $t->subtree[] = new tabobject(
                        "seleccionar-activitats-$num",
                        $this->controller->url_alumne(
                            'seleccionar_activitats', $params),
                        "Selecciona les activitats"
                    );
                }
                $t->subtree[] = new tabobject(
                    "afegir-activitat-$num",
                    $this->controller->url_alumne(
                        'afegir_activitat_complementaria', $params),
                    "Proposa una activitat"
                );
            }
        }

        $url = $this->controller->url_alumne('veure_qualificacio');
        $tabs[] = new tabobject('qualificacio', $url, 'Qualificacions finals');

        $url = $this->controller->url_alumne('veure_accions_pendents');
        $tabs[] = new tabobject('accions_pendents', $url, 'Accions pendents');

        $tabtree = $this->tabtree($tabs, $fase ? "$tab-$fase" : $tab);
        return $this->pagina(
            $user . $tabtree . $content, $this->controller->alumne->alumne_id);
    }

    private function pagina_llista($llista, $subtab, $content) {
        $tabs = array();

        foreach (mod_fpdquadern\veure_llista_view::$LLISTES as $k => $v) {
            $url = $this->controller->url('veure_llista', array('llista' => $k));
            $tabs[] = $t = new tabobject($k, $url, $v['nom']);
            if ($k == $llista) {
                $t->subtree[] = new tabobject('veure_llista', $url, "Llista");
                $url = $this->controller->url('exportar_llista', array('llista' => $k));
                $t->subtree[] = new tabobject('exportar', $url, "Exporta");
                $url = $this->controller->url('importar_llista', array('llista' => $k));
                $t->subtree[] = new tabobject('importar', $url, "Importa");
            }
        }

        $tabtree = $this->tabtree($tabs, $subtab ?: $llista);
        return $this->pagina($tabtree . $content);
    }

}

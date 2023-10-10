<?php
/**
 * @package mod_fpdquadern
 * @copyright 2014 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

namespace mod_fpdquadern;

require_once($CFG->dirroot . '/lib/pdflib.php');

class pdf extends \pdf {

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('FreeSans', '', 8);
        if ($this->getPage() > 1) {
            $this->Cell(0, 0, $this->getAliasNumPage(), 0, false, 'C');
        }
    }

    function Header() {
    }
}

class exporter {

    private $controller;
    private $pdf;
    private $tmpdir;

    function __construct($controller) {
        global $COURSE;

        $this->controller = $controller;
        $this->pdf = new pdf;
        $this->tmpdir = $this->make_temp_dir();

        $this->pdf->SetMargins(10, 10);
        $this->pdf->SetCellMargins(0, 0, 0, 0);
        $this->pdf->setCellPadding(0);
        $this->pdf->SetHtmlVSpace(array(
            'div' => array(array('h' => 0.5), array('h' => 0.5)),
            'p' => array(array('h' => 0.001),array('h' => 2)),
        ));

        $title = ($this->controller->quadern->name . ' - ' .
                  fullname($this->controller->alumne->alumne()));

        $this->pdf->SetTitle($title);

        $this->write_portada();

        $this->pdf->SetFont('FreeSans', '', 10);

        $this->write_dades_generals();
        foreach (range(1, N_FASES) as $num) {
            $this->write_fase($num);
        }
        $this->write_qualificacions();

        $this->write_index();

        $this->pdf->Output($title . '.pdf');

        remove_dir($this->tmpdir);
    }

    private function make_temp_dir($dir='') {
        $path = make_temp_directory('mod_fpdquadern/' . sesskey() . '/' . $dir);
        if (!$path) {
            print_error('cannotcreatetempdir');
        }
        return $path;
    }

    private function format_text($content, $format, $filearea, $itemid=false) {
        $dir = $this->make_temp_dir("$filearea/$itemid");
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $this->controller->context->id, 'mod_fpdquadern',
            $filearea, $itemid, '', false);
        $filenames = array();
        foreach ($files as $file) {
            $filenames[] = $file->get_filename();
            $file->copy_content_to($dir . '/' . $file->get_filename());
        }

        $content = file_rewrite_pluginfile_urls(
            $content, 'pluginfile.php',  $this->controller->context->id,
            'mod_fpdquadern', $filearea, $itemid);
        $content = format_text($content, $format);

        $baseurl = file_rewrite_pluginfile_urls(
            '@@PLUGINFILE@@/', 'pluginfile.php',
            $this->controller->context->id, 'mod_fpdquadern',
            $filearea, $itemid);
        $pattern = '/(<img[^>]+")' . preg_quote($baseurl, '/'). '(.*?)(")/is';
        $callback = function($matches) use ($dir, $filenames) {
            if (in_array($matches[2], $filenames)) {
                $path = 'file://' . $dir . '/' . $matches[2];
            } else {
                $path = K_BLANK_IMAGE;
            }
            return $matches[1] . $path . $matches[3];
        };
        return preg_replace_callback($pattern, $callback, $content);
    }

    private function html_avaluacions($valoracio, $rol) {
        $content = '';

        $avaluacions = array();
        foreach ($valoracio->avaluacions() as $id => $a) {
            $avaluacions[$a->competencia_id] = $a->{"grau_assoliment_$rol"};
        }

        foreach ($this->controller->quadern->competencies() as $c) {
            if (!empty($avaluacions[$c->id])) {
                $grau = s($this->controller->output->nom_element_llista(
                    'graus_assoliment', $avaluacions[$c->id]));
            } else {
                $grau = $this->html_buit();
            }
            $content .= '<div>' . s($c->codi) . ": $grau</div>";
        }

        return $content;
    }

    private function html_buit() {
        return '<i style="color: gray">(en blanc)</i>';
    }

    private function html_qualificacio($qualificacio) {
        $grades = make_grades_menu($this->controller->quadern->grade);

        if ($qualificacio === null) {
            return $this->html_buit();
        } elseif (isset($grades[(int) $qualificacio])) {
            return s($grades[(int) $qualificacio]);
        } else {
            return s($qualificacio);
        }
    }

    private function html_validat($validat=true) {
        global $CFG;

        if ($validat) {
            $path = "{$CFG->dirroot}/pix/t/check.svg";
            // &nbsp; perquè no s'afegeixi un espai vertical (?!)
            return '&nbsp;<img src="'. $path .'" height="9"/>&nbsp;';
        }

        return '';
    }

    private function html_valoracio($activitat, $valoracio, $rol) {
        $content = $this->format_text(
            $valoracio->{"valoracio_$rol"},
            $valoracio->{"format_valoracio_$rol"},
            "valoracio_activitat_$rol", $valoracio->id);

        if ($valoracio->{"data_valoracio_$rol"}) {
            $data_a = $activitat->{"data_valoracio_$rol"};
            $data_v = $valoracio->{"data_valoracio_$rol"};
            $retard = $data_a && $data_v > $data_a ? 'style="color:red"' : '';
            $data = $this->controller->output->data($data_v, 'datetime');
            $content .= "<div $retard>$data</div>";
        }

        return $content;
    }

    private function write($content, $bottom_margin=0) {
        $this->write_row(array(array(0, $content)), 0, $bottom_margin);
    }

    private function write_activitat($activitat) {
        global $CFG;

        $valoracio = $this->controller->alumne->valoracio($activitat->id);

        $titol = s($activitat->codi) . ' ' . s($activitat->titol);
        $estat = $this->controller->output->estat_activitat($activitat);
        $estat = $estat ? "<em>($estat)</em>" : '';
        $estat .= $this->html_validat($valoracio->valoracio_validada);
        $descripcio = $this->format_text(
            $activitat->descripcio, $activitat->format_descripcio,
            'descripcio_activitat', $activitat->id);
        $this->write_heading(3, $titol, $estat);

        $this->write($descripcio, 4);

        $this->write_form(array(
            array(
                array(0, "Data límit de lliurament de l'alumne",
                      $this->controller->output->data(
                          $activitat->data_valoracio_alumne)),
            ),
            array(
                array(100, "Data d'avaluació del professor de l'IOC",
                      $this->controller->output->data(
                          $activitat->data_valoracio_professor)),
                array(0, "Data límit d'avaluació del tutor/mentor",
                      $this->controller->output->data(
                          $activitat->data_valoracio_tutor)),
            ),
            array(
                array(0, "Observacions del tutor/mentor",
                      $this->html_valoracio(
                          $activitat, $valoracio, 'tutor')),
            ),
            array(
                array(0, "Observacions del professor de l'IOC",
                      $this->html_valoracio(
                          $activitat, $valoracio, 'professor')),
            ),
            array(
                array(102.5, "Grau d'assoliment (tutor/mentor)",
                      $this->html_avaluacions($valoracio, 'tutor')),
                array(0, "Grau d'assoliment (professor de l'IOC)",
                      $this->html_avaluacions($valoracio, 'professor')),
            ),
        ));
    }

    private function write_activitats($num) {
        $this->write_heading(2, "Activitats");

        $activitats = $this->controller->alumne->activitats($num);

        if ($activitats) {
            foreach ($activitats as $a) {
                $this->write_activitat($a);
            }
        } else {
            $this->write($this->html_buit(), 10);
        }
    }

    private function write_dades_generals() {
        $this->pdf->AddPage();

        $this->write_heading(1, "Dades generals");

        $alumne = $this->controller->alumne;

        $this->write_heading(
            2, "Alumne", $this->html_validat($alumne->alumne_validat));

        $this->write_form(array(
            array(
                array(70, 'Nom i cognoms',
                      s(fullname($alumne->alumne()))),
                array(30, 'DNI', s($alumne->alumne_dni)),
                array(0, 'Especialitat docent',
                      $this->controller->output->nom_element_llista(
                          'especialitats_docents',
                          $alumne->alumne_especialitat)),
            ), array(
                array(70, 'Adreça', s($alumne->alumne_adreca)),
                array(30, 'Codi postal', s($alumne->alumne_codi_postal)),
                array(0, 'Població', s($alumne->alumne_poblacio)),
            ), array(
                array(40, 'Telèfon', s($alumne->alumne_telefon)),
                array(0, 'Títol equivalent',
                      $this->controller->output->nom_element_llista(
                          'titols_equivalents', $alumne->alumne_titol)),
            ),
        ));

        $this->write_heading(2, "Centre d'estudis");

        $this->write_form(array(
            array(
                array(60, 'Nom',
                      s($this->controller->quadern->nom_centre_estudis)),
                array(30, 'Codi de centre',
                      s($this->controller->quadern->codi_centre_estudis)),
                array(0, 'Adreça',
                      s($this->controller->quadern->adreca_centre_estudis)),
            ),
        ));

        $this->write_heading(2, "Professor de l'IOC");
        $this->write_form(array(
            array(
                array(0, '', s(fullname($alumne->professor()))),
            ),
        ));

        $this->write_heading(
            2, "Centre de pràctiques",
            $this->html_validat($alumne->centre_validat));

        $this->write_form(array(
            array(
                array(90, 'Nom', s($alumne->centre_nom)),
                array(45, 'Codi del centre', s($alumne->centre_codi)),
                array(0, 'Tipus de centre',
                      $this->controller->output->nom_element_llista(
                          'tipus_centre', $alumne->centre_tipus)),
            ), array(
                array(90, 'Nom del director',
                      s($alumne->centre_director)),
                array(0, 'Nom del coordinador de pràctiques',
                      s($alumne->centre_coordinador)),
            )
        ));

        $this->write_heading(
            2, "Tutor del centre de pràctiques",
            $this->html_validat($alumne->tutor_validat));

        $this->write_form(array(
            array(
                array(90, 'Nom i cognoms',
                      s(fullname($alumne->tutor()))),
                array(0, 'Telèfon de contacte',
                      s($alumne->tutor_telefon)),
            ), array(
                array(90, 'Horari de contacte',
                      s($alumne->tutor_horari)),
                array(0, 'Especialitat docent',
                      s($alumne->tutor_especialitat)),
            ), array(
                array(0, 'Cicles que imparteix',
                      s($alumne->tutor_cicles)),
            ), array(
                array(0, 'Crèdits/mòduls que imparteix',
                      s($alumne->tutor_credits)),
            ),
        ));
    }

    private function write_fase($num) {
        $fase = $this->controller->alumne->fase($num);

        $this->pdf->AddPage();

        $this->write_heading(1, "Fase $num");

        $this->write_heading(2, "Calendari");
        $this->write_planificacio($fase);
        $this->write_seguiment($num);

        $this->write_activitats($num);
    }

    private function write_form(array $fields) {
        for ($i = 0; $i < count($fields); $i++) {
            $row = array();
            foreach ($fields[$i] as $field) {
                list($width, $label, $content) = $field;
                if (!$content) {
                    $content = $this->html_buit();
                }
                if ($label) {
                    $content = '<div><b>' . s($label) . '</b></div>' . $content;
                }
                $row[] = array($width, $content);
            }
            $bottom_margin = ($i < count($fields) - 1 ? 5 : 10);
            $this->write_row($row, 5, $bottom_margin);
        }
    }

    private function write_heading($level, $text, $extrahtml='') {
        assert($level >= 1 and $level <= 3);
        switch ($level) {
        case 1: $size = 18; $margin = 8; break;
        case 2: $size = 15; $margin = 6; break;
        case 3: $size = 12; $margin = 4; break;
        }
        $this->pdf->Bookmark($text, $level - 1);
        $html = ('<span style="font-weight:bold; font-size:' . $size . '">' .
                 s($text) . '</span>' . ($extrahtml ? " $extrahtml" : ''));
        $this->write($html, $margin);
    }

    private function write_index() {
        $this->pdf->addTOCPage();
        $this->pdf->SetFont('FreeSans', 'B', 15);
        $this->pdf->MultiCell(0, 0, 'Índex');
        $this->pdf->Ln();
        $this->pdf->SetFont('FreeSans', '', 10);
        $this->pdf->addTOC(2);
        $this->pdf->endTOCPage();
    }

    private function write_planificacio($fase) {
        $num = $fase->fase;

        $this->write_heading(
            3, "Planificació",
            $this->html_validat($fase->calendari_validat));

        $durada = $this->controller->quadern->{"durada_fase_$num"};

        $this->write_form(array(
            array(
                array(50, "Durada",
                      s($durada . ' hores')),
                array(50, "Data d'inici",
                      $this->controller->output->data($fase->data_inici)),
                array(0, "Data de finalització",
                      $this->controller->output->data($fase->data_final)),
            ),
            array(
                array(0, "Observacions", s($fase->observacions_calendari)),
            ),
        ));
    }

    private function write_portada() {
        global $DB;

        $course = $DB->get_record(
            'course', array('id' => $this->controller->cm->course));

        $this->pdf->addPage();
        $this->pdf->SetFont('FreeSans', 'B', 20);
        $this->pdf->Ln(80);
        $this->pdf->MultiCell(0, 0, $course->fullname, 0, 'C');
        $this->pdf->Ln();
        $this->pdf->MultiCell(
            0, 0, $this->controller->quadern->name, 0, 'C');
        $this->pdf->Ln();
        $this->pdf->MultiCell(
            0, 0, fullname($this->controller->alumne->alumne()), 0, 'C');
    }

    private function write_qualificacions() {
        $this->pdf->AddPage();

        $this->write_heading(1, "Qualificacions finals");

        $row = array();
        foreach (range(1, N_FASES) as $num) {
            $fase = $this->controller->alumne->fase($num);
            $row[] = array(60, "Fase $num",
                           $this->html_qualificacio($fase->qualificacio));
        }

        $this->write_form(array(
            $row,
            array(
                array(0, "Qualificació final de les pràctiques",
                      $this->html_qualificacio(
                          $this->controller->alumne->qualificacio)),
            ),
        ));
    }

    private function write_row(array $cells, $spacing, $bottom_margin) {
        $this->pdf->StartTransaction();

        $page = $this->pdf->GetPage();
        $x = $this->pdf->GetX();
        $y = $this->pdf->GetY();
        $next_page = $page;
        $next_y = $y;

        for ($i = 0; $i < count($cells); $i++) {
            list($width, $content) = $cells[$i];
            $this->pdf->SetPage($page);
            $this->pdf->SetY($y);
            $this->pdf->SetX($x);
            $this->pdf->WriteHTMLCell($width, 0, $x, $y, $content, 0, 1);
            $x += $width + $spacing;
            if ($this->pdf->GetPage() > $next_page) {
                $next_page = $this->pdf->GetPage();
                $next_y = $this->pdf->GetY();
            } else if ($this->pdf->GetY() > $next_y) {
                $next_y = max($next_y, $this->pdf->GetY());
            }
        }

        $dim = $this->pdf->GetPageDimensions();
        $max_y = $dim['hk'] - $dim['bm'];
        if ($this->pdf->GetPage() > $page and $y > $max_y - 10) {
            $this->pdf->RollbackTransaction(true);
            $this->pdf->AddPage();
            $this->write_row($cells, $spacing, $bottom_margin);
            return;
        }

        $this->pdf->CommitTransaction();
        $this->pdf->setPage($next_page);
        $this->pdf->setY($next_y);
        $this->pdf->Ln($bottom_margin);
    }

    private function write_seguiment($num) {
        $this->write_heading(3, "Seguiment");

        $dies = $this->controller->alumne->dies_seguiment($num);

        if ($dies) {
            $columns = array(
                array(40, 'Data'),
                array(30, 'Hores'),
                array(15,  'Validat'),
            );
            $data = array();
            foreach ($dies as $dia) {
                $data[] = array(
                    $this->controller->output->data($dia->data),
                    $this->controller->output->hores_dia_seguiment($dia),
                    $this->html_validat($dia->validat),
                );
            }
            $this->write_table($columns, $data, 2, 10);
        } else {
            $this->write($this->html_buit(), 10);
        }
    }

    private function write_table($columns, $data, $spacing, $bottom_margin) {
        $row = array();
        foreach ($columns as $column) {
            $row[] = array($column[0], '<b>' . s($column[1]) . '</b>');
        }
        $this->write_row($row, $spacing, $spacing);
        for ($i = 0; $i < count($data); $i++) {
            $row = array();
            for ($j = 0; $j < count($data[$i]); $j++) {
                $row[] = array($columns[$j][0], $data[$i][$j]);
            }
            $margin = ($i < count($data) - 1 ? $spacing : $bottom_margin);
            $this->write_row($row, $spacing, $margin);
        }
    }

}

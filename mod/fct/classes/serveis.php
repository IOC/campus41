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
 * FCT export class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class fct_serveis {

    public $diposit;


    public function __construct($diposit) {
        $this->diposit = $diposit;
    }

    public function crear_quadern($alumne, $cicle) {
        $quadern = new fct_quadern;
        $quadern->alumne = $alumne;
        $quadern->cicle = $cicle;
        $quadern->afegir_conveni(new fct_conveni);

        $ultim_quadern = $this->ultim_quadern($alumne, $cicle);
        if ($ultim_quadern) {
            $quadern->dades_alumne = clone($ultim_quadern->dades_alumne);
            $quadern->hores_credit = $ultim_quadern->hores_credit;
            $quadern->exempcio = $ultim_quadern->exempcio;
            $quadern->hores_anteriors = $ultim_quadern->hores_anteriors;
            $quadern->qualificacio_global =
                clone($ultim_quadern->qualificacio_global);
        }

        return $quadern;
    }

    public function data_prevista_valoracio_parcial($quadern) {
        $conveni = $quadern->ultim_conveni();
        $inici = DateTime::createFromFormat('U', $conveni->data_inici);
        $final = DateTime::createFromFormat('U', $conveni->data_final);
        $dies = (int) ($inici->diff($final)->format('%a') / 2);
        $interval = new DateInterval("P{$dies}D");
        return $inici->add($interval)->getTimestamp();
    }

    public function hores_realitzades_quadern($quadern) {
        $hores = 0;
        $quinzenes = $this->diposit->quinzenes($quadern->id);
        foreach ($quinzenes as $quinzena) {
            $hores += $quinzena->hores;
        }
        return $hores;
    }

    public function maxim_hores_quinzena($quadern, $any, $periode, $dies) {
        $hores = 0.0;
        foreach ($dies as $dia) {
            $data = new fct_data($dia, floor($periode / 2) + 1, $any);
            if ($conveni = $quadern->conveni_data($data)) {
                $hores += $conveni->hores_dia($data->dia_setmana());
            }
        }
        return $hores;
    }

    public function registrar_avis($quadern, $tipus, $quinzena=false) {
        $avisos = $this->diposit->avisos_quadern($quadern->id);
        foreach ($avisos as $avis) {
            if ($avis->tipus == $tipus and $avis->quinzena == $quinzena) {
                $avis->data = $this->moodle->time();
                $this->diposit->afegir_avis($avis);
                return;
            }
        }
        $avis = new fct_avis;
        $avis->quadern = $quadern->id;
        $avis->data = $this->moodle->time();
        $avis->tipus = $tipus;
        $avis->quinzena = $quinzena;
        $this->diposit->afegir_avis($avis);
    }

    public function resum_hores_fct($quadern) {
        $hores_practiques = 0;

        $especificacio = new fct_especificacio_quaderns;
        $especificacio->cicle = $quadern->cicle;
        $especificacio->alumne = $quadern->alumne;
        $especificacio->data_final_max = $quadern->data_final();

        $quaderns = $this->diposit->quaderns($especificacio);
        foreach ($quaderns as $q) {
            if ($q->qualificacio->apte != 2) {
                $hores_practiques += $this->hores_realitzades_quadern($q);
            }
        }

        return new fct_resum_hores_fct($quadern->hores_credit,
                                       $quadern->hores_anteriors,
                                       $quadern->exempcio,
                                       $hores_practiques);
    }

    public function suprimir_fct($fct) {
        $especificacio = new fct_especificacio_quaderns;
        $especificacio->fct = $fct->id;
        $quaderns = $this->diposit->quaderns($especificacio);
        foreach ($quaderns as $quadern) {
            $this->suprimir_quadern($quadern);
        }
        $cicles = $this->diposit->cicles($fct->id);
        foreach ($cicles as $cicle) {
            $this->diposit->suprimir_cicle($cicle);
        }

        $this->diposit->suprimir_fct($fct);
    }

    public function suprimir_quadern($quadern) {
        global $DB;

        $avisos = $this->diposit->avisos_quadern($quadern->id);
        foreach ($avisos as $avis) {
            $this->diposit->suprimir_avis($avis);
        }

        $quinzenes = $this->diposit->quinzenes($quadern->id);
        foreach ($quinzenes as $quinzena) {
            $this->diposit->suprimir_quinzena($quinzena);
        }

        $activitats = $this->diposit->activitats($quadern->id);
        foreach ($activitats as $activitat) {
            $this->diposit->suprimir_activitat($activitat);
        }

        $cicle = $this->diposit->cicle($quadern->cicle);
        // $fct = $this->diposit->fct($cicle->fct);
        // $this->moodle->delete_dir($fct->course, "quadern-{$quadern->id}");

        $this->diposit->suprimir_quadern($quadern);
    }

    public function suprimir_quinzena($quinzena) {
        $avisos = $this->diposit->avisos_quadern($quinzena->quadern);
        foreach ($avisos as $avis) {
            if ($avis->quinzena == $quinzena->id) {
                $this->diposit->suprimir_avis($avis);
            }
        }
        $this->diposit->suprimir_quinzena($quinzena);
    }

    public function ultim_quadern($alumne, $cicle) {
        $especificacio = new fct_especificacio_quaderns;
        $especificacio->alumne = $alumne;
        $especificacio->cicle = $cicle;

        $quaderns = $this->diposit->quaderns($especificacio, 'data_final');

        return array_pop($quaderns);
    }
}

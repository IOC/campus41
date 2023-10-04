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
 * Franja horari FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class fct_franja_horari {
    public $dia;
    public $hora_inici;
    public $hora_final;

    public function __construct($record) {
        $this->dia = $record->dia;
        $this->hora_inici = $record->hora_inici;
        $this->hora_final = $record->hora_final;
    }

    public static function cmp($a, $b) {
        $ordre_dia = array(
            'dilluns',
            'dimarts',
            'dimecres',
            'dijous',
            'divendres',
            'dissabte',
            'diumenge',
        );
        $cmp_dia = (array_search($a->dia, $ordre_dia) -
                    array_search($b->dia, $ordre_dia));
        if ($cmp_dia != 0) {
            return $cmp_dia;
        }
        if ($a->hora_inici != $b->hora_inici) {
            return $a->hora_inici - $b->hora_inici;
        }
        return $a->hora_final - $b->hora_final;
    }

    public function text_hora_final() {
        return self::text_hora($this->hora_final);
    }

    public function text_hora_inici() {
        return self::text_hora($this->hora_inici);
    }

    public static function text_hora($hora) {
        $minuts = round(($hora - floor($hora)) * 60);
        return sprintf("%02d:%02d", floor($hora), $minuts);
    }

    public function hores() {
        return ($this->hora_inici <= $this->hora_final ?
                $this->hora_final - $this->hora_inici :
                $this->hora_final - $this->hora_inici + 24);
    }
}

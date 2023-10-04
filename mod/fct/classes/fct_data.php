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
 * Data FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class fct_data {

    public $dia;
    public $mes;
    public $any;

    public function __construct($dia, $mes, $any) {
        $this->dia = $dia;
        $this->mes = $mes;
        $this->any = $any;
    }

    public function anterior_a($data) {
        if ($this->any != $data->any) {
            return $this->any < $data->any;
        }
        if ($this->mes != $data->mes) {
            return $this->mes < $data->mes;
        }
        return $this->dia < $data->dia;
    }

    public function dia_setmana() {
        $dow = array('diumenge', 'dilluns', 'dimarts', 'dimecres', 'dijous',
                     'divendres', 'dissabte');
        $jd = cal_to_jd(CAL_GREGORIAN, $this->mes, $this->dia, $this->any);
        return $jd > 0 ? $dow[jddayofweek($jd)] : false;
    }

    public function en_periode($inici, $final) {
        return ($this->igual_a($inici) or $this->igual_a($final) or
                ($this->posterior_a($inici)) and $this->anterior_a($final));
    }

    public function igual_a($data) {
        return ($this->any == $data->any and
                $this->mes == $data->mes and
                $this->dia == $data->dia);
    }

    public function posterior_a($data) {
        return $data->anterior_a($this) and !$data->igual_a($this);
    }

    public function valida() {
        $jd = cal_to_jd(CAL_GREGORIAN, $this->mes, $this->dia, $this->any);
        return ($jd > 0 and $this->dia <=
                cal_days_in_month(CAL_GREGORIAN, $this->mes, $this->any));
    }

    public static function time($time) {
        $date = getdate($time);
        return new fct_data($date['mday'], $date['mon'], $date['year']);
    }

    public static function final_periode($any, $periode) {
        $mes = floor($periode / 2) + 1;
        $dies_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $any);
        return new fct_data($periode % 2 == 0 ? 15 : $dies_mes, $mes, $any);
    }

    public static function inici_periode($any, $periode) {
        $mes = floor($periode / 2) + 1;
        return new fct_data($periode % 2 == 0 ? 1 : 16, $mes, $any);
    }
}
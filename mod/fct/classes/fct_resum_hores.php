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
 * Resum hores FCT class
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 Institut Obert de Catalunya
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class fct_resum_hores_fct {
    public $credit;
    public $exempcio;
    public $anteriors;
    public $practiques;

    public $realitzades;
    public $pendents;


    public function __construct($hores_credit, $hores_anteriors,
                         $exempcio, $hores_practiques) {
        $this->credit = $hores_credit;
        $this->anteriors = $hores_anteriors;
        $this->practiques = $hores_practiques;
        $this->exempcio = ceil((float) $exempcio / 100 * $hores_credit);

        $this->realitzades = $this->anteriors + $this->exempcio + $this->practiques;
        $this->pendents = max(0, $this->credit - $this->realitzades);
    }
}
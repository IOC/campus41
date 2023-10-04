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
 * Renderer for use with the fct mod output
 *
 * @package    mod
 * @subpackage fct
 * @copyright  2014 IOC (Institut Obert de Catalunya)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('classes/render/render_cicles.php');
require_once('classes/render/render_frases.php');
require_once('classes/render/render_centre.php');
require_once('classes/render/render_quaderns.php');
require_once('classes/render/render_quadern_main.php');
require_once('classes/render/render_quadern_alumne.php');
require_once('classes/render/render_quadern_empresa.php');
require_once('classes/render/render_quadern_convenis.php');
require_once('classes/render/render_quadern_horari.php');
require_once('classes/render/render_quadern_dades_relatives.php');
require_once('classes/render/render_quadern_quinzena.php');
require_once('classes/render/render_quadern_valoracio.php');
require_once('classes/render/render_quadern_valoracio_activitat.php');
require_once('classes/render/render_quadern_qualificacio.php');
require_once('classes/render/render_quadern_activitat.php');
require_once('classes/render/render_resum_seguiment.php');
require_once('classes/render/render_avisos.php');

class mod_fct_renderer extends plugin_renderer_base {

    public function print_tabs($tab) {
        $output = '';

        $output .= html_writer::start_div('fct_tabs');
        $output .= $this->tabtree($tab['row'], $tab['currentab'], $tab['inactivetabs']);
        $output .= html_writer::end_div();

        echo $output;
    }
}
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

defined('MOODLE_INTERNAL') || die;

use theme_cre\mod_hvp;

class theme_cre_mod_hvp_renderer extends \mod_hvp_renderer {

    static $character = '\x{0378}'; //͸

    /**
     * Add styles when an H5P is displayed.
     *
     * @param array $styles Styles that will be applied.
     * @param array $libraries Libraries that wil be shown.
     * @param string $embedType How the H5P is displayed.
     */
    public function hvp_alter_styles(&$styles, $libraries, $embedType) {
        global $CFG;

        if (
            isset($libraries['H5P.MultiChoice']) &&
            $libraries['H5P.MultiChoice']['majorVersion'] == '1'
        ) {
            $styles[] = (object) array(
                'path'    => $CFG->httpswwwroot . '/theme/cre/style/custom.css',
                'version' => '?ver=0.0.1',
            );
        }
    }

   /**
     * Add scripts when an H5P is displayed.
     *
     * @param object $scripts Scripts that will be applied.
     * @param array $libraries Libraries that will be displayed.
     * @param string $embedType How the H5P is displayed.
     */
    public function hvp_alter_scripts(&$scripts, $libraries, $embedType) {
        global $CFG;
        if (
            isset($libraries['H5P.MultiChoice']) &&
            $libraries['H5P.MultiChoice']['majorVersion'] == '1'
        ) {
            if ($embedType != 'editor') {
                $scripts[] = (object) array(
                    'path'    => $CFG->httpswwwroot . '/theme/cre/js/hvp.js',
                    'version' => '?ver=0.0.1',
                );
            }
        }
    }

    /**
     * Alter an H5Ps parameters.
     *
     * May be used to alter the content itself or the behaviour of an H5
     *
     * @param object $parameters Parameters of library as json object
     * @param string $name Name of library
     * @param int $majorVersion Major version of library
     * @param int $minorVersion Minor version of library
     */
    public function hvp_alter_filtered_parameters(&$parameters, $name, $majorVersion, $minorVersion) {
        if (
            $name === 'H5P.MultiChoice' &&
            $majorVersion == 1
        ) {
            if (preg_match('/' . self::$character . '/u', $parameters->question)) {
                $parameters->question = preg_replace('/' . self::$character . '/u', '', $parameters->question, 1);
                $parameters->question = '<span class="h5p-plugin-customized"></span>' . $parameters->question;
            }
        }
    }
}
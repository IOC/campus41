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
 * BoostIOC theme.
 *
 * @package    theme_boostioc
 * @copyright  2023 Roger Segú
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This line protects the file from being accessed by a URL directly.

defined('MOODLE_INTERNAL') || die();

// A description shown in the admin theme selector.

$string['choosereadme'] = 'Boostioc és un tema fill de Boost dissenyat per adaptar-se a les necesssitats pedagògiques de l\'Insitut Obert de Catalunya (IOC), seguint les pautes del sistema de disseny del centre.';

// The name of our plugin.

$string['pluginname'] = 'Boostioc';

// We need to include a lang string for each block region.

$string['region-side-pre'] = 'Dreta';

// The name of the second tab in the theme settings.

$string['advancedsettings'] = 'Configuració avançada';

// The brand colour setting.

$string['brandcolor'] = 'Color corporatiu';

// The brand colour setting description.

$string['brandcolor_desc'] = 'Color d\'accent';

// Name of the settings pages.

$string['configtitle'] = 'Configuració de la pàgina';

// Name of the first settings tab.

$string['generalsettings'] = 'Configuració general';

// Preset files setting.

$string['presetfiles'] = 'Arxius de preconfiguració addicionals del tema';

// Preset files help text.

$string['presetfiles_desc'] = 'Els arxius de preconfiguració poden alterar substancialment l\'aparença del tema. Vegeu <a href=https://docs.moodle.org/dev/Boost_Presets>Boost presets</a> per obtenir més informació sobre com crear i compratir els vostres propis arxius de preconfiguració, i vegeu també <a href=http://moodle.net/boost>Presets repository</a> per consultar i obtenir arxius de preconfiguració compartits per d\'altres usuaris';

// Preset setting.

$string['preset'] = 'Preconfiguració del tema';

// Preset help text.

$string['preset_desc'] = 'Trieu una preconfiguració per modificar substancialment l\'aspecte del tema.';

// Raw SCSS setting.

$string['rawscss'] = 'SCSS afegit';

// Raw SCSS setting help text.

$string['rawscss_desc'] = 'Feu servir aquest camp per afegir codi CSS i SCSS que serà injectat al final del full d\'estls.';

// Raw initial SCSS setting.

$string['rawscsspre'] = 'SCSS inicial afegit';

// Raw initial SCSS setting help text.

$string['rawscsspre_desc'] = 'Mitjançant aquest camp podeu proveir codi SCSS inicial, que serà injectat abans de qualsevol altre. Normalment es fa servir aquesta opció per definir variables.';

// We need to include a lang string for each block region.

$string['region-side-pre'] = 'Dreta';

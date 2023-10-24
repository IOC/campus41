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

// We will add callbacks here as we add features to our theme.

function theme_boostioc_get_main_scss_content($theme)
{
  global $CFG;

  $scss = '';
  $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
  $fs = get_file_storage();

  $context = context_system::instance();
  if ($filename == 'default.scss') {
    // We still load the default preset files directly from the boost theme. No sense in duplicating them.                      
    $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
  } else if ($filename == 'plain.scss') {
    // We still load the default preset files directly from the boost theme. No sense in duplicating them.                      
    $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
  } else if ($filename && ($presetfile = $fs->get_file($context->id, 'boostioc', 'preset', 0, '/', $filename))) {
    // This preset file was fetched from the file area for theme_boostioc and not theme_boost (see the line above).                
    $scss .= $presetfile->get_content();
  } else {
    // Safety fallback - maybe new installs etc.          
    $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
  }

  // Components styles
  $components = file_get_contents($CFG->dirroot . '/theme/boostioc/scss/components.scss');
  // Templates EOI styles
  $templates_eoi = file_get_contents($CFG->dirroot . '/theme/boostioc/scss/plantilles-components-eoi.scss');
  // Templates FP styles
  $templates_fp = file_get_contents($CFG->dirroot . '/theme/boostioc/scss/plantilles-components-fp.scss');
  // Templates GES styles
  $templates_ges = file_get_contents($CFG->dirroot . '/theme/boostioc/scss/plantilles-ges.scss');
  // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
  $pre = file_get_contents($CFG->dirroot . '/theme/boostioc/scss/pre.scss');
  // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
  $post = file_get_contents($CFG->dirroot . '/theme/boostioc/scss/post.scss');

  // Combine them together.                      
  return $pre . "\n" . $scss . "\n" . $components . "\n" . $templates_eoi . "\n" . $templates_fp . "\n" . $templates_ges . "\n" . $post;
}

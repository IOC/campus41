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
 * Settings maker.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\setting;
defined('MOODLE_INTERNAL') || die();

use ArrayIterator;
use admin_setting_configcheckbox;
use admin_setting_configselect;
use admin_setting_configtext;
use admin_setting_heading;
use block_xp\local\setting\environment;
use local_xp\local\config\default_course_world_config;

/**
 * Settings maker.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_maker extends \block_xp\local\setting\default_settings_maker {

    /**
     * Get the settings.
     *
     * @param environment $env The environment for creating the settings.
     * @return part_of_admin_tree|null
     */
    public function get_settings(environment $env) {
        $settings = parent::get_settings($env);

        // Remove the promo page.
        $settings->prune('block_xp_promo');

        return $settings;
    }

    /**
     * Get the general settings.
     *
     * @return admin_setting[]
     */
    protected function get_general_settings() {
        $parentsettings = parent::get_general_settings();
        $settings = [];

        foreach ($parentsettings as $setting) {
            // Replace keeplogs to contain other options.
            if ($setting->name == 'keeplogs') {
                $setting = (new admin_setting_configselect('block_xp/keeplogs',
                    get_string('keeplogs', 'block_xp'),
                    get_string('keeplogsdesc', 'local_xp'),
                    $this->defaults->get('keeplogs'), [
                        '0' => get_string('forever', 'block_xp'),
                        '14' => get_string('for2weeks', 'local_xp'),
                        '30' => get_string('for1month', 'block_xp'),
                        '90' => get_string('for3months', 'local_xp'),
                    ]
                ));
            }
            $settings[] = $setting;
        }

        return $settings;
    }

    /**
     * Get the default settings.
     *
     * @return admin_setting[]
     */
    protected function get_default_settings() {
        $parentsettings = parent::get_default_settings();
        $settings = [];
        $defaults = $this->defaults->get_all();

        // We loop over each setting to inject what more when we need.
        foreach ($parentsettings as $setting) {

            if ($setting->name == 'hdrcheatguard') {

                // Progress bar settings.
                $settings[] = (new admin_setting_heading('local_xp/hdrprogressbar', get_string('progressbar', 'block_xp'), ''));

                // Progress bar mode.
                $settings[] = (new admin_setting_configselect('local_xp/progressbarmode',
                    get_string('progressbarmode', 'local_xp'), get_string('progressbarmode_help', 'local_xp'),
                    $defaults['progressbarmode'], [
                        default_course_world_config::PROGRESS_BAR_MODE_LEVEL => get_string('progressbarmodelevel', 'local_xp'),
                        default_course_world_config::PROGRESS_BAR_MODE_OVERALL => get_string('progressbarmodeoverall', 'local_xp'),
                    ]
                ));
            }

            $settings[] = $setting;

            if ($setting->name == 'laddercols') {

                // Group ladder settings.
                $settings[] = (new admin_setting_heading('local_xp/hdrgroupladder', get_string('groupladder', 'local_xp'), ''));

                // Enable the group ladder?
                $settings[] = (new admin_setting_configcheckbox('local_xp/enablegroupladder',
                    get_string('enablegroupladder', 'local_xp'), get_string('enablegroupladder_help', 'local_xp'),
                    $defaults['enablegroupladder']));
            }

            if ($setting->name == 'timebetweensameactions') {

                // Max actions per time.
                $settings[] = (new admin_setting_configtext('local_xp/maxpointspertime',
                    get_string('maxpointspertime', 'local_xp'), get_string('maxpointspertime_help', 'local_xp'),
                    $defaults['maxpointspertime'], PARAM_INT));

                // Time for max actions.
                $settings[] = (new admin_setting_configtext('local_xp/timeformaxpoints',
                    get_string('timeformaxpoints', 'local_xp'), get_string('timeformaxpoints_help', 'local_xp'),
                    $defaults['timeformaxpoints'], PARAM_INT));

            }

        }

        return $settings;
    }

}

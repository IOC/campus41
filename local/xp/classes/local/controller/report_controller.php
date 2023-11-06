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
 * Report controller.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\controller;
defined('MOODLE_INTERNAL') || die();

/**
 * Report controller class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_controller extends \block_xp\local\controller\report_controller {

    protected function define_optional_params() {
        $params = parent::define_optional_params();
        $params[] = ['download', '', PARAM_ALPHA, false];
        return $params;
    }

    protected function get_table() {
        if (!$this->table) {
            $this->table = new \local_xp\output\report_table(
                \block_xp\di::get('db'),
                $this->world,
                $this->get_renderer(),
                $this->world->get_store(),
                $this->get_groupid(),
                $this->get_param('download')
            );
            // We must use a compatible URL for the download button to work.
            $this->table->define_baseurl($this->pageurl->get_compatible_url());
        }
        return $this->table;
    }

    protected function pre_content() {
        // We must send the table before the output starts.
        $table = $this->get_table();
        if ($table->is_downloading()) {
            $table->send_file();
        }

        parent::pre_content();
    }

}

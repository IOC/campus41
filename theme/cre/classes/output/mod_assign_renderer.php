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

use theme_cre\mod_assign;

class theme_cre_mod_assign_renderer extends \mod_assign_renderer {

    /**
     * Render a table containing the current status of the submission.
     *
     * @param assign_submission_status $status
     * @return string
     */
    public function render_assign_submission_status(assign_submission_status $status) {
        $showstatus = false;
        foreach ($status->submissionplugins as $plugin) {
            $showstatus = $showstatus || $plugin->is_enabled();
        }
        if (!$showstatus) {
            return false;
        }
        return parent::render_assign_submission_status($status);
    }
}
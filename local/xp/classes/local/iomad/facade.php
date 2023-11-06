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
 * Iomad facade.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\iomad;

defined('MOODLE_INTERNAL') || die();

use company;
use context_system;
use iomad;

/**
 * Iomad facade class.
 *
 * @package    local_xp
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class facade {

    /** @var bool Whether we initialised the facade. */
    protected $initialised = false;

    /**
     * Init the things.
     */
    public function init() {
        if (!$this->initialised) {
            $this->load_libraires();
            $this->initialised = true;
        }
    }

    /**
     * Return whether IOMAD exists.
     *
     * @return bool
     */
    public function exists() {
        $this->init();
        return class_exists('iomad');
    }

    /**
     * Get a company's name.
     *
     * @param int $id The ID.
     * @return string
     */
    public function get_company_name($id) {
        $this->init();
        $company = new company($id);
        return $company->get_name();
    }

    /**
     * Get a departments's name.
     *
     * @param int $id The ID.
     * @return string
     */
    public function get_department_name($id) {
        $this->init();
        $dept = company::get_departmentbyid($id);
        return $dept->name;
    }

    /**
     * Get the company ID being viewed.
     *
     * @return int
     */
    public function get_viewing_companyid() {
        $this->init();
        return iomad::get_my_companyid(context_system::instance(), false);
    }

    /**
     * Get the department ID being viewed.
     *
     * @return int
     */
    public function get_viewing_departmentid() {
        global $USER;
        $this->init();
        $company = new company($this->get_viewing_companyid());
        $department = $company->get_userlevel($USER);
        return $department->id;
    }

    /**
     * Attempt to load libraries.
     *
     * @return void
     */
    protected function load_libraires() {
        global $CFG, $DB, $SESSION, $USER;
        if (!class_exists('iomad') && file_exists($CFG->dirroot . '/local/iomad/lib/iomad.php')) {
            require_once($CFG->dirroot . '/local/iomad/lib/iomad.php');
            require_once($CFG->dirroot . '/local/iomad/lib/company.php');
        }
    }

    /**
     * Redirect to pick a company if needed.
     *
     * @return void
     */
    public function redirect_for_company_if_needed() {
        $this->init();
        iomad::get_my_companyid(context_system::instance(), true);
    }

}

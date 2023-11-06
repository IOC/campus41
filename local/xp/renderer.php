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
 * Local renderer.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/xp/renderer.php');

use local_xp\local\factory\course_currency_factory;
use local_xp\local\currency\currency;

/**
 * Local renderer class.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_xp_renderer extends block_xp_renderer {

    protected $currencyfactory;
    protected $currencyrendercache;

    public function currency(currency $currency) {
        $sign = $currency->get_sign();
        $classes = 'sign';

        if ($signurl = $currency->get_sign_url()) {
            $classes .= ' sign-img';
            $sign = html_writer::empty_tag('img', ['src' => $signurl, 'alt' => '']);

        } else if ($currency->use_sign_as_superscript()) {
            $classes .= ' sign-sup';
        }

        $o = '';
        $o .= html_writer::div($sign, $classes);
        return $o;
    }

    /**
     * Get the currency factory.
     *
     * We can't inject the factory here or we have circular dependency issues.
     *
     * @return course_currency_factory
     */
    protected function get_course_currency_factory() {
        if (!$this->currencyfactory) {
            $this->currencyfactory = \block_xp\di::get('course_currency_factory');
        }
        return $this->currencyfactory;
    }

    /**
     * Return the group picture.
     *
     * @param stdClass $group The group.
     * @return string
     */
    public function group_picture($group) {
        $pic = get_group_picture_url($group, $group->courseid);
        if (empty($pic)) {
            return;
        }
        return html_writer::empty_tag('img', [
            'src' => $pic->out(false),
            'class' => 'grouppic',
            'alt' => format_string($group->name, true, [
                'context' => context_course::instance($group->courseid)
            ])
        ]);
    }

    public function xp($points, currency $currency = null) {
        if (!$currency) {
            $courseid = $this->page->course->id;
            $currency = $this->get_course_currency_factory()->get_currency($courseid);
        }
        $o = '';
        $o .= html_writer::start_div('block_xp-xp');
        $o .= html_writer::div($this->xp_amount($points), 'pts');
        $o .= $this->currency($currency);
        $o .= html_writer::end_div();
        return $o;
    }

    private function xp_amount($points) {
        $xp = (int) $points;
        if ($xp > 999) {
            $thousandssep = get_string('thousandssep', 'langconfig');
            $xp = number_format($xp, 0, '.', $thousandssep);
        }
        return $xp;
    }

    public function xp_preview($points, $courseid = null) {
        if (!$courseid) {
            $currency = \block_xp\di::get('currency');
        } else {
            $currency = $this->get_course_currency_factory()->get_currency($courseid);
        }
        return $this->xp($points, $currency);
    }

}

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
 * Event reason.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\reason;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\reason\reason;

/**
 * Event reason.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_reason implements reason, reason_with_short_description {

    protected $name;
    protected $contextid;
    protected $objectid;
    protected $relateduserid;

    public function __construct($name, $contextid, $objectid, $relateduserid) {
        $this->name = $name;
        $this->contextid = $contextid;
        $this->objectid = $objectid;
        $this->relateduserid = $relateduserid;
    }

    public function get_short_description() {
        $class = $this->name;
        if (class_exists($class)) {
            return $class::get_name();
        }
        return get_string('somethinghappened', 'block_xp');
    }

    public function get_signature() {
        return $this->name . ':' . $this->contextid . ':' . $this->objectid . ':' . $this->relateduserid;
    }

    public static function get_type() {
        return __CLASS__;
    }

    public static function from_signature($signature) {
        list($name, $ctx, $obj, $relid) = explode(':', $signature);
        return new static($name, $ctx, $obj, $relid);
    }

    public static function from_event(\core\event\base $e) {
        return new static($e->eventname, $e->contextid, $e->objectid, $e->relateduserid);
    }

}

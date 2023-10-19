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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/classes/category.php');

function local_secretaria_extend_navigation($root) {
    $categories = core_course_category::make_categories_list('moodle/category:manage');
    if (!empty($categories)) {
        $node = navigation_node::create(get_string('allcategories'), new moodle_url('/'), navigation_node::TYPE_SETTING);
        $node->action->param('redirect', '0');
        $root->add_node($node, 'home');
    }
}

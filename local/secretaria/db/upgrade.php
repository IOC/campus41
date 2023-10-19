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
 * @package    local_secretaria
 * @copyright  TICxCAT 2022
 * @author     TICxCAT <info@ticxcat.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_secretaria_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2022040500) {

        // Migrate from "forum_favourite" to moodle favourite API.
        $favs = $DB->get_records_sql("SELECT * FROM {forum_favourite}", array());
        foreach ($favs as $fav) {
            $user = $DB->get_record('user', array('id' => $fav->userid, 'deleted' => 0));
            if (!$user) {
                continue;
            }
            $usercontext = \context_user::instance($fav->userid);

            // Get the discussion vault and the corresponding discussion entity.
            $vaultfactory = \mod_forum\local\container::get_vault_factory();
            $discussionvault = $vaultfactory->get_discussion_vault();
            $discussion = $discussionvault->get_from_id($fav->discussionid);

            if ($usercontext && $discussion && $discussion->get_id() > 0) {
                $forumvault = $vaultfactory->get_forum_vault();
                $forum_id = $discussion->get_forum_id();

                $forum = $DB->get_record('forum', array('id' => $forum_id));
                $cm = \get_coursemodule_from_instance('forum', $forum->id, $forum->course);

                $forumcontext = \context_module::instance($cm->id);
                $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

                $isfavourited = $ufservice->favourite_exists('mod_forum', 'discussions', $discussion->get_id(), $forumcontext);
                if (!$isfavourited) {
                    $ufservice->create_favourite('mod_forum', 'discussions', $discussion->get_id(), $forumcontext);
                    mtrace("Favourite created in discussion with ID ".$discussion->get_id()." for user with ID ".$fav->userid);
                }
            }
        }

        $dbman = $DB->get_manager();
        $table = new xmldb_table('forum_favourite');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
            mtrace("forum_favourite deleted");
        }

        upgrade_plugin_savepoint(true, 2022040500, 'local', 'secretaria');
    }

    return true;
}

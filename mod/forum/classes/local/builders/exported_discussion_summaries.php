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
 * Exported discussion summaries builder class.
 *
 * @package    mod_forum
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_forum\local\builders;

defined('MOODLE_INTERNAL') || die();

use mod_forum\local\entities\discussion as discussion_entity;
use mod_forum\local\entities\forum as forum_entity;
use mod_forum\local\entities\post as post_entity;
use mod_forum\local\factories\legacy_data_mapper as legacy_data_mapper_factory;
use mod_forum\local\factories\exporter as exporter_factory;
use mod_forum\local\factories\vault as vault_factory;
use mod_forum\local\factories\manager as manager_factory;
use rating_manager;
use renderer_base;
use stdClass;

/**
 * Exported discussion summaries builder class.
 *
 * This class is an implementation of the builder pattern (loosely). It is responsible
 * for taking a set of related forums, discussions, and posts and generate the exported
 * version of the discussion summaries.
 *
 * It encapsulates the complexity involved with exporting discussions summaries. All of the relevant
 * additional resources will be loaded by this class in order to ensure the exporting
 * process can happen.
 *
 * See this doc for more information on the builder pattern:
 * https://designpatternsphp.readthedocs.io/en/latest/Creational/Builder/README.html
 *
 * @package    mod_forum
 * @copyright  2019 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exported_discussion_summaries {
    /** @var renderer_base $renderer Core renderer */
    private $renderer;

    /** @var legacy_data_mapper_factory $legacydatamapperfactory Data mapper factory */
    private $legacydatamapperfactory;

    /** @var exporter_factory $exporterfactory Exporter factory */
    private $exporterfactory;

    /** @var vault_factory $vaultfactory Vault factory */
    private $vaultfactory;

    /** @var manager_factory $managerfactory Manager factory */
    private $managerfactory;

    /** @var rating_manager $ratingmanager Rating manager */
    private $ratingmanager;

    /**
     * Constructor.
     *
     * @param renderer_base $renderer Core renderer
     * @param legacy_data_mapper_factory $legacydatamapperfactory Legacy data mapper factory
     * @param exporter_factory $exporterfactory Exporter factory
     * @param vault_factory $vaultfactory Vault factory
     * @param manager_factory $managerfactory Manager factory
     */
    public function __construct(
        renderer_base $renderer,
        legacy_data_mapper_factory $legacydatamapperfactory,
        exporter_factory $exporterfactory,
        vault_factory $vaultfactory,
        manager_factory $managerfactory
    ) {
        $this->renderer = $renderer;
        $this->legacydatamapperfactory = $legacydatamapperfactory;
        $this->exporterfactory = $exporterfactory;
        $this->vaultfactory = $vaultfactory;
        $this->managerfactory = $managerfactory;
        $this->ratingmanager = $managerfactory->get_rating_manager();
    }

    /**
     * Build the exported discussion summaries for a given set of discussions.
     *
     * This will typically be used for a list of discussions in the same forum.
     *
     * @param stdClass $user The user to export the posts for.
     * @param forum_entity $forum The forum that each of the $discussions belong to
     * @param discussion_summary_entity[] $discussions A list of all discussion summaries to export
     * @return stdClass[] List of exported posts in the same order as the $posts array.
     */
    public function build(
        stdClass $user,
        forum_entity $forum,
        array $discussions
    ) : array {
        $capabilitymanager = $this->managerfactory->get_capability_manager($forum);
        $canseeanyprivatereply = $capabilitymanager->can_view_any_private_reply($user);

        $discussionids = array_keys($discussions);

        $postvault = $this->vaultfactory->get_post_vault();
        $posts = $postvault->get_from_discussion_ids($user, $discussionids, $canseeanyprivatereply);
        $groupsbyid = $this->get_groups_available_in_forum($forum);
        $groupsbyauthorid = $this->get_author_groups_from_posts($posts, $forum);

        $replycounts = $postvault->get_reply_count_for_discussion_ids($user, $discussionids, $canseeanyprivatereply);
        $latestposts = $postvault->get_latest_posts_for_discussion_ids($user, $discussionids, $canseeanyprivatereply);
        $latestauthors = $this->get_latest_posts_authors($latestposts);
        $latestpostsids = array_map(function($post) {
            return $post->get_id();
        }, $latestposts);

        $postauthorids = array_unique(array_reduce($discussions, function($carry, $summary) use ($latestposts){
            $firstpostauthorid = $summary->get_first_post_author()->get_id();
            $discussion = $summary->get_discussion();
            $lastpostauthorid = $latestposts[$discussion->get_id()]->get_author_id();
            return array_merge($carry, [$firstpostauthorid, $lastpostauthorid]);
        }, []));
        $postauthorcontextids = $this->get_author_context_ids($postauthorids);

        $unreadcounts = [];
        $favourites = $this->get_favourites($user);
        $forumdatamapper = $this->legacydatamapperfactory->get_forum_data_mapper();
        $forumrecord = $forumdatamapper->to_legacy_object($forum);

        if (forum_tp_can_track_forums($forumrecord)) {
            $unreadcounts = $postvault->get_unread_count_for_discussion_ids($user, $discussionids, $canseeanyprivatereply);
        }

        $summaryexporter = $this->exporterfactory->get_discussion_summaries_exporter(
            $user,
            $forum,
            $discussions,
            $groupsbyid,
            $groupsbyauthorid,
            $replycounts,
            $unreadcounts,
            $latestpostsids,
            $postauthorcontextids,
            $favourites,
            $latestauthors
        );

        $exportedposts = (array) $summaryexporter->export($this->renderer);
        $firstposts = $postvault->get_first_post_for_discussion_ids($discussionids);

        array_walk($exportedposts['summaries'], function($summary) use ($firstposts, $latestposts) {
            $summary->discussion->times['created'] = (int) $firstposts[$summary->discussion->firstpostid]->get_time_created();
            $summary->discussion->times['modified'] = (int) $latestposts[$summary->discussion->id]->get_time_created();
        });

        // @PATCH IOC043: Export whole forum using portfolio.
        global $CFG;

        $forumid = $forum->get_id();
        $context = forum_get_context($forumid);

        if (!empty($CFG->enableportfolios) && has_capability('mod/forum:exportdiscussion', $context)) {
            require_once $CFG->libdir . '/portfoliolib.php';

            $button = new \portfolio_add_button();
            $button->set_callback_options('forum_full_portfolio_caller', ['forumid' => $forumid], 'mod_forum');
            $button = $button->to_html(PORTFOLIO_ADD_FULL_FORM, get_string('exportforum', 'mod_forum'));
            $buttonextraclass = '';

            if (empty($button)) {
                // No portfolio plugin available.
                $button = '&nbsp;';
                $buttonextraclass = ' noavailable';
            }

            $exportedposts['exportbutton'] = '<div class="add-to-portfolio-button' . $buttonextraclass . '">' . $button . '</div>';
        }
        // Fi.

        // @PATCH IOC046: Show the number of attachments in each discussion.
        $getiddiscussions = static function($discussion) {
            return $discussion->id;
        };

        $discussionsids = array_map($getiddiscussions, $exportedposts['summaries']);
        $attachments = $this->forum_get_discussion_num_attachments($forumid, $context->id, $discussionsids);

        global $OUTPUT;

        foreach ($exportedposts['summaries'] as $key => $record) {
            $exportedposts['summaries'][$key]->discussion->title = $exportedposts['summaries'][$key]->discussion->name;
            if (isset($attachments[$record->id])) {
                $src = $OUTPUT->image_url('t/attachment', 'mod_forum');
                $title = get_string('overviewnumattachments', 'forum', $attachments[$record->id]->num);
                $exportedposts['summaries'][$key]->discussion->name .= "&nbsp;<img src=\"$src\" title=\"$title\" />";
            }
        }
        // Fi.

        // Pass the current, preferred sort order for the discussions list.
        $discussionlistvault = $this->vaultfactory->get_discussions_in_forum_vault();
        $sortorder = get_user_preferences('forum_discussionlistsortorder',
            $discussionlistvault::SORTORDER_LASTPOST_DESC);

        $sortoptions = array(
            'islastpostdesc' => $sortorder == $discussionlistvault::SORTORDER_LASTPOST_DESC,
            'islastpostasc' => $sortorder == $discussionlistvault::SORTORDER_LASTPOST_ASC,
            'isrepliesdesc' => $sortorder == $discussionlistvault::SORTORDER_REPLIES_DESC,
            'isrepliesasc' => $sortorder == $discussionlistvault::SORTORDER_REPLIES_ASC,
            'iscreateddesc' => $sortorder == $discussionlistvault::SORTORDER_CREATED_DESC,
            'iscreatedasc' => $sortorder == $discussionlistvault::SORTORDER_CREATED_ASC,
            'isdiscussiondesc' => $sortorder == $discussionlistvault::SORTORDER_DISCUSSION_DESC,
            'isdiscussionasc' => $sortorder == $discussionlistvault::SORTORDER_DISCUSSION_ASC,
            'isstarterdesc' => $sortorder == $discussionlistvault::SORTORDER_STARTER_DESC,
            'isstarterasc' => $sortorder == $discussionlistvault::SORTORDER_STARTER_ASC,
            'isgroupdesc' => $sortorder == $discussionlistvault::SORTORDER_GROUP_DESC,
            'isgroupasc' => $sortorder == $discussionlistvault::SORTORDER_GROUP_ASC,
        );

        $exportedposts['state']['sortorder'] = $sortoptions;

        return $exportedposts;
    }

    /**
     * Get a list of all favourited discussions.
     *
     * @param stdClass $user The user we are getting favourites for
     * @return int[] A list of favourited itemids
     */
    private function get_favourites(stdClass $user) : array {
        $ids = [];

        if (isloggedin()) {
            $usercontext = \context_user::instance($user->id);
            $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
            $favourites = $ufservice->find_favourites_by_type('mod_forum', 'discussions');
            foreach ($favourites as $favourite) {
                $ids[] = $favourite->itemid;
            }
        }

        return $ids;
    }

    /**
     * Returns a mapped array of discussionid to the authors of the latest post
     *
     * @param array $latestposts Mapped array of discussion to latest posts.
     * @return array Array of authors mapped to the discussion
     */
    private function get_latest_posts_authors($latestposts) {
        $authors = $this->vaultfactory->get_author_vault()->get_authors_for_posts($latestposts);

        $mappedauthors = array_reduce($latestposts, function($carry, $item) use ($authors) {
            $carry[$item->get_discussion_id()] = $authors[$item->get_author_id()];

            return $carry;
        }, []);
        return $mappedauthors;
    }

    /**
     * Get the groups details for all groups available to the forum.
     * @param forum_entity $forum The forum entity
     * @return stdClass[]
     */
    private function get_groups_available_in_forum($forum) : array {
        $course = $forum->get_course_record();
        $coursemodule = $forum->get_course_module_record();

        return groups_get_all_groups($course->id, 0, $coursemodule->groupingid);
    }

    /**
     * Get the author's groups for a list of posts.
     *
     * @param post_entity[] $posts The list of posts
     * @param forum_entity $forum The forum entity
     * @return array Author groups indexed by author id
     */
    private function get_author_groups_from_posts(array $posts, $forum) : array {
        $course = $forum->get_course_record();
        $coursemodule = $forum->get_course_module_record();
        $authorids = array_reduce($posts, function($carry, $post) {
            $carry[$post->get_author_id()] = true;
            return $carry;
        }, []);
        $authorgroups = groups_get_all_groups($course->id, array_keys($authorids), $coursemodule->groupingid,
                'g.*, gm.id, gm.groupid, gm.userid');

        $authorgroups = array_reduce($authorgroups, function($carry, $group) {
            // Clean up data returned from groups_get_all_groups.
            $userid = $group->userid;
            $groupid = $group->groupid;

            unset($group->userid);
            unset($group->groupid);
            $group->id = $groupid;

            if (!isset($carry[$userid])) {
                $carry[$userid] = [$group];
            } else {
                $carry[$userid][] = $group;
            }

            return $carry;
        }, []);

        foreach (array_diff(array_keys($authorids), array_keys($authorgroups)) as $authorid) {
            $authorgroups[$authorid] = [];
        }

        return $authorgroups;
    }

    /**
     * Get the user context ids for each of the authors.
     *
     * @param int[] $authorids The list of author ids to fetch context ids for.
     * @return int[] Context ids indexed by author id
     */
    private function get_author_context_ids(array $authorids) : array {
        $authorvault = $this->vaultfactory->get_author_vault();
        return $authorvault->get_context_ids_for_author_ids($authorids);
    }

    // @PATCH IOC046: Show the number of attachments in each discussion.
    /**
     * Count the number of attachments in each discussion
     *
     * @param int $forumid
     * @param int $contextid
     * @param array $discussions
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function forum_get_discussion_num_attachments(int $forumid, int $contextid, array $discussions = []): array {

        global $DB;

        if (empty($discussions)) {
            return [];
        }

        [$attachsql, $attachparams] = $DB->get_in_or_equal($discussions, SQL_PARAMS_NAMED);

        $sql = "SELECT fd.id, COUNT(f.id) as num
            FROM {forum_discussions} as fd
            JOIN {forum_posts} as fp ON fp.discussion=fd.id
            JOIN {files} as f ON f.itemid=fp.id
            WHERE fd.forum = :forum
            AND f.component = :component
            AND (f.filearea = :filearea1 OR f.filearea = :filearea2)
            AND f.contextid = :contextid
            AND f.filename != :filename
            AND fd.id $attachsql
            GROUP BY fd.id";

        $params = [
            'forum' => $forumid,
            'component' => 'mod_forum',
            'filearea1' => 'attachment',
            'filearea2' => 'post',
            'contextid' => $contextid,
            'filename' => '.',
        ];

        $params = array_merge($params, $attachparams);

        return $DB->get_records_sql($sql, $params);
    }
    // Fi.

}

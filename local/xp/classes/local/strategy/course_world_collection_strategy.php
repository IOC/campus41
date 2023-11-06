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
 * Course world collection strategy.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\strategy;
defined('MOODLE_INTERNAL') || die();

use context;
use DateTime;
use block_xp\local\config\config;
use block_xp\local\logger\course_user_event_collection_logger;
use block_xp\local\logger\reason_collection_logger;
use block_xp\local\notification\course_level_up_notification_service;
use block_xp\local\reason\reason;
use block_xp\local\strategy\event_collection_strategy;
use block_xp\local\xp\course_filter_manager;
use block_xp\local\xp\state_store_with_reason;
use local_xp\local\logger\collection_counts_indicator;
use local_xp\local\logger\reason_occurance_indicator;
use local_xp\local\reason\event_reason;
use local_xp\local\rule\event_subject;
use local_xp\local\rule\calculator;

/**
 * Course world collection strategy.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_world_collection_strategy implements event_collection_strategy {

    /** @var context The context. */
    protected $context;
    /** @var config The config. */
    protected $config;
    /** @var state_store_with_reason The store. */
    protected $store;
    /** @var calculator The calculator. */
    protected $calculator;
    /** @var course_level_up_notification_service The notification service. */
    protected $levelupnotifificationservice;
    /** @var collection_target_resolver_from_event Target resolver. */
    protected $targetresolver;

    protected $reasonoccuranceindicator;
    protected $collectioncountsindicator;
    protected $logger;
    protected $reasonmaker;

    public function __construct(
            context $context,
            config $config,
            state_store_with_reason $store,
            calculator $calculator,
            reason_collection_logger $logger,
            reason_occurance_indicator $reasonoccuranceindicator,
            collection_counts_indicator $collectioncountsindicator,
            course_level_up_notification_service $levelupnotifificationservice,
            collection_target_resolver_from_event $targetresolver
        ) {
        $this->context = $context;
        $this->config = $config;
        $this->store = $store;
        $this->calculator = $calculator;
        $this->reasonoccuranceindicator = $reasonoccuranceindicator;
        $this->collectioncountsindicator = $collectioncountsindicator;
        $this->levelupnotifificationservice = $levelupnotifificationservice;
        $this->targetresolver = $targetresolver;
        $this->logger = $logger;

        $this->reasonmaker = new \local_xp\local\reason\maker_from_event();
    }

    /**
     * Handle an event.
     *
     * @param \core\event\base $event The event.
     * @return void
     */
    public function collect_event(\core\event\base $event) {
        $userid = $this->targetresolver->get_target_from_event($event);
        if (!$userid) {
            // We shouldn't have gotten here...
            return;
        }

        // Get course config.
        $config = $this->config;
        if (!$config->get('enabled')) {
            return;
        }

        // We've got a reason here.
        $reason = $this->reasonmaker->make_from_event($event);

        // Cheatguard.
        if (!$this->can_capture($userid, $reason, $config)) {
            return;
        }

        // Get XP to reward with.
        $points = $this->calculator->get_points(new event_subject($event));
        if ($points === null) {
            return;
        }

        // Cheatguard pass two.
        if ($points > 0 && !$this->can_earn_points($userid, $points, $config)) {
            return;
        }

        // Collect.
        // No need to go through the following if the user did not gain XP.
        if ($points > 0) {

            // TODO Implement this differently.
            $initiallevel = $this->store->get_state($userid)->get_level()->get_level();
            $this->store->increase_with_reason($userid, $points, $reason);
            $level = $this->store->get_state($userid)->get_level()->get_level();

            if ($initiallevel != $level) {
                $params = array(
                    'context' => $this->context,
                    'relateduserid' => $userid,
                    'other' => array(
                        'level' => $level
                    )
                );
                $lupevent = \block_xp\event\user_leveledup::create($params);
                $lupevent->trigger();
            }

            if ($level > $initiallevel && $config->get('enablelevelupnotif')) {
                $this->levelupnotifificationservice->notify($userid);
            }
        } else {
            // We still want to log the thing.
            $this->logger->log_reason($userid, $points, $reason);
        }
    }

    protected function can_capture($userid, reason $reason, config $config) {
        $neverhappened = false;

        // There are some events we only want to see once! So they are not bound to the cheat guard.
        if ($reason instanceof \local_xp\local\reason\activity_completion_reason
                || $reason instanceof \local_xp\local\reason\graded_reason) {
            if ($this->reasonoccuranceindicator->has_reason_happened_since($userid, $reason, new DateTime('@0'))) {
                return false;
            }
            $neverhappened = true;
        }

        if (!$config->get('enablecheatguard')) {
            return true;
        }

        $maxactions = $config->get('maxactionspertime');
        $maxtime = $config->get('timeformaxactions');
        $actiontime = $config->get('timebetweensameactions');

        // Time between identical actions. Early skip if the reason never happened.
        if ($actiontime > 0 && !$neverhappened) {
            $since = new DateTime('@' . (time() - $actiontime));
            if ($this->reasonoccuranceindicator->has_reason_happened_since($userid, $reason, $since)) {
                return false;
            }
        }

        if ($maxtime > 0 && $maxactions > 0) {
            $since = new DateTime('@' . (time() - $maxtime));
            if ($this->collectioncountsindicator->count_collections_since($userid, $since) > $maxactions) {
                return false;
            }
        }

        return true;
    }

    protected function can_earn_points($userid, $points, config $config) {
        if (!$config->get('enablecheatguard')) {
            return true;
        }

        $maxpoints = $config->get('maxpointspertime');
        $maxtime = $config->get('timeformaxpoints');
        if (!$maxtime || !$maxpoints) {
            return true;
        }

        $since = new DateTime('@' . (time() - $maxtime));
        if ($this->collectioncountsindicator->get_collected_points_since($userid, $since) + $points > $maxpoints) {
            // Already earned more, skip.
            return false;
        }

        return true;
    }

}

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
 * Language file.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'Activity completed';
$string['badgetheme'] = 'Level badges theme';
$string['badgetheme_help'] = 'A badge theme defines the default appearance of the badges.';
$string['clicktoselectcourse'] = 'Click to select a course';
$string['courseselector'] = 'Course selector';
$string['currencysign'] = 'Points symbol';
$string['currencysign_help'] = 'With this setting you can change the meaning of the points. It will be displayed next to the amount of points each user has as a substitute for the reference to _experience points_.

For instance you could upload the image of a carrot for the users to be rewarded with carrots for their actions.';
$string['currencysignformhelp'] = 'The image uploaded here will be displayed next to the points as a substitute for the reference to experience points. The recommended image height is 18 pixels.';
$string['enablecheatguard'] = 'Enable cheat guard';
$string['enablecheatguard_help'] = 'The cheat guard prevents students from being rewarded once they reach certain limits.';
$string['enablegroupladder'] = 'Enable group ladder';
$string['enablegroupladder_help'] = 'When enabled, students can view a leaderboard of the course groups. The group points are computed from the points accrued by the members of each group. This currently only applies when the plugin is used per course, and not for the whole site.';
$string['errorunknowncourse'] = 'Error: unknown course';
$string['for2weeks'] = 'For 2 weeks';
$string['for3months'] = 'For 3 months';
$string['keeplogsdesc'] = 'The logs are playing an important role in the plugin. They are used for
the cheat guard, for finding the recent rewards, and for some other things. Reducing the time for
which the logs are kept can affect how points are distributed over time and should be dealt with carefully.';
$string['gradereceived'] = 'Grade received';
$string['groupladder'] = 'Group ladder';
$string['levelbadges'] = 'Level badges override';
$string['levelbadges_help'] = 'Upload images to override the designs provided by the badge theme.';
$string['levelup'] = 'Level up!';
$string['maxpointspertime'] = 'Max. points in time frame';
$string['maxpointspertime_help'] = 'The maxmimum number of points that can be earned during the time frame given. When this value is empty, or equals to zero, it does not apply.';
$string['missingpermssionsmessage'] = 'You do not have the required permissions to access this content.';
$string['mylevel'] = 'My level';
$string['navgroupladder'] = 'Group ladder';
$string['pluginname'] = 'Level up! Plus';
$string['points'] = 'Points';
$string['privacy:metadata:log'] = 'Stores a log of events';
$string['privacy:metadata:log:points'] = 'The points awarded for the event';
$string['privacy:metadata:log:signature'] = 'Some event data';
$string['privacy:metadata:log:time'] = 'The date at which it happened';
$string['privacy:metadata:log:type'] = 'The event type';
$string['privacy:metadata:log:userid'] = 'The user who gained the points';
$string['progressbarmode'] = 'Display progress towards';
$string['progressbarmode_help'] = '
When set to _The next level_, the progress bar displays the progress of the user towards the next level.

When set to _The ultimate level_, the progress bar will indicate the percentage of progression towards the very last level that users can attain.

In either case, the progress bar will remain full when the last level is attained.';
$string['progressbarmodelevel'] = 'The next level';
$string['progressbarmodeoverall'] = 'The ultimate level';
$string['ruleactivitycompletion'] = 'Activity completion';
$string['ruleactivitycompletion_help'] = '
This condition is met when an activity was just marked as complete, so long as the completion was not marked as failed.

As per the standard Moodle activity completion settings, teachers have full control over the conditions
needed to _complete_ an activity. Those can be individually set for each activity in the course and
be based on a date, a grade, etc... It is also possible to allow students to manually mark the activities
as complete.

This condition will only reward the student once.';
$string['ruleactivitycompletion_link'] = 'Activity_completion';
$string['ruleactivitycompletiondesc'] = 'An activity or resource was successfully completed';
$string['rulecoursecompletion'] = 'Course completion';
$string['rulecoursecompletion_help'] = 'This rule is met when a course is completed by a student.

__Note:__ Students will not instantaneously receive their points, it takes a little while for Moodle to process course completions. In other words, this requires a _cron_ run.';
$string['rulecoursecompletion_link'] = 'Course_completion';
$string['rulecoursecompletiondesc'] = 'A course was completed';
$string['rulecoursecompletioncoursemodedesc'] = 'The course was completed';
$string['rulecourse'] = 'Course';
$string['rulecourse_help'] = 'This condition is met when the event occurs in the course specified.

It is only available when the plugin is used for the whole site. When the plugin is used per course, this condition becomes ineffective.';
$string['rulecoursedesc'] = 'The course is: {$a}';
$string['ruleusergraded'] = 'Grade received';
$string['ruleusergraded_help'] = 'This condition is met when:

* The grade was received in an activity
* The activity specified a passing grade
* The grade met the passing grade
* The grade is _not_ based on ratings (e.g. in forums)
* The grade is point-based, not scale-based

This condition will only reward the student once.';
$string['ruleusergradeddesc'] = 'The student received a passing grade';
$string['uptoleveln'] = 'Up to level {$a}';
$string['themestandard'] = 'Standard';
$string['timeformaxpoints'] = 'Time frame for max. points';
$string['timeformaxpoints_help'] = 'The time frame (in seconds) during which the user cannot receive more than a certain amount of points.';
$string['visualsintro'] = 'Customise the appearance of the levels, and the points.';

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
 * Workshop evaluation class.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopeval_weightiest
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/workshop/eval/best/lib.php');

/**
 * Workshop evaluation class.
 *
 * @copyright 2014-2017 Albert Gasset <albertgasset@fsfe.org>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   workshopeval_weightiest
 */
class workshop_weightiest_evaluation extends workshop_best_evaluation {

    /**
     * Returns an instance of the form to provide evaluation settings.
     *
     * @param moodle_url|null $actionurl
     * @return \workshopeval_weightiest\settings_form
     */
    public function get_settings_form(moodle_url $actionurl=null) {

        $customdata['workshop'] = $this->workshop;
        $customdata['current'] = $this->settings;
        $attributes = ['class' => 'evalsettingsform best'];

        return new \workshopeval_weightiest\settings_form($actionurl, $customdata, 'post', '', $attributes);
    }

    /**
     * Given a list of all assessments of a single submission, updates the grading grades in database.
     *
     * @param stdClass[] $assessments  Array of (assessmentid, assessmentweight, reviewerid, gradinggrade,
     *                                 submissionid, dimensionid, grade)
     * @param stdClass[] $diminfo      Array of (id, weight, max, min)
     * @param stdClass   $settings     Grading evaluation settings
     */
    protected function process_assessments(array $assessments, array $diminfo, stdClass $settings) {
        global $DB;

        if (empty($assessments)) {
            return;
        }

        // Reindex the passed flat structure to be indexed by assessmentid.
        $assessments = $this->prepare_data_from_recordset($assessments);

        // Normalize the dimension grades to the interval 0 - 100.
        $assessments = $this->normalize_grades($assessments, $diminfo);

        // Calculate the maximum weight of assessments.
        $maxweight = array_reduce($assessments, function($weight, $assessment) {
            return max($weight, $assessment->weight);
        });

        // Get the assessments with maximum weight.
        $weightiest = array_filter($assessments, function($assessment) use ($maxweight) {
            return $assessment->weight == $maxweight;
        });

        // For every assessment, calculate its distance from the nearest weightiest assessment.
        $distances = [];
        foreach ($weightiest as $referential) {
            foreach ($assessments as $asid => $assessment) {
                $d = $this->assessments_distance($assessment, $referential, $diminfo, $settings);
                if ($d !== null and (!isset($distances[$asid]) or $d < $distances[$asid])) {
                    $distances[$asid] = $d;
                }
            }
        }

        // Calculate the grading grade.
        foreach ($distances as $asid => $distance) {
            $gradinggrade = 100 - $distance;
            if ($gradinggrade < 0) {
                $gradinggrade = 0;
            }
            if ($gradinggrade > 100) {
                $gradinggrade = 100;
            }
            $grades[$asid] = grade_floatval($gradinggrade);
        }

        // If the new grading grade differs from the one stored in database, update it.
        foreach ($grades as $asid => $grade) {
            if (grade_floats_different($grade, $assessments[$asid]->gradinggrade)) {
                $DB->set_field('workshop_assessments', 'gradinggrade', grade_floatval($grade), ['id' => $asid]);
            }
        }
    }

    /**
     * Measures the distance of the assessment from a referential one.
     *
     * @param stdClass   $assessment   Assessment being measured
     * @param stdClass   $referential  Referential assessment
     * @param stdClass[] $diminfo      Array of (weight, min, max) indexed by dimension id
     * @param stdClass   $settings
     * @return float|null Rounded to 4 valid decimals
     */
    protected function assessments_distance(stdClass $assessment, stdClass $referential, array $diminfo,
                                            stdClass $settings) {
        $distance = 0;
        $n = 0;
        foreach (array_keys($assessment->dimgrades) as $dimid) {
            $agrade = $assessment->dimgrades[$dimid];
            $rgrade = $referential->dimgrades[$dimid];
            $weight = $diminfo[$dimid]->weight;
            $n += $weight;

            if (abs($agrade - $rgrade) > $settings->comparison * 10 + 0.001) {
                $distance += 100 * $weight;
            }
        }
        if ($n > 0) {
            // Average distance across all dimensions.
            return round($distance / $n, 4);
        } else {
            return null;
        }
    }
}

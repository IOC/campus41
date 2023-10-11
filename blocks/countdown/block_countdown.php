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

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Countdown plugin
 *
 * @package    block_countdown
 * @copyright  Yevhen Matasar <matasar.ei@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_countdown extends block_base
{
    /**
     * Corporate style preset
     */
    const STYLE_DEFAULT = 'style-default';

    /**
     * Corporate style preset
     */
    const STYLE_CORPORATE = 'style-corporate';

    /**
     * @throws coding_exception
     */
    public function init()
    {
        $this->title = get_string('pluginname', 'block_countdown');
    }

    /**
     * @return bool
     */
    function instance_allow_multiple()
    {
        return true;
    }

    /**
     * @return string Content of the block
     *
     * @throws coding_exception
     */
    public function get_content()
    {
        if (!empty($this->content)) {
            return $this->content;
        }

        $this->content = new stdClass();

        if (is_null($this->config)) {
            $this->content->text = get_string('changesettings', 'block_countdown');

            return $this->content;
        }

        if ($this->config->title) {
            $this->title = $this->config->title;
        }

        $this->page->requires->jquery();
        $this->page->requires->js("/blocks/countdown/js/jquery.countdown.js");
        $this->page->requires->js("/blocks/countdown/js/start.js");

        $tag = 'div';
        $params = [];

        if ($this->config->url) {
            $params['href'] = $this->config->url;
            $params['target'] = $this->config->urltarget;
            $tag = 'a';
        }

        if ($this->config->until > time()) {
            $params['class'] = "block-countdown-timer {$this->config->style}";
            $params['data-daystext'] = get_string('daystext', 'block_countdown');
            $params['data-endedtext'] = $this->config->ended_text;

            try {
                $until = new DateTime();
                $until->setTimestamp($this->config->until);
                $params['data-datetime'] = $until->format(DATE_ATOM);
            } catch (\Exception $ex) {
                $params['data-datetime'] = date(DATE_ATOM, $this->config->until);
            }

            $this->content->text = html_writer::tag($tag, '', $params);
        } else {
            if ($this->config->ended_text) {
                $endedtext = $this->config->ended_text;
            } else {
                $endedtext = get_string('changesettings', 'block_countdown');
            }

            $params['class'] = 'countdown-ended';
            $this->content->text = html_writer::tag($tag, $endedtext, $params);
        }

        if ($this->config->css) {
            $this->content->text = html_writer::tag('style', $this->config->css) . $this->content->text;
        }

        return $this->content;
    }
}

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
 * External courses: link to secretaria
 *
 * @package    course
 * @copyright  Institut Obert de Catalunya (IOC) 2014
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

function extern_server_course($course) {
    global $USER, $CFG;

    $settings = get_config('local_secretaria');

    $courses = explode(',', str_replace(' ', '', $settings->courses));
    $registrars = explode(',', str_replace(' ', '', $settings->registrars));

    if (count($courses) != count($registrars)) {
        return false;
    }

    $pos = array_search($course->id, $courses);
    $returnurl = false;

    if ($pos !== false) {
        if (!function_exists('curl_init') ) {
            print_error('nocurl', 'local_secretaria');
        }

        $url = "{$CFG->local_secretaria_baseurl}/ioc/lib/serveiweb/secretaria.php";
        $data = array(
            'username' => urlencode($USER->username),
            'secretaria' => urlencode($registrars[$pos]),
            'password' => urlencode(trim($settings->password)),
            'method' => urlencode(trim($settings->method)),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600);
        if (!empty($CFG->proxyhost) and !empty($CFG->proxyport) and !empty($CFG->proxytype)) {
            curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost);
            curl_setopt($ch, CURLOPT_PROXYPORT, $CFG->proxyport);
            curl_setopt($ch, CURLOPT_PROXYTYPE, $CFG->proxytype);
        }

        $content = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if (!$error) {
            $content = json_decode($content);

            if (empty($content)) {
                print_error('nocontent', 'local_secretaria');
            }
            if (isset($content->error)) {
                print_error($content->token);
            }

            $params = array(
                'username' => $USER->username,
                'token' => $content->token,
                'secretaria' => $registrars[$pos],
            );
            $returnurl = new moodle_url($CFG->local_secretaria_baseurl . '/ioc/login_ws.php', $params);
        }
    }
    return $returnurl;
}

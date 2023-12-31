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
 * Repository_materials class, based in repository_filesystem class.
 *
 *
 * @package    repository_materials
 * @copyright  2013 IOC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/filelib.php');


class repository_materials extends repository {

    /**
     * Constructor
     *
     * @param int $repositoryid repository ID
     * @param int $context context ID
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        global $CFG;
        parent::__construct($repositoryid, $context, $options);
        $root = $CFG->dataroot . '/repository/';
        $subdir = $this->get_option('mt_path');

        $this->root_path = $root;
        if (!empty($subdir)) {
            $this->root_path .= $subdir . '/';
        }

        if (!empty($options['ajax'])) {
            if (!is_dir($this->root_path)) {
                $created = mkdir($this->root_path, $CFG->directorypermissions, true);
                $ret = array();
                $ret['msg'] = get_string('invalidpath', 'repository_materials');
                $ret['nosearch'] = true;
                if ($options['ajax'] && !$created) {
                    echo json_encode($ret);
                    exit;
                }
            }
        }
    }
    public function get_listing($path = '', $page = '') {
        global $CFG, $OUTPUT, $PAGE;

        $list = array();
        $list['list'] = array();
        // process breacrumb trail
        $list['path'] = array(
            array('name'=>get_string('root', 'repository_materials'), 'path'=>'')
        );
        $trail = '';
        if (!empty($path)) {
            $parts = explode('/', $path);
            if (count($parts) > 1) {
                foreach ($parts as $part) {
                    if (!empty($part)) {
                        $trail .= ('/'.$part);
                        $list['path'][] = array('name'=>$part, 'path'=>$trail);
                    }
                }
            } else {
                $list['path'][] = array('name'=>$path, 'path'=>$path);
            }
            $this->root_path .= ($path.'/');
        }
        $list['manage'] = false;
        $list['dynload'] = true;
        $list['nologin'] = true;
        $list['nosearch'] = true;
        // retrieve list of files and directories and sort them
        $fileslist = array();
        $dirslist = array();
        if ($dh = opendir($this->root_path)) {
            while (($file = readdir($dh)) != false) {
                if ( $file != '.' and $file !='..') {

                    if (is_dir($this->root_path.$file)) {
                        $dirslist[] = $file;
                        if (!isset($PAGE->course)) {
                            $fileslist[] = $file;
                        }
                    } else {
                        $fileslist[] = $file;
                     }
                }
            }
        }
        core_collator::asort($fileslist, core_collator::SORT_STRING);
        core_collator::asort($dirslist, core_collator::SORT_STRING);
        // fill the $list['list']
        foreach ($dirslist as $file) {
            if (!empty($path)) {
                $current_path = $path . '/'. $file;
            } else {
                $current_path = $file;
            }
            $list['list'][] = array(
                'title' => $file,
                'children' => array(),
                'datecreated' => filectime($this->root_path.$file),
                'datemodified' => filemtime($this->root_path.$file),
                'thumbnail' => $OUTPUT->image_url(file_folder_icon(90))->out(false),
                'path' => $current_path
                );
        }
        foreach ($fileslist as $file) {
            if (is_dir($this->root_path.$file)) {
                $list['list'][] = array(
                    'title' => $file,
                    'source' => $path.'/'.$file,
                    'size' => filesize($this->root_path.$file),
                    'datecreated' => filectime($this->root_path.$file),
                    'datemodified' => filemtime($this->root_path.$file),
                    'thumbnail' => $OUTPUT->image_url('f/foldericon-80', 'repository_materials')->out(false),
                    'icon' => $OUTPUT->image_url('f/foldericon-24', 'repository_materials')->out(false)
                );
            } else {
                $list['list'][] = array(
                    'title' => $file,
                    'source' => $path.'/'.$file,
                    'size' => filesize($this->root_path.$file),
                    'datecreated' => filectime($this->root_path.$file),
                    'datemodified' => filemtime($this->root_path.$file),
                    'thumbnail' => $OUTPUT->image_url(file_extension_icon($file, 90))->out(false),
                    'icon' => $OUTPUT->image_url(file_extension_icon($file, 24))->out(false)
                );
            }
        }
        $list['list'] = array_filter($list['list'], array($this, 'filter'));
        return $list;
    }
    public function check_login() {
        return true;
    }
    public function print_login() {
        return true;
    }
    public function global_search() {
        return false;
    }

    /**
     * Return file path
     * @return array
     */
    public function get_file($file, $title = '') {
        global $CFG;
        if ($file[0] == '/') {
            $file = $this->root_path.substr($file, 1, strlen($file)-1);
        } else {
            $file = $this->root_path.$file;
        }
        // this is a hack to prevent move_to_file deleteing files
        // in local repository
        $CFG->repository_no_delete = true;
        return array('path'=>$file, 'url'=>'');
    }

    /**
     * Return the source information
     *
     * @param stdClass $filepath
     * @return string|null
     */
    public function get_file_source_info($filepath) {
        return $filepath;
    }

    public function logout() {
        return true;
    }

    public static function get_instance_option_names() {
        return array('mt_path');
    }

    public function set_option($options = array()) {
        $options['mt_path'] = clean_param($options['mt_path'], PARAM_PATH);
        $ret = parent::set_option($options);
        return $ret;
    }

    public static function instance_config_form($mform) {
        global $CFG, $PAGE;
        if (has_capability('moodle/site:config', context_system::instance())) {
            $path = $CFG->dataroot . '/repository/';
            if (!is_dir($path)) {
                mkdir($path, $CFG->directorypermissions, true);
            }
            if ($handle = opendir($path)) {
                $fieldname = get_string('path', 'repository_materials');
                $choices = array();
                while (false !== ($file = readdir($handle))) {
                    if (is_dir($path.$file) && $file != '.' && $file!= '..') {
                        $choices[$file] = $file;
                        $fieldname = '';
                    }
                }
                if (empty($choices)) {
                    $mform->addElement('static', '', '', get_string('nosubdir', 'repository_materials', $path));
                    $mform->addElement('hidden', 'mt_path', '');
                    $mform->setType('mt_path', PARAM_PATH);
                } else {
                    $mform->addElement('select', 'mt_path', $fieldname, $choices);
                    $mform->addElement('static', null, '',  get_string('information','repository_materials', $path));
                }
                closedir($handle);
            }
        } else {
            $mform->addElement('static', null, '',  get_string('nopermissions', 'error', get_string('configplugin', 'repository_materials')));
            return false;
        }
    }

    public static function create($type, $userid, $context, $params, $readonly=0) {
        global $PAGE;
        if (has_capability('moodle/site:config', context_system::instance())) {
            return parent::create($type, $userid, $context, $params, $readonly);
        } else {
            require_capability('moodle/site:config', context_system::instance());
            return false;
        }
    }
    public static function instance_form_validation($mform, $data, $errors) {
        if (empty($data['mt_path'])) {
            $errors['mt_path'] = get_string('invalidadminsettingname', 'error', 'mt_path');
        }
        return $errors;
    }

    /**
     * Only File references, files can't be uploaded to moodledata
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_REFERENCE;
    }

    /**
     * Return reference file life time
     *
     * @param string $ref
     * @return int
     */
    public function get_reference_file_lifetime($ref) {
        // Does not cost us much to synchronise within our own filesystem, set to 1 minute
        return 60;
    }

    /**
     * Return human readable reference information
     *
     * @param string $reference value of DB field files_reference.reference
     * @param int $filestatus status of the file, 0 - ok, 666 - source missing
     * @return string
     */
    public function get_reference_details($reference, $filestatus = 0) {
        $details = $this->get_name().': '.$reference;
        if ($filestatus) {
            return get_string('lostsource', 'repository', $details);
        } else {
            return $details;
        }
    }

    /**
     * Returns information about file in this repository by reference
     *
     * Returns null if file not found or is not readable
     *
     * @param stdClass $reference file reference db record
     * @return stdClass|null contains one of the following:
     *   - 'filesize' if file should not be copied to moodle filepool
     *   - 'filepath' if file should be copied to moodle filepool
     */
    public function get_file_by_reference($reference) {
        $ref = $reference->reference;
        if ($ref[0] == '/') {
            $filepath = $this->root_path.substr($ref, 1, strlen($ref)-1);
        } else {
            $filepath = $this->root_path.$ref;
        }
        if (file_exists($filepath) && is_readable($filepath)) {
            if (file_extension_in_typegroup($filepath, 'web_image')) {
                // return path to image files so it will be copied into moodle filepool
                // we need the file in filepool to generate an image thumbnail
                return (object)array('filepath' => $filepath);
            } else {
                // return just the file size so file will NOT be copied into moodle filepool
                return (object)array(
                    'filesize' => filesize($filepath)
                );
            }
        } else {
            return null;
        }
    }

    /**
     * Repository method to serve the referenced file
     *
     * @see send_stored_file
     *
     * @param stored_file $storedfile the file that contains the reference
     * @param int $lifetime Number of seconds before the file should expire from caches (default 24 hours)
     * @param int $filter 0 (default)=no filtering, 1=all files, 2=html files only
     * @param bool $forcedownload If true (default false), forces download of file rather than view in browser/plugin
     * @param array $options additional options affecting the file serving
     */
    public function send_file($storedfile, $lifetime=86400 , $filter=0, $forcedownload=false, array $options = null) {
        $reference = $storedfile->get_reference();
        if ($reference[0] == '/') {
            $file = $this->root_path.substr($reference, 1, strlen($reference)-1);
        } else {
            $file = $this->root_path.$reference;
        }
        $url = '/'.$this->options['mt_path'].'/'.$reference;
        @header('Location: '. $url);
        die;
        if (is_readable($file)) {
            $filename = $storedfile->get_filename();
            if ($options && isset($options['filename'])) {
                $filename = $options['filename'];
            }
            $dontdie = ($options && isset($options['dontdie']));
            send_file($file, $filename, $lifetime , $filter, false, $forcedownload, '', $dontdie);
        } else {
            send_file_not_found();
        }
    }

    /**
     * Is this repository accessing private data?
     *
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }
}

<?php
/**
 * @package mod_fpdquadern
 * @copyright 2014 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

require_once(__DIR__ . '/backup_fpdquadern_stepslib.php');

class backup_fpdquadern_activity_task extends backup_activity_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(
            new backup_fpdquadern_activity_structure_step(
                'fpdquadern_structure', 'fpdquadern.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        $search = "/(" . $base . "\/mod\/quadern\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@FPDQUADERNINDEX*$2@$', $content);

        $search = "/(" . $base . "\/mod\/fpdquadern\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@FPDQUADERNVIEWBYID*$2@$', $content);

        return $content;
    }
}

<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();
require_once(__DIR__ . '/../../config-moodle2.php');

$CFG->forced_plugin_settings = [
    'logstore_standard' => [
        'loglifetime' => 1000,
    ],
];

require_once __DIR__ . '/local/userdebug/lib.php';
userdebug_get_debug();

require_once(__DIR__ . '/lib/setup.php');

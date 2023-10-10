<?php
/**
 * @package mod_fpdquadern
 * @copyright 2013 Institut Obert de Catalunya
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Albert Gasset <albert@ioc.cat>
 */

namespace mod_fpdquadern;

require_once('../../config.php');
require_once('locallib.php');
require_once('forms.php');

$accio = optional_param('accio', 'veure_alumnes', PARAM_ALPHAEXT);
$class = 'mod_fpdquadern\\' . $accio . '_view';

if (class_exists($class)) {
    new $class();
} else {
    print_error('nopermissiontoshow');
}

<?php

require_once('config.php');

if ($DB->get_record('course', array('id' => SITEID))) {
    echo 'OK';
}

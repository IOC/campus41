<?php

require_once('../config.php');

header('Content-type: text/plain');

if (!empty($USER->id)) {
    echo fullname($USER);
}


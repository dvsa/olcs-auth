<?php

$en = include(__DIR__ . '/en_GB.php');

foreach ($en as $key => $val) {
    $en[$key] = '{WELSH} ' . $val;
}

return $en;

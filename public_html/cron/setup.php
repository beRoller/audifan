<?php

/**
 * Sets up cron stuff.  This file should be required_once by cron scripts.
 */

require_once realpath(dirname(__FILE__) . "/../config.php");

if (!isset($audifan)) {
    require_once $_CONFIG["libsLocation"] . "/audifan/Audifan.php";
    $audifan = new Audifan($_CONFIG);
}
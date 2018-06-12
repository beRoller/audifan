<?php

require_once "setup.php";

/*
 * This is a cron script that runs all cron scripts that need to be ran on Monday at midnight.
 */
require_once "updatevipranking.php";
require_once "quests.php";
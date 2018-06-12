<?php

require_once "setup.php";

/*
 * This is a cron script that runs all cron scripts that need to be ran every day.
 */
require_once "removeunverified.php";
require_once "removeexpiredrequests.php";
require_once "removeexpiredsessions.php";
require_once "removeoldhappyboxwinners.php";
require_once "updatecandles.php";
require_once "removeexpiredstuff.php";
require_once "vipdrawing.php";
require_once "removeoldnotifications.php";
require_once "removeoldcoinhistory.php";

printf("\n\nDaily cron tasks were completed at %s EST.", date("g:i"));
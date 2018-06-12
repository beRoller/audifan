<?php

/*
 * This is a cron script to remove all expired stuff from accounts.
 * It should be run every day.
 */

require_once "setup.php";

/* @var $db Database */
$db = $audifan -> getDatabase();

$db -> prepareAndExecute("DELETE FROM AccountStuff WHERE expire_time<? AND expire_time!=-1", time());

print "\n\nExpired stuff was removed.";
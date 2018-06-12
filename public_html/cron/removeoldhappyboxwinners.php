<?php

/*
 * This is a cron script that removes Happy Box winners from more than a week ago.
 * It should be run every day.
 */

require_once "setup.php";

/* @var $db Database */
$db = $audifan -> getDatabase();

$db -> prepareAndExecute("DELETE FROM HappyBoxWinners WHERE win_time<?", time() - (3600 * 24 * 7));

print "\n\nOld Happy Box winners were deleted.";
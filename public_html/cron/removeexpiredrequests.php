<?php

/*
 * This is a cron script that removes all expired requests.
 * It should be run every day.
 */

require_once "setup.php";

$time = time() - (3600 * 24 * 30);
$deletedRequests = 0;

/* @var $db Database */
$db = $audifan -> getDatabase();

$deletedRequests = $db -> prepareAndExecute("SELECT COUNT(*) FROM Requests WHERE time<?", $time) -> fetchColumn();

$db -> prepareAndExecute("DELETE FROM Requests WHERE time<?", $time);

printf("\n\n%d requests(s) were deleted.", $deletedRequests);
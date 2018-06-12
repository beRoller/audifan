<?php

/**
 * This is a cron script that removes accounts that have not verified their email address in a week.
 * It should be run every day at midnight.
 */

/* @var $audifan Audifan */

require_once "setup.php";

$time = time() - (3600 * 24 * 7);

$db = $audifan -> getDatabase();

$acc = $db -> prepareAndExecute("SELECT id, display_name FROM Accounts WHERE account_type=-1 AND join_time<?", $time) -> fetchAll();

$db -> prepareAndExecute("DELETE FROM Accounts WHERE account_type=-1 AND join_time<?", $time);

print "\n\nThe following accounts were DELETED because they didn't verify their account within a week:\n";
foreach ($acc as $a)
    printf("%s (ID: %d)\n", $a["display_name"], $a["id"]);

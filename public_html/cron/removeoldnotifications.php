<?php

/* @var $audifan Audifan */
$db = $audifan -> getDatabase();

$delTime = time() - (3600 * 24 * 7 * 2);
$num = $db -> prepareAndExecute("SELECT COUNT(*) FROM Notifications WHERE time<=?", $delTime) -> fetchColumn();

$db -> prepareAndExecute("DELETE FROM Notifications WHERE time<=?", $delTime);

print "\nDeleted " . $num . " old database notification(s).\n";
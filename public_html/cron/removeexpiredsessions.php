<?php

/*
 * This is a cron script that removes all expired sessions.
 * It should be run every day.
 */

require_once "setup.php";


$db = $audifan->getDatabase();

$expire_time = time();

// Removed expired sessions.
$db->prepareAndExecute("DELETE FROM AccountSessions WHERE session_expire_time <= ?", $expire_time);

// Remove session IPs whose sessions no longer exist.
$db->prepareAndExecute("DELETE FROM AccountSessionIPs WHERE session_key NOT IN (SELECT session_key FROM AccountSessions)");

<?php

/* @var $audifan Audifan */
$db = $audifan -> getDatabase();

$delTime = time() - (3600 * 24 * 30);
$num = $db -> prepareAndExecute("SELECT COUNT(*) FROM CoinHistory WHERE history_time<=?", $delTime) -> fetchColumn();

$db -> prepareAndExecute("DELETE FROM CoinHistory WHERE history_time<=?", $delTime);

print "\nDeleted " . $num . " old coin history entries.\n";
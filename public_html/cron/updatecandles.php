<?php

/*
 * This is a cron script that adds new candles (which expire in 24 hours) and updates the HTML list of candle holders.
 * It should be run every day at midnight.
 */

require_once "setup.php";

/* @var $audifan Audifan */
/* @var $db Database */
$db = $audifan -> getDatabase();

$year = date("Y");
$month = date("n");
$day = date("j");

$db -> prepareAndExecute("SET time_zone='-5:00'");

$candles = array();

foreach ($db -> prepareAndExecute("SELECT id, display_name FROM Accounts WHERE YEAR(FROM_UNIXTIME(join_time))!=? AND MONTH(FROM_UNIXTIME(join_time))=? AND DAY(FROM_UNIXTIME(join_time))=? ORDER BY display_name ASC", $year, $month, $day) as $row) {
    array_push($candles, sprintf('<a href="/community/profile/%d/">%s</a>', $row["id"], $row["display_name"]));
    $db -> prepareAndExecute("INSERT INTO AccountStuff(account_id, item_id, expire_time) VALUES(?,?,?)", $row["id"], 14, time() + (3600 * 24));
}
<?php

require_once "setup.php";

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

// Only update on Mondays at midnight.  The script should be scheduled for Mondays, but this is to make sure.
if ($audifan -> getNow() -> getDayNumberOfWeek() == 1) {

    // Update VIP Ranking
    $q = "SELECT AccountStuff.account_id ";
    $q .= "FROM AccountStuff ";
    $q .= "LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id ";
    $q .= "WHERE AccountStuff.item_id=2 AND (AccountStuff.expire_time>? OR expire_time=-1) ";
    $q .= "ORDER BY Accounts.display_name ASC";

    $vips = $db -> prepareAndExecute($q, time() + 3600) -> fetchAll();

    // Update VIP counts
    foreach ($vips as $v) {
        if ($db -> prepareAndExecute("SELECT * FROM VIPRanking WHERE account_id=?", $v["account_id"]) -> rowCount() == 0)
            $db -> prepareAndExecute("INSERT INTO VIPRanking(account_id, vip_count) VALUES(?,?)", $v["account_id"], 1);
        else
            $db -> prepareAndExecute("UPDATE VIPRanking SET vip_count=vip_count+1 WHERE account_id=?", $v["account_id"]);
    }

    // Update ranks.
    $vips = $db -> prepareAndExecute("SELECT * FROM VIPRanking ORDER BY VIPRanking.vip_count DESC, VIPRanking.account_id ASC") -> fetchAll();

    for ($i = 0; $i < sizeof($vips); $i++)
        $db -> prepareAndExecute("UPDATE VIPRanking SET rank=? WHERE account_id=?", $i + 1, $vips[$i]["account_id"]);

    print "\n\nUpdated VIP Ranking.";
}
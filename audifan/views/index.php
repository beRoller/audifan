<?php

$viewData["template"] = "index.twig";

/* @var $audifan Audifan */
$db = $audifan -> getDatabase();
$user = $audifan -> getUser();

$context["news"] = $db -> prepareAndExecute("SELECT * FROM News WHERE type != 'Hidden' ORDER BY time DESC LIMIT 10") -> fetchAll();
$context["vips"] = $db -> prepareAndExecute("SELECT Accounts.display_name, Accounts.id FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE AccountStuff.item_id=2 AND AccountStuff.expire_time>? GROUP BY Accounts.id ORDER BY Accounts.display_name ASC", time()) -> fetchAll();
$context["candles"] = $db -> prepareAndExecute("SELECT Accounts.display_name, Accounts.id FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE AccountStuff.item_id=14 AND AccountStuff.expire_time>? GROUP BY Accounts.id ORDER BY Accounts.display_name ASC", time()) -> fetchAll();

$q  = "SELECT Accounts.id, Accounts.display_name, Accounts.account_type, VIP.is_vip, Candle.has_candle ";
$q .= "FROM AccountSessions ";
$q .= "LEFT JOIN Accounts ON AccountSessions.session_account=Accounts.id ";
$q .= "LEFT JOIN AccountFlags ON AccountFlags.account_id=Accounts.id ";
$q .= "LEFT JOIN (SELECT AccountStuff.account_id, 1 AS is_vip FROM AccountStuff WHERE item_id=? AND (expire_time>? OR expire_time=-1) GROUP BY AccountStuff.account_id) AS VIP ON Accounts.id=VIP.account_id ";
$q .= "LEFT JOIN (SELECT AccountStuff.account_id, 1 AS has_candle FROM AccountStuff WHERE item_id=? AND (expire_time>? OR expire_time=-1) GROUP BY AccountStuff.account_id) AS Candle ON Accounts.id=Candle.account_id ";
$q .= "WHERE AccountSessions.session_last_activity_time>? ";
$q .= "AND AccountFlags.invisible = 0 ";
$q .= "GROUP BY Accounts.id ";
$q .= "ORDER BY Accounts.display_name ASC";

$context["online"] = $db -> prepareAndExecute($q, Inventory::ITEM_VIPBADGE, time(), Inventory::ITEM_CANDLEDAYBADGE, time(), time() - (60 * 10)) -> fetchAll();

if ($user -> isLoggedIn()) {
    // Handle notification deletion via AJAX.
    $deleteNotif = filter_input(INPUT_GET, "deleteNotif", FILTER_VALIDATE_INT, array(
        "options" => array(
            "min_range" => 1
        )
    ));
    if (!is_null($deleteNotif) && $deleteNotif !== FALSE) {
        $audifan -> getNotificationManager() -> deleteDatabaseNotification($deleteNotif);
        print json_encode(array(
            "id" => $deleteNotif
        ));
        exit;
    }
    
    $context["notifications"] = $audifan -> getNotificationManager() -> getAll();
    
    $friends = $db -> prepareAndExecute("SELECT friends FROM Accounts WHERE id=?", $user -> getId()) -> fetchColumn();
    if ($friends == "")
        $friends = "0";
    elseif (substr($friends, 0, 1) == ",")
        $friends = substr($friends, 1);
    
    $q  = "SELECT ProfileComments.*, FromAccount.display_name AS from_display_name, FromAccount.profile_pic_type, ToAccount.display_name AS to_display_name, VIPRanking.rank AS viprank, ";
    $q .= "Characters.ign, Characters.level, Characters.fam, Characters.fam_member_type, ";
    $q .= "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS qp, QuestRanking.rank_overall AS qrank, Inventories.itemstring ";
    $q .= "FROM ProfileComments ";
    $q .= "LEFT JOIN Accounts AS FromAccount ON ProfileComments.from_id=FromAccount.id ";
    $q .= "LEFT JOIN Accounts AS ToAccount ON ProfileComments.to_id=ToAccount.id ";
    $q .= "LEFT JOIN Characters ON FromAccount.main_character=Characters.id ";
    $q .= "LEFT JOIN VIPRanking ON ProfileComments.from_id=VIPRanking.account_id ";
    $q .= "LEFT JOIN QuestData ON ProfileComments.from_id=QuestData.data_account_id ";
    $q .= "LEFT JOIN QuestRanking ON ProfileComments.from_id=QuestRanking.rank_account_id ";
    $q .= "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS itemstring FROM AccountStuff WHERE (expire_time>? OR expire_time=-1) AND in_use=1 GROUP BY account_id) AS Inventories ON ProfileComments.from_id=Inventories.account_id ";
    $q .= "WHERE (from_id=? OR to_id=? OR from_id IN (" . $friends . ") OR to_id IN (" . $friends . ")) ";
    $q .= "AND ProfileComments.private=0 ";
    $q .= "ORDER BY ProfileComments.time DESC ";
    $q .= "LIMIT 40";
    $context["feed"] = $db -> prepareAndExecute($q, time(), $user -> getId(), $user -> getId()) -> fetchAll();
    
    // My Stuff Alerts
    $context["stuff"] = [
        "coinBoxes" => $db -> prepareAndExecute("SELECT COUNT(*) FROM AccountStuff WHERE item_id IN (31,32,33,37) AND account_id=? AND (expire_time=-1 OR expire_time>?)", $user -> getId(), time()) -> fetchColumn(),
        "badgeVouchers" => $db -> prepareAndExecute("SELECT COUNT(*) FROM AccountStuff WHERE item_id=? AND account_id=? AND (expire_time=-1 OR expire_time>?)", Inventory::ITEM_BADGEVOUCHER, $user -> getId(), time()) -> fetchColumn(),
        "vipDrawingVouchers" => $db -> prepareAndExecute("SELECT COUNT(*) FROM AccountStuff WHERE item_id=? AND note='' AND account_id=? AND (expire_time=-1 OR expire_time>?)", Inventory::ITEM_VIPDRAWINGPRIZEVOUCHER, $user -> getId(), time()) -> fetchColumn()
    ];
    
    if ($user -> isAdmin()) {
        $context["openTickets"] = $db -> prepareAndExecute("SELECT COUNT(*) FROM Tickets WHERE ticket_open=1") -> fetchColumn();
    }
}
<?php

/* @var $audifan Audifan */

$viewData["template"] = "community/vipranking.twig";

$page = filter_input(INPUT_GET, "page", FILTER_VALIDATE_INT, array(
    "options" => array(
        "min_range" => 1
    )
));
if (is_null($page) || $page === FALSE)
    $page = 1;

$context["vips"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT VIPRanking.*, Accounts.display_name, VIP.is_vip FROM VIPRanking LEFT JOIN Accounts ON VIPRanking.account_id=Accounts.id LEFT JOIN (SELECT account_id, 1 AS is_vip FROM AccountStuff WHERE item_id=? AND (expire_time>? OR expire_time=-1)) AS VIP ON Accounts.id=VIP.account_id ORDER BY VIPRanking.vip_count DESC, VIPRanking.account_id ASC LIMIT 25 OFFSET ?", Inventory::ITEM_VIPBADGE, time(), ($page - 1) * 25) -> fetchAll();
$context["totalPages"] = ceil($audifan -> getDatabase() -> prepareAndExecute("SELECT COUNT(*) FROM VIPRanking") -> fetchColumn() / 25);
if ($audifan -> getUser() -> isLoggedIn()) {
    $myRank = $audifan -> getDatabase() -> prepareAndExecute("SELECT rank FROM VIPRanking WHERE account_id=?", $audifan -> getUser() -> getId()) -> fetchColumn();
    $context["myRankPage"] = ceil($myRank / 25);
}
$context["page"] = $page;
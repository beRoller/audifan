<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$getData = filter_input_array(INPUT_GET, array(
    "b" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 0,
            "max_range" => 7
        )
    ),
    "page" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    )
));
$board = 0;
$page = 1;

if (!is_null($getData["b"]) && $getData["b"] !== FALSE)
    $board = $getData["b"];
if (!is_null($getData["page"]) && $getData["page"] !== FALSE)
    $page = $getData["page"];

$viewData["template"] = "quests/ranking.twig";

$context["board"] = $board;

$queries = array(
    // 0 - Overall
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_overall AS rank, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "ORDER BY QuestRanking.rank_overall ASC",
    
    // 1 - Overall w/o Insane
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_overall AS rank, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_group_points+QuestData.data_battle_points) AS points, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS total_points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "ORDER BY QuestRanking.rank_overall_no_insane ASC",
    
    // 2 - Normal
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_normal AS rank, " .
    "QuestData.data_normal_points AS points, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS total_points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "WHERE QuestRanking.rank_normal != 0 " .
    "ORDER BY QuestRanking.rank_normal ASC",
    
    // 3 - Beat Up
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_beat_up AS rank, " .
    "QuestData.data_beatup_points AS points, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS total_points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "WHERE QuestRanking.rank_beat_up != 0 " .
    "ORDER BY QuestRanking.rank_beat_up ASC",
    
    // 4 - One Two Party
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_one_two AS rank, " .
    "QuestData.data_onetwo_points AS points, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS total_points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "WHERE QuestRanking.rank_one_two != 0 " .
    "ORDER BY QuestRanking.rank_one_two ASC",
    
    // 5 - Beat Rush
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_beat_rush AS rank, " .
    "QuestData.data_beatrush_points AS points, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS total_points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "WHERE QuestRanking.rank_beat_rush != 0 " .
    "ORDER BY QuestRanking.rank_beat_rush ASC",
    
    // 6 - Guitar
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_guitar AS rank, " .
    "QuestData.data_guitar_points AS points, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS total_points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "WHERE QuestRanking.rank_guitar != 0 " .
    "ORDER BY QuestRanking.rank_guitar ASC",
    
    // 7 - Individual Only
    "SELECT QuestRanking.rank_account_id, QuestRanking.rank_individual AS rank, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points) AS points, " .
    "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS total_points, " .
    "Accounts.display_name, Inventory.items, IGNs.igns " .
    "FROM QuestRanking " .
    "LEFT JOIN QuestData ON QuestRanking.rank_account_id=QuestData.data_account_id " .
    "LEFT JOIN Accounts ON QuestRanking.rank_account_id=Accounts.id " .
    "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS items FROM AccountStuff GROUP BY account_id) AS Inventory ON QuestRanking.rank_account_id=Inventory.account_id " .
    "LEFT JOIN (SELECT ign_account_id, GROUP_CONCAT(ign_ign) AS igns FROM QuestIGNs GROUP BY ign_account_id) AS IGNs ON QuestRanking.rank_account_id=IGNs.ign_account_id " .
    "ORDER BY QuestRanking.rank_individual ASC",
);

$context["page"] = $page;

$context["totalPages"] = ceil($db -> prepareAndExecute($queries[$board]) -> rowCount() / 20);

/*
 * The key points is the points for the current category and total_points is total points.
 */
$context["ranks"] = $db -> prepareAndExecute($queries[$board] . " LIMIT 20 OFFSET ?", 20 * ($page - 1)) -> fetchAll();

$context["rankNumberOffset"] = (($page - 1) * 20) + 1;
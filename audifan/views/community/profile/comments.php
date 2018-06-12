<?php

/* @var $audifan Audifan */

$id = $viewData["urlVariables"][1];

$getFilter = filter_input_array(INPUT_GET, array(
    "page" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    )
));
$context["page"] = (!is_null($getFilter["page"]) && $getFilter["page"] !== FALSE) ? $getFilter["page"] : 1;

/* @var $db Database */
$db = $audifan -> getDatabase();

$basicInfo = $db -> prepareAndExecute(
        "SELECT id, display_name, allow_comments " .
        "FROM Accounts WHERE id=?",
        $id) -> fetch();

if ($basicInfo === FALSE)
    return;

$viewData["template"] = "community/profile/comments.twig";

$context["basicInfo"] = $basicInfo;

$context["totalPages"] = ceil($db -> prepareAndExecute("SELECT COUNT(*) FROM ProfileComments WHERE to_id=?", $basicInfo["id"]) -> fetchColumn() / 20);

$q  = "SELECT ProfileComments.*, Accounts.display_name, Accounts.profile_pic_type, VIPRanking.rank AS viprank, ";
$q .= "Characters.ign, Characters.level, Characters.fam, Characters.fam_member_type, ";
$q .= "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS qp, QuestRanking.rank_overall AS qrank, Inventories.itemstring ";
$q .= "FROM ProfileComments ";
$q .= "LEFT JOIN Accounts ON ProfileComments.from_id=Accounts.id ";
$q .= "LEFT JOIN Characters ON Accounts.main_character=Characters.id ";
$q .= "LEFT JOIN VIPRanking ON ProfileComments.from_id=VIPRanking.account_id ";
$q .= "LEFT JOIN QuestData ON ProfileComments.from_id=QuestData.data_account_id ";
$q .= "LEFT JOIN QuestRanking ON ProfileComments.from_id=QuestRanking.rank_account_id ";
$q .= "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS itemstring FROM AccountStuff WHERE (expire_time>? OR expire_time=-1) AND in_use=1 GROUP BY account_id) AS Inventories ON ProfileComments.from_id=Inventories.account_id ";
$q .= "WHERE ProfileComments.to_id=? ";
$q .= "ORDER BY ProfileComments.time DESC ";
$q .= "LIMIT 20 OFFSET ?";
$context["comments"] = $db -> prepareAndExecute($q, time(), $basicInfo["id"], 20 * ($context["page"] - 1)) -> fetchAll();
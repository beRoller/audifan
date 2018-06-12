<?php

/* @var $audifan Audifan */

$viewData["template"] = "quests/index.twig";

date_default_timezone_set("America/New_York");

$db = $audifan -> getDatabase();

/* @var $user User */
$user = $audifan -> getUser();

$questData = array();
$questSubmissions = array();

if ($user -> isLoggedIn()) {
    // Quest Data
    $stmt = $db -> prepareAndExecute("SELECT * FROM QuestData WHERE data_account_id=?", $user -> getId());
    if ($stmt -> rowCount() == 0) {
        // Insert empty quest data if they don't have data already.
        $db -> prepareAndExecute("INSERT INTO QuestData(data_account_id) VALUES(?)", $user -> getId());
        $stmt = $db -> prepareAndExecute("SELECT * FROM QuestData WHERE data_account_id=?", $user -> getId());
        $user -> updateSession();
    }
    $questData = $stmt -> fetch();
    
    // Quest Submissions
    $stmt = $db -> prepareAndExecute("SELECT QuestSubmissions.*, QuestRequirements.req_difficulty FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id WHERE submit_account_id=? ORDER BY submit_time DESC", $user -> getId());
    if ($stmt -> rowCount() != 0) {
        $questSubmissions = $stmt -> fetchAll();
        $context["recentSubmissions"] = array_slice($questSubmissions, 0, 6);
    }
    
    // Get ungraded count if mod.
    if ($user -> isMod())
        $context["ungradedCount"] = $db -> prepareAndExecute("SELECT COUNT(*) FROM QuestSubmissions WHERE submit_grade_status=0") -> fetchColumn();
}

$currentWeek = (int) date("W");
$currentYear = (int) date("o");
$thisWeeksQuests = array();
if (date("N") == "7" && date("G") == "23" && ((int) date("i")) >= 50) {
    for ($i = 1; $i <= 6; $i++)
        $thisWeeksQuests[$i] = "";
} else {
    foreach ($db -> prepareAndExecute("SELECT req_difficulty, req_text FROM QuestRequirements WHERE req_year=? AND req_week_number=?", $currentYear, $currentWeek) as $row)
        $thisWeeksQuests[ $row["req_difficulty"] ] = $row["req_text"];
    
    for ($i = 1; $i <= 6; $i++)
        if (!isset($thisWeeksQuests[$i]))
            $thisWeeksQuests[$i] = "";
}

if ($audifan -> getUser() -> isLoggedIn()) {
    $audifan -> getUser() -> updateSession();
    $audifan -> getUser() -> updateObject();
    $context["submissionInfo"] = $audifan -> getUser() -> getQuestData()["submissions"];
}
$context["questData"] = $questData;
$context["thisWeeksQuests"] = $thisWeeksQuests;
$context["diffs"] = array("Easy", "Medium", "Hard", "Insane", "Group", "Battle");
$context["statuses"] = array("Pending", "Rejected", "Complete");
$context["questWorths"] = array(0, 2, 4, 8, 12);

// Medalists
$context["medalists"] = array(
    "1" => $db -> prepareAndExecute("SELECT Accounts.id, Accounts.display_name FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE AccountStuff.item_id=? ORDER BY Accounts.display_name", Inventory::ITEM_QUESTBADGETHUMBSUP) -> fetchAll(),
    "2" => $db -> prepareAndExecute("SELECT Accounts.id, Accounts.display_name FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE AccountStuff.item_id=? ORDER BY Accounts.display_name", Inventory::ITEM_QUESTBADGEBRONZE) -> fetchAll(),
    "3" => $db -> prepareAndExecute("SELECT Accounts.id, Accounts.display_name FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE AccountStuff.item_id=? ORDER BY Accounts.display_name", Inventory::ITEM_QUESTBADGESILVER) -> fetchAll(),
    "4" => $db -> prepareAndExecute("SELECT Accounts.id, Accounts.display_name FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE AccountStuff.item_id=? ORDER BY Accounts.display_name", Inventory::ITEM_QUESTBADGEGOLD) -> fetchAll()
);

$context["topTen"] = $db -> prepareAndExecute("SELECT (QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS qp, Accounts.display_name, Accounts.id FROM QuestData LEFT JOIN Accounts ON QuestData.data_account_id=Accounts.id ORDER BY qp DESC, id ASC LIMIT 10") -> fetchAll();


$q  = "SELECT Accounts.display_name, Accounts.id, QuestSubmissions.submit_battle_score ";
$q .= "FROM QuestSubmissions ";
$q .= "LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id=QuestRequirements.req_id ";
$q .= "LEFT JOIN Accounts ON QuestSubmissions.submit_account_id=Accounts.id ";
$q .= "WHERE QuestRequirements.req_week_number=? AND QuestRequirements.req_year=? AND QuestRequirements.req_difficulty=6 ";
$q .= "AND QuestSubmissions.submit_points_given=1 ";
$q .= "ORDER BY QuestSubmissions.submit_battle_score DESC";

$lastWeekNumber = date("W", time() - (3600 * 24 * 7));
$lastWeekYear = date("o", time() - (3600 * 24 * 7));

$context["bqLastWeekParticipants"] = $db -> prepareAndExecute($q, $lastWeekNumber, $lastWeekYear) -> fetchAll();

$n = sizeof($context["bqLastWeekParticipants"]);
if ($n > 0) {
    $s1 = $context["bqLastWeekParticipants"][$n - 1]["submit_battle_score"];
    $sn = $context["bqLastWeekParticipants"][0]["submit_battle_score"];
    $m = 0;
    if ($sn != $s1) // Prevent division by zero when there's only one score.
        $m = (min(array(6 + $n - 1, 20)) - 6) / ($sn - $s1);

    for ($i = 0; $i < $n; $i++) {
        $context["bqLastWeekParticipants"][$i]["points"] = floor(($m * $context["bqLastWeekParticipants"][$i]["submit_battle_score"]) - ($m * $s1) + 6);
    }
    
    $context["bqQpToScore"] = array();
    if ($sn != $s1) {
        for ($qp = 6; $qp <= min(array(6 + $n - 1, 20)); $qp++) {
            array_push($context["bqQpToScore"], ceil(($qp - 6 + ($m * $s1)) / $m));
        }
    } else {
        array_push($context["bqQpToScore"], $sn);
    }
}

$context["bqLastWeekReq"] = $db -> prepareAndExecute("SELECT * FROM QuestRequirements WHERE req_difficulty=6 AND req_week_number=? AND req_year=?", $lastWeekNumber, $lastWeekYear) -> fetch();

$bqNumParticipants = $db -> prepareAndExecute("SELECT COUNT(*) FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id=QuestRequirements.req_id WHERE QuestRequirements.req_year=? AND QuestRequirements.req_week_number=? AND QuestRequirements.req_difficulty=6 AND QuestSubmissions.submit_grade_status=2", $currentYear, $currentWeek) -> fetchColumn();
$context["maxBattleQuestPoints"] = min(array(6 + $bqNumParticipants - 1, 20));
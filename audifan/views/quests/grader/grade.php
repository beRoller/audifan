<?php

/* @var $audifan Audifan */

$user = $audifan -> getUser();
$db = $audifan -> getDatabase();

if (!$user -> isMod()) {
    header("Location: /quests/");
    exit;
}

$noMessages = array(
    "You must submit a screenshot of the scoreboard.",
    "The /time command wasn't in the screenshot.",
    "The /time command wasn't from the current week.",
    "The IGN on the screenshot didn't match your registered IGN for this week.",
    "The mode was incorrect.",
    "The song was incorrect."
);

$postData = filter_input_array(INPUT_POST, array(
    "submit_yes" => FILTER_DEFAULT,
    "submit_no" => FILTER_DEFAULT,
    "submit_idk" => FILTER_DEFAULT,
    "id" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    ),
    "score" => array(
        "filter" => FILTER_VALIDATE_INT
    ),
    "submit_nomessage" => FILTER_SANITIZE_SPECIAL_CHARS,
    "submit_nopremessage" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 0,
            "max_range" => sizeof($noMessages) - 1
        )
    )
));

if (!is_null($postData["id"]) && $postData["id"] !== FALSE) {
    if (!is_null($postData["submit_yes"])) {
        // Screenshot passes.
        $score = 0;
        $gradeMessage = "";
        if (!is_null($postData["score"]) && $postData["score"] !== FALSE) {
            $score = $postData["score"];
            $gradeMessage = number_format($score);
        }
        
        $q = "UPDATE QuestSubmissions SET submit_time_grader_id=?, submit_ign_grader_id=?, submit_mode_grader_id=?, submit_req_grader_id=?, submit_last_grade_time=?, submit_battle_score=?, submit_grade_message=?, submit_grade_status=2 WHERE submit_id=?";
        $db -> prepareAndExecute($q, $user -> getId(), $user -> getId(), $user -> getId(), $user -> getId(), time(), $score, $gradeMessage, $postData["id"]);
        
        $subInfo = $db -> prepareAndExecute("SELECT QuestSubmissions.submit_account_id, QuestRequirements.req_week_number FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id=QuestRequirements.req_id WHERE submit_id=?", $postData["id"]) -> fetch();
        $userId = $subInfo["submit_account_id"];
        
        // See if the user already has a notification for an accepted screenshot.
        $notif = $db -> prepareAndExecute("SELECT * FROM Notifications WHERE account_id=? AND notif_type=?", $userId, "questaccepted_week" . $subInfo["req_week_number"]) -> fetch();
        
        $count = 0;
        if ($notif !== FALSE) {
            $count = $notif["notif_data1"];
        }
        
        $notifMessage = 'A quest screenshot you submitted was accepted.  <a href="/quests/submissions/">Go to Submissions Page</a>';
        if ($count > 0) {
            $notifMessage = sprintf('%d quest screenshots you submitted were accepted.  <a href="/quests/submissions/">Go to Submissions Page</a>', $count + 1);
        }
        
        // Delete any previous notifications.
        $db -> prepareAndExecute("DELETE FROM Notifications WHERE account_id=? AND notif_type=?", $userId, "questaccepted_week" . $subInfo["req_week_number"]);
        $audifan -> getNotificationManager() -> addDatabaseNotification($notifMessage, $userId, "questaccepted_week" . $subInfo["req_week_number"], array($count + 1));
    } elseif (!is_null($postData["submit_no"])) {
        $message = "";
        
        if (!is_null($postData["submit_nomessage"]) && $postData["submit_nomessage"] != "") {
            $message = $postData["submit_nomessage"];
        } elseif (!is_null($postData["submit_nopremessage"]) && $postData["submit_nopremessage"] !== FALSE) {
            $message = $noMessages[ $postData["submit_nopremessage"] ];
        }
        
        $q = "UPDATE QuestSubmissions SET submit_time_grader_id=?, submit_ign_grader_id=?, submit_mode_grader_id=?, submit_req_grader_id=?, submit_grade_message=?, submit_grade_status=1 WHERE submit_id=?";
        $db -> prepareAndExecute($q, $user -> getId(), $user -> getId(), $user -> getId(), $user -> getId(), $message, $postData["id"]);
        
        $userId = $db -> prepareAndExecute("SELECT submit_account_id FROM QuestSubmissions WHERE submit_id=?", $postData["id"]) -> fetchColumn();
        $audifan -> getNotificationManager() -> addDatabaseNotification('A quest screenshot you submitted was rejected.  See the <a href="/quests/submissions/">submissions page</a> for more info.', $userId, "questgraded");
    } elseif (!is_null($postData["submit_idk"])) {
        $sub = $db -> prepareAndExecute("SELECT * FROM QuestSubmissions WHERE submit_id=?", $postData["id"]) -> fetch();
        $db -> prepareAndExecute("UPDATE QuestSubmissions SET submit_exclude_graders = CONCAT_WS(',',submit_exclude_graders,?) WHERE submit_id=?", $user -> getId(), $sub["submit_id"]);
    }
}

$viewData["template"] = "quests/grader/grade.twig";

// Get a random screenshot to grade.
$q  = "SELECT QuestSubmissions.*, QuestRequirements.*, QuestIGNs.* FROM QuestSubmissions ";
$q .= "LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id ";
$q .= "LEFT JOIN QuestIGNs ON QuestSubmissions.submit_ign_id = QuestIGNs.ign_id ";
$q .= "WHERE QuestSubmissions.submit_grade_status = 0 ";
$q .= "AND QuestSubmissions.submit_account_id != ? ";
$q .= "ORDER BY RAND()";

$sub = NULL;

// Determine if the grader has completed the battle quest for this week.
$now = $audifan -> getNow();
$completedBq = ($user -> isAdmin() || $db -> prepareAndExecute("SELECT QuestSubmissions.* FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id=QuestRequirements.req_id WHERE QuestRequirements.req_difficulty=6 AND QuestSubmissions.submit_grade_status=2 AND QuestRequirements.req_week_number=? AND QuestRequirements.req_year=? AND QuestSubmissions.submit_account_id=?", $now -> getWeekNumber(), $now -> getWeekYear(), $user -> getId()) -> fetch() !== FALSE);

foreach ($db -> prepareAndExecute($q, $user -> getId()) as $possibleSub) {
    if ($possibleSub["submit_exclude_graders"] != "" && in_array($user -> getId(), explode(",", $possibleSub["submit_exclude_graders"])))
        continue; // Skip if they're an excluded grader.
    
    if ($possibleSub["req_difficulty"] == 6 && $possibleSub["req_week_number"] == $now -> getWeekNumber() && $possibleSub["req_year"] == $now -> getWeekYear() && !$completedBq)
        continue; // Skip if it's a screenshot from this week's battle quest and the grader hasn't completed this week's battle quest.
    
    $sub = $possibleSub;
    break;
}

$context["sub"] = $sub;
$context["noMessages"] = $noMessages;

if (!is_null($sub)) {
    $imgData = explode(";", $sub["submit_screenshot"]);
    $imgUrl = "";
    
    if ($imgData[0] == "shack")
        $imgUrl = sprintf("http://imagizer.imageshack.com/img%d/%d/%s.jpg", $imgData[1], $imgData[2], $imgData[3]);
    elseif ($imgData[0] == "local")
        $imgUrl = sprintf($audifan -> getConfigVar("staticUrl") . "/img/questsubmissions/%s.jpg", $imgData[1]);
    
    $context["imgData"] = $imgData;
    $context["imgUrl"] = $imgUrl;
}
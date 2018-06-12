<?php

/* @var $audifan Audifan */

$user = $audifan -> getUser();
$db = $audifan -> getDatabase();

if (!$user -> isMod()) {
    header("Location: /quests/");
    exit;
}

$getData = filter_input_array(INPUT_GET, array(
    "do" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^ungrade$/"
        )
    ),
    "id" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    )
));

if (!is_null($getData["do"]) && $getData["do"] !== FALSE && !is_null($getData["id"]) && $getData["id"] !== FALSE) {
    $q  = "UPDATE QuestSubmissions ";
    $q .= "SET submit_grade_status=0, submit_grade_message='', ";
    $q .= "submit_exclude_graders=CONCAT_WS(',',submit_exclude_graders,submit_time_grader_id,?) ";
    $q .= "WHERE submit_id=? AND submit_grade_status=1";
    $db -> prepareAndExecute($q, $user -> getId(), $getData["id"]);
    $audifan -> getNotificationManager() -> addSessionNotification("The screenshot was ungraded.");
    header("Location: /quests/grader/");
    exit;
}

$viewData["template"] = "quests/grader/index.twig";

$context["ungradedCount"] = $db -> prepareAndExecute("SELECT * FROM QuestSubmissions WHERE submit_grade_status=0") -> rowCount();

$q  = "SELECT QuestSubmissions.*, QuestRequirements.* ";
$q .= "FROM QuestSubmissions ";
$q .= "LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id=QuestRequirements.req_id ";
$q .= "WHERE QuestSubmissions.submit_grade_status=1 ";
$q .= "ORDER BY QuestSubmissions.submit_id DESC";
$context["rejected"] = $db -> prepareAndExecute($q) -> fetchAll();

for ($i = 0; $i < sizeof($context["rejected"]); $i++) {
    $r = $context["rejected"][$i];
    
    $context["rejected"][$i]["imgData"] = explode(";", $r["submit_screenshot"]);
}

$context["diffs"] = array("Easy", "Medium", "Hard", "Insane");
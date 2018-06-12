<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();
$user = $audifan -> getUser();

$viewData["template"] = "quests/submissions.twig";

$context["diffs"] = array("Easy", "Medium", "Hard", "Insane", "Group", "Battle");
$context["statuses"] = array("Pending", "Rejected", "Complete");

if ($user -> isLoggedIn()) {
    $deleteId = filter_input(INPUT_GET, "delete", FILTER_VALIDATE_INT);
    if ($deleteId != NULL && $deleteId != FALSE) {
        $stmt = $db -> prepareAndExecute("SELECT * FROM QuestSubmissions WHERE submit_id=?", $deleteId);
        if ($stmt -> rowCount() == 1) {
            $sub = $stmt -> fetch();
            if ($sub["submit_account_id"] == $user -> getId()) {
                $db -> prepareAndExecute("DELETE FROM QuestSubmissions WHERE submit_id=?", $sub["submit_id"]);
            }
        }

        header("Location: /quests/submissions/");
        exit;
    }

    $user -> updateSession();
    
    $query = "SELECT QuestSubmissions.*, QuestRequirements.*, QuestIGNs.* FROM QuestSubmissions ";
    $query .= "LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id ";
    $query .= "LEFT JOIN QuestIGNs ON QuestSubmissions.submit_ign_id = QuestIGNs.ign_id ";
    $query .= "WHERE QuestSubmissions.submit_account_id=? ORDER BY QuestSubmissions.submit_time DESC LIMIT 50";
    $context["submissions"] = $db -> prepareAndExecute($query, $user -> getId()) -> fetchAll();
    
    for ($i = 0; $i < sizeof($context["submissions"]); $i++) {
        $imgData = explode(";", $context["submissions"][$i]["submit_screenshot"]);
        $imgUrl = "";
        if ($imgData[0] == "shack")
            $imgUrl = sprintf("http://imagizer.imageshack.com/img%d/%d/%s.jpg", $imgData[1], $imgData[2], $imgData[3]);
        elseif ($imgData[0] == "local")
            $imgUrl = sprintf("%s/img/questsubmissions/%s.jpg", $audifan -> getConfigVar("staticUrl"), $imgData[1]);
        $context["submissions"][$i]["imgUrl"] = $imgUrl;
    }
}
<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$profileId = $viewData["urlVariables"][1];
$otherId = $viewData["urlVariables"][2];

$profileBasicInfo = $db -> prepareAndExecute("SELECT id, display_name, allow_comments FROM Accounts WHERE id=?", $profileId) -> fetch();
$otherBasicInfo = $db -> prepareAndExecute("SELECT id, display_name, allow_comments FROM Accounts WHERE id=?", $otherId) -> fetch();

if ($profileBasicInfo === FALSE || $otherBasicInfo === FALSE)
    return;

$postFilter = filter_input_array(INPUT_POST, array(
    "submit_newcomment" => FILTER_DEFAULT,
    "private" => FILTER_DEFAULT,
    "comment" => FILTER_DEFAULT
));

$getFilter = filter_input_array(INPUT_GET, array(
    "page" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    ),
    "compose" => FILTER_DEFAULT,
    "private" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^(0|1)$/"
        )
    )
));

// Process new comment.
if ($audifan -> getUser() -> isLoggedIn() && !is_null($postFilter["submit_newcomment"])) {
    if (!is_null($postFilter["comment"])) {
        // Add the comment.
        
        // Audifan (profile) conversation with tr848 (other).  Audifan is logged in, so it's to tr848 (other).
        // tr848 (profile) conversation with Audifan (other). Audifan is logged in, so it's to tr848 (profile).
        // Audifan (profile and other) status updates.  It's to Audifan (doesn't matter if profile or other).
        $toId = ($audifan -> getUser() -> getId() == $profileBasicInfo["id"]) ? $otherBasicInfo["id"] : $profileBasicInfo["id"];
        
        $db -> prepareAndExecute("INSERT INTO ProfileComments(to_id,from_id,comment,time,been_read,private) VALUES(?,?,?,?,?,?)", $toId, $audifan -> getUser() -> getId(), $audifan -> prepareBBCode($postFilter["comment"]), time(), 0, is_null($postFilter["private"]) ? 0 : 1);

        // Add a notification for the recipient, if it is not a status update.
        if ($profileBasicInfo["id"] != $audifan -> getUser() -> getId()) {
            $addedNotif = false;

            // Get current profile comment notifications.
            /* $currNotifs = $db -> prepareAndExecute("SELECT * FROM Notifications WHERE account_id=? AND notif_type='newcomment'", $profileBasicInfo["id"]) -> fetchAll();
              $addedNotif = false;
              if (sizeof($currNotifs) > 0) {
              // Combine notifications, if needed.
              $commentors = array();
              foreach ($currNotifs as $notif) {
              for ($i = 1; $i <= 7; $i++) {
              if ($notif["notif_data" . $i] != "") {
              array_push($commentors, explode(";", $notif["notif_data" . $i]));
              } else
              break;
              }
              }

              $numCommentors = sizeof($commentors);

              // Combine only if there's at least one commentor and make sure if
              // there's only 1 commentor, that the person isn't the same person as who is adding a comment now.
              if ($numCommentors > 0 && !($numCommentors == 1 && $commentors[0][0] == $audifan -> getUser() -> getId())) {
              $html = '';

              if ($numCommentors == 1) {
              $html  = sprintf('<a href="/community/profile/%d/">%s</a>', $audifan -> getUser() -> getId(), $audifan -> getUser() -> getNickname());
              $html .= ' and ';
              $html .= sprintf('<a href="/community/profile/%d/">%s</a>', $commentors[0][0], $commentors[0][1]);
              } else {

              }

              $html .= ' left comments on your profile.';

              // Delete the current notification(s).

              // Add the new combined one.
              }
              } */

            if (!$addedNotif) {
                // Add a standalone notification.
                $audifan -> getNotificationManager() -> addDatabaseNotification(sprintf('<a href="/community/profile/%d/">%s</a> left a <a href="/community/profile/%d/conversation/%1$d/">comment</a> on your profile.', $audifan -> getUser() -> getId(), $audifan -> getUser() -> getNickname(), $profileBasicInfo["id"]), $profileBasicInfo["id"], "newcomment", array(
                    sprintf("%d;%s", $audifan -> getUser() -> getId(), $audifan -> getUser() -> getNickname())
                ));
            }
        }
    } else {
        $context["writingComment"] = filter_input(INPUT_POST, "comment");
        array_push($context["GLOBAL"]["messages"]["error"], "Your comment must have at least 10 characters.");
    }
}



$context["page"] = (!is_null($getFilter["page"]) && $getFilter["page"] !== FALSE) ? $getFilter["page"] : 1;
$context["compose"] = !is_null($getFilter["compose"]);
$context["private"] = (!is_null($getFilter["private"]) && $getFilter["private"] !== FALSE && $getFilter["private"] === "1");

$viewData["template"] = "community/profile/conversation.twig";

$context["selfComments"] = ($profileId == $otherId);
$context["profileBasicInfo"] = $profileBasicInfo;
$context["otherBasicInfo"] = $otherBasicInfo;
$context["selfComments"] = ($profileId == $otherId);

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
$q .= "WHERE (ProfileComments.to_id=? AND ProfileComments.from_id=?) OR (ProfileComments.to_id=? AND ProfileComments.from_id=?) ";
$q .= "ORDER BY ProfileComments.time DESC ";
$q .= "LIMIT 20 OFFSET ?";
$context["comments"] = $db -> prepareAndExecute($q, time(), $profileId, $otherId, $otherId, $profileId, 20 * ($context["page"] - 1)) -> fetchAll();
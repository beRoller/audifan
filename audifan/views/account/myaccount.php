<?php

require_once $audifan -> getConfigVar("libsLocation") . "/Facebook/autoload.php";

/* @var $audifan Audifan */

$user = $audifan -> getUser();

if (!$user -> isLoggedIn())
    return;

$viewData["template"] = "account/myaccount.twig";
$db = $audifan -> getDatabase();

$basicInfo = $db -> prepareAndExecute("SELECT * FROM Accounts WHERE id=?", $user -> getId()) -> fetch();

$context["fbid"] = $basicInfo["fbid"];

$context["email"] = $basicInfo["email"];
$context["canChangeName"] = ($basicInfo["last_name_change"] == 0 || ((time() - $basicInfo["last_name_change"]) >= ($audifan -> getConfigVar("nameChangeDays") * 3600 * 24)));
$context["lastNameChange"] = $basicInfo["last_name_change"];
$context["invisible"] = $db -> prepareAndExecute("SELECT invisible FROM AccountFlags WHERE account_id=?", $user -> getId()) -> fetchColumn();
$context["language"] = $user -> getFlag("language_filter");

$postFilter = filter_input_array(INPUT_POST, array(
    "submit_password" => FILTER_DEFAULT,
    "currentpassword" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^.{6,}$/"
        )
    ),
    "newpassword" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^.{6,}$/"
        )
    ),
    "newpassword2" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^.{6,}$/"
        )
    ),
    
    "submit_nickname" => FILTER_DEFAULT,
    "nickname" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^[A-Za-z0-9\-\_\~]{2,20}$/"
        )
    ),
    
    "submit_copy" => FILTER_DEFAULT
));

if (!is_null($postFilter["submit_copy"])) {
    // Copy account to beta site.
    
    /*$betaDb = new Database(array_merge($_CONFIG, [
        "dbDatabase" => "audition_audifanbeta"
    ]));
    
    $copyFields = [
        //"TableName" => [
        //    "multi" => true/false if multiple rows associated with the id are to be copied.
        //    "id" => "id" the name of the account ID field in the table.
        //    "fields => [] the fields to copy over.
        //]
        "Accounts" => [
            "multi" => false,
            "id" => "id",
            "fields" => ["id", "email", "password", "fbid", "account_type", "join_time", "unban_time", "display_name", "last_name_change", "profile_modules", "profile_pic_type", "favorite_songs", "about_me", "allow_comments", "coin_balance", "coin_total"]
        ],
        "AccountFlags" => [
            "multi" => false,
            "id" => "account_id",
            "fields" => ["account_id", "vip_count_last_week", "vip_count_last_day", "vip_count_days", "last_hb_spin", "garden_notif", "garden_notifvol", "invisible", "language_filter"]
        ],
        "QuestData" => [
            "multi" => false,
            "id" => "data_account_id",
            "fields" => ["data_account_id","data_first_submission_time","data_easy_count","data_medium_count","data_hard_count","data_insane_count","data_group_count","data_battle_count","data_easy_points","data_medium_points","data_hard_points","data_insane_points","data_group_points","data_battle_points","data_normal_points","data_onetwo_points","data_beatup_points","data_beatrush_points","data_guitar_points"]
        ],
        "AccountStuff" => [
            "multi" => true,
            "id" => "account_id",
            "fields" => ["account_id","item_id","expire_time","charges","in_use","note"]
        ],
        "Characters" => [
            "multi" => true,
            "id" => "account",
            "fields" => ["account","ign","gender","level","exp","couple","couple_level","ring","hearts","story_medal","story_medal2","team1","team2","team_title","fam","fam_member_type","tourn_expert","tourn_beatup","tourn_beatrush","tourn_guitar","tourn_team","tourn_couple","tourn_ballroom","diary","guitar_ctrlr","mission_n","mission_b","mission_o","mission_r","mission_h"]
        ],
        "CoinHistory" => [
            "multi" => true,
            "id" => "account_id",
            "fields" => ["account_id","history_amount","history_bonus","history_source","history_time"]
        ],
        "Megaphones" => [
            "multi" => true,
            "id" => "mega_account",
            "fields" => ["mega_account","mega_text","mega_color","mega_expiretime"]
        ]
    ];
    
    foreach ($copyFields as $tableName => $info) {
        $q = "SELECT * FROM " . $tableName;
        
        if ($info["id"]) {
            // Delete existing data.
            $betaDb -> prepareAndExecute("DELETE FROM " . $tableName . " WHERE " . $info["id"] . "=" . $user -> getId());
            
            // Append ID filter to query.
            $q .= " WHERE " . $info["id"] . "=" . $user -> getId();
        }
        
        $rows = [];
        
        if ($info["multi"]) {
            $rows = $db -> prepareAndExecute($q) -> fetchAll();
        } else {
            $rows[] = $db -> prepareAndExecute($q) -> fetch();
        }
            
        $questionMarks = str_repeat("?,", sizeof($info["fields"]));
        $questionMarks = substr($questionMarks, 0, strlen($questionMarks) - 1);
        
        foreach ($rows as $row) {
            $params = [];
            foreach ($info["fields"] as $field) {
                $params[] = $row[ $field ];
            }
            
            $betaDb -> prepareAndExecuteArray("INSERT INTO " . $tableName . " (" . implode(",", $info["fields"]) . ") VALUES (" . $questionMarks . ")", $params);
        }
    }*/
    
    // Copy profile picture.
    
    
    array_push($context["GLOBAL"]["messages"]["success"], 'Your account data was successfully copied to the BETA site!  You can now log in at beta.audifan.net.');
} elseif (!is_null($postFilter["submit_password"])) {
    if ($postFilter["newpassword"] !== FALSE && $postFilter["newpassword2"] !== FALSE) {
        if ($postFilter["newpassword"] == $postFilter["newpassword2"]) {
            $userInfo = $db -> prepareAndExecute("SELECT * FROM Accounts WHERE id=?", $user -> getId()) -> fetch();
            if ($userInfo !== FALSE) {
                $realCurrentPassword = $userInfo["password"];
                if ($user -> hashPassword($postFilter["currentpassword"], $userInfo["email"]) == $realCurrentPassword) {
                    $user -> changePassword($postFilter["newpassword"]);
                    array_push($context["GLOBAL"]["messages"]["success"], "Your password was successfully changed.");
                } else
                    array_push($context["GLOBAL"]["messages"]["error"], "Your current password was incorrect.");
            }
        } else
            array_push($context["GLOBAL"]["messages"]["error"], "Your passwords did not match.");
    } else
        array_push($context["GLOBAL"]["messages"]["error"], "Your password must be at least 6 characters in length.");
} elseif (!is_null($postFilter["submit_nickname"]) && $context["canChangeName"]) {
    if (!is_null($postFilter["nickname"]) && $postFilter["nickname"] !== FALSE) {
        // Check if nickname is taken.
        $taken = $db -> prepareAndExecute("SELECT id FROM Accounts WHERE display_name=?", $postFilter["nickname"]) -> fetch();
        if ($taken == FALSE) {
            $db -> prepareAndExecute("UPDATE Accounts SET display_name=?, last_name_change=? WHERE id=?", $postFilter["nickname"], time(), $user -> getId());
            $user -> setNickname($postFilter["nickname"]);
            $context["lastNameChange"] = time();
            $context["canChangeName"] = false;
            array_push($context["GLOBAL"]["messages"]["success"], "Your name was successfully changed.");
        } else {
            array_push($context["GLOBAL"]["messages"]["error"], "That nickname is already in use.");
        }
    }
} else {
    $do = filter_input(INPUT_GET, "do");
    
    if ($do == "invisible") {
        // Toggle invisible mode.
        $db -> prepareAndExecute("UPDATE AccountFlags SET invisible=? WHERE account_id=?", $context["invisible"] ? 0 : 1, $user -> getId());
        $audifan -> getNotificationManager() -> removeAllWithType("accountflagupdate");
        $audifan -> getNotificationManager() -> addSessionNotification(sprintf("Invisible mode was turned %s.", $context["invisible"] ? "off" : "on"), "accountflagupdate");
        header("Location: /account/myaccount/");
        exit;
    } elseif ($do == "language") {
        // Toggle language filter.
        $user -> setFlag("language_filter", $context["language"] ? 0 : 1);
        $audifan -> getNotificationManager() -> removeAllWithType("accountflagupdate");
        $audifan -> getNotificationManager() -> addSessionNotification(sprintf("Language filter was turned %s.", $context["language"] ? "off" : "on"), "accountflagupdate");
        header("Location: /account/myaccount/");
        exit;
    }
}
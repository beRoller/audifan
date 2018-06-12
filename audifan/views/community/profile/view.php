<?php

/* @var $audifan Audifan */

$id = $viewData["urlVariables"][1];

/* @var $db Database */
$db = $audifan -> getDatabase();

$TOTALMODULES = 9;

$basicInfo = $db -> prepareAndExecute(
                "SELECT Accounts.id, Accounts.display_name, Accounts.profile_modules, Accounts.about_me, Accounts.favorite_songs, " .
                "Accounts.join_time, Accounts.friends, Accounts.allow_comments, Accounts.main_character, Accounts.steamid, Accounts.profile_pic_type, AccountFlags.invisible, " .
                "VIPRanking.rank AS viprank, (QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS qp, " .
                "QuestRanking.rank_overall AS qrank, Inventories.itemstring " .
                "FROM Accounts " .
                "LEFT JOIN AccountFlags ON Accounts.id = AccountFlags.account_id " .
                "LEFT JOIN VIPRanking ON Accounts.id = VIPRanking.account_id " .
                "LEFT JOIN QuestData ON Accounts.id = QuestData.data_account_id " .
                "LEFT JOIN QuestRanking ON Accounts.id = QuestRanking.rank_account_id " .
                "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS itemstring FROM AccountStuff WHERE (expire_time>? OR expire_time=-1) AND in_use=1 GROUP BY account_id) AS Inventories ON Accounts.id=Inventories.account_id " .
                "WHERE id=?", time(), $id) -> fetch();

if ($basicInfo === FALSE)
    return;

$context["lastActivityTime"] = $db -> prepareAndExecute("SELECT MAX(session_last_activity_time) FROM AccountSessions WHERE session_account=?", $basicInfo["id"]) -> fetchColumn();
$context["onlineNow"] = (time() - $context["lastActivityTime"] <= (60 * 10));

// Helper functions
function goToDefaultEditURL() {
    global $basicInfo;

    header("Location: /community/profile/" . $basicInfo["id"] . "/?edit");
    exit;
}

// Process Steam AJAX request.
if (filter_input(INPUT_GET, "steamidlookup") == "1" && $basicInfo["steamid"] != "") {
    $out = array();

    $gameTime = json_decode(file_get_contents(sprintf(
                            "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=%s&steamid=%s&format=json&include_played_free_games=true", $audifan -> getConfigVar("steamWebApiKey"), $basicInfo["steamid"]
            )), true);
    if ($gameTime != NULL) {
        foreach ($gameTime["response"]["games"] as $g) {
            if ($g["appid"] == $audifan -> getConfigVar("steamAuditionAppId")) {
                $out["playtime"] = $g;
                break;
            }
        }
    }

    // Get online status
    $profile = json_decode(file_get_contents(sprintf(
                            "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s&format=json", $audifan -> getConfigVar("steamWebApiKey"), $basicInfo["steamid"]
            )), true);
    if ($profile != NULL)
        $out["profile"] = $profile["response"]["players"][0];
    print json_encode($out);
    exit;
}

// Process friend request AJAX.
if ($audifan -> getUser() -> isLoggedIn() && filter_input(INPUT_GET, "addfriend") == "1") {
    $out = array(
        "message" => "Friend request sent."
    );

    $MAXFRIENDS = $audifan -> getConfigVar("maxFriends");

    $friends = explode(",", $basicInfo["friends"]);
    $numFriends = sizeof($friends);
    $numRequesting = $db -> prepareAndExecute("SELECT COUNT(*) FROM Requests WHERE fromid=? OR toid=?", $basicInfo["id"], $basicInfo["id"]) -> fetchColumn();
    $isRequesting = ($db -> prepareAndExecute("SELECT COUNT(*) FROM Requests WHERE (fromid=? AND toid=?) OR (fromid=? AND toid=?)", $basicInfo["id"], $audifan -> getUser() -> getId(), $audifan -> getUser() -> getId(), $basicInfo["id"]) -> fetchColumn() > 0);

    $friendCount = $numFriends + $numRequesting;

    if ($audifan -> getUser() -> getId() == $basicInfo["id"]) {
        $out["message"] = "You cannot add yourself as a friend.";
    } else if ($numFriends >= $MAXFRIENDS) {
        $out["message"] = "This person already has the maximum number of friends.";
    } elseif ($isRequesting) {
        $out["message"] = "A friend request is already pending.";
    } elseif (in_array($audifan -> getUser() -> getId(), $friends)) {
        $out["message"] = "You are already friends.";
    } else {
        // Send friend request.
        $db -> prepareAndExecute("INSERT INTO Requests(type, fromid, toid, time) VALUES (?,?,?,?)", "friend", $audifan -> getUser() -> getId(), $basicInfo["id"], time());
        
        // Notify recipient.
        $audifan -> getNotificationManager() -> addDatabaseNotification(sprintf('<a href="/community/profile/%d/">%s</a> sent you a <a href="/community/requests/">friend request</a>.', $audifan -> getUser() -> getId(), $audifan -> getUser() -> getNickname()), $basicInfo["id"], "newfriendrequest", array(
            sprintf("%d;%s", $audifan -> getUser() -> getId(), $audifan -> getUser() -> getNickname())
        ));
    }

    print json_encode($out);
    exit;
}


$viewData["template"] = "community/profile/view.twig";

// Determine if own profile and/or if the profile is in Edit Mode.
$context["ownProfile"] = ($id == $audifan -> getUser() -> getId()); // If they're viewing their own profile.
$inputGet = filter_input_array(INPUT_GET, array(
    "edit" => FILTER_DEFAULT
        ));
$context["editMode"] = (!is_null($inputGet["edit"]) && $context["ownProfile"]);
$context["isFriend"] = in_array($audifan -> getUser() -> getId(), explode(",", $basicInfo["friends"]));

// Process submitted form data.
$postFilter = filter_input_array(INPUT_POST, array(
    "submit_songs" => FILTER_DEFAULT,
    "submit_newpicture" => FILTER_DEFAULT,
    "submit_allowcomments" => FILTER_DEFAULT,
    "submit_aboutme" => FILTER_DEFAULT
));

if ($context["editMode"] && !is_null($postFilter["submit_songs"]) && $context["ownProfile"]) {
    // Delete songs.
    $favoriteSongs = explode(",", $basicInfo["favorite_songs"]);
    $beforeNum = sizeof($favoriteSongs);

    for ($i = 0; $i < sizeof($favoriteSongs); $i++) {
        $curr = $favoriteSongs[$i];
        if (!is_null(filter_input(INPUT_POST, "song_" . $curr))) {
            array_splice($favoriteSongs, $i, 1);
            $i--;
        }
    }

    $afterNum = sizeof($favoriteSongs);

    $basicInfo["favorite_songs"] = implode(",", $favoriteSongs);
    $db -> prepareAndExecute("UPDATE Accounts SET favorite_songs=? WHERE id=?", $basicInfo["favorite_songs"], $basicInfo["id"]);

    $diff = $beforeNum - $afterNum;
    if ($diff > 0)
        array_push($context["GLOBAL"]["messages"]["success"], "Successfully deleted " . $diff . " song" . ($diff > 1 ? "s" : "") . ".");
} elseif ($context["editMode"] && !is_null($postFilter["submit_newpicture"]) && isset($_FILES["pic"])) {
    // Change profile picture.
    if ($_FILES["pic"]["error"] != UPLOAD_ERR_OK) {
        switch ($_FILES["pic"]["error"]) {
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                $lastError = "Server error #{$_FILES["pic"]["error"]} occurred. This should be a temporary error, but if it keeps occurring, please report it to us.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                $lastError = "The picture you uploaded was too big.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $lastError = "Please specify a picture to upload.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $lastError = "An error occurred and the picture was only partially uploaded. Please try again.";
                break;
            default:
                $lastError = "An unknown error occurred. Please try again.";
        }
        array_push($context["GLOBAL"]["messages"]["error"], $lastError);
    } else {
        /*
         * [0] = width
         * [1] = height
         * [2] = IMAGETYPE_???
         * [channels] = 3 for RGB, 4 for CMYK
         * [bits] = bits for each color
         */
        $info = getimagesize($_FILES["pic"]["tmp_name"]);

        if ($info[2] == IMAGETYPE_JPEG || $info[2] == IMAGETYPE_PNG || $info[2] == IMAGETYPE_GIF) {
            $width = $info[0];
            $height = $info[1];

            $ext = "png";
            if ($info[2] == IMAGETYPE_JPEG)
                $ext = "jpg";
            elseif ($info[2] == IMAGETYPE_GIF)
                $ext = "gif";

            // Resize it if it is too big.
            $newWidth = $newheight = 0;
            if ($width > 200 || $height > 200) {
                if ($width > $height) {
                    $newWidth = 200;
                    $scalePercent = 1 - (($width - $newWidth) / $width);
                    $newHeight = round($height * $scalePercent);
                } elseif ($height > $width) {
                    $newHeight = 200;
                    $scalePercent = 1 - (($height - $newHeight) / $height);
                    $newWidth = round($width * $scalePercent);
                } else
                    $newHeight = $newWidth = 200;

                $src;
                if ($info[2] == IMAGETYPE_JPEG)
                    $src = imagecreatefromjpeg($_FILES["pic"]["tmp_name"]);
                elseif ($info[2] == IMAGETYPE_GIF)
                    $src = imagecreatefromgif($_FILES["pic"]["tmp_name"]);
                else
                    $src = imagecreatefrompng($_FILES["pic"]["tmp_name"]);

                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                if ($info[2] == IMAGETYPE_JPEG)
                    imagejpeg($dst, $audifan -> getConfigVar("localPublicLocation") . "/static/img/profilepictures/" . $id . ".jpg");
                elseif ($info[2] == IMAGETYPE_GIF)
                    imagegif($dst, $audifan -> getConfigVar("localPublicLocation") . "/static/img/profilepictures/" . $id . ".gif");
                else
                    imagepng($dst, $audifan -> getConfigVar("localPublicLocation") . "/static/img/profilepictures/" . $id . ".png");
                
                imagedestroy($src);
                imagedestroy($dst);
            } else {
                move_uploaded_file($_FILES["pic"]["tmp_name"], $audifan -> getConfigVar("localPublicLocation") . "/static/img/profilepictures/" . $id . "." . $ext);
            }

            $db -> prepareAndExecute("UPDATE Accounts SET profile_pic_type=? WHERE id=?", $ext, $id);
            $basicInfo["profile_pic_type"] = $ext;
        } else
            array_push($context["GLOBAL"]["messages"]["error"], "Your picture must be in either JPEG, GIF, or PNG format.");
    }
} elseif ($context["editMode"] && !is_null($postFilter["submit_allowcomments"])) {
    $newVal = !is_null(filter_input(INPUT_POST, "allow")) ? 1 : 0;
    $db -> prepareAndExecute("UPDATE Accounts SET allow_comments=? WHERE id=?", $newVal, $basicInfo["id"]);
    $basicInfo["allow_comments"] = $newVal;
    array_push($context["GLOBAL"]["messages"]["success"], "Your comment settings have been saved.");
} elseif ($context["editMode"] && !is_null($postFilter["submit_aboutme"])) {
    $newAboutMe = filter_input(INPUT_POST, "aboutme");
    $db -> prepareAndExecute("UPDATE Accounts SET about_me=? WHERE id=?", $newAboutMe, $basicInfo["id"]);
    $basicInfo["about_me"] = $newAboutMe;
    array_push($context["GLOBAL"]["messages"]["success"], "Your About Me was saved.");
}


$context["basicInfo"] = $basicInfo;

// Get module lists.
$moduleList = explode(";", $basicInfo["profile_modules"]);
$context["leftModules"] = explode(",", $moduleList[0]);
if ($context["leftModules"][0] == "-1")
    $context["leftModules"] = array();
$context["rightModules"] = explode(",", $moduleList[1]);
if ($context["rightModules"][0] == "-1")
    $context["rightModules"] = array();

// Convert strings to ints.
array_walk($context["leftModules"], function(&$val) {
    $val = (int) $val;
});
array_walk($context["rightModules"], function(&$val) {
    $val = (int) $val;
});

if ($context["editMode"]) {
    // Profile is in edit mode.
    $context["hiddenModules"] = array();
    for ($i = 0; $i <= $TOTALMODULES - 1; $i++) {
        if (!in_array($i, $context["leftModules"]) && !in_array($i, $context["rightModules"])) {
            array_push($context["hiddenModules"], $i);
        }
    }

    // Process link "do" actions.
    $doFilter = filter_input_array(INPUT_GET, array(
        "do" => array(
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => array(
                "regexp" => "/^hide|(move(other|down|up))|(add(right|left))$/"
            )
        ),
        "module" => array(
            "filter" => FILTER_VALIDATE_INT,
            "options" => array(
                "min_range" => 0,
                "max_range" => $TOTALMODULES - 1
            )
        )
    ));

    if (!is_null($doFilter["do"]) && $doFilter["do"] !== FALSE && !is_null($doFilter["module"]) && $doFilter["module"] !== FALSE) {
        $modules = "";
        if (in_array($doFilter["module"], $context["leftModules"]))
            $modules = "leftModules";
        elseif (in_array($doFilter["module"], $context["rightModules"]))
            $modules = "rightModules";

        switch ($doFilter["do"]) {
            case "hide":
                if ($modules != "") {
                    $index = array_search($doFilter["module"], $context[$modules]);
                    array_splice($context[$modules], $index, 1);
                }
                break;

            case "movedown":
                if ($modules != "") {
                    $index = array_search($doFilter["module"], $context[$modules]);
                    if ($index < sizeof($context[$modules]) - 1) {
                        // Switch this module and the one after it.
                        $tmp = $context[$modules][$index + 1];
                        $context[$modules][$index + 1] = $context[$modules][$index];
                        $context[$modules][$index] = $tmp;
                    }
                }
                break;

            case "moveup":
                if ($modules != "") {
                    $index = array_search($doFilter["module"], $context[$modules]);
                    if ($index > 0) {
                        // Switch this module and the one before it.
                        $tmp = $context[$modules][$index - 1];
                        $context[$modules][$index - 1] = $context[$modules][$index];
                        $context[$modules][$index] = $tmp;
                    }
                }
                break;

            case "moveother":
                if ($modules != "") {
                    $index = array_search($doFilter["module"], $context[$modules]);
                    array_splice($context[$modules], $index, 1);
                    
                    array_push($context[ (($modules == "leftModules") ? "rightModules" : "leftModules") ], $doFilter["module"]);
                }
                break;

            case "addleft":
                if ($modules == "")
                    array_push($context["leftModules"], $doFilter["module"]);
                break;

            case "addright":
                if ($modules == "")
                    array_push($context["rightModules"], $doFilter["module"]);
                break;
        }

        $leftString = empty($context["leftModules"]) ? "-1" : implode(",", $context["leftModules"]);
        $rightString = empty($context["rightModules"]) ? "-1" : implode(",", $context["rightModules"]);
        $db -> prepareAndExecute("UPDATE Accounts SET profile_modules=? WHERE id=?", $leftString . ";" . $rightString, $basicInfo["id"]);
        goToDefaultEditURL();
    }

    // Process profile picture deletion
    if (!is_null(filter_input(INPUT_GET, "deletepicture"))) {
        $db -> prepareAndExecute("UPDATE Accounts SET profile_pic_type='' WHERE id=?", $basicInfo["id"]);
        $audifan -> getNotificationManager() -> removeAllWithType("profilepicdeleted");
        $audifan -> getNotificationManager() -> addSessionNotification("Your profile picture was successfully deleted.", "profilepicdeleted");
        goToDefaultEditURL();
    }
}




// Load module data, if the modules are to be shown.
// All data is needed in edit mode.
$characterInfo = array();

function getCharacterInfoIfEmpty() {
    global $characterInfo, $db, $basicInfo;

    if (!empty($characterInfo))
        return;

    $characterInfo = $db -> prepareAndExecute("SELECT * FROM Characters WHERE account=?", $basicInfo["id"]) -> fetchAll();

    // Put main character first.
    if ($basicInfo["main_character"] != 0) {
        $num = sizeof($characterInfo);
        for ($i = 0; $i < $num; $i++) {
            if ($characterInfo[$i]["id"] == $basicInfo["main_character"]) {
                $tmp = $characterInfo[0];
                $characterInfo[0] = $characterInfo[$i];
                $characterInfo[$i] = $tmp;
                break;
            }
        }
    }
}

$context["moduleData"] = array();
if ($context["editMode"] || in_array("0", $context["leftModules"]) || in_array("0", $context["rightModules"])) {
    // My Characters 0
    getCharacterInfoIfEmpty();
    $context["characterInfo"] = $characterInfo;
}
if ($context["editMode"] || in_array("1", $context["leftModules"]) || in_array("1", $context["rightModules"])) {
    // Favorite Songs 1
    if ($basicInfo["favorite_songs"] != "")
        $context["moduleData"][1]["songs"] = $db -> prepareAndExecute("SELECT id, artist, title FROM SongList WHERE id IN (" . $basicInfo["favorite_songs"] . ") ORDER BY artist ASC, title ASC") -> fetchAll();
}
if ($context["editMode"] || in_array("2", $context["leftModules"]) || in_array("2", $context["rightModules"])) {
    // Couple 2
    getCharacterInfoIfEmpty();
    $context["characterInfo"] = $characterInfo;
}
if ($context["editMode"] || in_array("3", $context["leftModules"]) || in_array("3", $context["rightModules"])) {
    // Latest Comments 3
    $q = "SELECT ProfileComments.*, FromAccount.display_name AS from_display_name, FromAccount.profile_pic_type, ";
    $q .= "VIPRanking.rank AS viprank, QuestRanking.rank_overall AS qrank, Inventories.itemstring, ";
    $q .= "Characters.ign, Characters.level, Characters.fam, Characters.fam_member_type, ";
    $q .= "(QuestData.data_easy_points+QuestData.data_medium_points+QuestData.data_hard_points+QuestData.data_insane_points+QuestData.data_group_points+QuestData.data_battle_points) AS qp ";
    $q .= "FROM ProfileComments ";
    $q .= "LEFT JOIN Accounts AS FromAccount ON ProfileComments.from_id=FromAccount.id ";
    $q .= "LEFT JOIN Characters ON FromAccount.main_character=Characters.id ";
    $q .= "LEFT JOIN VIPRanking ON FromAccount.id=VIPRanking.account_id ";
    $q .= "LEFT JOIN QuestData ON FromAccount.id=QuestData.data_account_id ";
    $q .= "LEFT JOIN QuestRanking ON FromAccount.id=QuestRanking.rank_account_id ";
    $q .= "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS itemstring FROM AccountStuff WHERE (expire_time>? OR expire_time=-1) AND in_use=1 GROUP BY account_id) AS Inventories ON FromAccount.id=Inventories.account_id ";
    $q .= "WHERE ProfileComments.to_id=? ";
    $q .= "AND ProfileComments.private=0 ";
    $q .= "ORDER BY ProfileComments.time DESC ";
    $q .= "LIMIT 20";
    $context["moduleData"][3]["comments"] = $db -> prepareAndExecute($q, time(), $basicInfo["id"]) -> fetchAll();
}
if ($context["editMode"] || in_array("4", $context["leftModules"]) || in_array("4", $context["rightModules"])) {
    // About Me 4
    $context["moduleData"][4]["aboutMe"] = $basicInfo["about_me"];
}
if ($context["editMode"] || in_array("5", $context["leftModules"]) || in_array("5", $context["rightModules"])) {
    // Teams 5
    getCharacterInfoIfEmpty();
    $context["characterInfo"] = $characterInfo;
}
if ($context["editMode"] || in_array("6", $context["leftModules"]) || in_array("6", $context["rightModules"])) {
    // Friend 6
    $context["moduleData"][6]["friend"] = $db -> prepareAndExecute("SELECT id, display_name, profile_pic_type FROM Accounts ORDER BY RAND() LIMIT 1") -> fetch();
}
if ($context["editMode"] || in_array("7", $context["leftModules"]) || in_array("7", $context["rightModules"])) {
    // Quest Stats 7
    $context["moduleData"][7]["questData"] = $db -> prepareAndExecute("SELECT * FROM QuestData WHERE data_account_id=?", $basicInfo["id"]) -> fetch();
    $context["moduleData"][7]["questRanks"] = $db -> prepareAndExecute("SELECT * FROM QuestRanking WHERE rank_account_id=?", $basicInfo["id"]) -> fetch();
}
if ($context["editMode"] || in_array("8", $context["leftModules"]) || in_array("8", $context["rightModules"])) {
    // Recent Happy Box Prizes 8
    $context["moduleData"][8]["itemInfo"] = Inventory::$ITEMINFO;
    $context["moduleData"][8]["prizes"] = $db -> prepareAndExecute("SELECT * FROM HappyBoxWinners WHERE account_id=? ORDER BY win_time DESC", $id) -> fetchAll();
}
/*
 * "SELECT Accounts.id, Accounts.display_name, Accounts.profile_modules, Accounts.about_me, Accounts.favorite_songs, " .
        "Accounts.join_time, Accounts.friends, Accounts.allow_comments, Accounts.main_character, Accounts.steamid, " .
        "VIPRanking.rank AS viprank, (QuestData.data_easy_points + QuestData.data_medium_points + QuestData.data_hard_points + QuestData.data_insane_points) AS qp, " .
        "QuestRanking.rank_overall AS qrank, Inventories.itemstring " .
        "FROM Accounts " .
        "LEFT JOIN VIPRanking ON Accounts.id = VIPRanking.account_id " .
        "LEFT JOIN QuestData ON Accounts.id = QuestData.data_account_id " .
        "LEFT JOIN QuestRanking ON Accounts.id = QuestRanking.rank_account_id " .
        "LEFT JOIN (SELECT account_id, GROUP_CONCAT(item_id) AS itemstring FROM AccountStuff WHERE (expire_time>? OR expire_time=-1) GROUP BY account_id) AS Inventories ON Accounts.id=Inventories.account_id " .
        "WHERE id=?",
 */
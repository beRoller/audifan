<?php

/* @var $audifan Audifan */

$viewData["template"] = "quests/submit.twig";

$db = $audifan -> getDatabase();
$now = $audifan -> getNow();
$user = $audifan -> getUser();

// First 623 bytes of an Audition screenshot.
$AUDISCREENSHOTHEXSIG = "ffd8ffe000104a46494600010100000100010000ffdb004300080606070605080707070909080a0c140d0c0b0b0c1912130f141d1a1f1e1d1a1c1" .
        "c20242e2720222c231c1c2837292c30313434341f27393d38323c2e333432ffdb0043010909090c0b0c180d0d1832211c2132323232323232323232323232323232323232323" .
        "23232323232323232323232323232323232323232323232323232323232ffc00011080300040003012200021101031101ffc4001f000001050101010101010000000000000000010" .
        "2030405060708090a0bffc400b5100002010303020403050504040000017d01020300041105122131410613516107227114328191a1082342b1c11552d1f02433627282090a161" .
        "718191a25262728292a3435363738393a434445464748494a535455565758595a636465666768696a737475767778797a838485868788898a92939495969798999aa2a3a4a5a" .
        "6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae1e2e3e4e5e6e7e8e9eaf1f2f3f4f5f6f7f8f9faffc4001f0100030101010101010101010000000" .
        "000000102030405060708090a0bffc400b51100020102040403040705040400010277000102031104052131061241510761711322328108144291a1b1c109233352f0156272d10a1" .
        "62434e125f11718191a262728292a35363738393a434445464748494a535455565758595a636465666768696a737475767778797a82838485868788898a92939495969798999aa2a3a" .
        "4a5a6a7a8a9aab2b3b4b5b6b7b8b9bac2c3c4c5c6c7c8c9cad2d3d4d5d6d7d8d9dae2e3e4e5e6e7e8e9eaf2f3f4f5f6f7f8f9faffda000c03010002110311003f00";

$questRequirements = array();
$questRequirementIds = array();

foreach ($db -> prepareAndExecute("SELECT * FROM QuestRequirements WHERE req_year=? AND req_week_number=?", $now -> getWeekYear(), $now -> getWeekNumber()) as $row) {
    $questRequirements[$row["req_difficulty"]] = $row;
    array_push($questRequirementIds, $row["req_id"]);
}

if (empty($questRequirements)) {
    $audifan -> getNotificationManager() -> removeAllWithType("noquests");
    $audifan -> getNotificationManager() -> addSessionNotification("There are no quests at this time.", "noquests");
    header("Location: /quests/");
    exit;
}

$questCurrentIgnId = 0;
$questCurrentIgn = "";
$questIgns = array();
$questSubmissions = array();

function updateQuestInfo() {
    global $questCurrentIgnId, $questCurrentIgn, $questIgns, $questSubmissions, $db, $user, $audifan;

    $stmt = $db -> prepareAndExecute("SELECT data_current_ign_id FROM QuestData WHERE data_account_id=?", $user -> getId());
    if ($stmt -> rowCount() == 0) {
        // They don't have quest data, which should have been set on the quest index page.
        // Redirect them to the quest data page with an error notification.
        // It should be set once they get there.
        $audifan -> getNotificationManager() -> addSessionNotification("There was a problem.  Please try submitting your screenshot again.  If this message keeps appearing, please contact us.");
        header("Location: /quests/");
        exit;
    } else {
        $ignId = $stmt -> fetchColumn();
        $stmt = $db -> prepareAndExecute("SELECT ign_ign FROM QuestIGNs WHERE ign_id=?", $ignId);
        if ($stmt -> rowCount() != 0) {
            $questCurrentIgnId = $ignId;
            $questCurrentIgn = $stmt -> fetchColumn();
        }
    }

    foreach ($db -> prepareAndExecute("SELECT ign_id, ign_ign FROM QuestIGNs WHERE ign_account_id=?", $user -> getId()) as $row)
        $questIgns[$row["ign_id"]] = $row["ign_ign"];

    $q = "SELECT QuestSubmissions.*, QuestRequirements.req_difficulty ";
    $q .= "FROM QuestSubmissions ";
    $q .= "LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id ";
    $q .= "WHERE QuestSubmissions.submit_account_id=? AND QuestRequirements.req_year=? AND QuestRequirements.req_week_number=? ";
    $q .= "ORDER BY QuestSubmissions.submit_time ASC";
    $questSubmissions = $db -> prepareAndExecute($q, $user -> getId(), $audifan -> getNow() -> getWeekYear(), $audifan -> getNow() -> getWeekNumber()) -> fetchAll();
}

$lastError = "";
$successMessage = "";

$pageState = "";

if ($now -> getDayNumberOfWeek() == 7 && $now -> getHour() == 23 && $now -> getMinute() >= 50)
    $pageState = "submissionsclosed";
elseif ($user -> isLoggedIn()) {
    updateQuestInfo();

    if (!is_null(filter_input(INPUT_POST, "submit_ign"))) {
        $pageState = "noign";
        $newIgn = filter_input(INPUT_POST, "newign", FILTER_SANITIZE_SPECIAL_CHARS);
        $prevIgn = filter_input(INPUT_POST, "prevign", FILTER_VALIDATE_INT);

        if (!is_null($prevIgn) && $prevIgn !== FALSE) {
            if (in_array($prevIgn, array_keys($questIgns))) {
                $db -> prepareAndExecute("UPDATE QuestData SET data_current_ign_id=? WHERE data_account_id=?", $prevIgn, $user -> getId());
                updateQuestInfo();
                if ($questCurrentIgn != "")
                    $pageState = "";
            } else
                $lastError = "That previous IGN isn't tied to your account.";
        } elseif ($newIgn !== FALSE && preg_match("/^[A-Za-z0-9\-\~\_]{2,30}$/", $newIgn)) {
            $canUseIgn = false;

            // Search for IGN.
            $stmt = $db -> prepareAndExecute("SELECT * FROM QuestIGNs WHERE ign_ign=?", $newIgn);
            if ($stmt -> rowCount() > 0) {
                if ($stmt -> rowCount() > 1) {
                    // If there's more than 1 instance, that's a problem!
                    $lastError = "This IGN cannot be used.";
                } else {
                    // One instance of the IGN exists.
                    $row = $stmt -> fetch();
                    $ninetyDaysAgo = time() - (3600 * 24 * 90);

                    if ($row["ign_time_added"] < $ninetyDaysAgo && ($row["ign_last_time_used"] == 0 || $row["ign_last_time_used"] < $ninetyDaysAgo)) {
                        // IGN has either never submitted a screenshot, or the last one they submitted was more than 90 days ago.
                        // Delete it.
                        $db -> prepareAndExecute("DELETE FROM QuestIGNs WHERE ign_id=?", $row["ign_id"]);
                        $canUseIgn = true;
                    } else {
                        $lastError = "This IGN was used in the past 90 days by someone else and cannot be used.";
                        if (!empty($questIgns))
                            $lastError .= "<br />If this is your IGN, please be sure your IGN isn't listed in your previously used IGNs below.";
                        $lastError .= "<br />If someone else is using your IGN, please contact us.";
                    }
                }
            } else
                $canUseIgn = true;

            if ($canUseIgn) {
                $db -> prepareAndExecute("INSERT INTO QuestIGNs(ign_account_id, ign_ign, ign_time_added) VALUES(?,?,?)", $user -> getId(), $newIgn, time());
                $db -> prepareAndExecute("UPDATE QuestData SET data_current_ign_id=? WHERE data_account_id=?", $db -> lastInsertId(), $user -> getId());
                updateQuestInfo();
                if ($questCurrentIgn != "")
                    $pageState = "";
            }
        } else
            $lastError = "Please enter a valid IGN.";
    } elseif ($questCurrentIgn == "") {
        $pageState = "noign";
    } elseif (!is_null(filter_input(INPUT_POST, "submit_change_ign"))) {
        if (0 == count($questSubmissions)) {
            // OK to change IGN.
            $db -> prepareAndExecute("UPDATE QuestData SET data_current_ign_id=0 WHERE data_account_id=?", $user -> getId());
            updateQuestInfo();
            $successMessage = "Your IGN has been reset.";
            $pageState = "noign";
        } else {
            // Cannot change IGN.
            $lastError = "Your IGN cannot be changed.";
        }
    } elseif (isset($_FILES["screenshot"])) {


        // Screenshot submission.

        if (!is_null(filter_input(INPUT_POST, "req"))) {
            $reqId = filter_input(INPUT_POST, "req", FILTER_VALIDATE_INT);

            if (!is_null($reqId) && $reqId !== FALSE && in_array($reqId, $questRequirementIds)) {
                if ($_FILES["screenshot"]["error"] != UPLOAD_ERR_OK) {
                    switch ($_FILES["screenshot"]["error"]) {
                        case UPLOAD_ERR_NO_TMP_DIR:
                        case UPLOAD_ERR_CANT_WRITE:
                        case UPLOAD_ERR_EXTENSION:
                            $lastError = "Server error #{$_FILES["screenshot"]["error"]} occurred. This should be a temporary error, but if it keeps occurring, please report it to us.";
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                        case UPLOAD_ERR_INI_SIZE:
                            $lastError = "The screenshot you uploaded was too big.";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $lastError = "Please specify a screenshot to upload.";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $lastError = "An error occurred and the screenshot was only partially uploaded. Please try again.";
                            break;
                        default:
                            $lastError = "An unknown error occurred. Please try again.";
                    }
                } elseif ($db -> prepareAndExecute("SELECT COUNT(*) FROM QuestSubmissions WHERE submit_req_id=? AND submit_account_id=? AND submit_grade_status!=1", $reqId, $user -> getId()) -> fetchColumn() != 0) { // Make sure they haven't already submitted a screenshot.
                    $lastError = "You have already submitted a screenshot for this quest.";
                } elseif ($questRequirements[1]["req_id"] == $reqId && in_array($questRequirements[1]["req_number"], array(8, 12))) {
                    // Check if it's a quest that doesn't need a screenshot. Easy #8 is the HB quest, Easy #12 is the "log in 4 days this week" quest.
                    $lastError = "This quest does not require a screenshot.";
                } else {
                    // Process screenshot
                    $badImageError = "The image you uploaded is not a valid screenshot from Audition's \"screenshot\" folder.";

                    if ($_FILES["screenshot"]["type"] == "image/jpeg") {
                        /*
                         * [0] = width
                         * [1] = height
                         * [2] = IMAGETYPE_???
                         * [channels] = 3 for RGB, 4 for CMYK
                         * [bits] = bits for each color
                         */
                        $info = getimagesize($_FILES["screenshot"]["tmp_name"]);
                        if ($info !== FALSE && ($info[2] == IMAGETYPE_JPEG && $info[0] == 1024 && $info[1] = 768) && ($info["channels"] == 3 && $info["bits"] == 8)) {
                            $fh = fopen($_FILES["screenshot"]["tmp_name"], 'r');
                            $hexSig = bin2hex(fread($fh, 623));
                            if ($hexSig == $AUDISCREENSHOTHEXSIG) {
                                // IT'S GOOD.  PHEW.
                                // Try to upload it to ImageShack.
                                $imgInfo = "";

                                /* ImageShack is unreliable. Don't use it.
                                  $SHACKVARS = array(
                                  "USER" => "",
                                  "PASSWORD" => "",
                                  "APIKEY" => ""
                                  );

                                  $shackLoginCurl = curl_init();
                                  curl_setopt_array($shackLoginCurl, array(
                                  CURLOPT_URL => "http://api.imageshack.com/v2/user/login",
                                  CURLOPT_POST => true,
                                  CURLOPT_POSTFIELDS => array(
                                  "user" => $SHACKVARS["USER"],
                                  "password" => $SHACKVARS["PASSWORD"],
                                  "set_cookies" => false,
                                  "remember_me" => false,
                                  "api_key" => $SHACKVARS["APIKEY"]
                                  ),
                                  CURLOPT_RETURNTRANSFER => true
                                  ));
                                  $result = curl_exec($shackLoginCurl);

                                  if ($result !== false) {
                                  $resultArray = json_decode($result, true);

                                  if ($resultArray["success"]) {
                                  $auth = $resultArray["result"]["auth_token"];

                                  $shackUploadCurl = curl_init();
                                  curl_setopt_array($shackUploadCurl, array(
                                  CURLOPT_URL => "http://api.imageshack.com/v2/images",
                                  CURLOPT_POST => true,
                                  CURLOPT_POSTFIELDS => array(
                                  "api_key" => $SHACKVARS["APIKEY"],
                                  "auth_token" => $auth,
                                  "file" => "@" . $_FILES["screenshot"]["tmp_name"],
                                  "album" => "Submissions",
                                  "title" => $_SESSION["user"]["display_name"] . " - " . date("r"),
                                  "public" => false,
                                  "comments_disabled" => true
                                  ),
                                  CURLOPT_RETURNTRANSFER => true
                                  ));
                                  $result = curl_exec($shackUploadCurl);

                                  if ($result !== false) {
                                  $resultArray = json_decode($result, true);

                                  if ($resultArray["success"] && $resultArray["result"]["passed"] == 1) {
                                  $imgData = $resultArray["result"]["images"][0];
                                  $imgInfo = sprintf("shack;%d;%d;%s", $imgData["server"], $imgData["bucket"], substr($imgData["filename"], 0, strlen($imgData["filename"]) - 4));
                                  }
                                  }
                                  }
                                  } else
                                  error_log("CURL ERROR: " . curl_error($shackLoginCurl)); */

                                if ($imgInfo == "") {
                                    // ImageShack upload failed.  Store screenshot locally.
                                    $filename = md5($_SESSION["user"]["id"] . "questss" . microtime());
                                    if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], sprintf("%s/static/img/questsubmissions/%s.jpg", $audifan -> getConfigVar("localPublicLocation"), $filename)))
                                        $imgInfo = sprintf("local;%s", $filename);
                                }

                                if ($imgInfo != "") {
                                    $db -> prepareAndExecute("INSERT INTO QuestSubmissions(submit_account_id, submit_req_id, submit_ign_id, submit_time, submit_screenshot) VALUES(?,?,?,?,?)", $user -> getId(), $reqId, $questCurrentIgnId, time(), $imgInfo);
                                    $db -> prepareAndExecute("UPDATE QuestData SET data_first_submission_time=? WHERE data_account_id=? AND data_first_submission_time=0", time(), $user -> getId());
                                    $successMessage = "Your screenshot was submitted! It will be checked as soon as possible.";
                                } else
                                    $lastError = "Your screenshot could not be uploaded for an unknown reason.  Please try again.";
                            } else
                                $lastError = $badImageError;
                        } else
                            $lastError = $badImageError;
                    } else
                        $lastError = $badImageError;
                }
            } else
                $lastError = "That quest does not exist or has expired.";
        } else
            $lastError = "Please select the quest for which you are submitting this screenshot.";
    }
} else
    $pageState = "notloggedin";

if ($user -> isLoggedIn()) {
    $user -> updateSession(); // TODO: updateSession(QUEST DATA ONLY), when that is implemented.
    updateQuestInfo();
}




// Set context variables.
$context["pageState"] = $pageState;
$context["questIgns"] = $questIgns;

if ($lastError != "")
    array_push($context["GLOBAL"]["messages"]["error"], $lastError);
if ($successMessage != "")
    array_push($context["GLOBAL"]["messages"]["success"], $successMessage);

$context["numSubmissions"] = count($questSubmissions);
$context["submittedDifficulties"] = array();
foreach ($questSubmissions as $sub) {
    if (!in_array($sub["req_difficulty"], $context["submittedDifficulties"]) && $sub["submit_grade_status"] != 1)
        array_push($context["submittedDifficulties"], $sub["req_difficulty"]);
}

$context["questCurrentIgn"] = $questCurrentIgn;
$context["diffLabels"] = array("Easy", "Medium", "Hard", "Insane", "Group", "Battle");
$context["questRequirements"] = $questRequirements;


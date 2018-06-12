<?php

/* @var $audifan Audifan */

$viewData["template"] = "quests/grader/admin.twig";

$db = $audifan -> getDatabase();

function matchesBitmask($bits, $mask) {
    return (($bits & $mask) == $mask);
}

$lastError = "";
$successMessage = "";



if (isset($_POST["submit_points"])) {
    // Update points.

    $year = filter_input(INPUT_POST, "year", FILTER_VALIDATE_INT);
    $week = filter_input(INPUT_POST, "week", FILTER_VALIDATE_INT);

    if ($year != NULL && $year != FALSE && $week != NULL && $week != FALSE) {
        // Get the req IDs for this week/year.
        $reqIds = array();

        $QUESTFLAGS = array(
            "normal" => 1,
            "beatup" => 2,
            "onetwo" => 4,
            "beatrush" => 8,
            "guitar" => 32
        );

        $reqTypes = array(
            1 => "",
            2 => "",
            3 => "",
            4 => "",
            5 => ""
        );

        if ($week != date("W")) {
            foreach ($db -> prepareAndExecute("SELECT * FROM QuestRequirements WHERE req_year=? AND req_week_number=?", $year, $week) as $row) {
                array_push($reqIds, $row["req_id"]);

                foreach ($QUESTFLAGS as $k => $v) {
                    if (matchesBitmask($row["req_flags"], $v)) {
                        $reqTypes[$row["req_difficulty"]] = $k;
                        break;
                    }
                }
            }
        } else
            $lastError = "The week isn't over yet!";

        // Check if all have been graded.
        if (!empty($reqIds) && $db -> prepareAndExecute("SELECT * FROM QuestSubmissions WHERE submit_req_id IN (" . implode(",", $reqIds) . ") AND submit_grade_status=0") -> rowCount() == 0) {
            // All have been graded.

            $points = array(
                1 => 2,
                2 => 4,
                3 => 8,
                4 => 12,
                5 => 10
            );

            $creditIds = array(
                1 => array(),
                2 => array(),
                3 => array(),
                4 => array(),
                5 => array(),
                6 => array() // Points are not awarded based on who is in this array like the other ones.  It's just a list of those who participated in the battle quest used for the notification.
            );

            $counts = array(
                1 => 0,
                2 => 0,
                3 => 0,
                4 => 0,
                5 => 0,
                6 => 0
            );
            
            // This is an assoc array of number of individual (easy ~ insane) quests completed with the key being the user's account ID and value being 1 through 4.
            // Used for giving out medals.
            $numIndividualQuestsCompletedById = array();

            // Update Easy, Medium, Hard, Insane, and Group quest points first.
            $stmt = $db -> prepareAndExecute("SELECT QuestSubmissions.*, QuestRequirements.req_difficulty FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id WHERE submit_req_id IN (" . implode(",", $reqIds) . ") AND QuestRequirements.req_difficulty != 6 AND submit_grade_status=2 AND submit_points_given=0");
            if ($stmt -> rowCount() > 0) {
                foreach ($stmt as $row) {
                    array_push($creditIds[$row["req_difficulty"]], $row["submit_account_id"]);
                    if ($row["req_difficulty"] <= 4) {
                        if (!array_key_exists($row["submit_account_id"], $numIndividualQuestsCompletedById)) {
                            $numIndividualQuestsCompletedById[ $row["submit_account_id"] ] = 1;
                        } else {
                            $numIndividualQuestsCompletedById[ $row["submit_account_id"] ]++;
                        }
                    }
                }

                $diffs = array("easy", "medium", "hard", "insane", "group");

                // Loop through Easy through Group quests and update counts and points.
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($creditIds[$i])) {
                        $ids = implode(",", $creditIds[$i]); // List of IDs of people who completed this quest.
                        
                        // Update difficulty counts and points for each level of difficulty.
                        $stmt = $db -> prepareAndExecute(sprintf('UPDATE QuestData SET data_%s_count = data_%1$s_count + 1, data_%1$s_points = data_%1$s_points + %d WHERE data_account_id IN (%s)', $diffs[$i - 1], $points[$i], $ids));
                        $counts[$i] = $stmt -> rowCount();

                        // Update mode points.
                        if ($reqTypes[$i] != "")
                            $db -> prepareAndExecute(sprintf('UPDATE QuestData SET data_%s_points = data_%1$s_points + %d WHERE data_account_id IN (%s)', $reqTypes[$i], $points[$i], $ids));
                    }
                }
            }


            // Determine and award points for the Battle Quest.
            
            // Get various stats about the participants' scores.
            $bqStats = $db -> prepareAndExecute("SELECT COUNT(*) AS score_count, MIN(submit_battle_score) AS min_score, MAX(submit_battle_score) AS max_score FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id WHERE submit_req_id IN (" . implode(",", $reqIds) . ") AND QuestRequirements.req_difficulty=6 AND submit_grade_status=2 AND submit_points_given=0") -> fetch();
            
            if ($bqStats["score_count"] > 0) {
                // Determine constants for point calculation.
                // See the update PDF for more info about what these values mean and how the scores are calculated.
                $n = $bqStats["score_count"];
                $s1 = $bqStats["min_score"];
                $sn = $bqStats["max_score"];
                $m = 0;
                if ($sn != $s1) // Prevent division by zero when there's only one score.
                    $m = (min(array(6 + $n - 1, 20)) - 6) / ($sn - $s1);
                
                //$audifan -> logUserEvent(sprintf("Quest Point Update - Battle Quest Info: n=%d ; s1 = %d ; sn = %d ; m = %f", $n, $s1, $sn, $m));
                
                $bqSubs = $db -> prepareAndExecute("SELECT QuestSubmissions.* FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id WHERE submit_req_id IN (" . implode(",", $reqIds) . ") AND QuestRequirements.req_difficulty=6 AND submit_grade_status=2 AND submit_points_given=0") -> fetchAll();
                foreach ($bqSubs as $b) {
                    // Determine points.
                    $pointsToGive = floor(($m * $b["submit_battle_score"]) - ($m * $s1) + 6);
                
                    // Add points and increase counts.
                    $db -> prepareAndExecute("UPDATE QuestData SET data_battle_count=data_battle_count+1, data_battle_points=data_battle_points+? WHERE data_account_id=?", $pointsToGive, $b["submit_account_id"]);
                    
                    // Update the status message with how many points they got.
                    $db -> prepareAndExecute("UPDATE QuestSubmissions SET submit_grade_message=? WHERE submit_id=?", number_format($b["submit_battle_score"]) . " (" . $pointsToGive . " QP)", $b["submit_id"]);
                    
                    array_push($creditIds[6], $b["submit_account_id"]);
                    $counts[6]++;
                }
            }

            // Add notifications for everyone who completed a quest that their points were updated.
            $pointNotifIds = array();
            for ($i = 1; $i <= 6; $i++) {
                foreach ($creditIds[$i] as $id)
                    if (!in_array($id, $pointNotifIds))
                        array_push($pointNotifIds, $id);
            }

            foreach ($pointNotifIds as $id)
                $audifan -> getNotificationManager() -> addDatabaseNotification('Your <a href="/quests/">quest</a> points were updated.', $id, "qpupdate");

            // Mark all submissions to say that points were given.
            $db -> prepareAndExecute("UPDATE QuestSubmissions SET submit_points_given=1 WHERE submit_req_id IN (" . implode(",", $reqIds) . ") AND submit_grade_status=2 AND submit_points_given=0");

            // Remove old badges from those who will be getting a new badge / extension of the badge they currently have.
            $newBadgeIds = array_keys($numIndividualQuestsCompletedById);
            if (!empty($newBadgeIds))
                $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE item_id IN (7,8,9,10) AND account_id IN (" . implode(",", $newBadgeIds) . ")");

            // Award badges.
            $badgeExpireTime = time() + (3600 * 24 * 7);
            
            foreach ($numIndividualQuestsCompletedById as $id => $num) {
                $itemId = 6 + $num; // Shortcut for the badge Item ID.  Item ID 7 is the #1 quest medal, ID 8 is #2 quest medal, etc.  6 plus the number of quests they completed gives this number.
                $db -> prepareAndExecute("INSERT INTO AccountStuff(account_id, item_id, expire_time, in_use) VALUES (?,?,?,?)", $id, $itemId, $badgeExpireTime, 1);
            }

            $successMessage = vsprintf("Number of accounts updated: Easy - %d ; Medium - %d ; Hard - %d ; Insane - %d ; Group - %d ; Battle - %d", array_values($counts));
        } elseif ($lastError == "")
            $lastError = "All screenshots have not been graded for this week or no screenshots exist for this week!";
    } else
        $lastError = "Please enter a valid year and week number.";
} elseif (isset($_POST["submit_rankings"])) {
    // Update rankings
    // Keys are account ids.
    // Values are off of the default.
    $default = array(
        "overall" => 0,
        "overall_no_insane" => 0,
        "normal" => 0,
        "beat_up" => 0,
        "one_two" => 0,
        "beat_rush" => 0,
        "guitar" => 0
    );

    $queries = array(
        "overall" => "SELECT data_account_id FROM QuestData WHERE (data_easy_points + data_medium_points + data_hard_points + data_insane_points + data_group_points + data_battle_points) != 0 ORDER BY (data_easy_points + data_medium_points + data_hard_points + data_insane_points + data_group_points + data_battle_points) DESC, data_first_submission_time ASC",
        "overall_no_insane" => "SELECT data_account_id FROM QuestData WHERE (data_easy_points + data_medium_points + data_hard_points + data_group_points + data_battle_points) != 0 ORDER BY (data_easy_points + data_medium_points + data_hard_points + data_group_points + data_battle_points) DESC, data_first_submission_time ASC",
        "individual" => "SELECT data_account_id FROM QuestData WHERE (data_easy_points + data_medium_points + data_hard_points + data_insane_points) != 0 ORDER BY (data_easy_points + data_medium_points + data_hard_points + data_insane_points) DESC, data_first_submission_time ASC",
        "normal" => "SELECT data_account_id FROM QuestData WHERE data_normal_points != 0 ORDER BY data_normal_points DESC, data_first_submission_time ASC",
        "beat_up" => "SELECT data_account_id FROM QuestData WHERE data_beatup_points != 0 ORDER BY data_beatup_points DESC, data_first_submission_time ASC",
        "one_two" => "SELECT data_account_id FROM QuestData WHERE data_onetwo_points != 0 ORDER BY data_onetwo_points DESC, data_first_submission_time ASC",
        "beat_rush" => "SELECT data_account_id FROM QuestData WHERE data_beatrush_points != 0 ORDER BY data_beatrush_points DESC, data_first_submission_time ASC",
        "guitar" => "SELECT data_account_id FROM QuestData WHERE data_guitar_points != 0 ORDER BY data_guitar_points DESC, data_first_submission_time ASC"
    );
    
    $ranks = array();
    
    foreach ($queries as $k => $v) {
        $num = 1;
        foreach ($db -> prepareAndExecute($v) as $row) {
            if (!array_key_exists($row["data_account_id"], $ranks)) {
                foreach (array_keys($queries) as $queryName)
                    $ranks[ $row["data_account_id"] ][$queryName] = 0;
            }
            
            $ranks[$row["data_account_id"]][$k] = $num;
            $num++;
        }
    }

    $db -> prepareAndExecute("DELETE FROM QuestRanking");

    // account id is in $k.
    foreach ($ranks as $k => $v) {
        $curr = array_merge($default, $v);
        $db -> prepareAndExecute("INSERT INTO QuestRanking(rank_account_id, rank_overall, rank_overall_no_insane, rank_individual, rank_normal, rank_beat_up, rank_one_two, rank_beat_rush, rank_guitar) VALUES(?,?,?,?,?,?,?,?,?)", $k, $curr["overall"], $curr["overall_no_insane"], $curr["individual"], $curr["normal"], $curr["beat_up"], $curr["one_two"], $curr["beat_rush"], $curr["guitar"]);
    }

    $fh = fopen($audifan -> getConfigVar("templateLocation") . "/generated/questrankingupdatetime.twig", 'w');
    fwrite($fh, date("l, F j, Y") . " at " . date("g:ia"));
    fclose($fh);

    $successMessage = "Rankings were successfully updated!";
} else {
    $keys = array_keys($_POST);
    if (!empty($keys)) {
        $useKey = "";
        foreach ($keys as $k) {
            if (preg_match('/^submit_[1-9]([0-9]+)?$/', $k)) {
                $useKey = $k;
                break;
            }
        }

        if ($useKey != "") {
            $status = $_POST["status"];
            $message = $_POST["message"];
            if (is_numeric($status)) {
                $id = explode("_", $useKey);
                $id = $id[1];
                $db -> prepareAndExecute("UPDATE QuestSubmissions SET submit_grade_status=?, submit_grade_message=? WHERE submit_id=?", $status, $message, $id);
                $successMessage = "Updated submission with ID " . $id;
            } else
                $lastError = "Invalid data!";
        }
    }
}





array_push($context["GLOBAL"]["messages"]["error"], $lastError);
array_push($context["GLOBAL"]["messages"]["success"], $successMessage);

$context["diffs"] = array("Easy", "Medium", "Hard", "Insane","Group","Battle");

$q = "SELECT QuestSubmissions.*, QuestRequirements.*, QuestIGNs.*, a0.display_name AS name0, a1.display_name AS name1, a2.display_name AS name2, ";
$q .= "a3.display_name AS name3, a4.display_name AS name4 ";
$q .= "FROM QuestSubmissions ";
$q .= "LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id ";
$q .= "LEFT JOIN QuestIGNs ON QuestSubmissions.submit_ign_id = QuestIGNs.ign_id ";
$q .= "LEFT JOIN Accounts AS a0 ON QuestSubmissions.submit_account_id = a0.id ";
$q .= "LEFT JOIN Accounts AS a1 ON QuestSubmissions.submit_time_grader_id = a1.id ";
$q .= "LEFT JOIN Accounts AS a2 ON QuestSubmissions.submit_ign_grader_id = a2.id ";
$q .= "LEFT JOIN Accounts AS a3 ON QuestSubmissions.submit_mode_grader_id = a3.id ";
$q .= "LEFT JOIN Accounts AS a4 ON QuestSubmissions.submit_req_grader_id = a4.id ";
$q .= "ORDER BY QuestSubmissions.submit_id DESC";
$context["submissions"] = $db -> prepareAndExecute($q) -> fetchAll();

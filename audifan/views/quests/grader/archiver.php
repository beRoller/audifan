<?php

/* @var $audifan Audifan */

$viewData["template"] = "quests/grader/archiver.twig";

$db = $audifan -> getDatabase();

$vars = filter_input_array(INPUT_POST, array(
    "submit_archive" => array(
        "filter" => FILTER_DEFAULT
    ),
    "month" => array(
        "filter" => FILTER_VALIDATE_INT
    ),
    "year" => array(
        "filter" => FILTER_VALIDATE_INT
    )
));

if ($vars["submit_archive"] != NULL && $vars["month"] != NULL && $vars["month"] != FALSE && $vars["year"] != NULL && $vars["year"] != FALSE) {
    $startTime = mktime(0, 0, 0, $vars["month"], 1, $vars["year"]);
    $endTime = mktime(23, 59, 59, $vars["month"], date("t", $startTime), $vars["year"]);
    
    $quests = $db -> prepareAndExecute("SELECT * FROM QuestRequirements WHERE req_start_time>=? AND req_start_time<=? ORDER BY req_week_number ASC, req_difficulty ASC", $startTime, $endTime);
    if ($quests -> rowCount() > 0) {
        $fileLocation = sprintf($audifan -> getConfigVar("localPublicLocation") . "/static/files/questarchives/%d-%d.zip", $vars["year"], $vars["month"]);
        if (file_exists($fileLocation))
            unlink($fileLocation);
        
        $reqs = array();
        $diffs = array(
            1 => "easy",
            2 => "medium",
            3 => "hard",
            4 => "insane",
            5 => "group",
            6 => "battle"
        );
        foreach ($quests as $row) {
            if (!isset($reqs[ $row["req_week_number"] ]))
                $reqs[ $row["req_week_number"] ] = array();
            
            $reqs[ $row["req_week_number"] ][ $row["req_difficulty"] ] = $row["req_text"];
        }
            
        
        $zip = new ZipArchive();
        if ($zip -> open($fileLocation, ZipArchive::CREATE) === TRUE) {
            $zip -> addFromString("README.txt",
                    "This ZIP file was automatically generated by the site. Please report any errors to us!\r\n" .
                    "The list of quest requirements is in each week's folder in the 'quests.txt' file.\r\n" .
                    "Each week's folder contains folders for everyone who submitted a screenshot with all screenshots submitted as well as grading information for each screenshot in a text file.\r\n" .
                    "If you have reason to believe that a screenshot was incorrectly rejected or accepted, please contact us!\r\n" .
                    "Administrator screenshots are not included.");
            
            $query  = "SELECT QuestSubmissions.*, QuestRequirements.*, ";
            $query .= "Accounts.display_name ";
            $query .= "FROM QuestSubmissions LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id=QuestRequirements.req_id ";
            $query .= "LEFT JOIN Accounts ON QuestSubmissions.submit_account_id=Accounts.id ";
            $query .= "WHERE req_start_time>=? AND req_start_time<=? AND QuestSubmissions.submit_account_id!=1 "; // ignore my submissions.
            $query .= "ORDER BY QuestRequirements.req_week_number";
            
            $lastWeekNum = 0;
            $weekDirectory = "";
            foreach ($db -> prepareAndExecute($query, $startTime, $endTime) as $row) {
                if ($lastWeekNum != $row["req_week_number"]) {
                    $weekDirectory = sprintf("Week of %s", date("F j", $row["req_start_time"]));
                    $zip -> addEmptyDir($weekDirectory);
                    
                    $zip -> addFromString($weekDirectory . "/quests.txt", sprintf("Easy: %s\r\nMedium: %s\r\nHard: %s\r\nInsane: %s\r\nGroup: %s\r\nBattle: %s", 
                            $reqs[ $row["req_week_number"] ][1], 
                            $reqs[ $row["req_week_number"] ][2], 
                            $reqs[ $row["req_week_number"] ][3], 
                            $reqs[ $row["req_week_number"] ][4], 
                            isset($reqs[ $row["req_week_number"] ][5]) ? $reqs[ $row["req_week_number"] ][5] : "(None)", 
                            isset($reqs[ $row["req_week_number"] ][6]) ? $reqs[ $row["req_week_number"] ][6] : "(None)"));
                }
                
                $str = sprintf("Screenshot submitted on %s at %s Audition time.\r\n", date("F j", $row["submit_time"]), date("g:ia", $row["submit_time"]));
                $str .= sprintf("Screenshot was %s by a grader on %s at %s Audition time.\r\n", ($row["submit_grade_status"] == 2) ? "ACCEPTED" : "REJECTED", date("F j", $row["submit_last_grade_time"]), date("g:ia", $row["submit_last_grade_time"]));
                $str .= sprintf("Grade Message/Battle Quest Score: %s", ($row["submit_grade_message"] != "") ? $row["submit_grade_message"] : "(None)");
                //$str .= "\r\nDEBUG:\r\n" . print_r($row, true);
                
                $zip -> addFromString($weekDirectory . "/" . $row["display_name"] . "/" . $row["req_difficulty"] . "_" . $diffs[$row["req_difficulty"]] . ".txt", $str);
                
                if ($row["submit_screenshot"] != "") {
                    $imgData = explode(";", $row["submit_screenshot"]);
                    $imgUrl = "";
                    if ($imgData[0] == "shack") {
                        //$imgUrl = sprintf("http://imagizer.imageshack.com/img%d/%d/%s.jpg", $imgData[1], $imgData[2], $imgData[3]);
                    } elseif ($imgData[0] == "local") {
                        $imgUrl = sprintf($audifan -> getConfigVar("localPublicLocation") . "/static/img/questsubmissions/%s.jpg", $imgData[1]);
                        if (!file_exists($imgUrl)) {
                            $imgUrl = "";
                        }
                    }
                    
                    if ($imgUrl != "") {
                        $zip -> addFromString($weekDirectory . "/" . $row["display_name"] . "/" . $row["req_difficulty"] . "_" . $diffs[$row["req_difficulty"]] . ".jpg", file_get_contents($imgUrl));
                    } else {
                        $zip -> addFromString($weekDirectory . "/" . $row["display_name"] . "/" . $row["req_difficulty"] . "_" . $diffs[$row["req_difficulty"]] . "_info.txt", "Screenshot was not available.");
                    }
                }
                
                $lastWeekNum = $row["req_week_number"];
            }
            
            $zip -> close();
        }
    } else
        notif_show("No quests for that month exist!");
}
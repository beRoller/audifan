<?php

/* @var $audifan Audifan */

$id = $viewData["urlVariables"][1];

$user = $audifan -> getDatabase() -> prepareAndExecute("SELECT display_name, main_character FROM Accounts WHERE id=?", $id) -> fetch();
if ($user === FALSE)
    return;

$db = $audifan -> getDatabase();

$viewData["template"] = "community/profile/characters.twig";


$context["name"] = $user["display_name"];
$context["mainCharacter"] = $user["main_character"];
$context["id"] = $id;

$getEdit = filter_input(INPUT_GET, "edit", FILTER_DEFAULT);
$context["canEdit"] = ($audifan -> getUser() -> isAdmin() || ($audifan -> getUser() -> isLoggedIn() && $audifan -> getUser() -> getId() == $id));
$context["editMode"] = ($context["canEdit"] && !is_null($getEdit));

$context["chars"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM Characters WHERE account=?", $id) -> fetchAll();
$context["numChars"] = sizeof($context["chars"]);

// Add blank fields and ID of -1 for new character.
if ($context["editMode"] && $context["numChars"] < 3) {
    array_push($context["chars"], array(
        "id" => -1,
        "ign" => "",
        "gender" => "m",
        "level" => 0,
        "couple" => "",
        "couple_level" => 0,
        "ring" => 0,
        "story_medal" => 0,
        "story_medal2" => 0,
        "team1" => "",
        "team2" => "",
        "team_title" => 0,
        "fam" => "",
        "fam_member_type" => 1,
        "tourn_expert" => 0,
        "tourn_beatup" => 0,
        "tourn_beatrush" => 0,
        "tourn_guitar" => 0,
        "tourn_team" => 0,
        "tourn_couple" => 0,
        "tourn_ballroom" => 0,
        "diary" => 0,
        "guitar_ctrlr" => 0,
        "mission_n" => 0,
        "mission_b" => 0,
        "mission_o" => 0,
        "mission_r" => 0,
        "mission_h" => 0
    ));
}


// This is a helper function to get data from the POST filter and change the names to the database values.
function getDatabaseValues($postFilter, $charId) {
    $formToDbName = array(
        "ign" => "ign",
        "gender" => "gender",
        "level" => "level",
        "couple" => "couple",
        "couplelevel" => "couple_level",
        "ring" => "ring",
        "storymedal1" => "story_medal",
        "storymedal2" => "story_medal2",
        "team1" => "team1",
        "team2" => "team2",
        "teamtitle" => "team_title",
        "fam" => "fam",
        "famtype" => "fam_member_type",
        "tournexpert" => "tourn_expert",
        "tournbeatup" => "tourn_beatup",
        "tournbeatrush" => "tourn_beatrush",
        "tournguitar" => "tourn_guitar",
        "tournteam" => "tourn_team",
        "tourncouple" => "tourn_couple",
        "tournballroom" => "tourn_ballroom",
        "diary" => "diary",
        "guitarctrlr" => "guitar_ctrlr",
        "missionn" => "mission_n",
        "missionb" => "mission_b",
        "missiono" => "mission_o",
        "missionr" => "mission_r",
        "missionh" => "mission_h"
    );
    
    $values = array(
        "ign" => "",
        "gender" => "m",
        "level" => 0,
        "couple" => "",
        "couple_level" => 0,
        "ring" => 0,
        "story_medal" => 0,
        "story_medal2" => 0,
        "team1" => "",
        "team2" => "",
        "team_title" => 0,
        "fam" => "",
        "fam_member_type" => 1,
        "tourn_expert" => 0,
        "tourn_beatup" => 0,
        "tourn_beatrush" => 0,
        "tourn_guitar" => 0,
        "tourn_team" => 0,
        "tourn_couple" => 0,
        "tourn_ballroom" => 0,
        "diary" => 0,
        "guitar_ctrlr" => 0,
        "mission_n" => 0,
        "mission_b" => 0,
        "mission_o" => 0,
        "mission_r" => 0,
        "mission_h" => 0
    );
    
    foreach ($formToDbName as $formName => $dbName) {
        if (!is_null($postFilter[$formName . "_" . $charId]) && $postFilter[$formName . "_" . $charId] !== FALSE)
            $values[ $dbName ] = $postFilter[ $formName . "_" . $charId ];
    }
    
    $values["diary"] = !is_null($postFilter["diary_" . $charId]) ? 1 : 0;
    $values["guitar_ctrlr"] = !is_null($postFilter["guitarctrlr_" . $charId]) ? 1 : 0;
    
    return $values;
}



// Process saves.
if ($context["editMode"]) {
    $def = array(
        "submit_all" => FILTER_DEFAULT,
        "submit_delete" => FILTER_DEFAULT,
        "submit_main" => FILTER_DEFAULT,
        "charid" => FILTER_VALIDATE_INT
    );
    foreach ($context["chars"] as $c) {
        $cid = $c["id"];
        $def["submit_" . $cid] = FILTER_DEFAULT;

        $def = array_merge_recursive($def, array(
            "submit_" . $cid => FILTER_DEFAULT,
            "ign_" . $cid => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^[a-zA-Z0-9\-\_\~]{2,30}$/"
                )
            ),
            "gender_" . $cid => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^(m|f)$/"
                )
            ),
            "level_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 99
                )
            ),
            "storymedal1_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 5
                )
            ),
            "storymedal2_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 5
                )
            ),
            "fam_" . $cid => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^[a-zA-Z0-9\-\_\~]{2,30}$/"
                )
            ),
            "famtype_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 1,
                    "max_range" => 3
                )
            ),
            "couple_" . $cid => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^[a-zA-Z0-9\-\_\~]{2,30}$/"
                )
            ),
            "couplelevel_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 61
                )
            ),
            "ring_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 40
                )
            ),
            "team1_" . $cid => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^[a-zA-Z0-9\-\_\~]{2,30}$/"
                )
            ),
            "team2_" . $cid => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^[a-zA-Z0-9\-\_\~]{2,30}$/"
                )
            ),
            "teamtitle_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 4
                )
            ),
            "tournexpert_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 999
                )
            ),
            "tournbeatup_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 999
                )
            ),
            "tournbeatrush_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 999
                )
            ),
            "tournguitar_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 999
                )
            ),
            "tournteam_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 999
                )
            ),
            "tourncouple_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 999
                )
            ),
            "tournballroom_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 999
                )
            ),
            "missionn_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 100
                )
            ),
            "missionb_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 100
                )
            ),
            "missiono_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 100
                )
            ),
            "missionr_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 100
                )
            ),
            "missionh_" . $cid => array(
                "filter" => FILTER_VALIDATE_INT,
                "options" => array(
                    "min_range" => 0,
                    "max_range" => 100
                )
            ),
            "diary_" . $cid => FILTER_DEFAULT,
            "guitarctrlr_" . $cid => FILTER_DEFAULT
        ));
    }
    
    $postFilter = filter_input_array(INPUT_POST, $def);
    
    $updateChars = false;
    
    if (!is_null($postFilter["submit_main"])) {
        // Set main character.
        $db -> prepareAndExecute("UPDATE Accounts SET main_character=? WHERE id=?", $postFilter["charid"], $audifan -> getUser() -> getId());
        if ($postFilter["charid"] != 0)
            array_push($context["GLOBAL"]["messages"]["success"], "Your main character was successfully changed.");
        else
            array_push($context["GLOBAL"]["messages"]["success"], "Your main character was successfully unset.");
        $context["mainCharacter"] = $postFilter["charid"];
    } elseif (!is_null($postFilter["submit_delete"])) {
        // Delete a character.
        $db -> prepareAndExecute("DELETE FROM Characters WHERE id=? AND account=?", $postFilter["charid"], $audifan -> getUser() -> getId());
        array_push($context["GLOBAL"]["messages"]["success"], "Character was successfully deleted.");
        $updateChars = true;
    } elseif (isset($postFilter["submit_-1"]) && !is_null($postFilter["submit_-1"])) {
        // Add new character.
        $values = getDatabaseValues($postFilter, -1);
        $q  = "INSERT INTO Characters(account,ign,gender,level,couple," .
            "couple_level,ring,story_medal,story_medal2,team1," .
            "team2,team_title,fam,fam_member_type,tourn_expert," .
            "tourn_beatup,tourn_beatrush,tourn_guitar,tourn_team," .
            "tourn_couple,tourn_ballroom,diary,guitar_ctrlr,mission_n," .
            "mission_b,mission_o,mission_r,mission_h) " .
            "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $db -> prepareAndExecute($q,
                        $audifan -> getUser() -> getId(),
                        $values["ign"],
                        $values["gender"],
                        $values["level"],
                        $values["couple"],
                        $values["couple_level"],
                        $values["ring"],
                        $values["story_medal"],
                        $values["story_medal2"],
                        $values["team1"],
                        $values["team2"],
                        $values["team_title"],
                        $values["fam"],
                        $values["fam_member_type"],
                        $values["tourn_expert"],
                        $values["tourn_beatup"],
                        $values["tourn_beatrush"],
                        $values["tourn_guitar"],
                        $values["tourn_team"],
                        $values["tourn_couple"],
                        $values["tourn_ballroom"],
                        $values["diary"],
                        $values["guitar_ctrlr"],
                        $values["mission_n"],
                        $values["mission_b"],
                        $values["mission_o"],
                        $values["mission_r"],
                        $values["mission_h"]
                );
        array_push($context["GLOBAL"]["messages"]["success"], "New character added.");
        $updateChars = true;
    } else {
        // Save all or one.
        foreach ($context["chars"] as $c) {
            if ($c["id"] != -1 && (!is_null($postFilter["submit_all"]) || !is_null($postFilter["submit_" . $c["id"]]))) {
                $values = getDatabaseValues($postFilter, $c["id"]);
                $q = "UPDATE Characters SET ign=?,gender=?,level=?,couple=?," .
                        "couple_level=?,ring=?,story_medal=?,story_medal2=?,team1=?," .
                        "team2=?,team_title=?,fam=?,fam_member_type=?,tourn_expert=?," .
                        "tourn_beatup=?,tourn_beatrush=?,tourn_guitar=?,tourn_team=?," .
                        "tourn_couple=?,tourn_ballroom=?,diary=?,guitar_ctrlr=?,mission_n=?," .
                        "mission_b=?,mission_o=?,mission_r=?,mission_h=? WHERE id=?";
                $res = $db -> prepareAndExecute($q,
                        $values["ign"],
                        $values["gender"],
                        $values["level"],
                        $values["couple"],
                        $values["couple_level"],
                        $values["ring"],
                        $values["story_medal"],
                        $values["story_medal2"],
                        $values["team1"],
                        $values["team2"],
                        $values["team_title"],
                        $values["fam"],
                        $values["fam_member_type"],
                        $values["tourn_expert"],
                        $values["tourn_beatup"],
                        $values["tourn_beatrush"],
                        $values["tourn_guitar"],
                        $values["tourn_team"],
                        $values["tourn_couple"],
                        $values["tourn_ballroom"],
                        $values["diary"],
                        $values["guitar_ctrlr"],
                        $values["mission_n"],
                        $values["mission_b"],
                        $values["mission_o"],
                        $values["mission_r"],
                        $values["mission_h"],
                        $c["id"]
                );
                array_push($context["GLOBAL"]["messages"]["success"], "Your changes to " . $c["ign"] . " were saved.");
                $updateChars = true;
            
            }
        }
    }
    
    if ($updateChars) {
        $context["chars"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM Characters WHERE account=?", $id) -> fetchAll();
        $context["numChars"] = sizeof($context["chars"]);
        
        if ($context["numChars"] < 3) {
            array_push($context["chars"], array(
                "id" => -1,
                "ign" => "",
                "gender" => "m",
                "level" => 0,
                "couple" => "",
                "couple_level" => 0,
                "ring" => 0,
                "story_medal" => 0,
                "story_medal2" => 0,
                "team1" => "",
                "team2" => "",
                "team_title" => 0,
                "fam" => "",
                "fam_member_type" => 1,
                "tourn_expert" => 0,
                "tourn_beatup" => 0,
                "tourn_beatrush" => 0,
                "tourn_guitar" => 0,
                "tourn_team" => 0,
                "tourn_couple" => 0,
                "tourn_ballroom" => 0,
                "diary" => 0,
                "guitar_ctrlr" => 0,
                "mission_n" => 0,
                "mission_b" => 0,
                "mission_o" => 0,
                "mission_r" => 0,
                "mission_h" => 0
            ));
        }
    }
}
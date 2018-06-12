<?php

/* @var $audifan Audifan */

$postFilter = filter_input_array(INPUT_POST, array(
    "submit" => FILTER_DEFAULT,
    "delete" => FILTER_DEFAULT,
    
    "artist" => FILTER_DEFAULT,
    "title" => FILTER_DEFAULT,
    "bpm" => FILTER_VALIDATE_INT,
    "lengthm" => FILTER_VALIDATE_INT,
    "lengths" => FILTER_VALIDATE_INT,
    "master" => FILTER_DEFAULT,
    "premium" => FILTER_DEFAULT,
    "normal" => FILTER_DEFAULT,
    "night" => FILTER_DEFAULT,
    "beatup" => FILTER_VALIDATE_INT,
    "onetwo" => FILTER_VALIDATE_INT,
    "ballroom" => FILTER_DEFAULT,
    "rhythmholic" => FILTER_VALIDATE_INT,
    "spacepangpang" => FILTER_DEFAULT,
    "guitar" => FILTER_DEFAULT,
    "blockbeat" => FILTER_VALIDATE_INT
));

if (!is_null($postFilter["delete"])) {
    // Delete song.
    $audifan -> getDatabase() -> prepareAndExecute("DELETE FROM SongList WHERE id=?", $viewData["urlVariables"][1]);
} else if (!is_null($postFilter["submit"])) {
    // Save edits.
    
    // Note that the length and new flags are at the end.
    $data = array(
        "", // artist 0
        "", // title 1
        0, // bpm 2
        0, // master 3
        0, // premium 4
        0, // normal 5
        0, // night 6
        0, // bu 7
        0, // 1,2 8
        0, // ballroom 9
        0, // holic 10
        0, // spp 11
        "", // guitar 12
        0, // bb 13
        0, // length 14
        0, // new flags 15
    );
    
    $keys = array("artist","title","bpm","master","premium","normal","night","beatup","onetwo","ballroom","rhythmholic","spacepangpang","guitar","blockbeat");
    for ($i = 0; $i < sizeof($keys); $i++) {
        $k = $keys[$i];
        if (!is_null($postFilter[$k]) && $postFilter[$k] !== FALSE) {
            if ($k == "master" || $k == "premium" || $k == "normal" || $k == "night" || $k == "ballroom" || $k == "spacepangpang")
                $data[$i] = 1;
            else
                $data[$i] = $postFilter[$k];
        }
    }
    
    if (!is_null($postFilter["lengthm"]) && $postFilter["lengthm"] !== FALSE && !is_null($postFilter["lengths"]) && $postFilter["lengths"] !== FALSE) {
        $data[14] = ($postFilter["lengthm"] * 60) + $postFilter["lengths"];
    }
    
    // New Flags
    for ($i = 0; $i <= 8; $i++) {
        if (!is_null(filter_input(INPUT_POST, "new_" . $i))) {
            $data[15] |= (1 << $i);
        }
    }
    
    array_push($data, $viewData["urlVariables"][1]); // Add the ID to the end of data.
    
    $audifan -> getDatabase() -> prepareAndExecuteArray("UPDATE SongList SET artist=?,title=?,bpm=?,master=?,premium=?,normal=?,night=?,beatup=?,onetwo=?,ballroom=?,rhythmholic=?,spacepangpang=?,guitar=?,blockbeat=?,length=?,new_flags=? WHERE id=?", $data);
}



$context["song"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM SongList WHERE id=?", $viewData["urlVariables"][1]) -> fetch();
if ($context["song"] !== FALSE) {
    $viewData["template"] = "admin/songs/edit.twig";
}
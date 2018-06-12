<?php

$viewData["template"] = "admin/songs/add.twig";

$fields = array("artist","title","bpm","length","normal","night","beatup","onetwo","ballroom","rhythmholic","spacepangpang","guitar","blockbeat");

$context["fields"] = $fields;

$postFilterDef = array(
    "submit" => FILTER_DEFAULT
);
foreach ($fields as $f)
    $postFilterDef[ $f ] = FILTER_DEFAULT;

$postFilter = filter_input_array(INPUT_POST, $postFilterDef);

if (!is_null($postFilter["submit"])) {
    $params = array();
    foreach ($fields as $f)
        array_push($params, $postFilter[ $f ]);
    
    $newFlags = 0;
    for ($i = 0; $i < 9; $i++) {
        if (isset($_POST["new_" . $i])) {
            $newFlags |= (1 << $i);
        }
    }
    
    array_push($params, $newFlags);
    
    $audifan -> getDatabase() -> prepareAndExecuteArray("INSERT INTO SongList(" . implode(",", $fields) . ",new_flags) VALUES(" . str_repeat("?,", sizeof($fields) - 1) . "?,?)", $params);
}
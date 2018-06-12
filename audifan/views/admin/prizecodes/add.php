<?php

/* @var $audifan Audifan */

$viewData["template"] = "admin/prizecodes/add.twig";

$postData = filter_input_array(INPUT_POST, array(
    "submit" => FILTER_DEFAULT,
    "id" => FILTER_VALIDATE_INT,
    "duration" => FILTER_VALIDATE_INT,
    "charges" => FILTER_VALIDATE_INT,
    "prizecodeduration" => FILTER_VALIDATE_INT
));

if (!is_null($postData["submit"])) {
    // Generate a code.
    $code = md5("audifanprizecode$$".$postData["id"]."$$".time());
    array_push($context["GLOBAL"]["messages"]["success"], "Code: " . $code);
    
    $audifan -> getDatabase() -> prepareAndExecute("INSERT INTO PrizeCodes(prize_code,prize_item,prize_duration,prize_charges,prize_code_expire) VALUES(?,?,?,?,?)", $code, $postData["id"], $postData["duration"], $postData["charges"], time() + $postData["prizecodeduration"]);
}
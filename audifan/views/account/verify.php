<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$getFilter = filter_input_array(INPUT_GET, array(
    "id" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    ),
    "code" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => '/^[a-f0-9]{16}$/'
        )
    )
));

if (!is_null($getFilter["id"]) && $getFilter["id"] !== FALSE && !is_null($getFilter["code"]) && $getFilter["code"] !== FALSE) {
    $id = $getFilter["id"];
    $result = $db -> prepareAndExecute("SELECT account_type, verification_code FROM Accounts WHERE id=?", $id) -> fetch();
    if ($result !== FALSE) {
        if ($result["account_type"] < 0) {
            $verificationCode = $result["verification_code"];
            if ($getFilter["code"] == $verificationCode) {
                $db -> prepareAndExecute("UPDATE Accounts SET account_type=1 WHERE id=?", $id);
                array_push($context["GLOBAL"]["messages"]["success"], "Thank you!  Your email has been verified.");
            } else
                array_push($context["GLOBAL"]["messages"]["error"], "That verification code is incorrect.");
        } else
            array_push($context["GLOBAL"]["messages"]["error"], "Your email has already been verified.");
    } else
        array_push($context["GLOBAL"]["messages"]["error"], "Sorry, that data doesn't seem to be valid.  Please try again.");
} else
    array_push($context["GLOBAL"]["messages"]["error"], "Sorry, that data doesn't seem to be valid.  Please try again.");

$viewData["template"] = "account/verify.twig";
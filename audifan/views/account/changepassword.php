<?php

/* @var $audifan Audifan */
$db = $audifan -> getDatabase();

$viewData["template"] = "account/changepassword.twig";

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
    $result = $db -> prepareAndExecute("SELECT verification_code, email FROM Accounts WHERE id=?", $id) -> fetch();
    if ($result !== FALSE) {
        $verificationCode = $result["verification_code"];
        if ($verificationCode != "" && $getFilter["code"] == $verificationCode) {
            $postFilter = filter_input_array(INPUT_POST, array(
                "submit_password" => FILTER_DEFAULT,
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
                )
            ));

            if (!is_null($postFilter["submit_password"])) {
                if ($postFilter["newpassword"] !== FALSE) {
                    // Check password.
                    if ($postFilter["newpassword"] == $postFilter["newpassword2"]) {
                        // Change password.
                        $db -> prepareAndExecute("UPDATE Accounts SET password=?, verification_code='' WHERE id=?", $audifan -> getUser() -> hashPassword($postFilter["newpassword"], $result["email"]), $id);
                        array_push($context["GLOBAL"]["messages"]["success"], "Your password was changed!");
                    } else {
                        array_push($context["GLOBAL"]["messages"]["error"], "Your passwords didn't match.");
                        $context["askForPassword"] = true;
                    }
                } else {
                    array_push($context["GLOBAL"]["messages"]["error"], "Your password must be at least 6 characters in length.");
                    $context["askForPassword"] = true;
                }
            } else
                $context["askForPassword"] = true;
        } else
            array_push($context["GLOBAL"]["messages"]["error"], "Sorry, that data doesn't seem to be valid.  Please try again.");
    } else
        array_push($context["GLOBAL"]["messages"]["error"], "Sorry, that data doesn't seem to be valid.  Please try again.");
} else
    array_push($context["GLOBAL"]["messages"]["error"], "Sorry, that data doesn't seem to be valid.  Please try again.");

$context["code"] = $getFilter["code"];
$context["id"] = $getFilter["id"];

<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$viewData["template"] = "account/resend.twig";

$postFilter = filter_input_array(INPUT_POST, array(
    "submit_resend" => FILTER_DEFAULT,
    "email" => FILTER_VALIDATE_EMAIL
));

if (!is_null($postFilter["submit_resend"])) {
    if (!is_null($postFilter["email"]) && $postFilter["email"] !== FALSE) {
        $acc = $db -> prepareAndExecute("SELECT * FROM Accounts WHERE email=?", $postFilter["email"]) -> fetch();
        if ($acc !== FALSE) {
            if ($acc["account_type"] == -1) {
                // Email the verification code.
                $message = sprintf("Hi %s,\n\nWelcome to Audifan.net!\n\nPlease click this link to verify your email address:\nhttp://%s/account/verify/?id=%d&code=%s\n\nIf you have any questions, please email us at kevin@audifan.net.\n\nThis message was sent to %s.  Please do not reply to this email.",
                    $acc["display_name"], $audifan -> getConfigVar("domain"), $acc["id"], $acc["verification_code"], $acc["email"]);
                $headers = "From: Audifan <noreply@audifan.net>\nX-Sender: <noreply@audifan.net>\n";
                mail($acc["email"], "Audifan.net - Account Verification", $message, $headers);
                array_push($context["GLOBAL"]["messages"]["success"], "Your verification code was resent.");
            } else
                array_push($context["GLOBAL"]["messages"]["error"], "Your account has already been verified.");
        } else
            array_push($context["GLOBAL"]["messages"]["error"], "That email isn't registered.");
    } else {
        array_push($context["GLOBAL"]["messages"]["error"], "Please enter a valid email address.");
    }
}
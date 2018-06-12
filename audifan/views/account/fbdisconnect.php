<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();
$user = $audifan -> getUser();

if ($user -> isLoggedIn()) {
    $viewData["template"] = "account/fbdisconnect.twig";
    
    $basicInfo = $db -> prepareAndExecute("SELECT * FROM Accounts WHERE id=?", $user -> getId()) -> fetch();
    
    $context["email"] = $basicInfo["email"];
    
    $postFilter = filter_input_array(INPUT_POST, array(
        "submit_disconnect" => FILTER_DEFAULT,
        "submit_email" => FILTER_DEFAULT,
        
        "email" => FILTER_VALIDATE_EMAIL,
        "email2" => FILTER_VALIDATE_EMAIL,
        "password" => array(
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => array(
                "regexp" => "/^.{6,}$/"
            )
        ),
        "password2" => array(
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => array(
                "regexp" => "/^.{6,}$/"
            )
        )
    ));
    
    if (!is_null($postFilter["submit_disconnect"])) {
        $db -> prepareAndExecute("UPDATE Accounts SET fbid='' WHERE id=?", $user -> getId());
        $user -> logOut();
        header("Location: /");
        exit;
    } elseif (!is_null($postFilter["submit_email"])) {
        if (!is_null($postFilter["email"]) && $postFilter["email"] !== FALSE) {
            if ($postFilter["email"] == $postFilter["email2"]) {
                if (!is_null($postFilter["password"]) && $postFilter["password"] !== FALSE) {
                    if ($postFilter["password"] == $postFilter["password2"]) {
                        $acc = $db -> prepareAndExecute("SELECT id, display_name FROM Accounts WHERE email=?", $postFilter["email"]) -> fetch();
                        if ($acc === FALSE) {
                            $verificationCode = str_shuffle(substr(md5($acc["id"] . "fbdisconnect" . time()), 0, 16));
                            
                            $db -> prepareAndExecute("UPDATE Accounts SET email=?, password=?, fbid='', verification_code=?, account_type=-1 WHERE id=?", $postFilter["email"], $user -> hashPassword($postFilter["password"], $postFilter["email"]), $verificationCode, $user -> getId());
                            
                            // Email the verification code.
                            $message = sprintf("Hi %s,\n\nPlease click this link to verify your email address so you can disconnect your Facebook account:\nhttp://%s/account/verify/?id=%d&code=%s\n\nThis message was sent to %s.  Please do not reply to this email.",
                                $acc["display_name"], $audifan -> getConfigVar("domain"), $acc["id"], $verificationCode, $postFilter["email"]);
                            $headers = "From: Audifan <noreply@audifan.net>\nX-Sender: <noreply@audifan.net>\n";
                            mail($postFilter["email"], "Audifan.net - Verify Your Email", $message, $headers);
                            
                            $user -> logOut();
                            header("Location: /");
                            exit;
                        } else {
                            array_push($context["GLOBAL"]["messages"]["error"], "That email is already in use.");
                        }
                    } else {
                        array_push($context["GLOBAL"]["messages"]["error"], "Your passwords didn't match.");
                    }
                } else {
                    array_push($context["GLOBAL"]["messages"]["error"], "Your password needs to be at least 6 characters in length.");
                }
            } else {
                array_push($context["GLOBAL"]["messages"]["error"], "The emails you entered didn't match.");
            }
        } else {
            array_push($context["GLOBAL"]["messages"]["error"], "Please enter a valid email address.");
        }
    }
}
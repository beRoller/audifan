<?php

/* @var $audifan Audifan */

$viewData["template"] = "account/forgot.twig";

$postFilter = filter_input_array(INPUT_POST, array(
    "submit" => FILTER_DEFAULT,
    "email" => FILTER_VALIDATE_EMAIL
));

if (!is_null($postFilter["submit"]) && !is_null($postFilter["email"]) && $postFilter["email"] !== FALSE) {
    $db = $audifan -> getDatabase();
    $user = $db -> prepareAndExecute("SELECT id, display_name, email FROM Accounts WHERE email=?", $postFilter["email"]) -> fetch();
    
    if ($user !== FALSE) {
        $verificationCode = str_shuffle(substr(md5($user["id"] . "passwordreset" . time()), 0, 16));
        $db -> prepareAndExecute("UPDATE Accounts SET verification_code=? WHERE id=?", $verificationCode, $user["id"]);
        
        // Email the verification code.
        $message = sprintf("Hi %s,\n\nYou requested a password reset.\n\nPlease click this link to change your password:\nhttp://%s/account/changepassword/?id=%d&code=%s\n\nThis request was made from IP address %s.  If you did not make this request, please contact us.\n\nThis message was sent to %s.  Please do not reply to this email.",
            $user["display_name"], $audifan -> getConfigVar("domain"), $user["id"], $verificationCode, $audifan -> getUser() -> getIpAddress(), $user["email"]);
        $headers = "From: Audifan <noreply@audifan.net>\nX-Sender: <noreply@audifan.net>\n";
        mail($user["email"], "Audifan.net - Password Reset Request", $message, $headers);
    }
    
    $context["sent"] = true;
}
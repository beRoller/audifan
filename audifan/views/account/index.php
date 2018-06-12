<?php

/* @var $audifan Audifan */

// Account login/register view.

$viewData["template"] = "account/index.twig";

// Process registration ajax
$ajaxRequest = filter_input_array(INPUT_POST, array(
    "lookupEmail" => FILTER_VALIDATE_EMAIL,
    "lookupNickname" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^[A-Za-z0-9\-\_\~]{2,20}$/"
        )
    )
));
$ajaxResponse = array();

if ($ajaxRequest["lookupEmail"] !== NULL) {
    if ($ajaxRequest["lookupEmail"] === FALSE)
        $ajaxResponse["invalid"] = true;
    else {
        // Look up
        $count = $audifan -> getDatabase() -> prepareAndExecute("SELECT COUNT(*) FROM Accounts WHERE email=?", $ajaxRequest["lookupEmail"]) -> fetchColumn();
        if ($count != 0)
            $ajaxResponse["inuse"] = true;
        else
            $ajaxResponse["ok"] = true;
    }
} elseif ($ajaxRequest["lookupNickname"] !== NULL) {
    if ($ajaxRequest["lookupNickname"] === FALSE)
        $ajaxResponse["invalid"] = true;
    else {
        // Look up
        $count = $audifan -> getDatabase() -> prepareAndExecute("SELECT COUNT(*) FROM Accounts WHERE display_name=?", $ajaxRequest["lookupNickname"]) -> fetchColumn();
        if ($count != 0)
            $ajaxResponse["inuse"] = true;
        else
            $ajaxResponse["ok"] = true;
    }
}

if (!empty($ajaxResponse)) {
    print json_encode($ajaxResponse);
    exit;
}


// Process form data.
if (filter_input(INPUT_POST, "submit_login") != NULL) {
    // Process login.
    $errors = $audifan -> getUser() -> authenticateFromPost();
    if (empty($errors)) {
        // Redirect to custom location.
        $thru = filter_input(INPUT_POST, "thru");
        if (is_null($thru) || $thru === FALSE)
            $thru = "/";
        header("Location: " . $thru);
        exit;
    } else
        $context["loginErrors"] = $errors;
} elseif ($_CONFIG["allowRegistration"] && filter_input(INPUT_POST, "submit_register") != NULL) {
    // Process registration.
    
    // Verify recaptcha.
    $recaptchaString = filter_input(INPUT_POST, "g-recaptcha-response");
    $secret = "6LeKtv8SAAAAAIZlCYedY0CuoVG9XxGxmSdIMdUt";
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = array(
        "secret" => $secret,
        "response" => $recaptchaString,
        "remoteip" => $audifan -> getUser() -> getIpAddress()
    );
    
    $recaptchaResult = file_get_contents($url, false, stream_context_create(array(
        "http" => array(
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($data)
        )
    )));
    
    $resultJson = json_decode($recaptchaResult, true);
    
    if ($resultJson["success"] == true) {
        $errors = $audifan -> getUser() -> createFromPost();
        if (empty($errors))
            $context["registrationSuccess"] = true;
        else
            $context["registrationErrors"] = $errors;
    } else
        $context["registrationErrors"] = array(
            "recaptcha" => "The Recaptcha was not completed successfully."
        );
}

$context["allowRegistration"] = $_CONFIG["allowRegistration"];
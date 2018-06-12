<?php

/* @var $audifan Audifan */
$viewData["template"] = "tickets/new.twig";

$db = $audifan -> getDatabase();

if ($audifan -> getUser() -> isLoggedIn()) {
    $context["email"] = $db -> prepareAndExecute("SELECT email FROM Accounts WHERE id=?", $audifan -> getUser() -> getId()) -> fetchColumn();
}

$postFilter = filter_input_array(INPUT_POST, array(
    "submit_ticket" => FILTER_DEFAULT,
    "nickname" => FILTER_DEFAULT,
    "email" => FILTER_VALIDATE_EMAIL,
    "title" => FILTER_DEFAULT,
    "body" => FILTER_DEFAULT
        ));

if (!is_null($postFilter["submit_ticket"])) {
    $context["nickname"] = $postFilter["nickname"];
    $context["email"] = $postFilter["email"];
    $context["title"] = $postFilter["title"];
    $context["body"] = $postFilter["body"];

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
        if (!is_null($postFilter["body"]) && $postFilter["body"] !== FALSE && strlen($postFilter["body"]) >= 10) {
            $hash = md5($audifan -> getUser() -> getId() . "ticket" . $postFilter["email"] . microtime());
            
            $context["ticketFinished"] = false;
            
            if ($audifan -> getUser() -> isLoggedIn()) {
                $db -> prepareAndExecute("INSERT INTO Tickets(ticket_account,ticket_email,ticket_hash,ticket_title,ticket_body,ticket_time) VALUES(?,?,?,?,?,?)", $audifan -> getUser() -> getId(), $postFilter["email"], $hash, $postFilter["title"], $postFilter["body"], time());
                $context["ticketFinished"] = true;
            } else {
                if (!is_null($postFilter["email"]) && $postFilter["email"] !== FALSE) {
                    $db -> prepareAndExecute("INSERT INTO Tickets(ticket_email,ticket_hash,ticket_title,ticket_body,ticket_time) VALUES(?,?,?,?,?)", $postFilter["email"], $hash, $postFilter["title"], "Nickname: " . $postFilter["nickname"] . "\n" . $postFilter["body"], time());
                    $context["ticketFinished"] = true;
                } else {
                    array_push($context["GLOBAL"]["messages"]["error"], "Please enter a valid email address.");
                }
            }

            if ($context["ticketFinished"]) {
                $context["ticketId"] = $db -> lastInsertId();
                $context["ticketHash"] = $hash;
                
                if (!is_null($postFilter["email"]) && $postFilter["email"] !== FALSE) {
                    // Send an email.
                    $message = sprintf("Hello,\n\nThank you for contacting us.  Your ticket was successfully created.\n\nAn Audifan administrator will reply as soon as possible.\n\nYou can view your ticket here: http://%s/tickets/view/%d-%s/\n\nThis message was sent to %s.  Please do not reply to this email.",
                        $audifan -> getConfigVar("domain"), $context["ticketId"], $context["ticketHash"], $postFilter["email"]);
                    $headers = "From: Audifan <noreply@audifan.net>\nX-Sender: <noreply@audifan.net>\n";
                    mail($postFilter["email"], "Audifan.net - Ticket Created", $message, $headers);
                }

                array_push($context["GLOBAL"]["messages"]["success"], "Your ticket was created successfully.");
            }
        } else {
            array_push($context["GLOBAL"]["messages"]["error"], "Your ticket details must have at least 10 characters.");
        }
    } else {
        array_push($context["GLOBAL"]["messages"]["error"], "The reCaptcha response was incorrect.");
    }
}
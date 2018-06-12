<?php

/* @var $audifan Audifan */

$id = $viewData["urlVariables"][1];
$hash = $viewData["urlVariables"][2];

$db = $audifan -> getDatabase();

$context["ticketInfo"] = $db -> prepareAndExecute("SELECT * FROM Tickets WHERE ticket_id=? AND ticket_hash=?", $id, $hash) -> fetch();

if ($context["ticketInfo"] === FALSE)
    return;

if ($audifan -> getUser() -> isAdmin()) {
// Process admin "do" links.
    $getFilter = filter_input_array(INPUT_GET, array(
        "do" => array(
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => array(
                "regexp" => "/^(close)$/"
            )
        )
    ));

    if (!is_null($getFilter["do"]) && $getFilter["do"] !== FALSE) {
        switch ($getFilter["do"]) {
            case "close":
                $db -> prepareAndExecute("UPDATE Tickets SET ticket_open=0 WHERE ticket_id=?", $context["ticketInfo"]["ticket_id"]);
                $db -> prepareAndExecute("INSERT INTO TicketComments(ticket_id, account_id, comment_body, comment_time) VALUES(?,?,?,?)", $context["ticketInfo"]["ticket_id"], -1, "Ticket was closed.", time());
                $context["ticketInfo"]["ticket_open"] = 0;
                break;
        }
    }
}

$postFilter = filter_input_array(INPUT_POST, array(
    "submit_comment" => FILTER_DEFAULT,
    "comment" => FILTER_DEFAULT
));

if (!is_null($postFilter["submit_comment"])) {
    // Process comment.
    $context["newcomment"] = $postFilter["comment"];
    if (!is_null($postFilter["comment"]) && strlen($postFilter["comment"]) >= 10) {
        $writer = ($audifan -> getUser() -> isLoggedIn()) ? $audifan -> getUser() -> getId() : 0;
        $db -> prepareAndExecute("INSERT INTO TicketComments(ticket_id, account_id, comment_body, comment_time) VALUES(?,?,?,?)", $context["ticketInfo"]["ticket_id"], $writer, $postFilter["comment"], time());
        
        if ($audifan -> getUser() -> isAdmin()) {
            // Send email and/or send notification.
            if ($context["ticketInfo"]["ticket_email"] != "") {
                $message = sprintf("Hello,\n\nAn Audifan administrator just posted a comment on your ticket.\n\nYou can view it here: http://%s/tickets/view/%d-%s/\n\nThis message was sent to %s.  Please do not reply to this email.",
                    $audifan -> getConfigVar("domain"), $context["ticketInfo"]["ticket_id"], $context["ticketInfo"]["ticket_hash"], $context["ticketInfo"]["ticket_email"]);
                $headers = "From: Audifan <noreply@audifan.net>\nX-Sender: <noreply@audifan.net>\n";
                mail($context["ticketInfo"]["ticket_email"], "Audifan.net - Ticket Update", $message, $headers);
            }
            
            if ($context["ticketInfo"]["ticket_account"] != 0) {
                $audifan -> getNotificationManager() -> addDatabaseNotification('An administrator commented on your <a href="/tickets/view/' . $context["ticketInfo"]["ticket_id"] . '-' . $context["ticketInfo"]["ticket_hash"] . '/" target="_blank">ticket</a>.', $context["ticketInfo"]["ticket_account"], "ticketupdate");
            }
        }
    } else {
        array_push($context["GLOBAL"]["messages"]["error"], "Your comment must have at least 10 characters.");
    }
}

$context["comments"] = $db -> prepareAndExecute("SELECT TicketComments.*, Accounts.display_name, Accounts.account_type FROM TicketComments LEFT JOIN Accounts ON TicketComments.account_id=Accounts.id WHERE ticket_id=? ORDER BY comment_time", $context["ticketInfo"]["ticket_id"]) -> fetchAll();

$viewData["template"] = "tickets/view.twig";
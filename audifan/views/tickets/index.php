<?php

/* @var $audifan Audifan */
$db = $audifan -> getDatabase();

$viewData["template"] = "tickets/index.twig";

$context["myTickets"] = $db -> prepareAndExecute("SELECT Tickets.*, LastCommentTimes.latest_comment_time FROM Tickets LEFT JOIN (SELECT TicketComments.ticket_id, MAX(comment_time) AS latest_comment_time FROM TicketComments GROUP BY TicketComments.ticket_id) AS LastCommentTimes ON Tickets.ticket_id=LastCommentTimes.ticket_id WHERE ticket_account=? ORDER BY ticket_open DESC, ticket_time DESC", $audifan -> getUser() -> getId()) -> fetchAll();

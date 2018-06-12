<?php

/* @var $audifan Audifan */

$viewData["template"] = "admin/ticketlist.twig";

$context["tickets"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM Tickets ORDER BY ticket_id DESC") -> fetchAll();
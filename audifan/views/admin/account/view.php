<?php

/* @var $audifan Audifan */

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, array(
    "options" => array(
        "min_range" => 1
    )
        ));

$viewData["template"] = "admin/account/view.twig";

if (!is_null($id) && $id !== FALSE) {
    // Save edits.
    
    $context["account"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM Accounts WHERE id=?", $id) -> fetch();
    if ($context["account"] !== FALSE) {
        $context["sessions"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT AccountSessions.*, IPs.ip_list FROM AccountSessions LEFT JOIN (SELECT session_key, GROUP_CONCAT(session_ip SEPARATOR '<br />') AS ip_list FROM AccountSessionIPs GROUP BY session_key) AS IPs ON AccountSessions.session_key=IPs.session_key WHERE session_account=? ORDER BY session_last_activity_time DESC", $id) -> fetchAll();
    } else {
        array_push($context["GLOBAL"]["messages"]["error"], "An account with that ID does not exist.");
    }
} else {
    array_push($context["GLOBAL"]["messages"]["error"], "No ID was specified.");
}
<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();
$user = $audifan -> getUser();
$now = $audifan -> getNow();

$viewData["template"] = "community/vipdrawing.twig";

$context["eligibleToEnter"] = false;
$context["enteredIgn"] = "";
$context["timesEntered"] = 0;
$context["maxEntries"] = 0;
$context["totalEntries"] = 0;
$context["error"] = "";

if ($user -> isLoggedIn()) {
    $postFilter = filter_input_array(INPUT_POST, array(
        "submit_remove" => FILTER_DEFAULT,
        "submit_ign" => FILTER_DEFAULT,
        "ign" => array(
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => array(
                "regexp" => '/^[A-Za-z0-9\-\~\_]{2,25}$/'
            )
        )
    ));
    
    if (!is_null($postFilter["submit_remove"])) {
        // Remove name from drawing.
        $db -> prepareAndExecute("UPDATE AccountStuff SET note='' WHERE account_id=? AND item_id=?", $user -> getId(), Inventory::ITEM_VIPDRAWINGENTRY);
    } elseif (!is_null($postFilter["submit_ign"]) && !is_null($postFilter["ign"]) && $postFilter["ign"] !== FALSE) {
        // Look for this IGN.
        if ($db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE item_id=? AND note=?", Inventory::ITEM_VIPDRAWINGENTRY, $postFilter["ign"]) -> rowCount() > 0)
            array_push($context["GLOBAL"]["messages"]["error"], "That IGN has already entered the drawing.");
        else
            $db -> prepareAndExecute("UPDATE AccountStuff SET note=? WHERE account_id=? AND item_id=?", $postFilter["ign"], $user -> getId(), Inventory::ITEM_VIPDRAWINGENTRY);
    }
    
    $item = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE account_id=? AND item_id=?", $user -> getId(), Inventory::ITEM_VIPDRAWINGENTRY) -> fetch();
    if ($item !== FALSE) {
        $context["eligibleToEnter"] = true;
        $context["enteredIgn"] = $item["note"];
        $context["timesEntered"] = $item["charges"];
    }
}

$context["maxEntries"] = $db -> prepareAndExecute("SELECT MAX(charges) FROM AccountStuff WHERE item_id=? AND note!=''", Inventory::ITEM_VIPDRAWINGENTRY) -> fetchColumn();
$context["totalEntries"] = $db -> prepareAndExecute("SELECT SUM(charges) FROM AccountStuff WHERE item_id=? AND note!=''", Inventory::ITEM_VIPDRAWINGENTRY) -> fetchColumn();

// Time until next drawing.
$time = $now -> getTime();
$weekday = $now -> getDayNumberOfWeek();

if ($weekday == 5 && date("n", $time - (3600 * 24 * 5)) != date("n", $time + (3600 * 24 * 2))) {
    // If the drawing took place today, add a day to the time and calculate it from there instead.
    $time += (3600 * 24);
    $weekday = (int) date("N", $time);
}

while ($weekday != 5) {
    $time += (3600 * 24);
    $weekday = (int) date("N", $time);
}

while (date("n", $time - (3600 * 24 * 5)) == date("n", $time + (3600 * 24 * 2))) {
    $time += (3600 * 24 * 7);
}

$context["nextDrawingTime"] = mktime(0, 0, 0, date("n", $time), date("j", $time), date("Y", $time));

$context["months"] = ["","January","February","March","April","May","June","July","August","September","October","November","December"];
$context["winners"] = $db -> prepareAndExecute("SELECT VIPDrawingWinners.*, Accounts.display_name FROM VIPDrawingWinners LEFT JOIN Accounts ON VIPDrawingWinners.winner_account_id=Accounts.id ORDER BY winner_year DESC, winner_month DESC") -> fetchAll();
<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();
$user = $audifan -> getUser();
$inventory = $user -> getInventory();

$viewData["template"] = "community/index.twig";

$context["newestMember"] = $db -> prepareAndExecute("SELECT * FROM Accounts ORDER BY join_time DESC LIMIT 1") -> fetch();
$context["randomMember"] = $db -> prepareAndExecute("SELECT * FROM Accounts ORDER BY rand() LIMIT 1") -> fetch();




$postFilter = filter_input_array(INPUT_POST, array(
    "submit" => FILTER_DEFAULT,
    "text" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => '/^.{1,75}$/'
        )
    ),
    "color" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => '/^[0-9a-zA-Z]{6}$/'
        )
    )
));

if ($user -> isLoggedIn() && !is_null($postFilter["submit"])) {
    // Process new megaphone.
    if ($inventory -> hasItem(Inventory::ITEM_MEGAPHONETICKET) && $db -> prepareAndExecute("SELECT COUNT(*) FROM Megaphones WHERE mega_account=? AND mega_expiretime>?", $user -> getId(), time()) -> fetchColumn() < 3) {
        if (!is_null($postFilter["text"]) && $postFilter["text"] !== FALSE) {
            $color = "ffffff";
            if (!is_null($postFilter["color"]) && $postFilter["color"] !== FALSE)
                $color = $postFilter["color"];

            $db -> prepareAndExecute("INSERT INTO Megaphones(mega_account,mega_text,mega_color,mega_expiretime) VALUES(?,?,?,?)", $user -> getId(), $postFilter["text"], $color, time() + (3600 * 24));
            
            // Remove a mega ticket.
            $tickets = $db -> prepareAndExecute("SELECT stuff_id, charges FROM AccountStuff WHERE item_id=? AND account_id=?", Inventory::ITEM_MEGAPHONETICKET, $user -> getId()) -> fetch();
            if ($tickets["charges"] > 1) {
                // Reduce charge.
                $db -> prepareAndExecute("UPDATE AccountStuff SET charges=charges-1 WHERE stuff_id=?", $tickets["stuff_id"]);
            } else {
                // Remove the item.
                $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $tickets["stuff_id"]);
            }
            $inventory -> invalidateCache();
        } else {
            array_push($context["GLOBAL"]["messages"]["error"], "Your message must have at least one character in it.");    
        }
    } else {
        array_push($context["GLOBAL"]["messages"]["error"], "You may have a maximum of 3 concurrent megaphones.");
    }
}

// Delete a mega.
$deleteMega = filter_input(INPUT_GET, "deletemega", FILTER_VALIDATE_INT, array(
    "options" => array(
        "min_range" => 1
    )
));

if (!is_null($deleteMega) && $deleteMega !== FALSE) {
    // Delete a mega (make its expire time 
    $db -> prepareAndExecute("UPDATE Megaphones SET mega_expiretime=0 WHERE mega_id=? AND mega_account=?", $deleteMega, $user -> getId());
}



$context["megatickets"] = 0;
$context["mymegas"] = array();

if ($user -> isLoggedIn()) {
    $context["mymegas"] = $db -> prepareAndExecute("SELECT * FROM Megaphones WHERE mega_account=? AND mega_expiretime>?", $user -> getId(), time()) -> fetchAll();

    $inv = $inventory -> getFullList();
    foreach ($inv as $i) {
        if ($i["item_id"] == Inventory::ITEM_MEGAPHONETICKET) {
            $context["megatickets"] = $i["charges"];
            break;
        }
    }
}

$q  = "SELECT Megaphones.*, Accounts.display_name, VIPs.is_vip FROM Megaphones ";
$q .= "LEFT JOIN Accounts ON Megaphones.mega_account=Accounts.id ";
$q .= "LEFT JOIN (SELECT account_id, 1 AS is_vip FROM AccountStuff WHERE item_id=? AND expire_time>?) AS VIPs ON Megaphones.mega_account=VIPs.account_id ";
$q .= "WHERE mega_expiretime>? ";
$q .= "ORDER BY RAND()";
$context["allmegas"] = $db -> prepareAndExecute($q, Inventory::ITEM_VIPBADGE, time(), time()) -> fetchAll();
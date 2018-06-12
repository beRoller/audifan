<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();
$user = $audifan -> getUser();

$viewData["template"] = "account/stuff.twig";

$badgeIds = array(1, 2, 7, 8, 9, 10, 14, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 38, 39);
$voucherIds = array(12, 13);
$otherIds = array(3, 4, 5, 6, 11, 15, 34, 35, 36,40,41);
$coinBoxIds = array(31, 32, 33, 37);

$context["prizes"] = array(
    12 => array(// Badge Voucher
        16 => "Ballroom Top Hat",
        17 => "Ballroom Flowers",
        18 => "Guardian Angel",
        19 => "Microphone",
        20 => "DJ",
        21 => "Flower Heart",
        22 => "Beat Up Mission Star",
        23 => "Beat Up Tournament Medal",
        24 => "Hidden Mission Star",
        25 => "Normal Mission Star",
        26 => "Couple Tournament Medal",
        27 => "Beat Rush Mission Star",
        28 => "One Two Party Mission Star",
        29 => "Beat Rush Tournament Medal",
        38 => "Guitar Tournament Medal"
    ),
    13 => array(// VIP Drawing Voucher
        "face" => "Any cash Face item (30 days) that has a 7 day price of 900 Cash or less",
        "shoes" => "Any cash Shoes item (30 days) that has a 7 day price of 900 Cash or less",
        "acc" => "Any cash Accessory item (7 days) that has a price of 1,800 cash or less",
        "x2exp" => "Three (3) x2 EXP Cards (20 rounds each)",
        "x2den" => "Three (3) BEATS 2x Cards (20 rounds each)",
        "x3exp" => "One (1) 3X EXP Card (20 rounds)",
        "x3den" => "One (1) 3X BEATS Card (20 rounds)",
        "2xcpl" => "Two (2) 2X COUPLE POINT Items (10 rounds each)",
        "event" => "Event Room Pass (7 days)",
        "diary" => "Premium Diary (30 days)",
        "cssfm" => "Crystal Star Style Finish (7 days)",
        "ffm" => "Fantasia Finish (7 days)",
        "tfm" => "Twinkling Finish (7 days)",
        "pm" => "Premium Messenger (30 days)",
        "pmp" => "Premium Messenger Plus (30 days)",
        "rm2016" => "Happy Audition 2016 Room Background (30 days)",
        "rmcool" => "Cool Summer Room Background (30 days)",
        "rmholc" => "Music holic Room Background (30 days)"
    )
);

// Process form submitted data.
$postFilter = filter_input_array(INPUT_POST, array(
    // Prize voucher claim
    "submit_claimprize" => FILTER_DEFAULT,
    "id" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    ),
    "prize" => FILTER_SANITIZE_STRING,
    "prizename" => FILTER_SANITIZE_STRING,
    // Badge visibility.
    "submit_show" => FILTER_DEFAULT,
    // Opening Coin Boxes
    "regular" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 0
        )
    ),
    "double" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 0
        )
    ),
    "triple" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 0
        )
    ),
    "mystery" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 0
        )
    ),
    "submit_selected" => FILTER_DEFAULT,
    "submit_all" => FILTER_DEFAULT
        ));




/**
 * "Opens" a coin box, returning the number of coins in it.
 * The Mystery Coin Box should first be checked to determine if it should contain a
 * coin bonus item instead of using this method to get the number of coins in it.
 * @param int $type The type of box to open (Inventory::ITEM_* value).
 * @return int The number of coins in a coin box of the specified type.
 */
function openCoinBox($type) {
    $coinBoxRanges = array(
        Inventory::ITEM_COINBOX => array(200, 400),
        Inventory::ITEM_DOUBLECOINBOX => array(400, 800),
        Inventory::ITEM_TRIPLECOINBOX => array(600, 1200),
        Inventory::ITEM_MYSTERYCOINBOX => array(1200, 1500)
    );
    
    $coins = 0;
    if (array_key_exists($type, $coinBoxRanges))
        $coins = rand($coinBoxRanges[$type][0], $coinBoxRanges[$type][1]);
    return $coins;
}






if (!is_null($postFilter["submit_claimprize"])) {
    // Process prize voucher claim.
    if (!is_null($postFilter["id"]) && $postFilter["id"] !== FALSE) {
        $item = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE stuff_id=?", $postFilter["id"]) -> fetch();
        if ($item !== FALSE && $item["account_id"] == $user -> getId()) {
            if (in_array($item["item_id"], array_keys($context["prizes"]))) {
                // Check prize.
                if (in_array($postFilter["prize"], array_keys($context["prizes"][$item["item_id"]]))) {
                    switch ($item["item_id"]) {
                        case 12: // badge
                            $user -> getInventory() -> addItem($postFilter["prize"], (3600 * 24 * 30));
                            // Delete voucher.
                            $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $item["stuff_id"]);
                            break;

                        case 13: // vip drawing prize
                            $db -> prepareAndExecute("UPDATE AccountStuff SET note=?,expire_time=-1 WHERE stuff_id=?", $postFilter["prize"] . ";" . $postFilter["prizename"], $item["stuff_id"]);
                            break;
                    }
                    array_push($context["GLOBAL"]["messages"]["success"], "The prize voucher was successfully claimed.");
                }
            }
        }
    }
} elseif (!is_null($postFilter["submit_selected"]) || !is_null($postFilter["submit_all"])) {
    // Open all or selected coin boxes.
    $openAll = !is_null($postFilter["submit_all"]);
    
    // All boxes, sorted by item ID.
    $allBoxes = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE item_id IN (" . implode(",", $coinBoxIds) . ") AND (expire_time>? OR expire_time=-1) AND account_id=? ORDER BY item_id, expire_time", time(), $audifan -> getUser() -> getId()) -> fetchAll();

    $numToOpen = array(
        "regular" => (!is_null($postFilter["regular"]) && $postFilter["regular"] !== FALSE) ? $postFilter["regular"] : 0,
        "double" => (!is_null($postFilter["double"]) && $postFilter["double"] !== FALSE) ? $postFilter["double"] : 0,
        "triple" => (!is_null($postFilter["triple"]) && $postFilter["triple"] !== FALSE) ? $postFilter["triple"] : 0,
        "mystery" => (!is_null($postFilter["mystery"]) && $postFilter["mystery"] !== FALSE) ? $postFilter["mystery"] : 0
    );

    $totalOpened = array(
        "regular" => 0,
        "double" => 0,
        "triple" => 0,
        "mystery" => 0
    );

    $totalCoins = 0;
    $num25CoinBonusItems = 0;

    foreach ($allBoxes as $b) {
        switch ($b["item_id"]) {
            case Inventory::ITEM_COINBOX:
                if ($openAll || $totalOpened["regular"] < $numToOpen["regular"]) {
                    $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $b["stuff_id"]);
                    $totalCoins += openCoinBox(Inventory::ITEM_COINBOX);
                    $totalOpened["regular"] ++;
                }
                break;

            case Inventory::ITEM_DOUBLECOINBOX:
                if ($openAll || $totalOpened["double"] < $numToOpen["double"]) {
                    $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $b["stuff_id"]);
                    $totalCoins += openCoinBox(Inventory::ITEM_DOUBLECOINBOX);
                    $totalOpened["double"] ++;
                }
                break;

            case Inventory::ITEM_TRIPLECOINBOX:
                if ($openAll || $totalOpened["triple"] < $numToOpen["triple"]) {
                    $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $b["stuff_id"]);
                    $totalCoins += openCoinBox(Inventory::ITEM_TRIPLECOINBOX);
                    $totalOpened["triple"] ++;
                }
                break;

            case Inventory::ITEM_MYSTERYCOINBOX:
                if ($openAll || $totalOpened["mystery"] < $numToOpen["mystery"]) {
                    $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $b["stuff_id"]);
                    if (rand(1, 2) == 2)
                        $totalCoins += openCoinBox(Inventory::ITEM_MYSTERYCOINBOX);
                    else
                        $num25CoinBonusItems++;
                    $totalOpened["mystery"] ++;
                }
                break;
        }
    }

    $numOpened = $totalOpened["regular"] + $totalOpened["double"] + $totalOpened["triple"] + $totalOpened["mystery"];

    if ($totalCoins > 0) {
        $source = "Opened " . $numOpened . " Coin Box";
        if ($numOpened != 1)
            $source .= "es";
        $totalCoins = $audifan -> getUser() -> getInventory() -> addCoins($totalCoins, $source);
    }
    
    if ($num25CoinBonusItems > 0) {
        if ($audifan -> getUser() -> getInventory() -> hasItem(Inventory::ITEM_COINBONUS5PERCENT))
            $audifan -> getUser() -> getInventory() -> removeItem(Inventory::ITEM_COINBONUS5PERCENT);
        if ($audifan -> getUser() -> getInventory() -> hasItem(Inventory::ITEM_COINBONUS15PERCENT))
            $audifan -> getUser() -> getInventory() -> removeItem(Inventory::ITEM_COINBONUS15PERCENT);

        $audifan -> getUser() -> getInventory() -> addItem(Inventory::ITEM_COINBONUS25PERCENT, 3600 * 24 * 7 * $num25CoinBonusItems);
    }

    $msg = "Opened " . $numOpened . " Coin Box";
    if ($numOpened != 1)
        $msg .= "es";
    $msg .= ".";
    if ($numOpened > 0) {
        $msg .= " You received ";
        if ($totalCoins > 0)
            $msg .= number_format($totalCoins) . " coins";
        if ($totalCoins > 0 && $num25CoinBonusItems > 0)
            $msg .= " and ";
        if ($num25CoinBonusItems > 0)
            $msg .= $num25CoinBonusItems . " Coin +25% item(s)";
        $msg .= ".";
    }

    array_push($context["GLOBAL"]["messages"]["success"], $msg);
} else {
    $openBoxId = filter_input(INPUT_GET, "openbox", FILTER_VALIDATE_INT, array(
        "options" => array(
            "min_range" => 1
        )
    ));
    
    if (!is_null($openBoxId) && $openBoxId !== FALSE) {
        // Open a single coin box.
        $boxInfo = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE stuff_id=? AND item_id IN (" . implode(",", $coinBoxIds) . ") AND account_id=? AND (expire_time>? OR expire_time=-1)", $openBoxId, $audifan -> getUser() -> getId(), time()) -> fetch();
        if ($boxInfo !== FALSE) {
            if ($boxInfo["item_id"] == Inventory::ITEM_MYSTERYCOINBOX && rand(1, 2) == 2) {
                // Give coin bonus item.
                if ($audifan -> getUser() -> getInventory() -> hasItem(Inventory::ITEM_COINBONUS5PERCENT))
                    $audifan -> getUser() -> getInventory() -> removeItem(Inventory::ITEM_COINBONUS5PERCENT);
                if ($audifan -> getUser() -> getInventory() -> hasItem(Inventory::ITEM_COINBONUS15PERCENT))
                    $audifan -> getUser() -> getInventory() -> removeItem(Inventory::ITEM_COINBONUS15PERCENT);

                $audifan -> getUser() -> getInventory() -> addItem(Inventory::ITEM_COINBONUS25PERCENT, 3600 * 24 * 7);
                array_push($context["GLOBAL"]["messages"]["success"], "Opened a Mystery Coin Box. You received a +25% Coin Bonus item (7 days).");
            } else {
                // Give coins.
                $receivedCoins = $audifan -> getUser() -> getInventory() -> addCoins(openCoinBox($boxInfo["item_id"]), "Opened a Coin Box");
                array_push($context["GLOBAL"]["messages"]["success"], "Opened a " . Inventory::$ITEMINFO[$boxInfo["item_id"]][2] . ". You received " . number_format($receivedCoins) . " coins.");
            }
            
            $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $boxInfo["stuff_id"]);
        }
    }
}

$context["itemInfo"] = Inventory::$ITEMINFO;
$context["badges"] = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE item_id IN (" . implode(",", $badgeIds) . ") AND (expire_time>? OR expire_time=-1) AND account_id=?", time(), $audifan -> getUser() -> getId()) -> fetchAll();

// Save visibilities.
if (!is_null($postFilter["submit_show"])) {
    for ($i = 0; $i < sizeof($context["badges"]); $i++) {
        $b = $context["badges"][$i];
        $newShow = !is_null(filter_input(INPUT_POST, "show_" . $b["stuff_id"])) ? 1 : 0;
        if ($newShow != $b["in_use"]) {
            // visibility changed.
            $db -> prepareAndExecute("UPDATE AccountStuff SET in_use=? WHERE stuff_id=?", $newShow, $b["stuff_id"]);
            $context["badges"][$i]["in_use"] = $newShow;
            array_push($context["GLOBAL"]["messages"]["success"], sprintf("Your %s is now %s.", Inventory::$ITEMINFO[$b["item_id"]][2], $newShow == 1 ? "visible" : "hidden"));
        }
    }
}

$context["vouchers"] = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE item_id IN (" . implode(",", $voucherIds) . ") AND (expire_time>? OR expire_time=-1) AND account_id=?", time(), $audifan -> getUser() -> getId()) -> fetchAll();

$context["coinboxes"] = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE item_id IN (" . implode(",", $coinBoxIds) . ") AND (expire_time>? OR expire_time=-1) AND account_id=?", time(), $audifan -> getUser() -> getId()) -> fetchAll();

$res = $db -> prepareAndExecute("SELECT item_id, COUNT(*) AS num_boxes FROM AccountStuff WHERE item_id IN (" . implode(",", $coinBoxIds) . ") AND (expire_time>? OR expire_time=-1) AND account_id=? GROUP BY item_id", time(), $audifan -> getUser() -> getId()) -> fetchAll();

$context["coinboxcounts"] = array(
    31 => 0,
    32 => 0,
    33 => 0,
    37 => 0
);
foreach ($res as $r) {
    $context["coinboxcounts"][$r["item_id"]] = $r["num_boxes"];
}

$context["others"] = $db -> prepareAndExecute("SELECT * FROM AccountStuff WHERE item_id IN (" . implode(",", $otherIds) . ") AND (expire_time>? OR expire_time=-1) AND account_id=?", time(), $audifan -> getUser() -> getId()) -> fetchAll();

$context["history"] = $db -> prepareAndExecute("SELECT * FROM CoinHistory WHERE account_id=? ORDER BY history_time DESC", $user -> getId()) -> fetchAll();

$context["totalCoins"] = $db -> prepareAndExecute("SELECT coin_total FROM Accounts WHERE id=?", $user -> getId()) -> fetchColumn();

// Bonus info
if ($user -> isLoggedIn()) {
    $context["jointime"] = $db -> prepareAndExecute("SELECT join_time FROM Accounts WHERE id=?", $user -> getId()) -> fetchColumn();
    $context["oneyearago"] = time() - (3600 * 24 * 365);
    $context["hasCoinBonusItem"] = $user -> getInventory() -> hasAnyItems(array(Inventory::ITEM_COINBONUS5PERCENT, Inventory::ITEM_COINBONUS15PERCENT, Inventory::ITEM_COINBONUS25PERCENT));
}
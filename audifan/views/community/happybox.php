<?php

$viewData["template"] = "community/happybox.twig";

/* @var $audifan Audifan */

/* @var $user User */
$user = $audifan -> getUser();
$db = $audifan -> getDatabase();

if ($user -> isLoggedIn()) {
    $user -> updateSession();

    /* @var $inventory Inventory */
    $inventory = $user -> getInventory();
    $qp = $user -> getQP();

    $context["timeTilNextSpin"] = $user -> getTimeUntilNextHappyBoxSpin();

    // Get current Jackpot info.
    $jackpotInfo = array();
    foreach ($db -> prepareAndExecute("SELECT * FROM SiteVariables WHERE var_name IN ('happyBoxJackpotAmount','happyBoxJackpotChance')") as $row) {
        $jackpotInfo[$row["var_name"]] = $row["var_int_value"];
    }

    if (filter_input(INPUT_GET, "spin") !== NULL) {
        $out = array(
            "resp" => "error",
            "sec" => $context["timeTilNextSpin"],
            "data1" => "",
            "data2" => ""
        );

        $badgeIds = array(16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 38);
        $prizeWon = -1; // -1 is a coin prize, -2 is the jackpot.  Otherwise, it's the item ID.
        $prizeData = array(0, 0);

        if ($context["timeTilNextSpin"] <= 3) {
            // Check for a Happy Box quest and give credit for it.
            // HB spin is Easy Quest #8.
            $q = $db -> prepareAndExecute("SELECT req_id FROM QuestRequirements WHERE req_week_number=? AND req_year=? AND req_number=8 AND req_difficulty=1", date("W"), date("Y"));
            if ($q -> rowCount() === 1) {
                $reqId = $q -> fetchColumn();
                if ($db -> prepareAndExecute("SELECT * FROM QuestSubmissions WHERE submit_req_id=? AND submit_account_id=?", $reqId, $user -> getId()) -> rowCount() === 0) {
                    $db -> prepareAndExecute("INSERT INTO QuestSubmissions(submit_account_id,submit_req_id,submit_screenshot,submit_grade_status,submit_last_grade_time,submit_time) VALUES(?,?,?,?,?,?)", $user -> getId(), $reqId, "", 2, time(), time());
                    $user -> updateSession(User::SESSIONPART_QUESTS);
                }
            }

            $number = rand(1, 10000);
            // $number = 4976;
            // Temporary event:
            //if ($number > 3625)
            //    $number = 3500;

            if ($number <= 500) { // 5.00%
                // -40% item
                $prizeWon = Inventory::ITEM_HBCOOLDOWN40;
            } elseif ($number <= 1000) { // 5.00%
                // Badge Voucher
                $ids = [16,17,18,19,20,21,22,23,24,25,26,27,28,29,38];
                if ($inventory -> numOfAllItems($ids) + $inventory -> numOfItem(Inventory::ITEM_BADGEVOUCHER) < 2) {
                    $prizeWon = Inventory::ITEM_BADGEVOUCHER;
                }
            } elseif ($number <= 1125) { // 1.25%
                // VIP Drawing Entry
                if ($inventory -> hasItem(Inventory::ITEM_VIPDRAWINGENTRY)) {
                    $prizeWon = Inventory::ITEM_VIPDRAWINGENTRY;
                }
            } elseif ($number <= 2125) { // 10.00%
                // Coin Box
                $prizeWon = Inventory::ITEM_COINBOX;
            } elseif ($number <= 2825) { // 7.00%
                // Double Coin Box
                $prizeWon = Inventory::ITEM_DOUBLECOINBOX;
            } elseif ($number <= 3125) { // 3.00%
                // Triple Coin Box
                $prizeWon = Inventory::ITEM_TRIPLECOINBOX;
            } elseif ($number <= 3275) { // 1.50%
                // Mystery Coin Box
                $prizeWon = Inventory::ITEM_MYSTERYCOINBOX;
            } elseif ($number <= 3875) { // 6.00%
                // Coin +5% Item
                if (!$inventory -> hasAnyItems(array(Inventory::ITEM_COINBONUS15PERCENT, Inventory::ITEM_COINBONUS25PERCENT))) {
                    $prizeWon = Inventory::ITEM_COINBONUS5PERCENT;
                }
            } elseif ($number <= 4275) { // 4.00%
                // Coin +15% Item
                if (!$inventory -> hasItem(Inventory::ITEM_COINBONUS25PERCENT)) {
                    $prizeWon = Inventory::ITEM_COINBONUS15PERCENT;
                }
            } elseif ($number <= 4475) { // 2.00%
                // Coin +25% Item
                $prizeWon = Inventory::ITEM_COINBONUS25PERCENT;
            } elseif ($number <= 4975) { // 5.00%
                // Megaphone Ticket
                $prizeWon = Inventory::ITEM_MEGAPHONETICKET;
            }
            
            if ($prizeWon == -1) { // They didn't win anything else.
                $jackpotAvailable = ($jackpotInfo["happyBoxJackpotAmount"] >= 1000);

                $minRange = 4975; // The minimum number range for the chance. The minimum plus the jackpot chance from the database is the number range.
                if ($jackpotAvailable && $number > $minRange && $number <= $minRange + $jackpotInfo["happyBoxJackpotChance"]) {
                    // Jackpot!
                    // Add coins to their inventory.
                    $coinsWon = $jackpotInfo["happyBoxJackpotAmount"];
                    
                    // Note: Coin bonuses do not apply to the Jackpot.
                    $actualCoinsWon = $inventory -> addCoins($jackpotInfo["happyBoxJackpotAmount"], "Happy Box Jackpot", false);
                    
                    // Remove the badge from the previous winner's inventory.
                    $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE item_id=?", Inventory::ITEM_JACKPOTBADGE);

                    // Give them the jackpot badge.
                    $inventory -> addItem(Inventory::ITEM_JACKPOTBADGE, -1);

                    // Reset site variables.
                    $db -> beginTransaction();
                    $db -> prepareAndExecute("UPDATE SiteVariables SET var_int_value=50 WHERE var_name='happyBoxJackpotChance'");
                    $db -> prepareAndExecute("UPDATE SiteVariables SET var_int_value=0 WHERE var_name='happyBoxJackpotAmount'");
                    $db -> finishTransaction();
                    
                    $prizeWon = -2;
                    $prizeData[0] = $actualCoinsWon;
                    $prizeData[1] = $actualCoinsWon;
                    $out["data1"] = number_format($actualCoinsWon);
                } else {
                    // No prize, so they won coins.
                    // Choose the amount of coins.
                    $coinsWon = rand(10, 50);

                    // Add those coins to their inventory.
                    $actualCoinsWon = $inventory -> addCoins($coinsWon, "Happy Box");

                    // Add those coins to the jackpot amount.
                    $db -> prepareAndExecute("UPDATE SiteVariables SET var_int_value=var_int_value+? WHERE var_name='happyBoxJackpotAmount'", $actualCoinsWon);

                    // If the Jackpot was available, but they didn't win it, increase the chance to win it.
                    if ($jackpotAvailable)
                        $db -> prepareAndExecute("UPDATE SiteVariables SET var_int_value=var_int_value+1 WHERE var_name='happyBoxJackpotChance'");

                    $prizeData[0] = $actualCoinsWon;
                    $prizeData[1] = $actualCoinsWon - $coinsWon;
                    $out["data1"] = number_format($actualCoinsWon);
                    $out["newbalance"] = number_format($inventory -> getCoinBalance());
                    $out["newjackpot"] = number_format($db -> prepareAndExecute("SELECT var_int_value FROM SiteVariables WHERE var_name='happyBoxJackpotAmount'") -> fetchColumn());
                }
            }

            $cooldownItemDuration = (3600 * 24 * 7);
            $coinBonusItemDuration = (3600 * 24 * 7);
            $voucherDuration = (3600 * 24 * 7 * 2);

            if ($prizeWon > 0) {
                // Add the prize to the user's inventory.
                switch ($prizeWon) {
                    case Inventory::ITEM_HBCOOLDOWN40:
                        if ($inventory -> hasItem(Inventory::ITEM_HBCOOLDOWN10))
                            $inventory -> removeItem(Inventory::ITEM_HBCOOLDOWN10);
                        if ($inventory -> hasItem(Inventory::ITEM_HBCOOLDOWN25))
                            $inventory -> removeItem(Inventory::ITEM_HBCOOLDOWN25);

                        $inventory -> addItem(Inventory::ITEM_HBCOOLDOWN40, $cooldownItemDuration);
                        break;

                    case Inventory::ITEM_BADGEVOUCHER:
                        $inventory -> addItem(Inventory::ITEM_BADGEVOUCHER, $voucherDuration);
                        break;

                    case Inventory::ITEM_VIPDRAWINGENTRY:
                        $inventory -> addItem(Inventory::ITEM_VIPDRAWINGENTRY, -1, 1);
                        break;
                    
                    case Inventory::ITEM_COINBOX:
                        $inventory -> addItem(Inventory::ITEM_COINBOX, $voucherDuration);
                        break;
                    
                    case Inventory::ITEM_DOUBLECOINBOX:
                        $inventory -> addItem(Inventory::ITEM_DOUBLECOINBOX, $voucherDuration);
                        break;
                    
                    case Inventory::ITEM_TRIPLECOINBOX:
                        $inventory -> addItem(Inventory::ITEM_TRIPLECOINBOX, $voucherDuration);
                        break;
                    
                    case Inventory::ITEM_MYSTERYCOINBOX:
                        $inventory -> addItem(Inventory::ITEM_MYSTERYCOINBOX, $voucherDuration);
                        break;
                    
                    case Inventory::ITEM_COINBONUS5PERCENT:
                        $inventory -> addItem(Inventory::ITEM_COINBONUS5PERCENT, $coinBonusItemDuration);
                        break;
                    
                    case Inventory::ITEM_COINBONUS15PERCENT:
                        if ($inventory -> hasItem(Inventory::ITEM_COINBONUS5PERCENT))
                            $inventory -> removeItem(Inventory::ITEM_COINBONUS5PERCENT);
                        
                        $inventory -> addItem(Inventory::ITEM_COINBONUS15PERCENT, $coinBonusItemDuration);
                        break;
                    
                    case Inventory::ITEM_COINBONUS25PERCENT:
                        if ($inventory -> hasItem(Inventory::ITEM_COINBONUS5PERCENT))
                            $inventory -> removeItem(Inventory::ITEM_COINBONUS5PERCENT);
                        if ($inventory -> hasItem(Inventory::ITEM_COINBONUS15PERCENT))
                            $inventory -> removeItem(Inventory::ITEM_COINBONUS15PERCENT);

                        $inventory -> addItem(Inventory::ITEM_COINBONUS25PERCENT, $coinBonusItemDuration);
                        break;
                        
                    case Inventory::ITEM_MEGAPHONETICKET:
                        $inventory -> addItem(Inventory::ITEM_MEGAPHONETICKET, -1, 1);
                        break;
                }
            }

            // Add to winner history.
            $db -> prepareAndExecute("INSERT INTO HappyBoxWinners(account_id,prize_id,prize_data1,prize_data2,win_time) VALUES(?,?,?,?,?)", $user -> getId(), $prizeWon, $prizeData[0], $prizeData[1], time());

            $user -> setFlag("last_hb_spin", $audifan -> getCurrentTime());
            $context["timeTilNextSpin"] = $user -> getTimeUntilNextHappyBoxSpin();

            $audifan -> logUserEvent(sprintf("Account ID %d spun HB. Prize: %d, data1=%s", $audifan -> getUser() -> getId(), $prizeWon, $out["data1"]));

            $out["sec"] = $context["timeTilNextSpin"];
            $out["resp"] = "prize_" . $prizeWon;
        }

        print json_encode($out);
        exit;
    }
}

$context["itemList"] = array(
    3 => array(
        "name" => "-10% Happy Box Cooldown",
        "description" => "Reduces the cooldown time of the Happy Box by 10%.",
        "category" => "ITEM",
        "type" => "EXPIRE"
    ),
    4 => array(
        "name" => "-25% Happy Box Cooldown",
        "description" => "Reduces the cooldown time of the Happy Box by 25%.",
        "category" => "ITEM",
        "type" => "EXPIRE"
    ),
    5 => array(
        "name" => "-40% Happy Box Cooldown",
        "description" => "Reduces the cooldown time of the Happy Box by 40%.",
        "category" => "ITEM",
        "type" => "EXPIRE"
    ),
    6 => array(
        "name" => "[ADMIN] -100% Happy Box Cooldown",
        "description" => "Reduces the cooldown time of the Happy Box by 100%.",
        "category" => "ITEM",
        "type" => "EXPIRE"
    ),
    11 => array(
        "name" => "Megaphone Ticket",
        "description" => "Lets you broadcast a message at the top of every page on Audifan.",
        "category" => "ITEM",
        "type" => "CHARGE"
    ),
    12 => array(
        "name" => "Badge Voucher",
        "description" => "Lets you redeem a badge that will appear next to your name on the site.",
        "category" => "VOUCHER",
        "type" => "EXPIRE"
    ),
    15 => array(
        "name" => '<a href="/community/vipdrawing/">VIP Drawing</a> Entry',
        "description" => "An entry in the VIP Drawing.",
        "category" => "ITEM",
        "type" => "CHARGE"
    ),
    31 => array(
        "name" => "Coin Box",
        "description" => "Contains a moderate amount of coins.",
        "category" => "COINBOX",
        "type" => "EXPIRE"
    ),
    32 => array(
        "name" => "Double Coin Box",
        "description" => "Contains twice the number of coins as a regular Coin Box.",
        "category" => "COINBOX",
        "type" => "EXPIRE"
    ),
    33 => array(
        "name" => "Triple Coin Box",
        "description" => "Contains triple the number of coins as a regular Coin Box.",
        "category" => "COINBOX",
        "type" => "EXPIRE"
    ),
    34 => array(
        "name" => "Coin +5% Item",
        "description" => "Increases coin gains from all sources by 5%.",
        "category" => "ITEM",
        "type" => "EXPIRE"
    ),
    35 => array(
        "name" => "Coin +15% Item",
        "description" => "Increases coin gains from all sources by 15%.",
        "category" => "ITEM",
        "type" => "EXPIRE"
    ),
    36 => array(
        "name" => "Coin +25% Item",
        "description" => "Increases coin gains from all sources by 25%.",
        "category" => "ITEM",
        "type" => "EXPIRE"
    ),
    37 => array(
        "name" => "Mystery Coin Box",
        "description" => "Contains either a large amount of coins or a Coin +25% Item.",
        "category" => "COINBOX",
        "type" => "EXPIRE"
    )
);

$context["qpThreshold"] = $audifan -> getConfigVar("happyBoxQPThreshold");

$context["recentWinners"] = $db -> prepareAndExecute("SELECT HappyBoxWinners.*, Accounts.display_name, Accounts.profile_pic_type FROM HappyBoxWinners LEFT JOIN Accounts ON HappyBoxWinners.account_id=Accounts.id ORDER BY HappyBoxWinners.win_time DESC LIMIT 20") -> fetchAll();
$context["totalWinners"] = array();

foreach ($db -> prepareAndExecute("SELECT prize_id, COUNT(*) AS prize_count FROM HappyBoxWinners GROUP BY prize_id ORDER BY prize_id") as $row) {
    $context["totalWinners"][$row["prize_id"]] = $row["prize_count"];
}

$context["dropRates"] = [
    5   => "Low",
    11  => "Low",
    12  => "Low",
    15  => "Very Low",
    31  => "High",
    32  => "Medium",
    33  => "Low",
    37  => "Very Low",
    34  => "Medium",
    35  => "Low",
    36  => "Very Low"
    
];

$context["totalCoins"] = $db -> prepareAndExecute("SELECT SUM(prize_data1) FROM HappyBoxWinners WHERE prize_id=-1") -> fetchColumn();

if ($user -> isLoggedIn()) {
    $context["inventory"] = $user -> getInventory() -> getSimpleList();

    $context["itemTimeLeft"] = array();
    foreach ($user -> getInventory() -> getFullList() as $item) {
        if ($item["expire_time"] == -1)
            $context["itemTimeLeft"][$item["item_id"]] = -1;
        else
            $context["itemTimeLeft"][$item["item_id"]] = $item["expire_time"] - $audifan -> getCurrentTime();
    }

    $context["myPrizes"] = $db -> prepareAndExecute("SELECT * FROM HappyBoxWinners WHERE account_id=? ORDER BY win_time DESC LIMIT 10", $user -> getId()) -> fetchAll();
    
    $context["cooldownReductionHours"] = ((8 * 3600) - $user -> getTimeBetweenHappyBoxSpins()) / 3600;
    
    $jackpotInfo["happyBoxJackpotChance"] = min($jackpotInfo["happyBoxJackpotChance"], 10000 - 4975);
    $context["jackpotInfo"] = $jackpotInfo;
    $context["lastJackpotWinner"] = $db -> prepareAndExecute("SELECT HappyBoxWinners.*, Accounts.display_name FROM HappyBoxWinners LEFT JOIN Accounts ON HappyBoxWinners.account_id=Accounts.id WHERE HappyBoxWinners.prize_id=-2 ORDER BY HappyBoxWinners.win_time DESC LIMIT 1") -> fetch();
}
<?php

/* @var $audifan Audifan */

$viewData["template"] = "store/index.twig";

// Prices by item ID.
$storePrices = array(
    Inventory::ITEM_MEGAPHONETICKET => 3500,
    16 => 5000,
    17 => 5000,
    18 => 5000,
    19 => 5000,
    20 => 5000,
    21 => 5000,
    22 => 5000,
    23 => 5000,
    24 => 5000,
    25 => 5000,
    26 => 5000,
    27 => 5000,
    28 => 5000,
    29 => 5000,
    Inventory::ITEM_HBCOOLDOWN10 => 2000,
    Inventory::ITEM_BADGE38 => 5000,
    Inventory::ITEM_NOADS => 25000
);

// Apply multiplier to prices.
$context["priceMultiplier"] = $audifan -> getConfigVar("storePriceMultiplier");
foreach ($storePrices as $k => $v) {
    $storePrices[$k] = floor($v * $context["priceMultiplier"]);
}

$buyFilter = filter_input_array(INPUT_GET, array(
    "buy" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    )
));

if ($audifan -> getUser() -> isLoggedIn() && !is_null($buyFilter) && $buyFilter !== FALSE) {
    // Process purchase.
    if (array_key_exists($buyFilter["buy"], $storePrices)) {
        $out = array(
            "error" => "",
            "newbalance" => number_format($audifan -> getUser() -> getInventory() -> getCoinBalance())
        );
        
        $canPurchase = true;
        
        $badgeIds = [16,17,18,19,20,21,22,23,24,25,26,27,28,29,38];
        
        if ($buyFilter["buy"] == Inventory::ITEM_HBCOOLDOWN10) {
            if ($audifan -> getUser() -> getInventory() -> hasAnyItems(array(Inventory::ITEM_HBCOOLDOWN25, Inventory::ITEM_HBCOOLDOWN40))) {
                $canPurchase = false;
                $out["error"] = "The -10% Cooldown item cannot be purchased if you already own another cooldown item.";
            }
        } elseif (in_array($buyFilter["buy"], $badgeIds)) {
            $numBadges = $audifan -> getUser() -> getInventory() -> numOfAllItems($badgeIds);
            $numVouchers = $audifan -> getUser() -> getInventory() -> numOfItem(Inventory::ITEM_BADGEVOUCHER);
            
            if ($numVouchers > 0) {
                $canPurchase = false;
                $out["error"] = 'Please claim all Badge Vouchers on the <a href="/account/stuff/">My Stuff page</a> before purchasing a badge.';
            } elseif ($numBadges == 2 && !$audifan -> getUser() -> getInventory() -> hasItem($buyFilter["buy"])) {
                $canPurchase = false;
                $out["error"] = "You already have the maximum of 2 badges.";
            }
        }
        
        if ($canPurchase) {
            // Try to purchase the item.
            $result = $audifan -> getUser() -> getInventory() -> removeCoins($storePrices[$buyFilter["buy"]], "Purchased " . Inventory::$ITEMINFO[$buyFilter["buy"]][2]);
            if (!$result) {
                $out["error"] = "You do not have enough coins.";
            } else {
                $out["newbalance"] = number_format($audifan -> getUser() -> getInventory() -> getCoinBalance());
                
                // Add the item to their inventory.
                if ($buyFilter["buy"] == Inventory::ITEM_MEGAPHONETICKET)
                    $audifan -> getUser() -> getInventory () -> addItem($buyFilter["buy"], -1, 1);
                else
                    $audifan -> getUser() -> getInventory() -> addItem($buyFilter["buy"], 3600 * 24 * 7);
            }
        }
        
        print json_encode($out);
        exit;
    }
}





$context["badgeListings"] = array_merge(range(16, 29), [Inventory::ITEM_BADGE38]);
$context["prices"] = $storePrices;
$context["itemInfo"] = Inventory::$ITEMINFO;
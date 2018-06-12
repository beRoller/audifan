<?php

/* @var $audifan Audifan */

$user = $audifan -> getUser();
$db = $audifan -> getDatabase();

$viewData["template"] = "account/prizecode.twig";

if ($user -> isLoggedIn()) {
    $code = $viewData["urlVariables"][1];

    $prizeInfo = $db -> prepareAndExecute("SELECT * FROM PrizeCodes WHERE prize_code=? AND prize_code_expire>?", $code, time()) -> fetch();

    if ($prizeInfo !== FALSE) {
        // Check if they've already claimed it.
        if ($db -> prepareAndExecute("SELECT * FROM PrizeClaims WHERE prize_code=? AND account_id=?", $code, $user -> getId()) -> fetch() === FALSE) {
            $inv = $user -> getInventory();
            
            // Record that the user claimed this prize code.
            $db -> prepareAndExecute("INSERT INTO PrizeClaims(prize_code,account_id,claim_time) VALUES(?,?,?)", $code, $user -> getId(), time());
            
            $msg = "";
            
            if ($prizeInfo["prize_item"] == -1) {
                // Coins
                $actual = $inv -> addCoins($prizeInfo["prize_charges"], "Claimed a Prize Code");
                if ($prizeInfo["prize_charges"] == $actual)
                    $msg = "You have received " . number_format($prizeInfo["prize_charges"]) . " Coins!";
                else
                    $msg = sprintf("You have received %s (+ %s Bonus) Coins!", number_format($prizeInfo["prize_charges"]), number_format($actual - $prizeInfo["prize_charges"]));
            } else {
                // Item
                $inv -> addItem($prizeInfo["prize_item"], $prizeInfo["prize_duration"], $prizeInfo["prize_charges"]);
                if ($prizeInfo["prize_charges"] > 0) {
                    $msg = "You have received " . number_format($prizeInfo["prize_charges"]) . " " . Inventory::$ITEMINFO[$prizeInfo["prize_item"]][2] . "!";
                } else {
                    $msg = "You have received a " . Inventory::$ITEMINFO[$prizeInfo["prize_item"]][2] . "! It expires in " . secondsToWords($prizeInfo["prize_duration"]) . ".";
                }
            }
            
            array_push($context["GLOBAL"]["messages"]["success"], $msg);
        } else {
            array_push($context["GLOBAL"]["messages"]["error"], "You have already claimed this prize.");
        }
    } else {
        array_push($context["GLOBAL"]["messages"]["error"], "This prize code does not exist or it has expired.");
    }
} else {
    array_push($context["GLOBAL"]["messages"]["error"], "Please log in at the top of this page to claim this prize.");
}
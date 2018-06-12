<?php

/* @var $audifan Audifan */

/* @var $user User */
$user = $audifan -> getUser();

/* @var $db Database */
$db = $audifan -> getDatabase();

$notif = $audifan -> getNotificationManager();

$viewData["template"] = "community/gardentimer.twig";




$MAXGARDENTIMERS = 6;




// Helper functions //

/**
 * @return array An array of full timer information.
 */
function getTimers() {
    global $user, $db;
    
    $timers = array();
    
    if ($user -> isLoggedIn()) {
        $qStr  = "SELECT GardenTimer.timer_id, GardenTimer.account_id1, GardenTimer.account_id2, ";
        $qStr .= "GardenTimer.water_time AS water, GardenTimer.dust_time AS dust, ";
        $qStr .= "GardenTimer.fertilize_time AS fertilize, GardenTimer.rosemary_time AS rosemary, ";
        $qStr .= "GardenTimer.spearmint_time AS spearmint, GardenTimer.peppermint_time AS peppermint, ";
        $qStr .= "GardenTimer.authorized, GardenTimerOptions.timer_name, GardenTimerOptions.display_order, ";
        $qStr .= "accounts1.display_name AS display_name1, accounts2.display_name AS display_name2 ";
        $qStr .= "FROM ((GardenTimer LEFT JOIN GardenTimerOptions ON GardenTimer.timer_id = GardenTimerOptions.timer_id) ";
        $qStr .= "LEFT JOIN Accounts AS accounts1 ON GardenTimer.account_id1 = accounts1.id) ";
        $qStr .= "LEFT JOIN Accounts AS accounts2 ON GardenTimer.account_id2 = accounts2.id ";
        $qStr .= "WHERE (GardenTimer.account_id1 = ? OR GardenTimer.account_id2 = ?) ";
        $qStr .= "AND GardenTimerOptions.account_id = ? ";
        $qStr .= "ORDER BY GardenTimerOptions.display_order ASC";
        
        $res = $db -> prepareAndExecute($qStr, $user -> getId(), $user -> getId(), $user -> getId());
        if ($res -> rowCount() == 0) {
            // Add a timer, since they do not have one.
            $db -> prepareAndExecute("INSERT INTO GardenTimer(account_id1) VALUES(?)", $user -> getId());
            $id = $db -> lastInsertId();
            $db -> prepareAndExecute("INSERT INTO GardenTimerOptions(account_id,timer_id,display_order) VALUES(?,?,1)", $user -> getId(), $id);
            
            $res = $db -> prepareAndExecute($qStr, $user -> getId(), $user -> getId(), $user -> getId());
        }
        
        $timers = $res -> fetchAll();
        
        // Set session data for main timer.
        $_SESSION["gardentimer"] = $timers[0];
    }
    
    return $timers;
}

/**
 * @param array $allTimers An array of timers retrieved by getTimers().
 * @param int $id The ID to look for.
 * @return int The index of the $allTimers array in which the timer with the specified ID resides, or -1 if it is not found.
 * @see getTimers()
*/
function getIndexFromId($allTimers, $id) {
    for ($i = 0; $i < sizeof($allTimers); $i++)
        if ($allTimers[$i]["timer_id"] == $id)
            return $i;
        
    return -1;
}

/**
 * @param array $allTimers All timers owned by the currently logged in user, retrieved by getTimers().
 * @param int $id The ID to check.
 * @return boolean true if the currently logged in user owns the timer with the specified ID, false otherwise.
 * @see getTimers()
 */
function ownsTimer($allTimers, $id) {
    return (getIndexFromId($allTimers, $id) !== -1);
}

/**
 * Changes database keys and values to keys and values that are used by the timer, client-side.
 * @param array $dbData Timer data retrieved by getTimers()
 * @return array Data that can be used by the client.
 */
function getTimerDataFromDbData($dbData) {
    global $audifan;
    
    $timerData = array();
    
    $copyKeys = array("water", "dust", "fertilize", "rosemary", "spearmint", "peppermint");
    $time = $audifan -> getNow() -> getAuditionTime();
    for ($i = 0; $i < sizeof($dbData); $i++) {
        $currOld = $dbData[$i];
        $currNew = array(
            "id"    => $currOld["timer_id"],
            "name"  => $currOld["timer_name"]
        );
        foreach($copyKeys as $k)
            $currNew[$k] = ($currOld[$k] == "0" || $time > $currOld[$k]) ? "0" : $currOld[$k] - $time;
        $timerData[$i] = $currNew;
    }
    
    return $timerData;
}

/**
 * Immediately exits this script and tells the browser to go to the default garden timer URL.
 */
function sendToDefaultURL() {
    header("Location: /community/gardentimer/");
    exit;
}









$timers = getTimers();
$numTimers = sizeof($timers);

// Filter input data.
$getFilter = filter_input_array(INPUT_GET, array(
    "action" => array( // The action requested by AJAX.
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/get|water|dust|fertilize|rosemary|spearmint|peppermint/"
        )
    ),
    "timer" => array( // The timer ID.
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    ),
    "secret" => array( // If secret garden has been used.  Only check if set.
        "filter" => FILTER_DEFAULT
    ),
    "stop" => array( // If the timer should be stopped.  Only check if set.
        "filter" => FILTER_DEFAULT
    )
));

// Filter do data.
$doFilter = filter_input_array(INPUT_GET, array(
    "do" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^(addtimer|removetimer|movetimer|sharecancel|shareaccept|sharedecline)$/"
        )
    ),
    "timer" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    ),
    "direction" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^(up|down)$/"
        )
    )
));






// Process AJAX requests.
if ($user -> isLoggedIn() && !is_null($getFilter["action"]) && $getFilter["action"] !== FALSE) {
    $out = array();
    $audiTime = $audifan -> getNow() -> getAuditionTime();
    $audiMin = (int) date("i", $audiTime);
    $audiSec = (int) date("s", $audiTime);
    
    $out["timers"] = getTimerDataFromDbData($timers);
    $out["currentTime"] = date("g:i:s", $audiTime);
    
    // For the timer actions, it means the timer is being started,
    // unless $_GET["stop"] is set.  It can be set with any value, as long
    // as it's there.
        
    // For water and dust, if $_GET["secret"] is set, seeds were planted
    // in the secret garden today, so those times should be used.
    
    if ($getFilter["action"] != "get" && !is_null($getFilter["timer"]) && $getFilter["timer"] !== FALSE && ownsTimer($timers, $getFilter["timer"])) {
        $timerId = $getFilter["timer"];
        $stop = (!is_null($getFilter["stop"]) && $getFilter["stop"] !== FALSE);
        $secret = (!is_null($getFilter["secret"]) && $getFilter["secret"] !== FALSE);
        
        switch ($getFilter["action"]) {
            case "water": // Water Timer
                if ($stop) {
                    $db -> prepareAndExecute("UPDATE GardenTimer SET water_time=0 WHERE timer_id=?", $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["water"] = 0;
                } else {
                    $time;
                    if ($secret) {
                        $timeBetweenSecret = 3600 + (600 - (($audiMin * 60 + $audiSec) % 600));
                        $timeBetweenNormal = 3600 + (300 - (($audiMin * 60 + $audiSec) % 300));
                        if ($timeBetweenSecret == $timeBetweenNormal)
                            $timeBetweenSecret += (60 * 10);
                        $time = $audiTime + $timeBetweenSecret;
                    } else
                        $time = $audiTime + (3600 + (300 - (($audiMin * 60 + $audiSec) % 300)));
                    
                    $db -> prepareAndExecute("UPDATE GardenTimer SET water_time=? WHERE timer_id=?", $time, $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["water"] = $time - $audiTime;
                }
                break;
                
                
            case "dust":
                if ($stop) {
                    $db -> prepareAndExecute("UPDATE GardenTimer SET dust_time=0 WHERE timer_id=?", $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["dust"] = 0;
                } else {
                    $time = $audiTime + (3600 + (600 - (($audiMin * 60 + $audiSec) % 600)));
                    if ($secret)
                        $time += (60 * 10);
                    
                    $db -> prepareAndExecute("UPDATE GardenTimer SET dust_time=? WHERE timer_id=?", $time, $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["dust"] = $time - $audiTime;
                }
                break;
                
                
            case "fertilize":
                if ($stop) {
                    $db -> prepareAndExecute("UPDATE GardenTimer SET fertilize_time=0 WHERE timer_id=?", $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["fertilize"] = 0;
                } else {
                    $time = $audiTime + ((3600 * 24) + (1200 - (($audiMin * 60 + $audiSec) % 1200)));
                    
                    $db -> prepareAndExecute("UPDATE GardenTimer SET fertilize_time=? WHERE timer_id=?", $time, $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["fertilize"] = $time - $audiTime;
                }
                break;
                
            case "rosemary":
                if ($stop) {
                    $db -> prepareAndExecute("UPDATE GardenTimer SET rosemary_time=0 WHERE timer_id=?", $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["rosemary"] = 0;
                } else {
                    $time = $audiTime + ((7 * 60) + (60 - $audiSec));
                    
                    $db -> prepareAndExecute("UPDATE GardenTimer SET rosemary_time=? WHERE timer_id=?", $time, $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["rosemary"] = $time - $audiTime;
                }
                break;
                
            case "spearmint":
                if ($stop) {
                    $db -> prepareAndExecute("UPDATE GardenTimer SET spearmint_time=0 WHERE timer_id=?", $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["spearmint"] = 0;
                } else {
                    $time = $audiTime + ((23 * 60) + (60 - $audiSec));
                    
                    $db -> prepareAndExecute("UPDATE GardenTimer SET spearmint_time=? WHERE timer_id=?", $time, $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["spearmint"] = $time - $audiTime;
                }
                break;
                
            case "peppermint":
                if ($stop) {
                    $db -> prepareAndExecute("UPDATE GardenTimer SET peppermint_time=0 WHERE timer_id=?", $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["peppermint"] = 0;
                } else {
                    $time = $audiTime + ((127 * 60) + (60 - $audiSec));
                    
                    $db -> prepareAndExecute("UPDATE GardenTimer SET peppermint_time=? WHERE timer_id=?", $time, $timerId);
                    $out["timers"][getIndexFromId($timers, $timerId)]["peppermint"] = $time - $audiTime;
                }
                break;
        }
    }
    
    getTimers();
    print json_encode($out);
    exit;
} elseif ($user -> isLoggedIn() && !is_null($doFilter["do"]) && $doFilter["do"] !== FALSE) {
    switch ($doFilter["do"]) {
        case "addtimer": // Add a timer
            if ($numTimers < $MAXGARDENTIMERS) {
                $db -> prepareAndExecute("INSERT INTO GardenTimer(account_id1) VALUES(?)", $user -> getId());
                $id = $db -> lastInsertId();
                $db -> prepareAndExecute("INSERT INTO GardenTimerOptions(account_id,timer_id,display_order) VALUES(?,?,?)", $user -> getId(), $id, $numTimers + 1);
                $notif -> removeAllWithType("gardentimer");
                $notif -> addSessionNotification("Successfully added another timer.", "gardentimer");
            }
            sendToDefaultURL();
            break;
            
        case "removetimer": // Remove a timer
            if (!is_null($doFilter["timer"]) && $doFilter["timer"] !== FALSE) {
                if ($numTimers > 1 && ownsTimer($timers, $doFilter["timer"])) {
                    $loc = getIndexFromId($timers, $doFilter["timer"]);
                    if ($timers[$loc]["account_id1"] == "0" || $timers[$loc]["account_id2"] == "0") {
                        // It can be completely deleted because nobody else is using it.
                        $db -> prepareAndExecute("DELETE FROM GardenTimer WHERE timer_id=?", $doFilter["timer"]);
                    } else {
                        // Someone is sharing the timer with this person.
                        // Remove delete requester's account ID from it.
                        $whichId = ($timers[$loc]["account_id1"] == $user -> getId()) ? "1" : "2";
                        $db -> prepareAndExecute("UPDATE GardenTimer SET account_id" . $whichId . "=0 WHERE timer_id=?", $doFilter["timer"]);
                    }
                    
                    // Remove user options for the timer, no matter what.
                    $db -> prepareAndExecute("DELETE FROM GardenTimerOptions WHERE account_id=? AND timer_id=?", $user -> getId(), $doFilter["timer"]);
                    
                    // Fix display orders.
                    $currDisplayOrder = 1;
                    for ($i = 0; $i < $numTimers; $i++) {
                        if ($i == $loc)
                            continue;
                        
                        $db -> prepareAndExecute("UPDATE GardenTimerOptions SET display_order=? WHERE account_id=? And timer_id=?", $currDisplayOrder, $user -> getId(), $timers[$i]["timer_id"]);
                        
                        $currDisplayOrder++;
                    }
                    
                    $notif -> removeAllWithType("gardentimer");
                    $notif -> addSessionNotification("Successfully removed timer.", "gardentimer");
                }
            }
            sendToDefaultURL();
            break;
            
        case "movetimer": // Move a timer
            if (!is_null($doFilter["direction"]) && $doFilter["direction"] !== FALSE && !is_null($doFilter["timer"]) && $doFilter["timer"] !== FALSE) {
                if (ownsTimer($timers, $doFilter["timer"])) {
                    $loc = getIndexFromId($timers, $doFilter["timer"]);
                    $change = false;
                    
                    if ($doFilter["direction"] == "up" && $loc > 0) {
                        $temp = $timers[$loc - 1];
                        $timers[$loc - 1] = $timers[$loc];
                        $timers[$loc] = $temp;
                        $change = true;
                    } elseif ($doFilter["direction"] == "down" && $loc < $MAXGARDENTIMERS - 1) {
                        $temp = $timers[$loc + 1];
                        $timers[$loc + 1] = $timers[$loc];
                        $timers[$loc] = $temp;
                        $change = true;
                    }
                    
                    if ($change) {
                        for ($i = 0; $i < sizeof($timers); $i++) {
                            $db -> prepareAndExecute("UPDATE GardenTimerOptions SET display_order=? WHERE account_id=? AND timer_id=?", $i + 1, $user -> getId(), $timers[$i]["timer_id"]);
                        }
                    }
                }
            }
            sendToDefaultURL();
            break;
        
        case "sharecancel": // Cancel share request or remove self from timer.
            if (!is_null($doFilter["timer"]) && $doFilter["timer"] !== FALSE && ownsTimer($timers, $doFilter["timer"])) {
                $timer = $timers[ getIndexFromId($timers, $doFilter["timer"]) ];
                if ($timer["authorized"] != "0") {
                    // Remove auth request.
                    $db -> prepareAndExecute("UPDATE GardenTimer SET authorized=0 WHERE timer_id=?", $timer["timer_id"]);
                } elseif ($timer["account_id1"] != "0" && $timer["account_id2"] != "0") { // Is timer still being shared?
                    $num = ($timer["account_id1"] == $user -> getId()) ? "1" : "2";
                    
                    $db -> prepareAndExecute("UPDATE GardenTimer SET account_id" . $num . "=0 WHERE timer_id=?", $timer["timer_id"]);
                    
                    $db -> prepareAndExecute("INSERT INTO GardenTimer(account_id1, water_time, dust_time, fertilize_time, rosemary_time, spearmint_time, peppermint_time) VALUES(?,?,?,?,?,?,?)",
                            $user -> getId(), $timer["water"], $timer["dust"], $timer["fertilize"], $timer["rosemary"], $timer["spearmint"], $timer["peppermint"]);

                    $newTimerId = $db -> lastInsertId();
                    
                    $db -> prepareAndExecute("DELETE FROM GardenTimerOptions WHERE account_id=? AND timer_id=?", $user -> getId(), $timer["timer_id"]);
                    
                    $db -> prepareAndExecute("INSERT INTO GardenTimerOptions(timer_id,account_id,timer_name,display_order) VALUES(?,?,?,?)", $newTimerId, $user -> getId(), $timer["timer_name"], $timer["display_order"]);
                }
            }
            sendToDefaultURL();
            break;
        
        case "shareaccept":
            if ($numTimers < $MAXGARDENTIMERS) {
                if (!is_null($doFilter["timer"]) && $doFilter["timer"] !== FALSE ) {
                    $f = $db -> prepareAndExecute("SELECT * FROM GardenTimer WHERE timer_id=?", $doFilter["timer"]) -> fetch();

                    if ($f !== FALSE) {
                        if ($f["authorized"] == $user -> getId()) {
                            $num = ($f["account_id1"] == "0") ? "1" : "2";
                            
                            $db -> prepareAndExecute("UPDATE GardenTimer SET authorized=0, account_id" . $num . "=? WHERE timer_id=?", $user -> getId(), $f["timer_id"]);

                            $db -> prepareAndExecute("INSERT INTO GardenTimerOptions(timer_id,account_id,display_order) VALUES(?,?,?)", $f["timer_id"], $user -> getid(), $numTimers + 1);
                        }
                    }
                }
            }
            sendToDefaultURL();
            break;
        
        case "sharedecline":
            if (!is_null($doFilter["timer"]) && $doFilter["timer"] !== FALSE ) {
                $f = $db -> prepareAndExecute("SELECT * FROM GardenTimer WHERE timer_id=?", $doFilter["timer"]) -> fetch();
                
                if ($f !== FALSE) {
                    if ($f["authorized"] == $user -> getId()) {
                        $db -> prepareAndExecute("UPDATE GardenTimer SET authorized=0 WHERE timer_id=?", $f["timer_id"]);
                    }
                }
            }
            sendToDefaultURL();
            break;
    }
} elseif ($user -> isLoggedIn()) {
    // Process form submitted data.
    $postFilter = filter_input_array(INPUT_POST, array(
        "submit_nameedit" => FILTER_DEFAULT,
        "submit_notifopts" => FILTER_DEFAULT,
        "timernotif" => FILTER_DEFAULT,
        "timersound" => FILTER_DEFAULT,
        "volumelevel" => array(
            "filter" => FILTER_VALIDATE_INT,
            "options" => array(
                "min_range" => 1,
                "max_range" => 5
            )
        )
    ));
    
    if (!is_null($postFilter["submit_nameedit"])) {
        // Timer name changes.
        $newNames = array();
        
        for ($i = 0; $i < $numTimers; $i++) {
            $id = $timers[$i]["timer_id"];
            $name = filter_input(INPUT_POST, "timername_" . $id);
            if (!is_null($name)) {
                $name = preg_replace("/[^A-Za-z0-9 ]/", "", $name);
                $name = substr($name, 0, 20);
                $newNames[$id] = $name;
            }
        }
        
        if (!empty($newNames)) {
            foreach ($newNames as $k => $v) {
                $db -> prepareAndExecute("UPDATE GardenTimerOptions SET timer_name=? WHERE account_id=? AND timer_id=?", $v, $user -> getId(), $k);
            }
            $timers = getTimers();
        }
    } elseif (!is_null($postFilter["submit_notifopts"])) {
        // Timer notification option changes.
        $newOptsParts = array("0", "0", "0");
        
        if ($postFilter["timernotif"] == "alert")
            $newOptsParts[0] = "1";
        elseif ($postFilter["timernotif"] == "desktop")
            $newOptsParts[1] = "1";
        
        if (!is_null($postFilter["timersound"]))
            $newOptsParts[2] = "1";
        
        $newOpts = base_convert(implode("", $newOptsParts), 2, 10);
        $newVol = 5;
        
        if (!is_null($postFilter["volumelevel"]) && $postFilter["volumelevel"] !== FALSE)
            $newVol = (int) $postFilter["volumelevel"];
        
        if ($newOpts != $user -> getFlag("garden_notif") || $newVol != $user -> getFlag("garden_notifvol")) {
            $user -> setFlag("garden_notif", $newOpts);
            $user -> setFlag("garden_notifvol", $newVol);
            array_push($context["GLOBAL"]["messages"]["success"], "Your notification options have been saved.");
        }
    } else {
        // Look for submit for sharing a timer.
        for ($i = 0; $i < $numTimers; $i++) {
            if (!is_null(filter_input(INPUT_POST, "submit_shareenable_" . $timers[$i]["timer_id"]))) {
                $data = filter_input_array(INPUT_POST, array(
                    "share_displayname_" . $timers[$i]["timer_id"] => array(
                        "filter" => FILTER_VALIDATE_REGEXP,
                        "options" => array(
                            "regexp" => "/^[A-Za-z0-9\-\_\~]{2,20}$/"
                        )
                    )
                ));
                if (!is_null($data["share_displayname_" . $timers[$i]["timer_id"]]) && $data["share_displayname_" . $timers[$i]["timer_id"]] !== FALSE) {
                    // Look up name.
                    $lookUp = $db -> prepareAndExecute("SELECT id FROM Accounts WHERE display_name=?", $data["share_displayname_" . $timers[$i]["timer_id"]]) -> fetch();
                    if ($lookUp !== FALSE) {
                        // Check if that person already sent them a share request.
                        $counterCheck = $db -> prepareAndExecute("SELECT timer_id FROM GardenTimer WHERE authorized=? AND (account_id1=? OR account_id2=?)", $user -> getId(), $lookUp["id"], $lookUp["id"]) -> fetch();
                        if ($counterCheck !== FALSE) {
                            array_push($context["GLOBAL"]["messages"]["error"], "This person already sent you a share request.  You can accept it below.");
                        } elseif ($lookUp["id"] == $user -> getId()) {
                            array_push($context["GLOBAL"]["messages"]["error"], "You cannot share a timer with yourself.");
                        } else {
                            $db -> prepareAndExecute("UPDATE GardenTimer SET authorized=? WHERE timer_id=?", $lookUp["id"], $timers[$i]["timer_id"]);
                        }
                    } else {
                        array_push($context["GLOBAL"]["messages"]["error"], "Nobody has the display name '" . $data["share_displayname_" . $timers[$i]["timer_id"]] . "'.");
                    }
                }
                
                $timers = getTimers();
                break;
            }
        }
    }
}








// At this point, they are viewing the garden timer page, so set context variables.
$context["timers"] = $timers;
$context["numTimers"] = $numTimers;
$context["maxTimers"] = $MAXGARDENTIMERS;

$q  = "SELECT GardenTimer.*, accounts1.display_name AS display_name1, accounts2.display_name AS display_name2 ";
$q .= "FROM GardenTimer ";
$q .= "LEFT JOIN Accounts AS accounts1 ON GardenTimer.account_id1=accounts1.id ";
$q .= "LEFT JOIN Accounts AS accounts2 ON GardenTimer.account_id2=accounts2.id ";
$q .= "WHERE GardenTimer.authorized=?";
$context["shareRequests"] = $db -> prepareAndExecute($q, $user -> getId()) -> fetchAll();


$notifOptions = array("0", "0", "0");
$notifVolume = 5;

if ($user -> isLoggedIn()) {
    $notifFlags = $user -> getFlag("garden_notif");
    $notifOptions = str_split(base_convert($notifFlags, 10, 2));
    while (sizeof($notifOptions) < 3)
        array_unshift($notifOptions, "0");
    
    $notifFlags = $user -> getFlag("garden_notifvol");
    $notifVolume = (int) $notifFlags;
}

$context["notifOptions"] = $notifOptions;
$context["notifVolume"] = $notifVolume;
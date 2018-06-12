<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();
$user = $audifan -> getUser();

if (!$user -> isLoggedIn())
    return;

$viewData["template"] = "community/requests.twig";

$doFilter = filter_input_array(INPUT_GET, array(
    "do" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^(accept|decline|unsend)$/"
        )
    ),
    "id" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    )
));

if (!is_null($doFilter["do"]) && $doFilter["do"] !== FALSE && !is_null($doFilter["id"]) && $doFilter["id"] !== FALSE) {
    switch ($doFilter["do"]) {
        case "accept":
            if ($db -> prepareAndExecute("SELECT * FROM Requests WHERE fromid=? AND toid=?", $doFilter["id"], $user -> getId()) -> fetch() !== FALSE) {
                // Add to own friend list.
                $friendString = $db -> prepareAndExecute("SELECT friends FROM Accounts WHERE id=?", $user -> getId()) -> fetch();
                
                if ($friendString["friends"] == "") {
                    $newFriendString = $doFilter["id"];
                } else {
                    $friends = explode(",", $friendString["friends"]);
                    array_push($friends, $doFilter["id"]);
                    $newFriendString = implode(",", $friends);
                }
                
                $db -> prepareAndExecute("UPDATE Accounts SET friends=? WHERE id=?", $newFriendString, $user -> getId());
                
                // Add to other friend list.
                $otherString = $db -> prepareAndExecute("SELECT friends FROM Accounts WHERE id=?", $doFilter["id"]) -> fetch();
                if ($otherString !== FALSE) {
                    if ($otherString["friends"] == "") {
                        $newOtherString = $user -> getId();
                    } else {
                        $others = explode(",", $otherString["friends"]);
                        array_push($others, $user -> getId());
                        $newOtherString = implode(",", $others);
                    }
                    
                    $db -> prepareAndExecute("UPDATE Accounts SET friends=? WHERE id=?", $newOtherString, $doFilter["id"]);
                }
                
                // Delete request.
                $db -> prepareAndExecute("DELETE FROM Requests WHERE fromid=? AND toid=?", $doFilter["id"], $user -> getId());
                $audifan -> getNotificationManager() -> removeAllWithType("requestmessage");
                $audifan -> getNotificationManager() -> addSessionNotification("Friend request was accepted.", "requestmessage");
            }
            break;
        
        case "decline":
            $db -> prepareAndExecute("DELETE FROM Requests WHERE fromid=? AND toid=?", $doFilter["id"], $user -> getId());
            $audifan -> getNotificationManager() -> removeAllWithType("requestmessage");
            $audifan -> getNotificationManager() -> addSessionNotification("Friend request was declined.", "requestmessage");
            break;
        
        case "unsend":
            $db -> prepareAndExecute("DELETE FROM Requests WHERE fromid=? AND toid=?", $user -> getId(), $doFilter["id"]);
            $audifan -> getNotificationManager() -> removeAllWithType("requestmessage");
            $audifan -> getNotificationManager() -> addSessionNotification("Friend request was unsent.", "requestmessage");
            break;
    }
}

$context["incoming"] = $db -> prepareAndExecute("SELECT Requests.*, Accounts.display_name FROM Requests LEFT JOIN Accounts ON Requests.fromid=Accounts.id WHERE toid=? ORDER BY time", $user -> getId()) -> fetchAll();
$context["sent"] = $db -> prepareAndExecute("SELECT Requests.*, Accounts.display_name FROM Requests LEFT JOIN Accounts ON Requests.toid=Accounts.id WHERE fromid=? ORDER BY time", $user -> getId()) -> fetchAll();
<?php

/* @var $audifan Audifan */

$id = $viewData["urlVariables"][1];

$db = $audifan -> getDatabase();

$user = $audifan -> getDatabase() -> prepareAndExecute("SELECT friends, display_name FROM Accounts WHERE id=?", $id) -> fetch();

if (is_null($user))
    return;

$context["ownFriends"] = ($audifan -> getUser() -> getId() == $id);

$doFilter = filter_input_array(INPUT_GET, array(
    "do" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^remove$/"
        )
    ),
    "id" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1
        )
    )
));

if ($context["ownFriends"] && !is_null($doFilter["do"]) && $doFilter["do"] !== FALSE) {
    if (!is_null($doFilter["id"]) && $doFilter["id"] !== FALSE) {
        // Update user's friends.
        $friends = explode(",", $user["friends"]);
        $index = array_search($doFilter["id"], $friends);
        if ($index !== FALSE) {
            array_splice($friends, $index, 1);
        
            $newFriendString = empty($friends) ? "" : implode(",", $friends);
            $user["friends"] = $newFriendString;
            $audifan -> getDatabase() -> prepareAndExecute("UPDATE Accounts SET friends=? WHERE id=?", $newFriendString, $audifan -> getUser() -> getId());
        
            // Update other person's friends.
            $otherFriends = $db -> prepareAndExecute("SELECT friends FROM Accounts WHERE id=?", $doFilter["id"]) -> fetch();
            if ($otherFriends !== FALSE) {
                $otherFriendsArray = explode(",", $otherFriends["friends"]);
                $index = array_search($id, $otherFriendsArray);
                
                if ($index !== FALSE) {
                    array_splice($otherFriendsArray, $index, 1);
                    
                    $newFriendString = empty($otherFriendsArray) ? "" : implode(",", $otherFriendsArray);
                    $db -> prepareAndExecute("UPDATE Accounts SET friends=? WHERE id=?", $newFriendString, $doFilter["id"]);
                }
            }
            
            array_push($context["GLOBAL"]["messages"]["success"], "Friend was successfully removed.");
        }
    }
}

$viewData["template"] = "community/profile/friends.twig";

$context["name"] = $user["display_name"];
$context["id"] = $id;

$context["friendCount"] = 0;

if ($user["friends"] != "") {
    $context["friends"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT id, display_name, profile_pic_type FROM Accounts WHERE id IN (" . $user["friends"] . ") ORDER BY display_name ASC") -> fetchAll();
    $context["friendCount"] = sizeof($context["friends"]);
}
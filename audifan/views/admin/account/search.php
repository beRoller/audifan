<?php

/* @var $audifan Audifan */

$viewData["template"] = "admin/account/search.twig";

$postFilter = filter_input_array(INPUT_POST, array(
    "submit" => FILTER_DEFAULT,
    "query" => FILTER_DEFAULT,
    "by" => FILTER_DEFAULT
));

if (!is_null($postFilter["submit"])) {
    $context["results"] = array();
    
    switch($postFilter["by"]) {
        case "email":
            $context["results"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT id, display_name, email FROM Accounts WHERE email LIKE ?", "%".$postFilter["query"]."%") -> fetchAll();
            break;
        
        case "itemid":
            $context["results"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT Accounts.id, Accounts.display_name, AccountStuff.* FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE AccountStuff.item_id=? ORDER BY Accounts.display_name", $postFilter["query"]) -> fetchAll();
            break;
    }
}
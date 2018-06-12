<?php

/* @var $audifan Audifan */

$viewData["template"] = "community/search.twig";

$input = filter_input_array(INPUT_GET, array(
    "query" => array(
        "filter" => FILTER_SANITIZE_STRING
    ),
    "in" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/(all|nicknames|igns|fams)/"
        )
    )
));

if (!is_null($input["query"]) && $input["query"] !== FALSE && $input["query"] != "") {
    $context["query"] = $input["query"];
    
    $in = "all";
    if (!is_null($input["in"]) && $input["in"] !== FALSE)
        $in = $input["in"];
    
    $context["in"] = $in;
    
    if ($in == "all" || $in == "nicknames")
        $context["nicknames"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM Accounts WHERE display_name LIKE ? LIMIT 100", "%".$input["query"]."%") -> fetchAll();
    if ($in == "all" || $in == "igns")
        $context["characters"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT *, Accounts.display_name FROM Characters LEFT JOIN Accounts ON Characters.account=Accounts.id WHERE ign LIKE ? LIMIT 100", "%".$input["query"]."%") -> fetchAll();
    if ($in == "all" || $in == "fams")
        $context["fams"] = $audifan -> getDatabase() -> prepareAndExecute ("SELECT *, Accounts.display_name FROM Characters LEFT JOIN Accounts ON Characters.account=Accounts.id WHERE fam LIKE ? LIMIT 100", "%".$input["query"]."%") -> fetchAll();
}
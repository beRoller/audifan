<?php

/* @var $audifan Audifan */

$viewData["template"] = "community/memberlist.twig";

$getData = filter_input_array(INPUT_GET, array(
    "cat" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^(nickname|ign|)$/"
        )
    ),
    "letter" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^[a-z0]$/"
        )
    )
));

$context["cat"] = (!is_null($getData["cat"]) && $getData["cat"] !== FALSE) ? $getData["cat"] : "nickname";
$context["letter"] = (!is_null($getData["letter"]) && $getData["letter"] !== FALSE) ? $getData["letter"] : "0";

$db = $audifan -> getDatabase();

$context["members"] = array();

$q = "";
if ($getData["cat"] == "ign") {
    $q  = "SELECT Characters.ign, Accounts.id, Accounts.display_name FROM Characters ";
    $q .= "LEFT JOIN Accounts ON Characters.account=Accounts.id ";
    $q .= "WHERE Characters.ign REGEXP ";
    if ($context["letter"] == "0")
        $q .= "'^[^a-zA-Z]'";
    else
        $q .= sprintf("'^[%s%s]'", $context["letter"], strtoupper($context["letter"]));
    $q .= "ORDER BY Characters.ign";
    $context["members"] = $db -> prepareAndExecute($q) -> fetchAll();
} else {
    $q  = "SELECT id, display_name FROM Accounts ";
    $q .= "WHERE display_name REGEXP ";
    if ($context["letter"] == "0")
        $q .= "'^[^a-zA-Z]'";
    else
        $q .= sprintf("'^[%s%s]'", $context["letter"], strtoupper($context["letter"]));
    $q .= " ORDER BY display_name";
    $context["members"] = $db -> prepareAndExecute($q) -> fetchAll();
}
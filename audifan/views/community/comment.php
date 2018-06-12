<?php

/* @var $audifan Audifan */

$user = $audifan -> getUser();
$db = $audifan -> getDatabase();

$viewData["template"] = "community/comment.twig";

$q  = "SELECT ProfileComments.*, toaccount.display_name AS to_display_name, fromaccount.display_name AS from_display_name ";
$q .= "FROM ProfileComments ";
$q .= "LEFT JOIN Accounts AS toaccount ON ProfileComments.to_id=toaccount.id ";
$q .= "LEFT JOIN Accounts AS fromaccount ON ProfileComments.from_id=fromaccount.id ";
$q .= "WHERE ProfileComments.id=?";
$context["comment"] = $audifan -> getDatabase() -> prepareAndExecute($q, $viewData["urlVariables"][1]) -> fetch();

if (!is_null(filter_input(INPUT_POST, "submit_delete")) && ($context["comment"]["to_id"] == $user -> getId() || $context["comment"]["from_id"] == $user -> getId())) {
    $db -> prepareAndExecute("UPDATE ProfileComments SET to_id=0, from_id=0, private=1, comment=? WHERE id=?", $context["comment"]["comment"]."/to:".$context["comment"]["to_id"] . "/from:".$context["comment"]["from_id"], $context["comment"]["id"]);
    $context["comment"]["to_id"] = $context["comment"]["from_id"] = 0;
    $context["comment"]["private"] = 1;
}
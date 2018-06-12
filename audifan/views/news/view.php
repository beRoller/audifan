<?php

/* @var $audifan Audifan */

$id = $viewData["urlVariables"][1];

$db = $audifan -> getDatabase();

if ($audifan -> getUser() -> isAdmin()) {
    $postFilter = filter_input_array(INPUT_POST, array(
        "submit_add" => FILTER_DEFAULT,
        "submit_delete" => FILTER_DEFAULT,
        "name" => FILTER_DEFAULT,
        "type" => FILTER_DEFAULT,
        "link" => FILTER_DEFAULT,
        "body" => FILTER_DEFAULT
    ));
    
    if (!is_null($postFilter["submit_add"])) {
        $db -> prepareAndExecute("UPDATE News SET type=?,title=?,link=?,description=? WHERE id=?", $postFilter["type"], $postFilter["name"], $postFilter["link"], $postFilter["body"], $id);
    } elseif (!is_null($postFilter["submit_delete"])) {
        $db -> prepareAndExecute("DELETE FROM News WHERE id=?", $id);
    }
}

$res = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM News WHERE id=? AND type!=?", $id, $audifan -> getUser() -> isAdmin() ? "" : "Hidden") -> fetch();
if ($res !== FALSE) {
    $viewData["template"] = "news/view.twig";
    $context["story"] = $res;
}
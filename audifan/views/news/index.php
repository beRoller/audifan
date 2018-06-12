<?php

$viewData["template"] = "news/index.twig";

$page = 1;

if (isset($viewData["urlVariables"][1]))
    $page = $viewData["urlVariables"][1];

$db = $audifan -> getDatabase();

if ($audifan -> getUser() -> isAdmin()) {
    $postFilter = filter_input_array(INPUT_POST, array(
        "submit_add" => FILTER_DEFAULT,
        "name" => FILTER_DEFAULT,
        "type" => FILTER_DEFAULT,
        "link" => FILTER_DEFAULT,
        "body" => FILTER_DEFAULT
    ));
    
    if (!is_null($postFilter["submit_add"])) {
        $db -> prepareAndExecute("INSERT INTO News(type,title,link,description,time) VALUES(?,?,?,?,?)", $postFilter["type"], $postFilter["name"], $postFilter["link"], nl2br($postFilter["body"]), time());
    }
}

$context["stories"] = $db -> prepareAndExecute("SELECT * FROM News WHERE type!=? ORDER BY time DESC LIMIT 20 OFFSET ?", ($audifan -> getUser() -> isAdmin()) ? "" : "Hidden", ($page - 1) * 20) -> fetchAll();
$context["total"] = ceil($db -> prepareAndExecute("SELECT COUNT(*) FROM News") -> fetchColumn() / 20);
$context["page"] = $page;
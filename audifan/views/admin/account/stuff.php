<?php

/* @var $audifan Audifan */

$viewData["template"] = "/admin/account/stuff.twig";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT, array(
    "options" => array(
        "min_range" => 1
    )
));

if (is_null($id) || $id === FALSE)
    return;

// Process saving.
$postFilter = filter_input_array(INPUT_POST, array(
    "submit_save" => FILTER_DEFAULT,
    "id" => FILTER_VALIDATE_INT,
    "expiretime" => FILTER_VALIDATE_INT,
    "charges" => FILTER_VALIDATE_INT,
    "inuse" => FILTER_VALIDATE_INT,
    "note" => FILTER_DEFAULT,
    "delete" => FILTER_DEFAULT
));

if (!is_null($postFilter["submit_save"]) && !is_null($postFilter["id"]) && $postFilter["id"] !== FALSE) {
    if (!is_null($postFilter["delete"])) {
        $audifan -> getDatabase() -> prepareAndExecute("DELETE FROM AccountStuff WHERE stuff_id=?", $postFilter["id"]);
    } else {
        $audifan -> getDatabase() -> prepareAndExecute("UPDATE AccountStuff SET expire_time=?, charges=?, in_use=?, note=? WHERE stuff_id=?", $postFilter["expiretime"], $postFilter["charges"], $postFilter["inuse"], $postFilter["note"], $postFilter["id"]);
    }
}


// Process new item being added.
$postFilter = filter_input_array(INPUT_POST, array(
    "submit_add" => FILTER_DEFAULT,
    "item_id" => FILTER_VALIDATE_INT,
    "duration" => FILTER_VALIDATE_INT
));

if (!is_null($postFilter["submit_add"])) {
    $inventory = new Inventory($audifan, $id);
    $inventory -> addItem($postFilter["item_id"], (!is_null($postFilter["duration"]) && $postFilter["duration"] !== FALSE) ? $postFilter["duration"] : -1);
}

$context["id"] = $id;

$context["stuff"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM AccountStuff WHERE account_id=?", $id) -> fetchAll();
$context["itemInfo"] = Inventory::$ITEMINFO;
$context["now"] = time();
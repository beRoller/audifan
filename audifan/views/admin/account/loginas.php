<?php

/* @var $audifan Audifan */

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id != NULL && $id !== FALSE) {
    $account = $audifan -> getDatabase() -> prepareAndExecute("SELECT email, fbid FROM Accounts WHERE id=?", $id) -> fetch();
    if ($account !== FALSE) {
        $audifan -> getUser() -> logOut();
        if ($account["fbid"] != "")
            $audifan -> getUser() -> authenticate ("", "", 0, true, $account["fbid"]);
        else
            $audifan -> getUser() -> authenticate($account["email"], "", 0, true);
        header("Location: /");
        exit;
    }
    
}

$viewData["template"] = "admin/account/loginas.twig";
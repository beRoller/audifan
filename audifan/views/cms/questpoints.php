<?php

/* @var $audifan Audifan */

$context["permissions"] = [];

if ($audifan -> getUser() -> isLoggedIn()) {
    $res = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM CMSPermissions WHERE user_id=?", $audifan -> getUser() -> getId()) -> fetch();
    if ($res) {
        $content["permissions"] = $res;
    } elseif ($audifan -> getUser() -> isAdmin()) {
        $context["permissions"] = [
            "edit_song_list" => 1,
            "update_quest_points" => 1
        ];
    }
}


if (!empty($context["permissions"]) && $context["permissions"]["update_quest_points"]) {
    $viewData["template"] = "cms/questpoints.twig";
}
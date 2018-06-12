<?php

/*
 * This view sets some customization variable.
 */

$getInput = filter_input_array(INPUT_GET, array(
    "theme" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/(default|purple)/"
        )
    ),
    "beta" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => "/^(yes|no)$/"
        )
    )
));

if (!is_null($getInput["theme"]) && $getInput["theme"] !== FALSE) {
    setcookie("audifan_theme", $getInput["theme"], time() + (3600 * 24 * 365), "/");
}

if ($getInput["beta"]) {
    if ("yes" == $getInput["beta"]) {
        setcookie("audifan_beta", "yes", time() + (3600 * 24 * 365), "/");
    } else {
        setcookie("audifan_beta", "dead", time() - 3600, "/");
    }
}

header("Location: /");
exit;
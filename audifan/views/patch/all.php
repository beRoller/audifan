<?php

/* @var $audifan Audifan */

$viewData["template"] = "patch/all.twig";

$year = $viewData["urlVariables"][1];

$context["monthNames"] = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$context["year"] = $year;
$context["patchNumbers"] = array();

$dir = $audifan -> getConfigVar("templateLocation") . "/patch/patchinfo/";
$files = scandir($dir);

foreach ($files as $curr) {
    if (strlen($curr) == 13 && substr_compare($year, $curr, 0, 4) == 0) {
        array_push($context["patchNumbers"], $curr);
    }
}
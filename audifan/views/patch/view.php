<?php

/* @var $audifan Audifan */

$num = $viewData["urlVariables"][1];

$filename = $_CONFIG["templateLocation"] . "/patch/patchinfo/" . $num . ".twig";
if (file_exists($filename)) {
    $viewData["template"] = "patch/view.twig";
    
    $context["num"] = $num;
    
    $monthNames = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $month = (int) substr($num, 4, 2);
    
    $context["month"] = $monthNames[$month - 1];
}
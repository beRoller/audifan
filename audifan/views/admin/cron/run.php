<?php

/* @var $audifan Audifan */

$viewData["template"] = "admin/cron/run.twig";

$context["script"] = filter_input(INPUT_GET, "script", FILTER_VALIDATE_REGEXP, array(
    "options" => array(
        "regexp" => "/[a-z]+/"
    )
));

if (!is_null($context["script"]) && $context["script"] !== FALSE) {
    $filename = $audifan -> getConfigVar("cronLocation") . "/" . $context["script"] . ".php";
    
    if (file_exists($filename)) {
        $submit = filter_input(INPUT_POST, "submit", FILTER_DEFAULT);
        if (!is_null($submit)) {
            ob_start();
            
            include $filename;
            
            $context["result"] = ob_get_clean();
        }
    } else {
        $context["script"] = NULL;
    }
}
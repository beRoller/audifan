<?php

$viewData["template"] = "admin/bitbucket.twig";

if (!is_null(filter_input(INPUT_POST, "submit"))) {
    $context["output"] = array();
    
    exec("sh /var/www/audifan_repo/update.sh 2>&1", $context["output"]);
}
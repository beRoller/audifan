<?php

/* @var $audifan Audifan */

$viewData["template"] = "admin/prizecodes/list.twig";

$context["codes"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM PrizeCodes") -> fetchAll();
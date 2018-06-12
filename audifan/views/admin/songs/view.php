<?php

/* @var $audifan Audifan */

$viewData["template"] = "admin/songs/view.twig";

$context["songs"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM SongList ORDER BY id") -> fetchAll();
<?php

/* @var $audifan Audifan */

$viewData["template"] = "guides/licenses.twig";

$q  = "SELECT Licenses.*, FLOOR((Licenses.level+4) / 5) AS leveltitle, SongList.artist, SongList.title, SongList.bpm ";
$q .= "FROM Licenses LEFT JOIN SongList ON Licenses.song=SongList.id ";
$q .= "WHERE level >= 6 AND level <= 99 ";
$q .= "ORDER BY level";

$context["licenses"] = $audifan -> getDatabase() -> prepareAndExecute($q) -> fetchAll();
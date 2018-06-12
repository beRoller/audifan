<?php

/* @var $audifan Audifan */

// Process ajax favoriting
if ($audifan -> getUser() -> isLoggedIn()) {
    $favorite = filter_input(INPUT_GET, "favorite", FILTER_VALIDATE_INT, array(
        "options" => array(
            "min_range" => 1
        )
    ));
    
    if (!is_null($favorite) && $favorite !== FALSE) {
        $favoriteSongs = $audifan -> getDatabase() -> prepareAndExecute("SELECT favorite_songs FROM Accounts WHERE id=?", $audifan -> getUser() -> getId()) -> fetchColumn();
        $favoriteSongs = ($favoriteSongs == "") ? array() : explode(",", $favoriteSongs);
        
        if (in_array($favorite, $favoriteSongs)) {
            $i = array_search($favorite, $favoriteSongs);
            array_splice($favoriteSongs, $i, 1);
        } else
            array_push($favoriteSongs, $favorite);
        
        $audifan -> getDatabase() -> prepareAndExecute("UPDATE Accounts SET favorite_songs=? WHERE id=?", implode(",", $favoriteSongs), $audifan -> getUser() -> getId());
        
        print '{}';
        exit;
    }
}

$possibleLists = array(
    "normal" => "Normal",
    "ballroom" => "Ballroom",
    "beatup" => "Beat Up",
    "blockbeat" => "Block Beat",
    "guitar" => "Guitar",
    "night" => "Night Dance",
    "onetwo" => "One Two Party",
    "rhythmholic" => "Rhythm Holic",
    "spacepangpang" => "Space Pang Pang",
    "bgmmaster" => "BGM Master",
    "removed" => "Removed"
);
$context["list"] = "normal";
$context["listName"] = "Normal";

if (isset($viewData["urlVariables"][1]) && array_key_exists($viewData["urlVariables"][1], $possibleLists)) {
    $context["list"] = $viewData["urlVariables"][1];
    $context["listName"] = $possibleLists[ $context["list"] ];
}

$queries = array(
    "normal" => "SELECT *, ((new_flags & 1) != 0) AS newmode FROM SongList WHERE normal=1 ORDER BY artist ASC, title ASC",
    "ballroom" => "SELECT *, ((new_flags & 16) != 0) AS newmode FROM SongList WHERE ballroom=1 ORDER BY artist ASC, title ASC",
    "beatup" => "SELECT *, ((new_flags & 4) != 0) AS newmode FROM SongList WHERE beatup>0 ORDER BY artist ASC, title ASC",
    "blockbeat" => "SELECT *, ((new_flags & 256) != 0) AS newmode FROM SongList WHERE blockbeat>0 ORDER BY artist ASC, title ASC",
    "guitar" => "SELECT *, ((new_flags & 128) != 0) AS newmode FROM SongList WHERE guitar!='' ORDER BY artist ASC, title ASC",
    "night" => "SELECT *, ((new_flags & 2) != 0) AS newmode FROM SongList WHERE night=1 ORDER BY artist ASC, title ASC",
    "onetwo" => "SELECT *, ((new_flags & 8) != 0) AS newmode FROM SongList WHERE onetwo>0 ORDER BY artist ASC, title ASC",
    "rhythmholic" => "SELECT *, ((new_flags & 32) != 0) AS newmode FROM SongList WHERE rhythmholic>0 ORDER BY artist ASC, title ASC",
    "spacepangpang" => "SELECT *, ((new_flags & 64) != 0) AS newmode FROM SongList WHERE spacepangpang=1 ORDER BY artist ASC, title ASC",
    "bgmmaster" => "SELECT * FROM SongList WHERE master=1 ORDER BY artist ASC, title ASC",
    "removed" => "SELECT * FROM SongList WHERE normal=0 AND ballroom=0 AND beatup=0 AND blockbeat=0 AND guitar='' AND night=0 AND onetwo=0 AND rhythmholic=0 AND spacepangpang=0 ORDER BY artist ASC, title ASC"
);

$context["favoriteSongs"] = array();
if ($audifan -> getUser() -> isLoggedIn()) {
    $favoriteSongs = $audifan -> getDatabase() -> prepareAndExecute("SELECT favorite_songs FROM Accounts WHERE id=?", $audifan -> getUser() -> getId()) -> fetchColumn();
    $context["favoriteSongs"] = explode(",", $favoriteSongs);
}

// Get songs.

$context["songs"] = $audifan -> getDatabase() -> prepareAndExecute($queries[ $context["list"] ]) -> fetchAll();

$viewData["template"] = "music/list.twig";
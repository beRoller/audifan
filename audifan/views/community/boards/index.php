<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$viewData["template"] = "community/boards/index.twig";

$q  = "SELECT Boards.*, BoardPosts.post_id, BoardPosts.post_account, BoardPosts.post_time, BoardTopics.topic_name, BoardTopics.topic_id, Accounts.display_name ";
$q .= "FROM Boards ";
$q .= "LEFT JOIN (";
    $q .= "SELECT MAX(BoardPosts.post_id) AS latest_post_id, BoardTopics.board_id FROM BoardPosts ";
    $q .= "LEFT JOIN BoardTopics ON BoardPosts.topic_id=BoardTopics.topic_id ";
    $q .= "GROUP BY BoardTopics.board_id";
$q .= ") AS AllLatestPosts ON Boards.board_id=AllLatestPosts.board_id ";
$q .= "LEFT JOIN BoardPosts ON AllLatestPosts.latest_post_id=BoardPosts.post_id ";
$q .= "LEFT JOIN BoardTopics ON BoardPosts.topic_id=BoardTopics.topic_id ";
$q .= "LEFT JOIN Accounts ON BoardPosts.post_account=Accounts.id ";
$q .= "ORDER BY board_display_order";
$context["boards"] = $db -> prepareAndExecute($q) -> fetchAll();
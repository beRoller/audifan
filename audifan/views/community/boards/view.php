<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$context["boardInfo"] = $db -> prepareAndExecute("SELECT * FROM Boards WHERE board_id=?", $viewData["urlVariables"][1]) -> fetch();
if ($context["boardInfo"] === FALSE)
    return;

$viewData["template"] = "community/boards/view.twig";

$q  = "SELECT BoardTopics.*, ";
$q .= "LatestPost.post_id AS latest_post_id, LatestPost.post_time AS latest_post_time, LatestPost.post_account AS latest_post_account, ";
$q .= "LatestAccount.display_name AS latest_display_name, ";
$q .= "FirstPost.post_id AS first_post_id, FirstPost.post_time AS first_post_time, FirstPost.post_account AS first_post_account, FirstPost.post_body AS first_post_body, ";
$q .= "FirstAccount.display_name AS first_display_name, ";
$q .= "AllTotalPosts.total_posts ";
$q .= "FROM BoardTopics ";
$q .= "LEFT JOIN (";
    $q .= "SELECT MAX(BoardPosts.post_id) AS latest_post_id, BoardPosts.topic_id FROM BoardPosts ";
    $q .= "GROUP BY BoardPosts.topic_id";
$q .= ") AS AllLatestPosts ON BoardTopics.topic_id=AllLatestPosts.topic_id ";
$q .= "LEFT JOIN BoardPosts AS LatestPost ON AllLatestPosts.latest_post_id=LatestPost.post_id ";
$q .= "LEFT JOIN Accounts AS LatestAccount ON LatestPost.post_account=LatestAccount.id ";
$q .= "LEFT JOIN (";
    $q .= "SELECT MIN(BoardPosts.post_id) AS first_post_id, BoardPosts.topic_id FROM BoardPosts ";
    $q .= "GROUP BY BoardPosts.topic_id";
$q .= ") AS AllFirstPosts ON BoardTopics.topic_id=AllFirstPosts.topic_id ";
$q .= "LEFT JOIN (";
    $q .= "SELECT COUNT(*) AS total_posts, BoardPosts.topic_id FROM BoardPosts ";
    $q .= "GROUP BY BoardPosts.topic_id";
$q .= ") AS AllTotalPosts ON BoardTopics.topic_id=AllTotalPosts.topic_id ";
$q .= "LEFT JOIN BoardPosts AS FirstPost ON AllFirstPosts.first_post_id=FirstPost.post_id ";
$q .= "LEFT JOIN Accounts AS FirstAccount ON FirstPost.post_account=FirstAccount.id ";
$q .= "WHERE board_id=? ";
$q .= "ORDER BY LatestPost.post_time DESC";
$context["topics"] = $db -> prepareAndExecute($q, $context["boardInfo"]["board_id"]) -> fetchAll();
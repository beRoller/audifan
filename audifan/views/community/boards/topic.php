<?php

/* @var $audifan Audifan */
$db = $audifan -> getDatabase();

$id = $viewData["urlVariables"][1];

$context["topicInfo"] = $db -> prepareAndExecute("SELECT * FROM BoardTopics WHERE topic_id=?", $id) -> fetch();

if ($context["topicInfo"] !== FALSE) {
    $context["boardInfo"] = $db -> prepareAndExecute("SELECT * FROM Boards WHERE board_id=?", $context["topicInfo"]["board_id"]) -> fetch();
    
    if ($context["boardInfo"] !== FALSE) {
        $viewData["template"] = "community/boards/topic.twig";
        
        $q = "SELECT BoardPosts.*, Accounts.display_name ";
        $q .= "FROM BoardPosts ";
        $q .= "LEFT JOIN Accounts ON BoardPosts.post_account=Accounts.id ";
        $q .= "WHERE BoardPosts.topic_id=?";
        $context["posts"] = $db -> prepareAndExecute($q, $id) -> fetchAll();
    }
}
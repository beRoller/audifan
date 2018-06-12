<?php

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$viewData["template"] = "community/boards/newtopic.twig";

$context["boardInfo"] = $db -> prepareAndExecute("SELECT * FROM Boards WHERE board_id=?", $viewData["urlVariables"][1]) -> fetch();
if ($context["boardInfo"] === FALSE)
    return;

if ($audifan -> getUser() -> isLoggedIn()) {
    $postFilter = filter_input_array(INPUT_POST, array(
        "submit_post" => FILTER_DEFAULT,
        "title" => FILTER_DEFAULT,
        "body" => FILTER_DEFAULT
    ));

    if (!is_null($postFilter["submit_post"])) {
        $context["posttitle"] = $postFilter["title"];
        $context["postbody"] = $postFilter["body"];

        if (!is_null($postFilter["title"]) && strlen($postFilter["title"]) >= 1 && strlen($postFilter["title"]) <= 250) {
            if (!is_null($postFilter["body"]) && strlen($postFilter["body"]) >= 10 && strlen($postFilter["body"]) <= 10000) {
                $db -> beginTransaction();
                try {
                    $db -> prepareAndExecute("INSERT INTO BoardTopics(board_id,topic_name) VALUES(?,?)", $context["boardInfo"]["board_id"], $postFilter["title"]);
                    $id = $db -> lastInsertId();
                    $db -> prepareAndExecute("INSERT INTO BoardPosts(topic_id,post_account,post_body,post_time) VALUES(?,?,?,?)", $id, $audifan -> getUser() -> getId(), $postFilter["body"], time());
                    $db -> finishTransaction();
                    
                    header(sprintf("Location: /community/boards/%d/", $context["boardInfo"]["board_id"]));
                    exit;
                } catch (PDOException $e) {
                    print $e -> getMessage();
                    array_push($context["GLOBAL"]["messages"]["error"], "Failed to post due to a server error. Please try again.");
                    $db -> finishTransaction(false);
                }
            } else {
                array_push($context["GLOBAL"]["messages"]["error"], "Your post must be between 10 and 10,000 characters in length.");
            }
        } else {
            array_push($context["GLOBAL"]["messages"]["error"], "Your topic title must be between 1 and 250 characters in length.");
        }
    }
}

$viewData["template"] = "community/boards/newtopic.twig";
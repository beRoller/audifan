<?php

/* @var $audifan Audifan */

$storyEpisodes = array(
    "e1" => 1,
    "e2" => 2,
    "secret" => 3,
    "e3" => 4
);

if (array_key_exists($viewData["urlVariables"][1], $storyEpisodes)) {
    $ep = $storyEpisodes[ $viewData["urlVariables"][1] ];
    
    $q  = "SELECT Stories.*, StoryRequirements.*, Modes.mode_name, SongList.artist, SongList.title, SongList.bpm, SongList.length ";
    $q .= "FROM Stories ";
    $q .= "LEFT JOIN StoryRequirements ON Stories.story_id=StoryRequirements.story_id ";
    $q .= "LEFT JOIN Modes ON Stories.story_mode=Modes.mode_id ";
    $q .= "LEFT JOIN SongList ON Stories.story_song=SongList.id ";
    $q .= "WHERE story_episode=? ";
    $q .= "ORDER BY story_number";
    
    $context["stories"] = $audifan -> getDatabase() -> prepareAndExecute($q, $ep);
    
    $context["episodeName"] = "Episode 1";
    if ($ep > 1) {
        if ($ep == 2)
            $context["episodeName"] = "Episode 2";
        elseif ($ep == 3)
            $context["episodeName"] = "Secret Episode";
        elseif ($ep == 4)
            $context["episodeName"] = "Episode 3";
    }
    
    $viewData["template"] = "guides/story.twig";
}
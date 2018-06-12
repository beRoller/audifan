<?php

/* @var $audifan Audifan */

$viewData["template"] = "guides/tournamenttime.twig";

$tourneys = array(
    // Expert
    array(
        "length" => 30 * 60,
        "starthour" => 21,
        "startmin" => 0
    ),
    // Beat Rush
    array(
        "length" => 60 * 60,
        "starthour" => 19,
        "startmin" => 0
    ),
    // Couple
    array(
        "length" => 60 * 60,
        "starthour" => 22,
        "startmin" => 0
    ),
    // Team
    array(
        "length" => 60 * 60,
        "starthour" => 21,
        "startmin" => 30
    ),
    // Beat Up
    array(
        "length" => 60 * 60,
        "starthour" => 18,
        "startmin" => 0
    ),
    // Ballroom
    array(
        "length" => 60 * 60,
        "starthour" => 23,
        "startmin" => 0
    ),
    // Guitar
    array(
        "length" => 60 * 60,
        "starthour" => 19,
        "startmin" => 45
    ),
    // Beginner
    array(
        "length" => 60 * 60,
        "starthour" => 20,
        "startmin" => 57
    ),
    // Intermediate
    array(
        "length" => 60 * 60,
        "starthour" => 21,
        "startmin" => 3
    )
);

$now = $audifan -> getNow();

$CURRENTHOUR = $now -> getHour();
$CURRENTTIME = $now -> getAuditionTime();

for ($i = 0; $i < sizeof($tourneys); $i++) {
    $t = $tourneys[$i];

    $startTime = mktime($t["starthour"], $t["startmin"], 0);

    // If the tournament is already over, add 24 hours to the time.
    if ($CURRENTHOUR >= $t["starthour"] + 1)
        $startTime += (24 * 3600);

    $timeUntil = $startTime - $CURRENTTIME;
    
    $context["timeUntil"][$i] = $timeUntil;
}
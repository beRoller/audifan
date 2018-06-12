<?php

require_once "setup.php";

/*
 * This is a cron script that adds new quests.
 * It should be ran on Mondays at midnight.
 */

// Whether or not to just output random quest choice tests and exit instead of running the entire script.
$RANDOMDEBUG = false;

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$currentWeek = $audifan -> getNow() -> getWeekNumber();
$currentYear = $audifan -> getNow() -> getWeekYear();
$currentDay = $audifan -> getNow() -> getDayNumberOfWeek();

$QUESTFLAGS = array(
    "NONE" => 0, // No affiliation.
    "NORMAL" => 1, // Normal (expert tournament) mode.
    "BEATUP" => 2, // Beat Up mode.
    "ONETWO" => 4, // One Two Party mode.
    "BEATRUSH" => 8, // Beat Rush mode.
    "SITE" => 16, // Site-based quest (e.g. HB spin quest).
    "GUITAR" => 32 // Guitar mode.
);

$questSelections = array(
    // Easy
    1 => array(
        array(
            "text" => "Get at least {{150-250}},000 points with Insane off in {{Choreography - 4 Key,Choreography - C - 4 Keys,Dynamic - 4,Dynamic - C - 4 Direction}} with any song.",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 1
        ),
        array(
            "text" => "Get at least {{150-250}},000 points in Freestyle Battle with any song.",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 2
        ),
        array(
            "text" => "Get at least {{7-12}} Perfects in {{Choreography - 4 Key,Choreography - C - 4 Keys,Dynamic - 4,Freestyle Battle,Dynamic - C - 4 Direction}} with any song.",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 3
        ),
        array(
            "text" => "Get at least {{7-12}} Perfects in {{Fashion Week - 4 Keys,Fashion Week - C - 4 Keys}} with any song.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 4
        ),
        array(
            "text" => "Get at least {{50-75}} Perfects in Beat Up - 6 Keys with any song.",
            "flags" => $QUESTFLAGS["BEATUP"],
            "number" => 5
        ),
        array(
            "text" => "Get 100% Holic Perfect in Rhythmholic with any song.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 6,
        ),
        array(
            "text" => "Get at least {{20-30}} Perfects while wearing the Pink Bangs hair (Male) or Pink Rocker hair (Female) in One Two Party - Easy with any song.",
            "flags" => $QUESTFLAGS["ONETWO"],
            "number" => 7
        ),
        array(
            "text" => 'Spin the <a href="/community/happybox/">Audifan Happy Box</a>.<br />This quest will instantly complete.',
            "flags" => $QUESTFLAGS["SITE"],
            "number" => 8
        ),
        array(
            "text" => "Get at least {{250000-350000}} points in {{Beat Rush Hard - 4 Keys,Beat Rush Normal - 4 Keys}} with any song.",
            "flags" => $QUESTFLAGS["BEATRUSH"],
            "number" => 9
        ),
        array(
            "text" => "While playing alone, get at least {{100000-150000}} points in Guitar Mode with any song.",
            "flags" => $QUESTFLAGS["GUITAR"],
            "number" => 10
        ),
        array(
            "text" => "Get at least {{100-200}},000 points in Practice Mode - 8 with any song while playing on the {{Club Bana,Malibu,Dreamforest}} stage.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 11
        ),
        array(
            "text" => 'Log in to Audifan at least 4 days this week.<br />This quest will instantly complete.',
            "flags" => $QUESTFLAGS["SITE"],
            "number" => 12
        )
    ),
    
    
    
    
    
    
    
    
    
    // Medium
    2 => array(
        array(
            "text" => "Get at least {{188-232}},000 points in One Two Party - Easy with the song Audition - Don't Fool Around (138 bpm).",
            "flags" => $QUESTFLAGS["ONETWO"],
            "number" => 1
        ),
        array(
            "text" => "Get between {{395000-400000}} and {{405000-410000}} points in {{Choreography - C - 4 Keys,Dynamic - C - 4 Direction}} with any song with a BPM of at least 130.",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 2
        ),
        array(
            "text" => "Get at least {{94-97}}% Rhythm Perfect in Rhythmholic with the song Audition - X Girlfriend (128 bpm).",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 3
        ),
        array(
            "text" => "Get at least {{180-220}} Perfects in Beat Up - 6 Keys with the song Audition - Have a Nice day (Lv. 2, 120 bpm).",
            "flags" => $QUESTFLAGS["BEATUP"],
            "number" => 4
        ),
        array(
            "text" => "Get a combo of at least {{185-215}} in Beat Rush Normal - 4 Keys with the song Audition - {{Just One,Oh My Girl}} (126 bpm).",
            "flags" => $QUESTFLAGS["BEATRUSH"],
            "number" => 5
        ),
        array(
            "text" => "Get at least {{150-190}} Perfects with no more than 2 players in the room in Space Pang Pang with the song Audition - K.O. (120 bpm).",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 6
        ),
        array(
            "text" => "Get at least {{11-15}} Perfects in Perfect Champion - 4 Key with the song Audition - Move (140 bpm).",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 7
        ),
        array(
            "text" => "While playing alone, get at least {{225000-250000}} points and at least {{40-50}} Swing Notes in Guitar Mode with any song.",
            "flags" => $QUESTFLAGS["GUITAR"],
            "number" => 8
        ),
        array(
            "text" => "Get at least {{175000-225000}} points in Choreography - e - 8 Keys with the song Audition - What Else Do You Want? (128 bpm).",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 9
        ),
        array(
            "text" => "Get at least {{150000-200000}} points in Item Mode - {{4 Key,8 Key}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 10
        )
    ),
    
    
    
    
    
    
    
    
    
    // Hard
    3 => array(
        array(
            "text" => "Get at least {{52-56}} Perfects in Beat Rush Random - 8 Keys with the song Audition - Doo Doop (140 bpm).",
            "flags" => $QUESTFLAGS["BEATRUSH"],
            "number" => 1
        ),
        array(
            "text" => "Get at least {{36-41}} Perfects in {{Choreography - C - 8 Keys,Choreography - 8 Key,Dynamic - 8}} with the song Audition - Hands Up!!! (135 bpm).",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 2
        ),
        array(
            "text" => "Get 100% Rhythm Perfect in Rhythmholic with the song Diplo - Revolution (128 bpm).",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 3
        ),
        array(
            "text" => "Get at least {{500-550}} Perfects, no more than {{2-3}} Bads and 0 Misses in Beat Up - 6 Keys with the song Audition - Queen of Dancing (Lv. 3; 135 bpm).",
            "flags" => $QUESTFLAGS["BEATUP"],
            "number" => 4
        ),
        array(
            "text" => "Get no more than {{5-8}} Misses in One Two Party - Hard with the song Audition - {{Hands Up!!! (Lv. 5; 135 bpm),No Space (Lv. 3; 138 bpm)}}.",
            "flags" => $QUESTFLAGS["ONETWO"],
            "number" => 5
        ),
        array(
            "text" => "Get at least {{1000-1075}},000 points in Perfect Champion - 8 Key with the song MJ&iRoK - Rock This Place (128 bpm).",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 6
        ),
        array(
            "text" => "Get at least {{850-950}},000 points in Freestyle Battle - C with the song Steve Aoki - Cudi the Kid (144 bpm).",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 7
        ),
        array(
            "text" => "Get at least 900,000 points, at least 32 Perfects, and a chain of at least Perfect x5 in Choreography - C - 8 Keys with the song Audition - You're Already Gone (150 bpm).",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 8
        ),
        array(
            "text" => "While playing alone, get at least {{625-675}},000 points in Guitar Mode with the song Audition - Father's Hands (124 bpm, Lv. 3 Hard).",
            "flags" => $QUESTFLAGS["GUITAR"],
            "number" => 9
        ),
        array(
            "text" => "Get at least {{900-975}},000 points in Crazy 9 with the song Gorillaz - Feel Good Inc (139 bpm).",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 10
        ),
        array(
            "text" => "Complete at least {{5-8}} moves and get a score of 0 in Item Mode - {{4 Key,8 Key}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 11
        )
    ),
    
    
    
    
    
    
    
    
    
    // Insane
    4 => array(
        array(
            "text" => "Get at least {{650-750}} Perfects and 0 Misses in Beat Up - 6 Keys with the song Audition - Can Can (Lv. 3; 150 bpm).",
            "flags" => $QUESTFLAGS["BEATUP"],
            "number" => 1
        ),
        array(
            "text" => "Get at least {{950-999}},000 points in One Two Party - {{Easy,Hard}} with any song.",
            "flags" => $QUESTFLAGS["ONETWO"],
            "number" => 2
        ),
        array(
            "text" => "Get a chain of at least Perfect x{{6-7}} and no more than {{8-9}} Perfects in {{Choreography - C - 8 Key,Dynamic - C - 8 Direction}} with any song by Audition with a BPM of at least 140.",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 3
        ),
        array(
            "text" => "Finish a game with a combo of exactly {{450-500}} (as displayed on the scoreboard) in Beat Rush Hard - 8 Keys with the song Audition - Go Go (150 bpm).",
            "flags" => $QUESTFLAGS["BEATRUSH"],
            "number" => 4
        ),
        array(
            "text" => "Get at least {{5000-6000}},000 points in Fashion Week - C - 8 Keys with any song.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 5
        ),
        array(
            "text" => "Get 0 Misses in Dynamic - C - 8 Direction with any song with a BPM of at least 160.",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 6
        ),
        array(
            "text" => "Get 0 Misses in Dynamic - C - 4 Direction with the song Audition - {{Love Mode (190 bpm),Magic World (185 bpm)}}.",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 7
        ),
        array(
            "text" => "Get a combo of no more than 200 and no more than 7 Misses in Beat Up - 6 Keys with the song Sirocco - Brain On Music (Lv. 3; 130 bpm).",
            "flags" => $QUESTFLAGS["ONETWO"],
            "number" => 8
        ),
        array(
            "text" => "While playing alone, get no more than {{15-25}} misses and no more than 6 FlameOut in Guitar Mode with the song Audition - Bang! (200 bpm, Lv. 4 Crazy).",
            "flags" => $QUESTFLAGS["GUITAR"],
            "number" => 9
        ),
        array(
            "text" => "Get at least {{900000-999999}} points and no Bads in Normal Individual with the song Audition - Magic World (185 bpm).",
            "flags" => $QUESTFLAGS["NORMAL"],
            "number" => 10
        )
    ),
    
    
    
    
    
    
    
    
    // Group
    5 => array(
        array(
            "text" => "Get at least {{450000-500000}} points as a team in Team Choreography - 4 Keys (2 vs 2) with any song with Insane off.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 1
        ),
        array(
            "text" => "Get at least {{10-15}} Perfects in {{Couple Dance,Couple Dance - Hard,Couple Dance - 8 Keys,Couple Dance 31 Hearts - 4 Key,Couple Dance 31 Hearts - 8 Key}} with any song.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 2
        ),
        array(
            "text" => "Defeat the NPC {{Roby,Daria,Abel,Jane,Fernan}} in Normal Battle Party [Choreography - 4 Key/Audition - Promise (122 bpm)].",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 3
        ),
        array(
            "text" => "Defeat the {{Monkey,Crimson,Magic,Lakers,Pound Cake}} NPC team in Union Battle Party [Choreography - 4 Key/Audition - Elec Bossa (123 bpm)].",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 4
        ),
        array(
            "text" => "Get at least {{13-17}} Perfects in Boys & Girls - 4 Keys with any song.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 5
        ),
        array(
            "text" => "Win a game of S. B-Boy Battle - {{4,8}} Directions (2:2 Duo Battle) as a team with the song {{song:}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 6
        ),
        array(
            "text" => "Get at least 50 hearts in Club Dance II - 4 Directions with any song.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 7
        ),
        array(
            "text" => "Defeat the {{Chandler & Charlene,Rayson & Rachael,Benny & Bambi,Alice & Amy,Beatrice & Maurice,}} NPC team in Beat Rush Battle Party [Beat Rush Normal - 4 Key/Audition - What Else Do You Want? (128 bpm)].",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 8
        ),
        array(
            "text" => "Finish a game of Night Dance - 4 Keys (2 vs 2) with any song where the Red team's score is at least {{100-200}},000 points greater than the Blue team's score.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 9
        ),
        array(
            "text" => "Win any prize at the end of a game of Coin Collect - 4 Keys with the song {{song:bpm=119,bpmcompare=>}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 10
        )
    ),
    
    
    
    
    
    
    
    
    
    // Battle
    6 => array(
        array(
            "text" => "Get as many points as you can in Choreography - C - 4 Keys with the song {{song:bpm=119,bpmcompare=>}} with Insane off.",
            "flags" => $QUESTFLAGS["NONE"],
            "noun" => "score",
            "number" => 1
        ),
        array(
            "text" => "Get as many Perfects as you can in Beat Up - 6 Keys with the song {{song:mode=beatup,level=1,levelcompare=>}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "noun" => "perfects",
            "number" => 2
        ),
        array(
            "text" => "Get as many points as you can in Choreography - C - 8 Keys with the song {{song:bpm=119,bpmcompare=>}} with Insane off.",
            "flags" => $QUESTFLAGS["NONE"],
            "noun" => "score",
            "number" => 3
        ),
        array(
            "text" => "Get as many points as you can in Guitar Mode with the song {{song:mode=guitar,level=N}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "noun" => "score",
            "number" => 4
        ),
        array(
            "text" => "Get as many points as you can in One Two Party - {{Hard,Easy}} with the song {{song:mode=onetwo,level=2,levelcompare=>}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "noun" => "score",
            "number" => 5
        ),
        array(
            "text" => "Get as many Bads as you can in One Two Party - {{Hard,Easy}} with the song {{song:mode=onetwo,level=2,levelcompare=>}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "noun" => "bads",
            "number" => 6
        ),
        /*array( This quest was removed because the score range can become ridiculous.
            "text" => "Get as many points as you can in Beat Rush Hard - {{4 Key,8 Key}} with the song {{song:}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "number" => 7
        ),*/
        array(
            "text" => "Get as many Greats as you can in {{mode:}} with the song {{song:bpm=119,bpmcompare=>}}.",
            "flags" => $QUESTFLAGS["NONE"],
            "noun" => "greats",
            "number" => 8
        )
    )
);

$NOREPEATWEEKS = 5;

$lastRandomMode = "";

/**
 * Callback for the preg_replace_callback function to replace certain randomization sequences with a random value.
 * - {{15-20}} returns a random value between 15 and 20, inclusive.
 * - {{A,B,C}} returns either A, B, or C at random.
 * 
 * New with quest update:
 * - {{song:}} returns a random song from the song list. You MUST have a colon after song, even if there are no additional parameters.
 * - {{song:mode=beatup}} returns a random song from the song list that is available to play in Beat Up mode.
 * - {{song:bpm>140}} returns a random song from the song list that has a bpm of at least 140.
 * - {{song:mode=beatup,level=3}} returns a random song from the song list that available to play in Beat Up and has a level of 3.
 * - {{mode:type=normal}} returns a random normal mode.
 * 
 * Parameters:
 * = song =
 * mode=night|beatup|normal|onetwo|spacepangpang|rhythmholic|guitar|lastrandom ; If no mode is specified, it is assumed to be normal
 * bpm=x
 * bpmcompare== OR bpmcompare=> OR bpmcompare=<
 * level=x
 * levelcompare== OR levelcompare=> OR levelcompare=<
 * artist=x
 * 
 * Note - guitar mode can only accept one level of difficulty choice.  e.g. level=N OR level=H etc...
 * Note - song:mode=lastrandom will use the last {{mode:}} randomization value to determine which song list to use.
 * 
 * = mode =
 * type=normal|beatrush|beatup ; If no type is specified, it is assumed to be normal.
 * 
 * @param array $matches The array of match pieces from preg_replace. 
 * @return string
 */
function randomizationReplace($matches) {
    global $lastRandomMode, $db;
    
    $val = $matches[1];
    
    if (strstr($val, "song:") !== FALSE) {
        // Random song.
        $params = array(
            "mode" => "normal",
            "bpm" => "0",
            "bpmcompare" => ">",
            "level" => "0",
            "levelcompare" => ">",
            "artist" => ""
        );
        
        $paramString = substr($val, 5);
        if ($paramString != "") {
            $fullEqs = explode(",", substr($val, 5));
            foreach ($fullEqs as $e) {
                $curr = explode("=", $e);
                $params[ $curr[0] ] = $curr[1];
            }
        }
        
        if ($params["mode"] == "lastrandom") {
            $params["mode"] = ($lastRandomMode == "") ? "normal" : $lastRandomMode;
        }
        
        $q = "";
        switch ($params["mode"]) {
            case "normal":
                $q = sprintf("SELECT * FROM SongList WHERE normal=1 AND bpm%s%s%s ORDER BY RAND() LIMIT 1",
                        $params["bpmcompare"], $params["bpm"],
                        $params["artist"] == "" ? "" : " AND artist='" . $params["artist"] . "'");
                break;
            
            case "beatup":
                $q = sprintf("SELECT * FROM SongList WHERE beatup%s%s AND bpm%s%s%s ORDER BY RAND() LIMIT 1",
                        $params["levelcompare"], $params["level"], $params["bpmcompare"], $params["bpm"],
                        $params["artist"] == "" ? "" : " AND artist='" . $params["artist"] . "'");
                break;
            
            case "onetwo":
                $q = sprintf("SELECT * FROM SongList WHERE onetwo%s%s AND bpm%s%s%s ORDER BY RAND() LIMIT 1",
                        $params["levelcompare"], $params["level"], $params["bpmcompare"], $params["bpm"],
                        $params["artist"] == "" ? "" : " AND artist='" . $params["artist"] . "'");
                break;
            
            case "guitar":
                $q = sprintf("SELECT * FROM SongList WHERE guitar LIKE '%%%s%%' AND bpm%s%s%s ORDER BY RAND() LIMIT 1",
                        $params["level"], $params["bpmcompare"], $params["bpm"],
                        $params["artist"] == "" ? "" : " AND artist='" . $params["artist"] . "'");
                break;
            
            case "night":
                $q = sprintf("SELECT * FROM SongList WHERE night=1 AND bpm%s%s%s ORDER BY RAND() LIMIT 1",
                        $params["bpmcompare"], $params["bpm"],
                        $params["artist"] == "" ? "" : " AND artist='" . $params["artist"] . "'");
                break;
        }
        
        $result = FALSE;
        if ($q != "")
            $result = $db -> prepareAndExecute($q) -> fetch();
        
        if ($result !== FALSE) {
            if ($params["mode"] == "beatup" || $params["mode"] == "onetwo") {
                return sprintf("%s - %s (%d bpm) [Lv. %d]", $result["artist"], $result["title"], $result["bpm"], $result[$params["mode"]]);
            } elseif ($params["mode"] == "guitar") {
                $guitarDiffs = array(
                    "E" => "1 Easy",
                    "N" => "2 Normal",
                    "H" => "3 Hard",
                    "C" => "4 Crazy"
                );
                return sprintf("%s - %s (%d bpm) [Lv. %s]", $result["artist"], $result["title"], $result["bpm"], $guitarDiffs[$params["level"]]);
            } else {
                return sprintf("%s - %s (%d bpm)", $result["artist"], $result["title"], $result["bpm"]);
            }
        } else {
            return "[ failed to choose a song :( ]";
        }
    } elseif (strstr($val, "mode:") !== FALSE) {
        // Random mode.
        $params = array(
            "type" => "normal"
        );
        
        $paramString = substr($val, 5);
        if ($paramString != "") {
            $fullEqs = explode(",", substr($val, 5));
            foreach ($fullEqs as $e) {
                $curr = explode("=", $e);
                $params[ $curr[0] ] = $curr[1];
            }
        }
        
        $lastRandomMode = $params["type"];
        
        switch ($params["type"]) {
            case "normal":
                $modes = array("Normal Individual","Freestyle Battle","Freestyle Battle - C","Choreography - 4 Key","Choreography - Expert","Choreography - 8 Key","Choreography - C - 4 Keys","Choreography - C - 8 Keys","Crazy 9","Dynamic - 4","Dynamic - 8","Dynamic - C - 4 Direction","Dynamic - C - 8 Direction");
                return $modes[ array_rand($modes) ];
        }
    } elseif (strstr($val, ",") !== FALSE) {
        $poss = explode(",", $val);
        return $poss[array_rand($poss)];
    } elseif (strstr($val, "-") !== FALSE) {
        $range = explode("-", $val);
        return number_format(rand($range[0], $range[1]));
    }
}










if ($RANDOMDEBUG) {
    print "TESTING RANDOM QUEST SELECTIONS FOR EACH QUEST DESCRIPTION\n\n";
    
    foreach ($questSelections as $diff => $choices) {
        print "Difficulty: " . $diff . "\n";
        
        foreach ($choices as $c) {
            for ($i = 1; $i <= 5; $i++) {
                printf("#%d (%d): %s\n", $c["number"], $i, preg_replace_callback("/\{\{(.*?)\}\}/", "randomizationReplace", $c["text"]));
            }
            print "\n";
        }
        
        print "\n\n\n";
    }
    
    return;
}









if ($db -> prepareAndExecute("SELECT * FROM QuestRequirements WHERE req_year=? AND req_week_number=?", $currentYear, $currentWeek) -> rowCount() != 6) {
    // At least one quest from this week does not exist.  Create the missing ones.
    
    // Get list of quests in each difficulty that have been chosen in the past x weeks.
    $noRepeatQuestNumbers = array(
        1 => array(),
        2 => array(),
        3 => array(),
        4 => array(),
        5 => array(),
        6 => array()
    );
    
    // Note that this subtracts an extra hour from the time exactly x weeks ago, just in case.
    foreach ($db -> prepareAndExecute("SELECT req_difficulty, req_number FROM QuestRequirements WHERE req_start_time>=?", time() - (3600 * 24 * 7 * $NOREPEATWEEKS) - 3600) as $row) {
        array_push($noRepeatQuestNumbers[ $row["req_difficulty"] ], $row["req_number"]);
    }
    
    $diffs = array("medium", "hard", "insane", "group", "battle");
    
    print "\nQuests with easy numbers " . implode(",", $noRepeatQuestNumbers[1]);
    for ($i = 2; $i <= 6; $i++)
        print " and " . $diffs[$i - 2] . " numbers " . implode(",", $noRepeatQuestNumbers[$i]);
    print " were excluded.\n";
    
    for ($i = 1; $i <= 6; $i++) {
        if ($db -> prepareAndExecute("SELECT * FROM QuestRequirements WHERE req_year=? AND req_week_number=? AND req_difficulty=?", $currentYear, $currentWeek, $i) -> fetch() !== FALSE)
            continue; // Quest for this difficulty already exists. Don't create a new one.
        
        $selection = NULL;
        
        // Keep picking a quest until one that hasn't been chosen in a month has been picked.
        // To make sure there's no infinite loop, remove the item if it can't be picked and discontinue the loop if there are no more choices.
        while (is_null($selection) && !empty($questSelections[$i])) {
            $selectedIndex = array_rand($questSelections[$i]);
            $selection = $questSelections[$i][ $selectedIndex ];
            if (in_array($selection["number"], $noRepeatQuestNumbers[$i])) {
                unset($questSelections[$i][$selectedIndex]);
                $selection = NULL;
            }
        }
        
        if (!is_null($selection)) {
            $text = preg_replace_callback("/\{\{(.*?)\}\}/", "randomizationReplace", $selection["text"]);
            print $i . " Quest: " . $text . "\n";
            
            $noun = "";
            if ($i == 6)
                $noun = $selection["noun"];
            
            $db -> prepareAndExecute("INSERT INTO QuestRequirements(req_year, req_week_number, req_difficulty, req_text, req_noun, req_flags, req_start_time, req_number) VALUES(?,?,?,?,?,?,?,?)", $currentYear, $currentWeek, $i, $text, $noun, $selection["flags"], time(), $selection["number"]);
        }
    }
    
    print "\nFinished creating new quests for this week ($currentWeek) of year $currentYear\n";
    
    
    // Remove current week IGN IDs.
    $db -> prepareAndExecute("UPDATE QuestData SET data_current_ign_id=0");
}

if ($currentDay == 1) {
    // Save date range HTML.
    $fh = fopen($audifan -> getConfigVar("templateLocation") . "/generated/questdaterange.twig", 'w');
    fwrite($fh, date("F j, Y") . " ~ " . date("F j, Y", time() + (3600 * 24 * 6)));
    fclose($fh);
    print "\nUpdated quest date range.\n";
}
<?php

/**
 * Initialization is very long.  Actual stuff starts around line 465.
 */

/* @var $audifan Audifan */

$viewData["template"] = "community/siggenerator.twig";

$DEBUGLEVEL = 0;

$bkgProperties = array(
	array(), // 0

	array( // 1
		"width"  => 333,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(147, 0), array(333, 100) )
	),
	
	array( // 2
		"width"  => 333,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(0, 0), array(217, 100) )
	),
	
	array( // 3
		"width"  => 496,
		"height" => 97,
		"urllocation" => "right",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(198, 0), array(496, 97) )
	),
	
	array( // 4
		"width"  => 338,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(102, 0), array(338, 100) )
	),
	
	array( // 5
		"width"  => 283,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(0, 0), array(195, 100) )
	),
	
	array( // 6
		"width" => 313,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(0, 0), array(247, 100) )
	),
	
	array( // 7
		"width" => 309,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(117, 0), array(309, 100) )
	),
	
	array( // 8
		"width" => 348,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(0, 0), array(237, 100) )
	),
	
	array( // 9
		"width" => 388,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(0, 0), array(230, 100) )
	),
	
	array( // 10
		"width" => 406,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(0, 0), array(298, 100) )
	),
	
	array( // 11
		"width" => 364,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(0, 0), array(280, 100) )
	),
	
	array( // 12
		"width" => 408,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(0, 0), array(328, 100) )
	),
	
	array( // 13
		"width" => 413,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(0, 0), array(316, 100) )
	),
	
	array( // 14
		"width" => 340,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(68, 0), array(340, 100) )
	),
	
	array( // 15
		"width" => 356,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(74, 0), array(356, 100) )
	),
	
	array( // 16
		"width" => 444,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(73, 0), array(444, 100) )
	),
	
	array( // 17
		"width" => 333,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(0, 0), array(261, 100) )
	),
	
	array( // 18
		"width" => 333,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(95, 0), array(333, 100) )
	),
	
	array( // 19
		"width" => 333,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(92, 0), array(333, 100) )
	),
	
	array( // 20
		"width" => 333,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(0, 0), array(254, 100) )
	),
	
	array( // 21
		"width" => 322,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(84, 0), array(322, 100) )
	),
	
	array( // 22
		"width" => 400,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(255, 255, 255),
		"contentcoords" => array( array(85, 0), array(400, 100) )
	),
	
	array( // 23
		"width" => 377,
		"height" => 103,
		"urllocation" => "left",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(0, 0), array(225, 103) )
	),
	
	array( // 24
		"width" => 400,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(60, 0), array(400, 100) )
	),
	
	array( // 25
		"width" => 380,
		"height" => 100,
		"urllocation" => "left",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(0, 0), array(280, 100) )
	),
	
	array( // 26
		"width" => 392,
		"height" => 100,
		"urllocation" => "right",
		"foreground" => array(0, 0, 0),
		"contentcoords" => array( array(85, 0), array(392, 100) )
	),

        array( // 27
            "width" => 429,
            "height" => 100,
            "urllocation" => "right",
            "foreground" => array(0, 0, 0),
            "contentcoords" => array( array(81, 0), array(429, 100) )
        ),
    
        array( // 28
            "width" => 403,
            "height" => 100,
            "urllocation" => "right",
            "foreground" => array(0, 0, 0),
            "contentcoords" => array( array(158, 0), array(403, 100) )
        ),
    
        array( // 29
            "width" => 386,
            "height" => 100,
            "urllocation" => "right",
            "foreground" => array(0, 0, 0),
            "contentcoords" => array( array(135, 0), array(386, 100) )
        ),
    
        array( // 30
            "width" => 309,
            "height" => 100,
            "urllocation" => "right",
            "foreground" => array(255, 255, 255),
            "contentcoords" => array( array(96, 0), array(309, 100) )
        ),
    
        array( // 31
            "width" => 293,
            "height" => 100,
            "urllocation" => "left",
            "foreground" => array(0, 0, 0),
            "contentcoords" => array( array(0, 0), array(198, 100) )
        ),
    
        array( // 32
            "width" => 373,
            "height" => 100,
            "urllocation" => "right",
            "foreground" => array(255, 255, 255),
            "contentcoords" => array( array(71, 0), array(373, 100) )
        ),
    
        array( // 33
            "width" => 500,
            "height" => 100,
            "urllocation" => "left",
            "foreground" => array(0, 0, 0),
            "contentcoords" => array( array(0, 0), array(376, 100) )
        ),
    
        array( // 34
            "width" => 333,
            "height" => 100,
            "urllocation" => "right",
            "foreground" => array(255, 255, 255),
            "contentcoords" => array( array(60, 0), array(333, 100) )
        ),
    
        array( // 35
            "width" => 375,
            "height" => 100,
            "urllocation" => "left",
            "foreground" => array(255, 255, 255),
            "contentcoords" => array( array(0, 0), array(286, 100) )
        ),
    
        array( // 36
            "width" => 400,
            "height" => 100,
            "urllocation" => "left",
            "foreground" => array(0, 0, 0),
            "contentcoords" => array( array(0, 0), array(268, 100) )
        ),
    
        array( // 37
            "width" => 400,
            "height" => 100,
            "urllocation" => "left",
            "foreground" => array(255, 255, 255),
            "contentcoords" => array( array(0, 0), array(246, 100) )
        ),
    
        array( // 38
            "width" => 450,
            "height" => 100,
            "urllocation" => "left",
            "foreground" => array(0, 0, 0),
            "contentcoords" => array( array(0, 0), array(270, 100) )
        ),
    
        array( // 39
            "width" => 317,
            "height" => 120,
            "urllocation" => "",
            "foreground" => array(255, 255, 255),
            "contentcoords" => array( array(0, 0), array(181, 120) )
        )
);




$tournaments = array("expert", "beatup", "beatrush", "guitar", "couple", "ballroom", "team");

function getTextWidth($text, $font, $size = 16) {
    $bbox = imagettfbbox($size, 0, $font, $text);
    
    return $bbox[4] - $bbox[6];
}

function getTextHeight($text, $font, $size = 16) {
    $bbox = imagettfbbox($size, 0, $font, $text);
    
    return $bbox[1] - $bbox[7];
}

function cutStringTo20(&$string) {
    $string = substr($string, 0, 20);
}

function cleanseFields(&$fields) {
    global $tournaments;
    
    if (isset($fields["sigign"])) {
        if (strlen($fields["sigign"]) > 20)
            cutStringTo20($fields["sigign"]);
    }
    else
        $fields["sigign"] = "";
    
    if (isset($fields["siglevel"])) {
        if (!is_numeric($fields["siglevel"]) || $fields["siglevel"] < 1 || $fields["siglevel"] > 99)
            $fields["siglevel"] = "null";
    }
    else
        $fields["siglevel"] = "null";
            
    if (isset($fields["sigfam"])) {
        if (strlen($fields["sigfam"]) > 20)
            cutStringTo20($fields["sigfam"]);
    }
    else
        $fields["sigfam"] = "";
    
    if (isset($fields["sigfamtype"])) {
        if (!is_numeric($fields["sigfamtype"]) || $fields["sigfamtype"] < 1 || $fields["sigfamtype"] > 3)
            $fields["sigfamtype"] = "null";
    }
    else
        $fields["sigfamtype"] = "null";
    
    if (isset($fields["sigcouple"])) {
        if (strlen($fields["sigcouple"]) > 20)
            cutStringTo20($fields["sigcouple"]);
    }
    else
        $fields["sigcouple"] = "";
    
    if (isset($fields["sigcouplelevel"])) {
        if (!is_numeric($fields["sigcouplelevel"]) || $fields["sigcouplelevel"] < 0 || $fields["sigcouplelevel"] > 61)
            $fields["sigcouplelevel"] = "0";
    }
    else
        $fields["sigcouplelevel"] = "0";
    
    if (isset($fields["sigring"])) {
        if (!is_numeric($fields["sigring"]) || $fields["sigring"] < 0 || $fields["sigring"] > 40)
            $fields["sigring"] = "0";
    }
    else
        $fields["sigring"] = "0";
    
    if (isset($fields["sigstory1"])) {
        if (!is_numeric($fields["sigstory1"]) || $fields["sigstory1"] < 1 || $fields["sigstory1"] > 5)
            $fields["sigstory1"] = "null";
    }
    else
        $fields["sigstory1"] = "null";
    
    if (isset($fields["sigstory2"])) {
        if (!is_numeric($fields["sigstory2"]) || $fields["sigstory2"] < 1 || $fields["sigstory2"] > 5)
            $fields["sigstory2"] = "null";
    }
    else
        $fields["sigstory2"] = "null";
    
    for ($i = 1; $i <= 2; $i++) { // lol
        if (isset($fields["sigteammate" . $i])) {
            if (strlen($fields["sigteammate" . $i]) > 20)
                cutStringTo20($fields["sigteammate" . $i]);
        }
        else
            $fields["sigteammate" . $i] = "";
    }
    
    if (isset($fields["sigtitle"])) {
        if (!is_numeric($fields["sigtitle"]) || $fields["sigtitle"] < 1 || $fields["sigtitle"] > 4)
            $fields["sigtitle"] = "null";
    }
    else
        $fields["sigtitle"] = "null";
    
    if (isset($fields["sigtitletype"])) {
        if (!is_numeric($fields["sigtitletype"]) || $fields["sigtitletype"] < 1 || $fields["sigtitletype"] > 3)
            $fields["sigtitletype"] = "null";
    }
    else
        $fields["sigtitletype"] = "null";
    
    foreach ($tournaments as $t) {
        if (isset($fields["sigtourn" . $t])) {
            if (!is_numeric($fields["sigtourn" . $t]) || $fields["sigtourn" . $t] < 0 || $fields["sigtourn" . $t] > 100)
                $fields["sigtourn" . $t] = "null";
        }
        else
            $fields["sigtourn" . $t] = "null";
    }
}




















$NUMSIGBKGS = sizeof($bkgProperties) - 1;

$user = $audifan -> getUser();
$db = $audifan -> getDatabase();

$errors = array();

$postFilter = filter_input_array(INPUT_POST, array(
    "submit_step1" => FILTER_DEFAULT,
    "sigbkg" => array(
        "filter" => FILTER_VALIDATE_INT,
        "options" => array(
            "min_range" => 1,
            "max_range" => $NUMSIGBKGS
        )
    ),
    
    "submit_step2" => FILTER_DEFAULT,
    "sigign" => FILTER_SANITIZE_STRING,
    
    "submit_step3" => FILTER_DEFAULT
));

if (!is_null($postFilter["submit_step1"])) {
    if (isset($_SESSION["sig"]))
        unset($_SESSION["sig"]);
    
    if (!is_null($postFilter["sigbkg"]) && $postFilter["sigbkg"] !== FALSE) {
        $_SESSION["sig"]["bkg"] = $postFilter["sigbkg"];
    } else
        array_push($errors, "Please select a signature background.");
} elseif (!is_null($postFilter["submit_step2"])) {
    // Clean up fields.
    if (is_null($postFilter["sigign"]) || $postFilter["sigign"] == "")
        array_push($errors, "Please enter your in-game name.");
    $_SESSION["sig"]["character"] = $_POST;
} elseif (!is_null($postFilter["submit_step3"])) {
    // Make this nicer than making them start over, please.
    if (!isset($_SESSION["sig"]["character"]["sigign"]) || !isset($_SESSION["sig"]["bkg"])) {
        header("Location: /community/siggenerator/");
        exit;
    }
    
    
    
    
    
    
    
    // *****  CREATE SIG  *****

    // Set the graphics library's font path to this directory.
    putenv("GDFONTPATH=" . realpath("."));
    
    $IMGLOCATION = $audifan -> getConfigVar("localPublicLocation") . "/static/img";
    $FONTLOCATION = $audifan -> getConfigVar("localPublicLocation") . "/static/fonts/arialunicode.ttf";
    
    if ($DEBUGLEVEL == 0) {
        header("Content-disposition: attachment; filename=sig.png");
        header("Content-type: application/x-unknown");
    }
    elseif ($DEBUGLEVEL == 1)
        header("Content-type: image/png");
    
    // props are: width:int, height:int, urllocation:String, foreground:array, contentcoords:array
    $prop = $bkgProperties[ (int) $_SESSION["sig"]["bkg"] ];
    
    // character information from session.
    $characterInfo = $_SESSION["sig"]["character"];
    cleanseFields($characterInfo);
    
    // $im is the main image resource. Everything will be drawn on it.
    $im = imagecreatefrompng($IMGLOCATION . "/siggenerator/bkg/" . $_SESSION["sig"]["bkg"] . ".png");
    
    // $res is an array of image resources to be destroyed right before the sig is completed.
    $res = array();
    
    // The content area's width, height, starting x, y coords, and ending x, y coords.
    $contentArea = array(
        "start" => $prop["contentcoords"][0],
        "end" => $prop["contentcoords"][1]
    );
    $contentArea["width"] = $contentArea["end"][0] - $contentArea["start"][0];
    $contentArea["height"] = $contentArea["end"][1] - $contentArea["start"][1];
    
    // Foreground color of sig text.
    $foregroundColor = imagecolorallocate($im, $prop["foreground"][0], $prop["foreground"][1], $prop["foreground"][2]);
    
    // Write the tiny audifan.net URL.
    if ($prop["urllocation"] == "right")
        imagestring($im, 1, $prop["width"] - 55, 0, "audifan.net", $foregroundColor);
    elseif ($prop["urllocation"] == "left")
        imagestring($im, 1, 1, 0, "audifan.net", $foregroundColor);
    
    $strings = array(
        "fam" => "<" . $characterInfo["sigfam"] . ">",
        "level" => "",
        "ign" => $characterInfo["sigign"]
    );
    
    // Determine text for hidden levels and levels below max.
    if ($characterInfo["siglevel"] == "0")
        $strings["level"] = "Lv. ??";
    elseif ($characterInfo["siglevel"] != "99")
        $strings["level"] = "Lv. " . $characterInfo["siglevel"];
    
    // Sizes of strings
    $stringSizes = array(
        "fam" => 12,
        "ign" => 16,
        "level" => 10,
        "strip" => 8
    );
    
    // String widths & heights.
    $stringWidths = array();
    $stringHeights = array();
    foreach ($strings as $k => $v) {
        $stringWidths[$k] = getTextWidth($v, $FONTLOCATION, $stringSizes[$k]);
        $stringHeights[$k] = getTextHeight($v, $FONTLOCATION, $stringSizes[$k]);
    }
    
    // (x, y) coords of strings
    $stringPositions = array(
        "fam" => array(),
        "level" => array(),
        "ign" => array()
    );
    
    $yPositions = array(
        "icons" => 0,
        "level" => 0
    );
    
    
    
    
    
    // Write FAM & IGN
    if ($characterInfo["sigfam"] == "") {
        $stringPositions["ign"] = array(
            $contentArea["start"][0] + ($contentArea["width"] / 2) - ($stringWidths["ign"] / 2),
            $contentArea["start"][1] + ($contentArea["height"] / 2) + ($stringHeights["ign"] * (5/4))
        );
        imagettftext($im, $stringSizes["ign"], 0, $stringPositions["ign"][0], $stringPositions["ign"][1], $foregroundColor,
            $FONTLOCATION, $strings["ign"]);
        $yPositions["level"] = $stringPositions["ign"][1] - $stringHeights["ign"] - 3;
    }
    else {
        $stringPositions["ign"] = array(
            $contentArea["start"][0] + ($contentArea["width"] / 2) - ($stringWidths["ign"] / 2),
            $contentArea["start"][1] + ($contentArea["height"] / 2) + ($stringHeights["ign"] * (5/4)) + 1
        );
        $stringPositions["fam"] = array(
            $contentArea["start"][0] + ($contentArea["width"] / 2) - ($stringWidths["fam"] / 2),
            $stringPositions["ign"][1] - $stringHeights["ign"] - 2
        );
        imagettftext($im, $stringSizes["ign"], 0, $stringPositions["ign"][0], $stringPositions["ign"][1], $foregroundColor,
            $FONTLOCATION, $strings["ign"]);
        $famColor = array(0, 128, 0);
        switch ($characterInfo["sigfamtype"]) {
            case 3: // Master - Red
			if ($prop["foreground"][0] == 0) $famColor = array(128, 0, 0);
			else $famColor = array(255, 128, 128);
			break;
			
            case 2: // Co - Blue
			if ($prop["foreground"][0] == 0) $famColor = array(0, 0, 128);
			else $famColor = array(192, 192, 255);
			break;
			
            case 1: // Member - Green
            default:
                if ($prop["foreground"][0] == 0) $famColor = array(0, 128, 0);
		else $famColor = array(128, 255, 128);
	}
        imagettftext($im, $stringSizes["fam"], 0, $stringPositions["fam"][0], $stringPositions["fam"][1], imagecolorallocate($im, $famColor[0], $famColor[1], $famColor[2]),
            $FONTLOCATION, $strings["fam"]);
        $yPositions["level"] = $stringPositions["fam"][1] - $stringHeights["fam"] - 3;
    }
    
    
    
    
    
    // Write Level
    if ($characterInfo["siglevel"] != "null" && $characterInfo["siglevel"] != "99") {
        $longWidth = ($stringWidths["fam"] < $stringWidths["ign"]) ? $stringWidths["ign"] : $stringWidths["fam"];
        $stringPositions["level"] = array(
            $contentArea["start"][0] + ($contentArea["width"] / 2) - ($longWidth / 2) - ($stringWidths["level"] * (2 / 3)),
            $yPositions["level"]
        );
        imagettftext($im, $stringSizes["level"], 0, $stringPositions["level"][0], $stringPositions["level"][1], $foregroundColor, $FONTLOCATION, $strings["level"]);
        $yPositions["icons"] = $yPositions["level"] - $stringHeights["ign"];
    }
    elseif ($characterInfo["siglevel"] == "99") {
        $maxImage = $IMGLOCATION . "/leveltitles/max.png";
        $maxImageSize = getimagesize($maxImage);
        $longWidth = ($stringWidths["fam"] < $stringWidths["ign"]) ? $stringWidths["ign"] : $stringWidths["fam"];
        $maxImageRes = imagecreatefrompng($maxImage);
        array_push($res, $maxImageRes);
        imagecopy($im, $maxImageRes, 
            $contentArea["start"][0] + ($contentArea["width"] / 2) - ($longWidth / 2) - ($maxImageSize[0]) * (2 / 3), $yPositions["level"] - $maxImageSize[1], 0, 0, $maxImageSize[0], $maxImageSize[1]);
        $yPositions["icons"] = $yPositions["level"] - $stringHeights["ign"];
    }
    else
        $yPositions["icons"] = $yPositions["level"];
    
    // Copy Level Title
    if ($characterInfo["siglevel"] != "null") {
        $ltimsrc = $IMGLOCATION . "/leveltitles/";
        if ($characterInfo["siglevel"] == 0)   $ltimsrc .= "0.png";
        elseif ($characterInfo["siglevel"] <= 5)  $ltimsrc .= "1.png";
	elseif ($characterInfo["siglevel"] <= 10) $ltimsrc .= "2.png";
	elseif ($characterInfo["siglevel"] <= 15) $ltimsrc .= "3.png";
	elseif ($characterInfo["siglevel"] <= 20) $ltimsrc .= "4.png";
	elseif ($characterInfo["siglevel"] <= 25) $ltimsrc .= "5.png";
	elseif ($characterInfo["siglevel"] <= 30) $ltimsrc .= "6.png";
	elseif ($characterInfo["siglevel"] <= 35) $ltimsrc .= "7.png";
	elseif ($characterInfo["siglevel"] <= 40) $ltimsrc .= "8.png";
	elseif ($characterInfo["siglevel"] <= 45) $ltimsrc .= "9.png";
	elseif ($characterInfo["siglevel"] <= 50) $ltimsrc .= "10.png";
	elseif ($characterInfo["siglevel"] <= 55) $ltimsrc .= "11.png";
	elseif ($characterInfo["siglevel"] <= 60) $ltimsrc .= "12.png";
	elseif ($characterInfo["siglevel"] <= 65) $ltimsrc .= "13.png";
	elseif ($characterInfo["siglevel"] <= 70) $ltimsrc .= "14.png";
	elseif ($characterInfo["siglevel"] <= 75) $ltimsrc .= "15.png";
	elseif ($characterInfo["siglevel"] <= 80) $ltimsrc .= "16.png";
	elseif ($characterInfo["siglevel"] <= 85) $ltimsrc .= "17.png";
	elseif ($characterInfo["siglevel"] <= 90) $ltimsrc .= "18.png";
	elseif ($characterInfo["siglevel"] <= 95) $ltimsrc .= "19.png";
	elseif ($characterInfo["siglevel"] <= 99) $ltimsrc .= "20.png";
        
        $longWidth = ($stringWidths["fam"] < $stringWidths["ign"]) ? $stringWidths["ign"] : $stringWidths["fam"];
        $ltSize = getimagesize($ltimsrc);
        $ltIm = imagecreatefrompng($ltimsrc);
        array_push($res, $ltIm);
        
        imagecopy($im, $ltIm, ($contentArea["start"][0] + ($contentArea["width"] / 2) + ($longWidth / 2) - ($ltSize[0] * (1 / 10))) - 16,
            $yPositions["level"] - $ltSize[1] + 2, 0, 0, $ltSize[0], $ltSize[1]);
    }
    
    
    
    
    // Icons
    $iconX = $contentArea["start"][0] + ($contentArea["width"] * (2 / 5));
    $icons = array();
    $iconSizes = array();
    $iconRealWidths = array();
    $iconYOffsets = array();
    
    // Team Title
    if ($characterInfo["sigtitle"] != "null") {
        $titleSrc = $IMGLOCATION . "/siggenerator/sigtitles/" . $characterInfo["sigtitle"] . "_";
        $titleSrc .= ($characterInfo["sigtitletype"] == "2" || $characterInfo["sigtitletype"] == "3") ? $characterInfo["sigtitletype"] . ".png" : "1.png";
        array_push($icons, $titleSrc);
        array_push($iconSizes, getimagesize($titleSrc));
        array_push($iconRealWidths, 25);
        array_push($iconYOffsets, 3);
    }
    
    // Ring
    if ($characterInfo["sigring"] != "0") {
        $ringSrc;
        if ($characterInfo["sigring"] <= 13) // Default Rings
            $ringSrc = $IMGLOCATION . "/rings/small/" . $characterInfo["sigring"] . ".png";
        else // Couple Shop Rings are protected
            $ringSrc = $IMGLOCATION . "/siggenerator/rings/" . $characterInfo["sigring"] . ".png";
        array_push($icons, $ringSrc);
        array_push($iconSizes, getimagesize($ringSrc));
        array_push($iconRealWidths, 25);
        array_push($iconYOffsets, -5);
    }
    
    // E1 Medal
    if ($characterInfo["sigstory1"] != "null") {
        $storySrc = $IMGLOCATION . "/siggenerator/story/1_" . $characterInfo["sigstory1"] . ".png";
        array_push($icons, $storySrc);
        array_push($iconSizes, getimageSize($storySrc));
        array_push($iconRealWidths, 24);
        array_push($iconYOffsets, 0);
    }
    
    // E2 Medal
    if ($characterInfo["sigstory2"] != "null") {
        $story2Src = $IMGLOCATION . "/siggenerator/story/2_" . $characterInfo["sigstory2"] . ".png";
        array_push($icons, $story2Src);
        array_push($iconSizes, getimageSize($story2Src));
        array_push($iconRealWidths, 25);
        array_push($iconYOffsets, 0);
    }
    
    // Diary
    if (isset($_POST["sigshowdiary"]) && false) { // TODO: Add diary icon.
        $diarySrc = $IMGLOCATION . "/miscaudiicons/diarybday.png";
        array_push($icons, $diarySrc);
        array_push($iconSizes, getimagesize($diarySrc));
        array_push($iconRealWidths, 25);
        array_push($iconYOffsets, 0);
    }
    
    // Guitar Controller
    if (isset($_POST["sigshowguitar"]) && false) { // TODO: Add guitar icon.
        $guitarSrc = $IMGLOCATION . "/miscaudiicons/guitarctrl.png";
        array_push($icons, $guitarSrc);
        array_push($iconSizes, getimageSize($guitarSrc));
        array_push($iconRealWidths, 22);
        array_push($iconYOffsets, 1);
    }
    
    // Write icons.
    $iconX -= (array_sum($iconRealWidths) / 2);
    if ($iconX < $contentArea["start"][0]) // Make sure it's not out of the content area. If it is, push it over.
        $iconX = $contentArea["start"][0];
    for ($i = 0; $i < sizeof($icons); $i++) {
        $currIcon = imagecreatefrompng($icons[$i]);
        array_push($res, $currIcon);
        $currIconSize = $iconSizes[$i];

        imagecopy($im, $currIcon, $iconX, $yPositions["icons"] - 21 + $iconYOffsets[$i], 0, 0, $currIconSize[0], $currIconSize[1]);
        
        $iconX += $iconRealWidths[$i];
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // Strip text.

    
    // Couple & Teammates
    if ($_POST["sigstrip"] == "coupleteam") {
        $coupleString = "";
        $teamString = "";
        
        if ($characterInfo["sigcouple"] != "") {
            $coupleString .= "Couple: " . $characterInfo["sigcouple"] . " ";
        }
        
        if ($characterInfo["sigcouplelevel"] != "0") {
            $parenthetical = ($coupleString != "");
            if ($parenthetical)
                $coupleString .= "(";
            $coupleString .= "Cpl Lv. ";
            $coupleString .= ($characterInfo["sigcouplelevel"] == "61") ? "MAX" : $characterInfo["sigcouplelevel"];
            if ($parenthetical)
                $coupleString .= ")";
        }
        
        if ($characterInfo["sigteammate1"] != "" || $characterInfo["sigteammate2"] != "") {
            $teamString .= "Team: ";
            $teamString .= ($characterInfo["sigteammate1"] == "") ? "??" : $characterInfo["sigteammate1"];
            $teamString .= " & ";
            $teamString .= ($characterInfo["sigteammate2"] == "") ? "??" : $characterInfo["sigteammate2"];
        }
        
        if ($coupleString != "" || $teamString != "") {
            imagefilledrectangle($im, 0, $prop["height"] - 13, $prop["width"], $prop["height"], 
                imagecolorallocatealpha($im, 0, 0, 0, 70));
            $coupleStringWidth = getTextWidth($coupleString, $FONTLOCATION, $stringSizes["strip"]);
            $totalWidth = $coupleStringWidth + getTextWidth($teamString, $FONTLOCATION, $stringSizes["strip"]);
            if ($coupleString != "" && $teamString != "")
                $totalWidth += 15; // Padding if both are present.
            $firstX = ($prop["width"] / 2) - ($totalWidth / 2);
            imagettftext($im, $stringSizes["strip"], 0, $firstX, $prop["height"] - 3,
                imagecolorallocate($im, 255, 255, 255), $FONTLOCATION, $coupleString);
            imagettftext($im, $stringSizes["strip"], 0, $firstX + $coupleStringWidth + 15, $prop["height"] - 3,
                imagecolorallocate($im, 255, 255, 255), $FONTLOCATION, $teamString);
        }
    }
    

    
    // Tournament Wins
    elseif ($_POST["sigstrip"] == "tournament") {
        $IMAGEWIDTH = 23;
        $MEDALTEXTPADDING = 1;
        $SECTIONPADDING = 3;
        $TEXTCOLOR = imagecolorallocate($im, 255, 255, 255);
        
        $currentX = $prop["width"] / 2;
        $winsText = "Wins:";
        $winsWidth = getTextWidth($winsText, $FONTLOCATION, $stringSizes["strip"]);
        $currentX -= ($winsWidth / 2);
        $currentX -= $SECTIONPADDING; // Padding for wins text.
        
        $winCounts = array();
        $winTextWidths = array();
        foreach ($tournaments as $t) {
            if ($characterInfo["sigtourn" . $t] == "null")
                continue;
            else {
                $winCounts[$t] = $characterInfo["sigtourn"  . $t] == "100" ? "100+" : $characterInfo["sigtourn" . $t];
                $currentX -= (($IMAGEWIDTH + $MEDALTEXTPADDING + $SECTIONPADDING) / 2); // 23 for image, 3 for padding between medal and text, 7 for padding between sections.
                $winTextWidths[$t] = getTextWidth($winCounts[$t], $FONTLOCATION, $stringSizes["strip"]);
                $currentX -= ($winTextWidths[$t] / 2);
            }
        }
        
        if (!empty($winCounts)) {
            imagefilledrectangle($im, 0, $prop["height"] - 13, $prop["width"], $prop["height"], 
                imagecolorallocatealpha($im, 0, 0, 0, 70));
            imagettftext($im, $stringSizes["strip"], 0, $currentX, $prop["height"] - 3, $TEXTCOLOR, $FONTLOCATION, $winsText); // Wins text.
            $currentX += $winsWidth + $SECTIONPADDING;
            foreach ($winCounts as $k => $v) {
                // draw medal
                $medalRes = imagecreatefrompng($IMGLOCATION . "/tournamentmedals/" . $k . "_s.png");
                array_push($res, $medalRes);
                imagecopy($im, $medalRes, $currentX, $prop["height"] - 20, 0, 0, 23, 23);
                // change current X.
                $currentX += $IMAGEWIDTH + $MEDALTEXTPADDING;
                // draw text next to medal
                imagettftext($im, $stringSizes["strip"], 0, $currentX, $prop["height"] - 3, $TEXTCOLOR, $FONTLOCATION, $v);
                // change currentX.
                $currentX += $winTextWidths[$k] + $SECTIONPADDING;
            }
            // imagecopy($im, $currIcon, $iconX, $yPositions["icons"] - 21 + $iconYOffsets[$i], 0, 0, $currIconSize[0], $currIconSize[1]);
        }
    }
    elseif ($_POST["sigstrip"] == "custom" && $_POST["sigcustomtext"] != "") {
        if (strlen($_POST["sigcustomtext"]) > 50)
            $_POST["sigcustomtext"] = substr($_POST["sigcustomtext"], 0, 50);
        $width = getTextWidth($_POST["sigcustomtext"], $FONTLOCATION, $stringSizes["strip"]);
        imagefilledrectangle($im, 0, $prop["height"] - 13, $prop["width"], $prop["height"], 
            imagecolorallocatealpha($im, 0, 0, 0, 70));
        imagettftext($im, $stringSizes["strip"], 0, ($prop["width"] / 2) - ($width / 2), $prop["height"] - 3, 
            imagecolorallocate($im, 255, 255, 255), $FONTLOCATION, $_POST["sigcustomtext"]);
    }
    
    
    
    
    
    // Output the final image.
    imagepng($im);
    
    // Destroy the main image resource.
    imagedestroy($im);
    
    // Destroy all secondary image resources used.
    foreach($res as $r)
        imagedestroy($r);
    
    // Exit the page so nothing else below happens.
    exit;
}









/*
 * A sig hasn't been displayed printed at this point.
 */

$currentStep = 1;
if (!is_null($postFilter["submit_step1"]) && empty($errors))
    $currentStep = 2;
elseif (!is_null($postFilter["submit_step2"]) && empty($errors))
    $currentStep = 3;

$characterInfo = array();
if ($user -> isLoggedIn() && $currentStep == 2)
    $characterInfo = $db -> prepareAndExecute("SELECT * FROM Characters WHERE account=?", $user -> getId()) -> fetchAll();


$context["currentStep"] = $currentStep;
$context["NUMSIGBKGS"] = $NUMSIGBKGS;

$context["characterInfo"] = $characterInfo;
$context["characterInfoJson"] = array();

for ($i = 0; $i < sizeof($characterInfo); $i++) {
    $context["characterInfoJson"][ $characterInfo[$i]["id"] ] = json_encode(array(
        "sigign" => $characterInfo[$i]["ign"],
        "siglevel" => $characterInfo[$i]["level"],
        "sigfam" => $characterInfo[$i]["fam"],
        "sigfamtype" => $characterInfo[$i]["fam_member_type"],
        "sigcouple" => $characterInfo[$i]["couple"],
        "sigcouplelevel" => $characterInfo[$i]["couple_level"],
        "sigring" => $characterInfo[$i]["ring"],
        "sigstory1" => $characterInfo[$i]["story_medal"],
        "sigstory2" => $characterInfo[$i]["story_medal2"],
        "sigteammate1" => $characterInfo[$i]["team1"],
        "sigteammate2" => $characterInfo[$i]["team2"],
        "sigtitle" => $characterInfo[$i]["team_title"],
        "sigtournexpert" => $characterInfo[$i]["tourn_expert"] > 100 ? 100 : $characterInfo[$i]["tourn_expert"],
        "sigtournbeatup" => $characterInfo[$i]["tourn_beatup"] > 100 ? 100 : $characterInfo[$i]["tourn_beatup"],
        "sigtournbeatrush" => $characterInfo[$i]["tourn_beatrush"] > 100 ? 100 : $characterInfo[$i]["tourn_beatrush"],
        "sigtournguitar" => $characterInfo[$i]["tourn_guitar"] > 100 ? 100 : $characterInfo[$i]["tourn_guitar"],
        "sigtournteam" => $characterInfo[$i]["tourn_team"] > 100 ? 100 : $characterInfo[$i]["tourn_team"],
        "sigtourncouple" => $characterInfo[$i]["tourn_couple"] > 100 ? 100 : $characterInfo[$i]["tourn_couple"],
        "sigtournballroom" => $characterInfo[$i]["tourn_ballroom"] > 100 ? 100 : $characterInfo[$i]["tourn_ballroom"]
    ));
}

$context["tournaments"] = $tournaments;

$context["GLOBAL"]["messages"]["error"] = $errors;
<?php

/*
 * This is the main controller for Audifan.net.
 * All requests go through this file.
 */

$startTime = microtime(true);

header("Content-Type: text/html; charset=utf-8");

// Server-specific configuration
if (!file_exists("config.php"))
    die("I need a configuration file!");

require_once "config.php";

if ($_CONFIG["maintenanceMessage"] != "" && !in_array($_SERVER["REMOTE_ADDR"], $_CONFIG["maintenanceAllowedIps"])) {
    print "<!DOCTYPE html>\n";
    print '<html><head><title>Audifan.net - Maintenance</title><style>body{font-family:Arial,sans-serif;}</style></head>';
    print '<body><div style="text-align:center;">';
    print '<div style="font-size:14pt;">Audifan.net is down for maintenance.</div>';
    print '<div style="font-size:10pt;">' . $_CONFIG["maintenanceMessage"] . '<br /><br />Sorry for the inconvenience.</div>';
    print '</div></body></html>';
    exit;
}





$requestUri = $_SERVER["REQUEST_URI"];
$questionMarkLoc = strpos($requestUri, "?");

if ($questionMarkLoc !== FALSE)
    $requestUri = substr($requestUri, 0, $questionMarkLoc);







// Audifan libraries
require_once $_CONFIG["libsLocation"] . "/audifan/Audifan.php";

// Start the session.
session_start();

$audifan = new Audifan($_CONFIG);






// If this is an ajax request, handle it here and don't load any front end stuff.
if ("/ajax/" == $requestUri) {
	$ajaxResponse = [];

	function addToAjaxResponse($key, $value) {
		global $ajaxResponse;
		$ajaxResponse[ $key ] = $value;
	}

	function setAjaxResponse(array $arr) {
		global $ajaxResponse;
		$ajaxResponse = $arr;
	}

	function setAjaxSuccess() {
		addToAjaxResponse("success", true);
	}

	function setAjaxError($errorMessage) {
		addToAjaxResponse("success", false);
		addToAjaxResponse("message", $errorMessage);
	}

	$do = filter_input(INPUT_POST, "do", FILTER_VALIDATE_REGEXP, [
		"options" => [
			"regexp" => '/^[a-z0-9_\-]+$/'
		]
	]);

	if ($do) {
		$ajaxFilename = $_CONFIG["ajaxLocation"] . "/" . $do . ".php";

		if (file_exists($ajaxFilename)) {
			include $ajaxFilename;
		}
	}

	header("Content-Type: application/json; charset=utf-8");
	print json_encode($ajaxResponse);
	exit;
}








// Twig
require_once $_CONFIG["libsLocation"] . "/Twig/Autoloader.php";
Twig_Autoloader::register();


$templateLocation = $_CONFIG["templateLocation"];
if (isset($_COOKIE["audifan_beta"])) {
    $templateLocation .= "/v2";
}

$loader = new Twig_Loader_Filesystem($templateLocation);
$twig = new Twig_Environment($loader, array(
    "cache" => $audifan -> getConfigVar("templateCacheLocation"),
    "auto_reload" => true
));

// Parses emotes.
function emoteFilter($text, $escape = false) {
    return preg_replace_callback(
        "/;(Angl|Bawl|Bday|Blln|Boat|Bulb|Cam|Cell|Cess|Chr|Cold|Com|Cpd|Crkr|Cry2?|Cute2?|Dog|Dork|Drnk|Dvl|Dzzy|Etc|Flu|Flwr|Fury|Ghst|Gift|Grin2?" .
        "|Heat|Hi|Hmph|Hot|Hset|Hum|Kiss2?|Lol|Look|Losr|Love|Lttr|Lush|LuvU2?|Mad2?|Meh|Mkup|Nml|No|Ouch2?|Pray|Prc|\?\?\?" .
        "|SLK|Sad|Shy|Sigh2?|Sly|Sng|Spit|Sprs|Spy|Star|Sun|VV2?|Wave|What|Whn|Wink|Yawn2?|Yay|haha)/", function($matches) {
            global $_CONFIG;
            return sprintf('<img src="%s/img/emotes/%s.gif" class="_vmiddle" title="%s" />', $_CONFIG["staticUrl"], $matches[1] == "???" ? "Q" : $matches[1], $matches[0]);
    }, $text);
}


// Removes 'mature' language.
function languageFilter($text) {
    global $audifan;
    
    if ($audifan -> getUser() -> getFlag("language_filter") != 0) {
        $words = array("motherfucker", "fuck", "shit", "bitch", "whore", "slut", "cock", "dick", "damn", "pussy", "faggot", "fag", "nigger", "twat",
            "cunt", "skank", "cocksucker", "bastard", "dyke", "chink", "spic", "dink", "bullshit");
    
        return preg_replace_callback("/((?:" . implode("|", $words) . ")s?)/", function($matches) {
            return str_repeat("*", strlen($matches[1]));
        }, $text);
    } else
        return $text;
}

// Filter for megaphones.
function megaphoneFilter($text) {
    $text = htmlentities(stripslashes($text));
    
    $text = emoteFilter($text);
    
    $text = languageFilter($text);
    
    return $text;
}
$twig -> addFilter(new Twig_SimpleFilter("megaphone", "megaphoneFilter", array(
    "is_safe" => array("html")
)));

// Filter for BB Code and emote parsing.
function bbCodeFilter($text) {
    $text = htmlentities(stripslashes($text));
        
    // BB codes with no arguments
    $text = preg_replace_callback("/\[(b|i|s|u)\](.*)\[\/\\1\]/i", function($matches) {
        $replacements = array(
            "b"   => '<b>%s</b>',
            "i"   => '<i>%s</i>',
            "s"   => '<span style="text-decoration:line-through;">%s</span>',
            "u"   => '<span style="text-decoration:underline;">%s</span>',
            //"img" => '<img src="%s" alt="Picture" />'
        );
        
        $tag = strtolower($matches[1]);
        $text = $matches[2];
        return sprintf($replacements[$tag], bbCodeFilter($text));
    }, $text);
    
    // URL tag
    $text = preg_replace_callback("/\[(url|URL)\](.*)\[\/\\1\]/i", function($matches) {
        $url = $matches[2];
        if (!preg_match("/^((https?)|ftp)\:\/\//", $url))
            $url = 'http://' . $url;
        
        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $matches[2]);
    }, $text);
    
    // BB codes with arguments
    /*$text = preg_replace_callback('/\[(url|user)\=(.*?)\](.*?)\[\/\\1\]/i', function($matches) {
        $str = "";
        
        switch ($matches[1]) {
            case "url":
                
                break;
            
            case "user":
                if (is_numeric($matches[2]))
                    $str = sprintf('<a href="/community/profile/%d/" target="_blank">%s</a>', $matches[2], $matches[3]);
                else
                    $str = $matches[3];
                break;
        }
        
        return $str;
    }, $text);*/
        
    // Emotes
    $text = emoteFilter($text);
    
    // Language filter
    $text = languageFilter($text);
    
    // Easter Egg - tehpanlidboy is censored.
    $text = preg_replace("/tehpanlidboy/i", "************", $text);
        
    return nl2br($text);
}
$twig -> addFilter(new Twig_SimpleFilter("bbcode", "bbCodeFilter", array(
    "is_safe" => array("html")
)));

// Filter for pluralization
$twig -> addFilter(new Twig_SimpleFilter("pluralize", function() {
    $args = func_get_args();
    
    if (is_numeric($args[0]))
        if ($args[0] != 1)
            return (sizeof($args) >= 3) ? $args[2] : "s";
    
    return (sizeof($args) >= 2) ? $args[1] : "";
}));

function secondsToWords($sec) {
    $gap = $sec;
    
    $years = floor($gap / (3600 * 24 * 365));
    $gap -= $years * (3600 * 24 * 365);
    $months = floor($gap / (3600 * 24 * 30));
    $gap -= $months * (3600 * 24 * 30);
    
    if ($years > 0) {
        $s = $years . " year";
        if ($years > 1)
            $s .= "s";
        
        if ($months > 0) {
            $s .= " and " . $months . " month";
            if ($months > 1)
                $s .= "s";
        }
        
        return $s;
    }
    
    $days = floor($gap / (3600 * 24));
    $gap -= $days * (3600 * 24);
    
    if ($months > 0) {
        $s = $months . " month";
        if ($months > 1)
            $s .= "s";
        
        if ($days > 0) {
            $s .= " and " . $days . " day";
            if ($days > 1)
                $s .= "s";
        }
        
        return $s;
    }
    
    $hours = floor($gap / 3600);
    $gap -= $hours * 3600;
    
    if ($days > 0) {
        $s = $days . " day";
        if ($days > 1)
            $s .= "s";
        
        if ($hours > 0) {
            $s .= " and " . $hours . " hour";
            if ($hours > 1)
                $s .= "s";
        }
        
        return $s;
    }
    
    $minutes = floor($gap / 60);
    $gap -= $minutes * 60;
    
    if ($hours > 0) {
        $s = $hours . " hour";
        if ($hours > 1)
            $s .= "s";
        
        if ($minutes > 0) {
            $s .= " and " . $minutes . " minute";
            if ($minutes > 1)
                $s .= "s";
        }
        
        return $s;
    }
    
    if ($minutes > 0) {
        $s = $minutes . " minute";
        if ($minutes > 1)
            $s .= "s";
        
        if ($gap > 0) {
            $s .= " and " . $gap . " second";
            if ($gap > 1)
                $s .= "s";
        }
        
        return $s;
    }
    
    $s = $gap . " second";
    if ($gap != 1)
        $s .= "s";
    
    return $s;
}

// Filter for secondsToWords and relativeTime.
$twig -> addFilter(new Twig_SimpleFilter("secondsToWords", "secondsToWords"));
$twig -> addFilter(new Twig_SimpleFilter("relativeTime", function($time) {
    return secondsToWords(abs(time() - $time));
}));

// urlizeText filter turns text into a string that can go in a url, Used for topic/board titles on the message board.
$twig -> addFilter(new Twig_SimpleFilter("urlizeText", function($text) {
    $text = trim(substr($text, 0, 64));
    $text = strtolower($text);
    $text = preg_replace("/ /", "-", $text);
    $text = preg_replace("/[^a-z0-9\-]/", "", $text);
    return $text;
}));




// Functions for the start and end content sections.
$twig -> addFunction(new Twig_SimpleFunction("sectionStart", function($title) {
    $str = '<div class="sectioncontainer">';
    
    if ($title) {
        $str .= '<div class="sectionheading"><h3><i class="fa fa-chevron-right"></i> ' . $title . '</h3></div>';
    }
    
    $str .= '<div class="sectiontext">';
    
    return $str;
}, array(
    "is_safe" => array("html")
)));
$twig -> addFunction(new Twig_SimpleFunction("sectionEnd", function() {
    return '</div></div>';
}, array(
    "is_safe" => array("html")
)));






$twig -> addFunction(new Twig_SimpleFunction("pagination", function($urlBefore, $urlAfter = "", $totalPages = 1, $currentPage = 1, $maxLinks = 15, $size = "sm") {
    $str = '<ul class="pagination pagination-' . $size . '">';

    if ($totalPages <= $maxLinks) {
        // Print all of them.
        for ($i = 1; $i <= $totalPages; $i++) {
            print '<li class="' . (($i == $currentPage) ? 'active' : '') . '">';
            print '<a href="' . $urlBefore . $i . $urlAfter . '">' . $i . '</a>';
            print '</li>';
        }
    } else {
        // Figure out which ones to show.
        $pagesToShow = [];

        function addPage($val) {
            global $pagesToShow;

            if (!in_array($val, $pagesToShow)) {
                $pagesToShow[] = $val;
            }
        }

        addPage($currentPage);
        addPage(1);
        addPage($totalPages);

        // Find the midpoint between the first and current page.
        $mid = floor((1 + $currentPage) / 2);
        addPage($mid);

        // Find the midpoint between the current and last page.
        $mid = floor(($currentPage + $totalPages) / 2);
        addPage($mid);
    }

    $str .= '</ul>';

    return $str;
}, array(
    "is_safe" => array("html")
)));







// Determine the requested view.
// Key is the request URI, value is the view name.
$urls = array(
    '/' => 'index.php',
    
    '/siteevents/' => 'siteevents.php',
    '/terms/' => 'terms.php',
    '/contact/' => 'contact.php',
    
    // Coin Store
    '/store/' => 'store/index.php',
    
    // News
    '/news/(?:([0-9]+)/)?' => 'news/index.php',
    '/news/view/([0-9]+)/' => 'news/view.php',
    
    // Community
    '/community/' => 'community/index.php',
    '/community/search/' => 'community/search.php',
    '/community/memberlist/' => 'community/memberlist.php',
    '/community/happybox/' => 'community/happybox.php',
    '/community/vipdrawing/' => 'community/vipdrawing.php',
    '/community/vipranking/' => 'community/vipranking.php',
    '/community/siggenerator/' => 'community/siggenerator.php',
    '/community/gardentimer/' => 'community/gardentimer.php',
    '/community/requests/' => 'community/requests.php',
    '/community/comment/([0-9]+)/' => 'community/comment.php',
    
    // Community - Profile
    '/community/profile/([0-9]+)/' => 'community/profile/view.php',
    '/community/profile/([0-9]+)/comments/' => 'community/profile/comments.php',
    '/community/profile/([0-9]+)/characters/' => 'community/profile/characters.php',
    '/community/profile/([0-9]+)/friends/' => 'community/profile/friends.php',
    '/community/profile/([0-9]+)/conversation/([0-9]+)/' => 'community/profile/conversation.php',
    
    '/webpractice/' => 'community/webpractice.php',
    
    // Message Boards
    //'/community/boards/' => 'community/boards/index.php',
    //'/community/boards/([0-9]+)[a-z0-9\-]*/' => '/community/boards/view.php',
    //'/community/boards/topic/([0-9]+)[a-z0-9\-]*/' => '/community/boards/topic.php',
    //'/community/boards/post/([0-9]+)/' => '/community/boards/post.php',
    //'/community/boards/newtopic/([0-9]+)/' => '/community/boards/newtopic.php',
    //'/community/boards/newpost/([0-9]+)/' => '/community/boards/newpost.php',
    
    // Guides
    '/guides/' => 'guides/index.php',
    '/guides/exp/' => 'guides/exp.php',
    '/guides/licenses/' => 'guides/licenses.php',
    '/guides/story/(e1|e2|e3|secret)/' => 'guides/story.php',
    '/guides/couple/' => 'guides/couple.php',
    '/guides/coupleshop/' => 'guides/coupleshop.php',
    '/guides/couplegarden/' => 'guides/couplegarden.php',
    '/guides/tournamenttime/' => 'guides/tournamenttime.php',
    '/guides/timebox/' => 'guides/timebox.php',
    
    // Patches
    '/patch/' => 'patch/index.php',
    '/patch/([0-9]{8})/' => 'patch/view.php',
    '/patch/manualpatches/' => 'patch/manualpatches.php',
    '/patch/all/(20(?:07|08|09|10|11|12|13|14|15|16|17))/' => 'patch/all.php',
    
    // Music
    '/music/list/(?:([a-z]+)/)?' => 'music/list.php',
    '/music/nexonlist/' => 'music/nexonlist.php',
    
    // Account
    '/account/' => 'account/index.php',
    '/account/dashboard/' => 'account/dashboard.php',
    '/account/logout/' => 'account/logout.php',
    '/account/stuff/' => 'account/stuff.php',
    '/account/myaccount/' => 'account/myaccount.php',
    '/account/verify/' => 'account/verify.php',
    '/account/resend/' => 'account/resend.php',
    '/account/forgot/' => 'account/forgot.php',
    '/account/changepassword/' => 'account/changepassword.php',
    '/account/fbconnect/' => 'account/fbconnect.php',
    '/account/fbdisconnect/' => 'account/fbdisconnect.php',
    '/account/prizecode/([a-f0-9]{32})/' => 'account/prizecode.php',
    
    // Quests
    '/quests/' => 'quests/index.php',
    '/quests/ranking/' => 'quests/ranking.php',
    '/quests/archive/' => 'quests/archive.php',
    '/quests/submit/' => 'quests/submit.php',
    '/quests/submissions/' => 'quests/submissions.php',
    '/quests/grader/' => 'quests/grader/index.php',
    '/quests/grader/grade/' => 'quests/grader/grade.php',
    
    // Tickets
    '/tickets/' => 'tickets/index.php',
    '/tickets/new/' => 'tickets/new.php',
    '/tickets/view/([0-9]+)-([a-z0-9]{32})/' => 'tickets/view.php',
    
    // Help
    '/help/' => 'help/index.php',
    '/help/editprofile/' => 'help/editprofile.php',
    '/help/expcalculator/' => 'help/expcalculator.php',
    '/help/facebook/' => 'help/facebook.php',
    '/help/friends/' => 'help/friends.php',
    '/help/gardentimer/' => 'help/gardentimer.php',
    '/help/happybox/' => 'help/happybox.php',
    '/help/quests/' => 'help/quests.php',
    '/help/vip/' => 'help/vip.php',
    '/help/commentsyntax/' => 'help/commentsyntax.php',
    
    '/customize/' => 'customize.php',
    
    // CMS
    '/cms/' => 'cms/index.php',
    '/cms/songlist/' => 'cms/songlist.php',
    '/cms/questpoints/' => 'cms/questpoints.php'
);

// These URLs are only available to administrators.
if ($audifan -> getUser() -> isAdmin()) {
    $urls = array_merge($urls, array(
        '/admin/' => 'admin/index.php',
        '/admin/phpinfo/' => 'admin/phpinfo.php',
        
        '/admin/account/view/' => 'admin/account/view.php',
        '/admin/account/stuff/' => 'admin/account/stuff.php',
        '/admin/account/search/' => 'admin/account/search.php',
        '/admin/account/loginas/' => 'admin/account/loginas.php',
        
        '/admin/prizecodes/add/' => 'admin/prizecodes/add.php',
        '/admin/prizecodes/list/' => 'admin/prizecodes/list.php',
        '/admin/prizecodes/claims/' => 'admin/prizecodes/claims.php',
        
        '/admin/db/view/' => 'admin/db/view.php',
        
        '/admin/songs/add/' => 'admin/songs/add.php',
        '/admin/songs/view/' => 'admin/songs/view.php',
        '/admin/songs/edit/([0-9]+)/' => 'admin/songs/edit.php',
        '/admin/songs/removenewflags/' => 'admin/songs/removenewflags.php',
        
        '/admin/cron/run/' => 'admin/cron/run.php',
        
        '/admin/bitbucket/' => 'admin/bitbucket.php',
        
        '/admin/ticketlist/' => 'admin/ticketlist.php',
        
        '/admin/accountsbycoins/' => 'admin/accountsbycoins.php',
        
        '/quests/grader/admin/' => 'quests/grader/admin.php',
        '/quests/grader/archiver/' => 'quests/grader/archiver.php'
    ));
}

$context = array();
$viewData = array(
    "template" => NULL,
    "urlVariables" => array()
);
$context["GLOBAL"]["messages"]["error"] = array();
$context["GLOBAL"]["messages"]["success"] = array();



// Include view.
foreach ($urls as $k => $v) {
    $matchPattern = preg_replace("/\//", "\\/", $k);
    $matchPattern = '/^' . $matchPattern . '$/';
    
    if (preg_match($matchPattern, $requestUri, $viewData["urlVariables"])) {
        $filename = sprintf("%s/%s", $_CONFIG["viewLocation"], $v);
        if (file_exists($filename))
            include $filename;
        break;
    }
}


// ALL views do the following actions BEFORE RENDER:
if ($audifan -> getUser() -> isLoggedIn()) {
    // Logged in view actions
} else {
    // Logged out view actions
    // Create login form token if one does not already exist.
    if (!$audifan -> formTokenExists("login"))
        $audifan -> createFormToken("login");
}

// Megaphone query.
$megaQuery  = "SELECT Megaphones.*, Accounts.display_name, VIPs.is_vip FROM Megaphones ";
$megaQuery .= "LEFT JOIN Accounts ON Megaphones.mega_account=Accounts.id ";
$megaQuery .= "LEFT JOIN (SELECT account_id, 1 AS is_vip FROM AccountStuff WHERE item_id=? AND expire_time>?) AS VIPs ON Megaphones.mega_account=VIPs.account_id ";
$megaQuery .= "WHERE mega_expiretime>? ";
$megaQuery .= "ORDER BY RAND()";

// Set global context variables for the template.
$context["GLOBAL"] = array_merge_recursive($context["GLOBAL"], array(
    "staticUrl" => $_CONFIG["staticUrl"],
    "theme" => $audifan -> getTheme(),
    "notifManager" => $audifan -> getNotificationManager(),
    "user" => $audifan -> getUser(),
    "formTokens" => $audifan -> getFormTokens(),
    "requestUri" => $requestUri,
    "scrollerMessage" => $_CONFIG["scrollerMessage"],
    "now" => $audifan -> getNow(),
    "advertisementCount" => $_CONFIG["advertisementCount"],
    "coinGainMultiplier" => $_CONFIG["coinGainMultiplier"],
    "megaphones" => $audifan -> getDatabase() -> prepareAndExecute($megaQuery, Inventory::ITEM_VIPBADGE, time(), time()) -> fetchAll()
));

// Statuses for a logged in user.
if ($audifan -> getUser() -> isLoggedIn()) {
    $gt = $audifan -> getUser() -> getMainGardenTimerData();
    $audiTime = $audifan -> getNow() -> getAuditionTime();
    
    if ($audifan->getUser()->getInventory()->hasItem(Inventory::ITEM_NOADS)) {
        $context["GLOBAL"]["advertisementCount"] = 0;
    }

    $context["GLOBAL"]["status"] = array(
        "hb" => $audifan -> getUser() -> getTimeUntilNextHappyBoxSpin(),
        "quests" => $audifan -> getUser() -> getQuestData(),
        "coins" => $audifan -> getUser() -> getInventory() -> getCoinBalance()
    );
    
    if (!empty($gt)) {
        $context["GLOBAL"]["status"]["garden"] = array(
            "water" => ($gt["water"] < $audiTime) ? 0 : $gt["water"] - $audiTime,
            "dust" => ($gt["dust"] < $audiTime) ? 0 : $gt["dust"] - $audiTime,
            "fertilize" => ($gt["fertilize"] < $audiTime) ? 0 : $gt["fertilize"] - $audiTime,
            "rosemary" => ($gt["rosemary"] < $audiTime) ? 0 : $gt["rosemary"] - $audiTime,
            "spearmint" => ($gt["spearmint"] < $audiTime) ? 0 : $gt["spearmint"] - $audiTime,
            "peppermint" => ($gt["peppermint"] < $audiTime) ? 0 : $gt["peppermint"] - $audiTime
        );
    }
}

try {
    if ($viewData["template"] != NULL) {
        $twigTemplate = $twig -> loadTemplate($viewData["template"]);
        echo $twigTemplate -> render($context);
    } else
        throw new Twig_Error_Loader("Template not specified.");
} catch (Twig_Error_Loader $e) {
    //print $e -> getMessage();
    // 404
    http_response_code(404);
    $twigTemplate = $twig -> loadTemplate("404.twig");
    echo $twigTemplate -> render($context);
}




// ALL views do the following actions AFTER RENDER:
if ($audifan -> getUser() -> isLoggedIn()) {
    // Logged in actions
} else {
    // Logged out actions
    // Create new CSRF token for the login form if the user is not logged in.
    $audifan -> createFormToken("login");
}








if ($_CONFIG["debug"]) {
    print '<pre style="white-space:pre-wrap;">';
    print "CONTEXT:";
    unset($context['GLOBAL']);
    var_dump($context);
    print "SESSION:";
    var_dump($_SESSION);
    print "GET:";
    var_dump($_GET);
    print "POST:";
    var_dump($_POST);
    print "COOKIE:";
    var_dump($_COOKIE);
    print "SERVER:";
    var_dump($_SERVER);
    print '</pre>';
}

printf('<!-- Page rendered in %f seconds. -->', microtime(true) - $startTime);

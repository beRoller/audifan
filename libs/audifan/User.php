<?php

require_once "Inventory.php";

/**
 * Represents the current user of the site.
 */
class User {
    const ACCOUNTTYPE_UNVERIFIED = -1;
    const ACCOUNTTYPE_USER = 1;
    const ACCOUNTTYPE_MOD = 2;
    const ACCOUNTTYPE_ADMIN = 3;
    
    const SESSIONSTATUS_NONE = 1; // No session at all.
    const SESSIONSTATUS_VALID = 2; // The session is valid.
    const SESSIONSTATUS_REAUTH = 3; // The session needs the user to enter their password again.
    const SESSIONSTATUS_INVALID = 4; // The session is invalid or expired.
    
    const SESSIONPART_BASIC = 1; // From Accounts table.
    const SESSIONPART_SESSION = 2;
    const SESSIONPART_FLAGS = 4;
    const SESSIONPART_QUESTS = 8;
    const SESSIONPART_ALL = 15;
    
    /**
     * @var Audifan
     */
    private $audifan;
    
    private $loggedIn;
    private $ipAddress;
    
    private $accountType;
    private $nickname;
    private $id;
    
    private $flags;
    
    private $inventory;
    private $qp;
    
    private $mainGardenTimer;
    
    private $questData;
    
    public function __construct(Audifan $audifan) {
        $this -> audifan = $audifan;
        $this -> loggedIn = false;
        $this -> accountType = User::ACCOUNTTYPE_USER;
        $this -> ipAddress = $_SERVER["REMOTE_ADDR"];
        $this -> nickname = "";
        $this -> id = 0;
        $this -> flags = array();
        $this -> inventory = NULL;
        $this -> mainGardenTimer = array();
        $this -> questData = array();
        
        // Check session.
        $sessionStatus = $this -> getSessionStatus();
        
        if ($sessionStatus != User::SESSIONSTATUS_VALID) {
            $this -> updateSession();
            $sessionStatus = $this -> getSessionStatus();
        }
        
        if ($sessionStatus == User::SESSIONSTATUS_VALID) {
            $this -> loggedIn = true;
            $this -> nickname = $_SESSION["user"]["display_name"];
            $this -> accountType = $_SESSION["user"]["account_type"];
            $this -> id = $_SESSION["user"]["id"];
            $this -> flags = $_SESSION["user"]["flags"];
            
            $this -> inventory = new Inventory($this -> audifan, $this -> id);
            
            // Show hello notifications, if able.
            //if ($this -> audifan -> getNotificationManager() -> canShowType("hello")) {
            //    $this -> audifan -> getNotificationManager() -> addSessionNotification("Hello " . $this -> nickname . "!", "hello");
            //    $this -> audifan -> getNotificationManager() -> stopShowingType("hello");
            //}
            
            $this -> updateObject();
            
            // Check VIP status
            $vipCount = array(
                $this -> getFlag("vip_count_last_week"),
                $this -> getFlag("vip_count_last_day"),
                $this -> getFlag("vip_count_days")
            );
            $weekNum = $this -> audifan -> getNow() -> getWeekNumber();
            $dayNum = $this -> audifan -> getNow() -> getDayNumberOfWeek();
            $vipExpires = time() + (3600 * 24 * 7);
            $notifDays = 0;
            $loginDays = is_null($vipCount[2]) ? array('0','0','0','0','0','0','0') : str_split(base_convert($vipCount[2], 10, 2));
            
            while (7 - sizeof($loginDays) > 0)
                array_unshift($loginDays, '0');
            
            if ($weekNum != $vipCount[0]) {
                // They haven't yet logged in this week.
                $loginDays = array('0','0','0','0','0','0','0');
                $loginDays[$dayNum - 1] = '1';
                $this -> setFlag("vip_count_last_week", $weekNum);
                $this -> setFlag("vip_count_last_day", $dayNum);
                $this -> setFlag("vip_count_days", base_convert(implode("", $loginDays), 2, 10));
                $notifDays = 1;
            } elseif ($dayNum > $vipCount[1]) {
                // They haven't yet logged in today, according to their cache.
                // Check the database value just in case their cache isn't up to date.
                $vipCount[1] = $audifan -> getDatabase() -> prepareAndExecute("SELECT vip_count_last_day FROM AccountFlags WHERE account_id=?", $this -> getid()) -> fetchColumn();
                
                if ($dayNum > $vipCount[1]) {
                    // Now it's verified.
                    $loginDays[$dayNum - 1] = '1';
                    $this -> setFlag("vip_count_last_day", $dayNum);
                    $this -> setFlag("vip_count_days", base_convert(implode("", $loginDays), 2, 10));
                    
                    $valueCount = array_count_values($loginDays);
                    $daysLoggedIn = isset($valueCount["1"]) ? $valueCount["1"] : 0;
                    
                    if ($daysLoggedIn == 7) {
                        // Logged in every day.  Add a VIP badge to their inventory and add an additional
                        // VIP drawing entry.
                        $this -> inventory -> addItem(Inventory::ITEM_VIPBADGE, 3600 * 24 * 7);
                        $this -> inventory -> addItem(Inventory::ITEM_VIPDRAWINGENTRY, -1, 1);
                    }
                    
                    $notifDays = $daysLoggedIn;
                }
            }
            
            if ($notifDays > 0) {
                $n = "";
                
                if ($notifDays == 1)
                    $n = "You have logged in 1 day this week.";
                elseif ($notifDays == 7)
                    $n = sprintf('You have logged in every day this week.<br />You have earned VIP status until %s!<br />You have also earned an entry in our <a href="/community/vipdrawing/">VIP Drawing</a>!', date("F j", $vipExpires));
                else
                    $n = sprintf('You have logged in %d days this week.', $notifDays);
                
                $audifan -> logUserEvent(sprintf("Account ID %d VIP login days is now: %d", $this -> getId(), $notifDays));
                
                $this -> audifan -> getNotificationManager() -> addSessionNotification($n);
                
                // Add coin boxes
                if ($notifDays == 3) {
                    $this -> inventory -> addItem(Inventory::ITEM_COINBOX, 3600 * 24 * 14);
                    $this -> audifan -> getNotificationManager() -> addSessionNotification('You have received a Coin Box for logging in 3 days this week!  Open it from <a href="/account/stuff/">My Stuff</a>.');
                } elseif ($notifDays == 6) {
                    $this -> inventory -> addItem(Inventory::ITEM_DOUBLECOINBOX, 3600 * 24 * 14);
                    $this -> audifan -> getNotificationManager() -> addSessionNotification('You have received a Double Coin Box for logging in 6 days this week!  Open it from <a href="/account/stuff/">My Stuff</a>.');
                } elseif ($notifDays == 4) {
                    // Check for a "Log in at least 4 days" quest and give credit for it.
                    // Logging in quest is Easy Quest #12.
                    $q = $this -> audifan -> getDatabase() -> prepareAndExecute("SELECT req_id FROM QuestRequirements WHERE req_week_number=? AND req_year=? AND req_number=12 AND req_difficulty=1", date("W"), date("Y"));
                    if ($q -> rowCount() === 1) {
                        $reqId = $q -> fetchColumn();
                        if ($this -> audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM QuestSubmissions WHERE submit_req_id=? AND submit_account_id=?", $reqId, $this -> getId()) -> rowCount() === 0) {
                            $this -> audifan -> getDatabase() -> prepareAndExecute("INSERT INTO QuestSubmissions(submit_account_id,submit_req_id,submit_screenshot,submit_grade_status,submit_last_grade_time,submit_time) VALUES(?,?,?,?,?,?)", $this -> getId(), $reqId, "", 2, time(), time());
                            $this -> audifan -> getNotificationManager() -> addSessionNotification('You have successfully completed this week\'s easy <a href="/quests/">quest</a>!');
                            $this -> updateSession(User::SESSIONPART_QUESTS);
                        }
                    }
                }
            }
            
            // Update last activity time.
            $audifan -> getDatabase() -> prepareAndExecute("UPDATE AccountSessions SET session_last_activity_time=? WHERE session_key=?", time(), $_SESSION["user"]["session"]["session_key"]);
            
            // Set custom timezone.
            
            // Set cached values from other functions to null.
            $this -> qp = NULL;
        }
    }
    
    public function getSessionCookieValue() {
        return filter_input(INPUT_COOKIE, "audifan_sess", FILTER_VALIDATE_REGEXP, array(
            "options" => array(
                "regexp" => "/[a-z0-9]{64}/"
            )
        ));
    }
    
    public function getSessionStatus() {
        if (isset($_SESSION["user"]) && isset($_SESSION["user"]["session"])) {
            $info = array_merge(array(
                "session_key" => "",
                "session_ips" => array(),
                "session_create_time" => 0,
                "session_expire_time" => 0
            ), $_SESSION["user"]["session"]);
            
            $ip = $this -> getIpAddress();
            $cookieKey = $this -> getSessionCookieValue();
            
            if ($cookieKey != $info["session_key"])
                return User::SESSIONSTATUS_INVALID;
            elseif (!in_array($ip, $info["session_ips"]))
                return User::SESSIONSTATUS_REAUTH;
            else
                return User::SESSIONSTATUS_VALID;
        } else
            return User::SESSIONSTATUS_NONE;
    }
    
    /**
     * Updates the logged in user's session based on the user's session cookie.
     * This only updates the $_SESSION variable.  The constructor will set the appropriate values in the User class, if they are set in $_SESSION.
     * If some value needs to be updated AFTER the object is already constructed, call $this -> updateObject().
     * @param int $parts A bitwise disjunction of User::SESSIONPART_* variables for which part(s) of the session should be updated.
     */
    public function updateSession($parts = User::SESSIONPART_ALL) {
        // Updates session from the database.
        $db = $this -> audifan -> getDatabase();
        
        $cookieVal = $this -> getSessionCookieValue();
        
        $sessionInfo = $db -> prepareAndExecute("SELECT * FROM AccountSessions WHERE session_key=?", $cookieVal) -> fetch();
        if ($sessionInfo !== FALSE) {
            $_SESSION["user"]["session"] = array(
                "session_key" => $sessionInfo["session_key"],
                "session_ips" => array(),
                "session_create_time" => $sessionInfo["session_create_time"],
                "session_expire_time" => $sessionInfo["session_expire_time"]
            );
            
            foreach ($db -> prepareAndExecute("SELECT * FROM AccountSessionIPs WHERE session_key=?", $cookieVal ) as $row)
                array_push($_SESSION["user"]["session"]["session_ips"], $row["session_ip"]);
            
            $userId = $sessionInfo["session_account"];
            $userInfo = $db -> prepareAndExecute("SELECT id, display_name, account_type FROM Accounts WHERE id=?", $userId) -> fetch();
            
            $_SESSION["user"]["flags"] = $db -> prepareAndExecute("SELECT * FROM AccountFlags WHERE account_id=?", $userId) -> fetch();
            // Add flags if they do not exist.
            if ($_SESSION["user"]["flags"] === FALSE) {
                $db -> prepareAndExecute("INSERT INTO AccountFlags(account_id) VALUES(?)", $userId);
                $_SESSION["user"]["flags"] = $db -> prepareAndExecute("SELECT * FROM AccountFlags WHERE account_id=?", $userId) -> fetch();
            }
            
           // Garden Timer
            $q  = "SELECT GardenTimer.water_time AS water, GardenTimer.dust_time AS dust, GardenTimer.fertilize_time AS fertilize, ";
            $q .= "GardenTimer.rosemary_time AS rosemary, GardenTimer.spearmint_time AS spearmint, GardenTimer.peppermint_time AS peppermint ";
            $q .= "FROM GardenTimer ";
            $q .= "LEFT JOIN GardenTimerOptions ON GardenTimer.timer_id=GardenTimerOptions.timer_id ";
            $q .= "WHERE GardenTimer.account_id1=? OR GardenTimer.account_id2=? ";
            $q .= "ORDER BY GardenTimerOptions.display_order ASC ";
            $q .= "LIMIT 1";
            $_SESSION["gardentimer"] = $db -> prepareAndExecute($q, $userId, $userId) -> fetch();
            
            // Quests
            $stmt = $db -> prepareAndExecute("SELECT (data_easy_points + data_medium_points + data_hard_points + data_insane_points + data_group_points + data_battle_points) AS points FROM QuestData WHERE data_account_id=?", $userId);
            if ($stmt -> rowCount() == 1) {
                $points = $stmt -> fetchColumn();
                $rank = 0;
                
                $stmt = $db -> prepareAndExecute("SELECT rank_overall FROM QuestRanking WHERE rank_account_id=?", $userId);
                if ($stmt -> rowCount() == 1)
                    $rank = $stmt -> fetchColumn();
                
                $_SESSION["quests"] = array(
                    "points" => $points,
                    "rank" => $rank,
                    "submissions" => array(
                        "1" => "Not Yet Submitted",
                        "2" => "Not Yet Submitted",
                        "3" => "Not Yet Submitted",
                        "4" => "Not Yet Submitted",
                        "5" => "Not Yet Submitted",
                        "6" => "Not Yet Submitted"
                    )
                );
                
                $q  = "SELECT QuestSubmissions.submit_grade_status, QuestRequirements.req_difficulty FROM QuestSubmissions ";
                $q .= "LEFT JOIN QuestRequirements ON QuestSubmissions.submit_req_id = QuestRequirements.req_id ";
                $q .= "WHERE QuestSubmissions.submit_account_id=? AND QuestRequirements.req_year=? AND QuestRequirements.req_week_number=? ";
                $q .= "ORDER BY QuestSubmissions.submit_time ASC";
                foreach ($db -> prepareAndExecute($q, $userId, $this -> audifan -> getNow() -> getWeekYear(), $this -> audifan -> getNow() -> getWeekNumber()) as $row) {
                    $msg = "";
                    switch ($row["submit_grade_status"]) {
                        case 0:
                            $msg = "Pending";
                            break;
                        case 1:
                            $msg = "Rejected";
                            break;
                        case 2:
                            $msg = "Complete";
                            break;
                    }
                    $_SESSION["quests"]["submissions"][$row["req_difficulty"]] = $msg;
                }
            }
            
            if ($userInfo !== FALSE) {
                foreach ($userInfo as $k => $v)
                    $_SESSION["user"][$k] = $v;
            }
            
            $this -> audifan -> logUserEvent("Account ID " . $userId . " refreshed session.");
        }
    }
    
    /**
     * Updates this object with proper data from the session.
     */
    public function updateObject() {
        if (isset($_SESSION["gardentimer"]))
            $this -> mainGardenTimer = $_SESSION["gardentimer"];
            
        if (isset($_SESSION["quests"]))
            $this -> questData = $_SESSION["quests"];
    }
    
    /**
     * Attempts to log out the currently logged in user.
     */
    public function logOut() {
        if ($this -> isLoggedIn()) {
            // Remove session from database.
            $this -> audifan -> getDatabase() -> prepareAndExecute("DELETE FROM AccountSessions WHERE session_key=?", $_SESSION["user"]["session"]["session_key"]);
            
            // Delete session's account variables.
            unset($_SESSION["user"]);
            unset($_SESSION["notifs"]);
                    
            // Delete cookie
            setcookie("audifan_sess", "dead", time() - 3600, "/", null, false, true);
            
            // Regenerate session ID.
            session_regenerate_id(true);
            
            $this -> loggedIn = false;
            
           $this -> audifan -> logUserEvent(sprintf("Account ID %d logged out.", $this -> getId()));
        }
    }
    
    public function hashPassword($password, $email) {
        $nonce = 'audifanpassword7125';
        return hash("sha256", $password .'$'.$nonce.'$'.$email);
    }
    
    public function generateSessionKey($id) {
        $nonce = 'audifansession7125';
        return hash("sha256", $id.'$'.$nonce.'$'.  $this -> getIpAddress() . '$' . microtime() . '$'.rand(1,10000));
    }
    
    public function getNickname() {
        return $this -> nickname;
    }
    
    public function setNickname($name) {
        $_SESSION["user"]["display_name"] = $name;
        $this -> nickname = $name;
    }
    
    public function getMainGardenTimerData() {
        return $this -> mainGardenTimer;
    }
    
    /**
     * The ID of this user, if they are logged in.
     * @return int ID of the logged in user or 0 if not logged in.
     */
    public function getId() {
        return $this -> id;
    }
    
    public function getTimeBetweenHappyBoxSpins() {
        if ($this -> isLoggedIn()) {
            $BASECOOLDOWN = (8*3600);
            $QPTHRESHOLD = 1234;
            
            $inventory = $this -> getInventory();
            
            if ($inventory -> hasItem(Inventory::ITEM_HBCOOLDOWN100))
                return 0;
            
            $qp = $this -> getQP();
            
            $gap = $BASECOOLDOWN;
            
            if ($inventory -> hasItem(Inventory::ITEM_VIPBADGE))
                $gap *= .6;
            
            // Cooldown items
            if ($inventory -> hasItem(Inventory::ITEM_HBCOOLDOWN10))
                $gap *= .9;
            elseif ($inventory -> hasItem(Inventory::ITEM_HBCOOLDOWN25))
                $gap *= .75;
            elseif ($inventory -> hasItem(Inventory::ITEM_HBCOOLDOWN40))
                $gap *= .6;
            
            // QP bonus
            if ($qp >= $QPTHRESHOLD)
                $gap *= .5;
            else
                $gap *= 1 - ($qp / ($QPTHRESHOLD * 2));
            
            // Quest badge bonus
            if ($inventory -> hasItem(Inventory::ITEM_QUESTBADGETHUMBSUP))
                $gap -= (60 * 7.5);
            elseif ($inventory -> hasItem(Inventory::ITEM_QUESTBADGEBRONZE))
                $gap -= (60 * 15);
            elseif ($inventory -> hasItem(Inventory::ITEM_QUESTBADGESILVER))
                $gap -= (60 * 22.5);
            elseif ($inventory -> hasItem(Inventory::ITEM_QUESTBADGEGOLD))
                $gap -= (60 * 30);
            
            return $gap;
        } else
            return 0;
    }
    
    public function getTimeUntilNextHappyBoxSpin() {
        if ($this -> isLoggedIn()) {
            $gap = $this -> getTimeBetweenHappyBoxSpins();
            
            $lastSpinTime = $this -> getFlag("last_hb_spin", false);
            if ($lastSpinTime == NULL)
                $lastSpinTime = 0;
            
            if ($lastSpinTime == 0) // Hasn't spun yet.
                return 1;
            
            $timeUntil = ($lastSpinTime + $gap) - $this -> audifan -> getCurrentTime();
            if ($timeUntil <= 0)
                $timeUntil = 1;
            
            return ceil($timeUntil);
        } else
            return 0;
    }
    
    public function getQP() {
        if ($this -> isLoggedIn()) {
            if (!is_null($this -> qp))
                return $this -> qp;
            else {
                $res = $this -> audifan -> getDatabase() -> prepareAndExecute("SELECT (data_easy_points+data_medium_points+data_hard_points+data_insane_points+data_group_points+data_battle_points) AS qp FROM QuestData WHERE data_account_id=?", $this -> getId()) -> fetch();
                $this -> qp = $res["qp"];
                return $this -> qp;
            }
        } else
            return 0;
    }
    
    public function getQuestData() {
        return $this -> questData;
    }
    
    /**
     * @return Inventory
     */
    public function getInventory() {
        return $this -> inventory;
    }
    
    public function getFlag($flagName, $useCache = true) {
        if (array_key_exists($flagName, $this -> flags)) { // also means they're logged in.
            if ($useCache) {
                return $this -> flags[$flagName];
            } else {
                return $this -> audifan -> getDatabase() -> prepareAndExecute("SELECT " . $flagName . " FROM AccountFlags WHERE account_id=?", $this -> getId()) -> fetchColumn();
            }
        } else
            return NULL;
    }
    
    public function setFlag($flagName, $newValue, $sessionOnly = false) {
        if (array_key_exists($flagName, $this -> flags)) {
            $this -> flags[$flagName] = $newValue;
            $_SESSION["user"]["flags"][$flagName] = $newValue;
            
            if (!$sessionOnly)
                $this -> audifan -> getDatabase() -> prepareAndExecute("UPDATE AccountFlags SET " . $flagName . "=? WHERE account_id=?", $newValue, $this -> getId());
        }
    }
    
    /**
     * Changes the currently logged in user's password to a new password.
     * @param type $newPassword
     */
    public function changePassword($newPassword) {
        if ($this -> isLoggedIn()) {
            $db = $this -> audifan -> getDatabase();
            $user = $db -> prepareAndExecute("SELECT email FROM Accounts WHERE id=?", $this -> id);
            if ($user !== FALSE) {
                $email = $user -> fetchColumn();
                $db -> prepareAndExecute("UPDATE Accounts SET password=? WHERE id=?", $this -> hashPassword($newPassword, $email), $this -> id);
            }
        }
    }
    
    public function isLoggedIn() {
        return $this -> loggedIn;
    }
    
    public function getIpAddress() {
        return $this -> ipAddress;
    }
    
    public function isAdmin() {
        return ($this -> loggedIn && $this -> accountType >= User::ACCOUNTTYPE_ADMIN);
    }
    
    public function isMod() {
        return ($this -> loggedIn && $this -> accountType >= User::ACCOUNTTYPE_MOD);
    }
    
    public function getCachedCoinBalance() {
        
    }
    
    /**
     * Attempts to create a new user.
     * @param string $email The email to use.
     * @param string $password The password to use.
     * @param string $nickname The nickname to use.
     * @param &$id Where to store the ID of the user after their account is created.
     * @return array An array of errors, if any occurred, while trying to create the user.
     * The key is the part that had an issue, the value is an array of error messages.
     */
    public function create($email, $password, $nickname, &$id = NULL) {
        $errors = array();
        
        $db = $this -> audifan -> getDatabase();
        
        $q  = "SELECT t1.email_count, t2.nickname_count ";
        $q .= "FROM (SELECT COUNT(*) AS email_count FROM Accounts WHERE email=?) AS t1, ";
        $q .= "(SELECT COUNT(*) AS nickname_count FROM Accounts WHERE display_name=?) AS t2";
        
        $counts = $db -> prepareAndExecute($q, $email, $nickname) -> fetch();
        if ($counts["email_count"] != 0) {
            $errors["email"] = "This email is already in use.";
        }
        if ($counts["nickname_count"] != 0) {
            $errors["nickname"] = "This nickname is already in use.";
        }
        
        if (empty($errors)) {
            // Create a verification code.
            $verificationCode = substr(md5($email . $nickname . "codetoverifyemail"), 0, 16);
            
            // Register the user.
            $db -> prepareAndExecute("INSERT INTO Accounts (email, password, display_name, account_type, join_time, verification_code, profile_modules) VALUES (?,?,?,?,?,?,?)", 
                $email, $this -> hashPassword($password, $email), $nickname, User::ACCOUNTTYPE_UNVERIFIED, time(), $verificationCode, "0;1,3");
            $id = $db -> lastInsertId();
            
            // Email the verification code.
            $message = sprintf("Hi %s,\n\nWelcome to Audifan.net!\n\nPlease click this link to verify your email address:\nhttp://%s/account/verify/?id=%d&code=%s\n\nIf you have any questions, please email us at kevin@audifan.net.\n\nThis message was sent to %s.  Please do not reply to this email.",
                    $nickname, $this -> audifan -> getConfigVar("domain"), $id, $verificationCode, $email);
            $headers = "From: Audifan <noreply@audifan.net>\nX-Sender: <noreply@audifan.net>\n";
            mail($email, "Audifan.net - Account Verification", $message, $headers);
        }
        
        return $errors;
    }
    
    public function createFromFacebook($fbid, $nickname, &$id = NULL) {
        $errors = array();
        $db = $this -> audifan -> getDatabase();
        
        $count = $db -> prepareAndExecute("SELECT COUNT(*) FROM Accounts WHERE display_name=?", $nickname) -> fetchColumn();
        if ($count != 0) {
            $errors["nickname"] = "That nickname is already in use.";
        }
        
        if (empty($errors)) {
            // Register the user.
            $db -> prepareAndExecute("INSERT INTO Accounts(fbid, display_name, account_type, join_time, profile_modules) VALUES(?,?,?,?,?)", $fbid, $nickname, User::ACCOUNTTYPE_USER, time(), "0;1,3");
            $id = $db -> lastInsertId();
        }
        
        return $errors;
    }
    
    /**
     * Attempts to create a new user using data from POST parameters from the
     * registration form.
     * @param &$id Where to store the ID of the user after their account is created.
     * @return array An array of errors, if any occurred, while trying to create the user.
     * The key is the part that had an issue, the value is an array of error messages.
     */
    public function createFromPost(&$id = NULL) {
        $errors = array();
        
        $input = filter_input_array(INPUT_POST, array(
            "email" => FILTER_VALIDATE_EMAIL,
            "email2" => FILTER_VALIDATE_EMAIL,
            "nickname" => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^[A-Za-z0-9\-\_\~]{2,20}$/"
                )
            ),
            "password" => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^.{6,}$/"
                )
            ),
            "password2" => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^.{6,}$/"
                )
            ),
            "g-recaptcha-response" => FILTER_DEFAULT,
            "agree" => FILTER_DEFAULT
        ));
        
        if ($input["email"] === NULL) {
            $errors["email"] = "Please enter an email address.";
        } elseif ($input["email"] === FALSE) {
            $errors["email"] = "Please enter a valid email address.";
        } elseif ($input["email"] != $input["email2"]) {
            $errors["email2"] = "These emails do not match.";
        }
        
        if ($input["nickname"] === NULL) {
            $errors["nickname"] = "Please enter a nickname";
        } elseif ($input["nickname"] === FALSE) {
            $errors["nickname"] = "Your nickname must be between 2 and 20 characters, and it may only contain letters, numbers, underscores (_), dashes (-), and tildes (~).";
        }
        
        if ($input["password"] === NULL) {
            $errors["password"] = "Please create a password.";
        } elseif ($input["password"] === FALSE) {
            $errors["password"] = "Your password must have at least 6 characters.";
        } elseif ($input["password"] != $input["password2"]) {
            $errors["password2"] = "Your passwords did not match.";
        }
        
        if ($input["agree"] === NULL)
            $errors["agree"] = "You must agree to Audifan's Terms of Use.";
        
        // Check Captcha.
        
        if (empty($errors))
            $errors = $this -> create($input["email"], $input["password"], $input["nickname"], $id);
        
        return $errors;
    }
    
    /**
     * Attempts to authenticate a user.
     * @param string $email The user's email.
     * @param string $password The user's password.
     * @param int $expireTime When the session should expire. 0 for end of browser session, which will be 12 hours saved in the database.
     * @param boolean $bypass If the user should be immediately authenticated without checking the password.  DO NOT USE in most cases.
     * @param int $fbid The Facebook ID to use instead of an email.  It will be used only if it is nonzero.
     */
    public function authenticate($email, $password, $expireTime = 0, $bypass = false, $fbid = "0") {
        $errors = array();
        
        $db = $this -> audifan -> getDatabase();
        
        $user;
        if ($fbid != "0") {
            $user = $db -> prepareAndExecute("SELECT * FROM Accounts WHERE fbid=?", $fbid) -> fetch();
        } else {
            $user = $db -> prepareAndExecute("SELECT * FROM Accounts WHERE email=?", $email) -> fetch();
        }
        
        if ($user !== FALSE) {
            $passwordOk = false;
            
            if ($bypass || $user["password"] == $this -> hashPassword($password, $email))
                $passwordOk = true;
            else {
                // See if their password matches any of the old encryption methods and update it.
                $salt = "audifan7125";
                if ($user["password"] == hash("sha256", $password . $salt) || $user["password"] == md5($password)) {
                    $passwordOk = true;
                    $db -> prepareAndExecute("UPDATE Accounts SET password=? WHERE id=?", $this -> hashPassword($password, $email), $user["id"]);
                }
            }
            
            if ($passwordOk) {
                // TODO: Check if banned.
                if ($user["account_type"] == 0) {
                    // User is banned.  See if their ban can be removed.
                }
                
                if ($user["account_type"] < 0) {
                    // User hasn't verified their email.
                    $errors["account"] = "You haven't verified your email yet.<br />Do you need us to <a href=\"/account/resend/\">resend the email</a>?";
                } elseif ($user["account_type"] == 0) {
                    // User is banned.
                    $errors["account"] = "Unfortunately, your account has been banned.";
                } else {
                    $sessionKey = $this -> generateSessionKey($user["id"]);

                    $db -> beginTransaction();
                    try {
                        $db -> prepareAndExecute("INSERT INTO AccountSessions(session_key, session_account, session_create_time, session_expire_time) VALUES (?,?,?,?)", $sessionKey, $user["id"], time(), ($expireTime == 0) ? time() + (3600 * 12) : $expireTime);
                        $db -> prepareAndExecute("INSERT INTO AccountSessionIPs(session_key, session_ip) VALUES(?,?)", $sessionKey, $this -> getIpAddress());
                        $db -> finishTransaction();
                    } catch (PDOException $e) {
                        error_log($e -> getMessage());
                        $db -> finishTransaction(false);
                    }

                    setcookie("audifan_sess", $sessionKey, $expireTime, "/", null, false, true);
                    session_regenerate_id(true);
                }
            } else
                $errors["password"] = "The password you entered was incorrect.";
            
            if (empty($errors)) {
                $this -> audifan -> logUserEvent(sprintf("Account ID %d authenticated (bypass=%s, fbid=%s, ip=%s)", $user["id"], $bypass, $fbid, $this -> getIpAddress()));
            }
        } else
            $errors["email"] = "The email you entered is not registered.";
        
        return $errors;
    }
    
    /**
     * Attempts to authenticate a user from POST parameters set by a login form.
     * 
     * @return array An array of errors.  The array is empty if there were no errors.
     */
    public function authenticateFromPost($bypass = false) {
        $errors = array();
        
        $input = filter_input_array(INPUT_POST, array(
            "email" => FILTER_VALIDATE_EMAIL,
            "password" => array(
                "filter" => FILTER_VALIDATE_REGEXP,
                "options" => array(
                    "regexp" => "/^.{6,}$/"
                )
            ),
            "rememberme" => FILTER_DEFAULT
        ));
        
        if ($input["email"] !== NULL) {
            if ($input["email"] === FALSE)
                $errors["email"] = "Please enter a valid email address.";
        } else {
            $errors["email"] = "Please enter an email address.";
        }
        
        if (!$bypass) {
            if ($input["password"] !== NULL) {
                if ($input["password"] === FALSE)
                    $errors["password"] = "The password you entered was incorrect.";
            } else {
                $errors["password"] = "Please enter your password.";
            }
        }
        
        if (empty($errors))
            $errors = $this -> authenticate($input["email"], $input["password"], !is_null($input["rememberme"]) ? (time() + (3600 * 24 * 365)) : 0, $bypass);
        
        return $errors;
    }
}

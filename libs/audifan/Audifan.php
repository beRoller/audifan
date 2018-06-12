<?php

require_once "Now.php";
require_once "Database.php";
require_once "NotificationManager.php";
require_once "User.php";

class Audifan {
    private $config;
    
    private $now;
    
    private $database;
    private $user;
    private $notificationManager;
    
    public function __construct($configVars = array()) {
        $this -> config = $configVars;
        
        // Set the current time before the user's custom timezone is set.
        date_default_timezone_set("America/New_York");
        $this -> now = new Now($configVars["timeOffset"]);
        
        $this -> database = new Database($this -> config);
        $this -> destroyDatabaseConfig();
        
        $this -> notificationManager = new NotificationManager($this);
        $this -> user = new User($this);
        
        $this -> notificationManager -> initialize();
        
        header("Content-Type: text/html; charset=utf-8");
        
        if (!isset($_SESSION["accessCount"]) || $_SESSION["accessCount"] >= 5) {
            // Actions on stale session.
            $_SESSION["accessCount"] = 0;
        }
        
        $_SESSION["accessCount"]++;
    }
    
    /**
     * This will destroy the database configuration variables after the database has been
     * initialized to prevent them from accidentally printing on the website while it is
     * in debug mode.
     */
    private function destroyDatabaseConfig() {
        unset($this -> config["dbUser"]);
        unset($this -> config["dbPassword"]);
        unset($this -> config["dbDatabase"]);
    }
    
    /**
     * @return Database
     */
    public function getDatabase() {
        return $this -> database;
    }
    
    /**
     * @return User
     */
    public function getUser() {
        return $this -> user;
    }
    
    /**
     * @return NotificationManager
     */
    public function getNotificationManager() {
        return $this -> notificationManager;
    }
    
    public function getCurrentTime() {
        return $this -> now -> getTime();
    }
    
    /**
     * @return Now
     */
    public function getNow() {
        return $this -> now;
    }
    
    public function getConfigVar($name) {
        return array_key_exists($name, $this -> config) ? $this -> config[$name] : NULL;
    }
    
    public function logUserEvent($message) {
        error_log(sprintf("[%s] %s\n", date("n/j/y H:i:s"), $message), 3, $this -> getConfigVar("logLocation") . "/user-" . date("Ymd") . ".log");
    }
    
    public function createFormToken($formName) {
        $token = md5($formName . '$token$' . time());
        $_SESSION["formTokens"][$formName] = $token;
    }
    
    public function formTokenExists($formName) {
        if (array_key_exists("formTokens", $_SESSION))
            return array_key_exists($formName, $_SESSION["formTokens"]);
        else
            return false;
    }
    
    public function getFormTokens() {
        return isset($_SESSION["formTokens"]) ? $_SESSION["formTokens"] : array();
    }
    
    /**
     * Turns shortcuts into BB code in the text.
     * For example, turns @tr848 into [user=1]tr848[/user]
     * @param type $text
     */
    public function prepareBBCode($text) {
        // Change @ user references to [user] tags.
        /*$text = preg_replace_callback('/\@([A-Za-z0-9\-\_\~]+)/i', function($matches) {
            $nickname = $matches[1];
            // Lookup nickname.
            $idLookup = $this -> getDatabase() -> prepareAndExecute("SELECT id FROM Accounts WHERE display_name=?", $nickname) -> fetch();
            if ($idLookup !== false) {
                // Send notification if it's not the user posting it.
                if ($this -> getUser() -> isLoggedIn() && $this -> getUser() -> getId() != $idLookup["id"]) {
                    //$this -> getNotificationManager() -> addDatabaseNotification('<a href=""></a> mentioned you in a <a href="">comment</a>.', $userId, $type)
                }
                
                return sprintf('[user=%d]@%s[/user]', $idLookup["id"], $nickname);
            } else {
                return '@' . $nickname;
            }
        }, $text);*/
        
        return $text;
    }
    
    public function getTheme() {
        $colors = array(
            "default" => array( // Dark
                "darkest" => "000000",
                "darker" => "222222",
                "darkerhighlight" => "333333",
                "middle" => "002B39",
                "lighter" => "0099cc",
                "lightest" => "ffffff"
            ),
            "purple" => array( // Purple (old new 3.0 theme)
                "darkest" => "421c52",
                "darker" => "732c7b",
                "darkerhighlight" => "843d8c",
                "middle" => "9c8aa5",
                "lighter" => "bdaec6",
                "lightest" => "ffffff"
            )
        );
        
        $theme = "default";
        
        $cookieTheme = filter_input(INPUT_COOKIE, "audifan_theme", FILTER_VALIDATE_REGEXP, array(
            "options" => array(
                "regexp" => "/(purple)/"
            )
        ));
        if (!is_null($cookieTheme) && $cookieTheme !== FALSE)
            $theme = $cookieTheme;
        
        return array(
            "name" => $theme,
            "colors" => $colors[$theme]
        );
    }

    public function __toString() {
        return "Audifan";
    }
}
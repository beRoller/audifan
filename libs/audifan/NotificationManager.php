<?php

/**
 * The Notification Manager handles notification data for session-only notifications that
 * should be displayed immediately for the current user as well as database notifications
 * that are displayed either when the user to be notified logs in or after a few
 * session reloads.
 */
class NotificationManager {
    /**
     * An array of assoc arrays with notification data.
     * The assoc array looks like this:
     * array(
     *  "html" => "The HTML of the notification.",
     *  "type" => "The notif type.",
     *  "unread" => 1 if unread, 0 if read,
     *  "time" => Unix timestamp,
     *  "dbid" => The database ID of the notification or 0 if it is a session notification,
     *  "data" => "Optional data stored with the notif.  Usually used when coalescing multiple notifs into one.",
     * )
     * @var array
     */
    private $notifData;
    
    private $cachedDbIds;
    
    private $hasUnread;
    private $filters;
    
    /**
     * @var Audifan
     */
    private $audifan;
    
    public function __construct(Audifan $audifan) {
        $this -> audifan = $audifan;
        
        if (!isset($_SESSION["notifs"])) {
            $_SESSION["notifs"] = array(
                "data" => array(),
                "filters" => array(),
                "hasUnread" => false,
                "cachedDbIds" => array(0)
            );
        }
        
        $this -> getFromSession();
    }
    
    public function initialize() {
        $this -> getFromDatabase();
        
        // Check if cached database notifications still exist in the database.
        $db = $this -> audifan -> getDatabase();
        
        foreach ($this -> cachedDbIds as $id) {
            if ($db -> prepareAndExecute("SELECT * FROM Notifications WHERE notif_id=?", $id) -> fetch() === FALSE) {
                $this -> deleteDatabaseNotification($id);
            }
        }
    }
    
    /**
     * Loads notification data from the session.
     */
    private function getFromSession() {
        $this -> notifData = $_SESSION["notifs"]["data"];
        $this -> filters = $_SESSION["notifs"]["filters"];
        $this -> hasUnread = $_SESSION["notifs"]["hasUnread"];
        $this -> cachedDbIds = $_SESSION["notifs"]["cachedDbIds"];
    }
    
    /*
     * Loads notification data from the database.
     */
    private function getFromDatabase() {
        if ($this -> audifan -> getUser() -> isLoggedIn()) {
            $db = $this -> audifan -> getDatabase();
            $addedDbNotif = false;
            
            $ids = $this -> cachedDbIds;
            array_push($ids, 0);
            foreach ($db -> prepareAndExecute("SELECT * FROM Notifications WHERE account_id=? AND notif_id NOT IN (" . implode(",", $ids) . ") ORDER BY time DESC", $this -> audifan -> getUser() -> getId()) as $row) {
                array_push($this -> notifData, array(
                    "html" => $row["notif_text"],
                    "type" => $row["notif_type"],
                    "unread" => $row["notif_unread"],
                    "time" => $row["time"],
                    "dbid" => $row["notif_id"],
                    "data" => array($row["notif_data1"], $row["notif_data2"], $row["notif_data3"], $row["notif_data4"], $row["notif_data5"], $row["notif_data6"], $row["notif_data7"])
                ));
                $this -> hasUnread = ($this -> hasUnread || $row["notif_unread"]);
                array_push($this -> cachedDbIds, $row["notif_id"]);
                $addedDbNotif = true;
            }
            
            if ($addedDbNotif)
                $this -> sortByTime();
        }
    }
    
    /**
     * Sorts notifications by time (most recent first).
     */
    private function sortByTime() {
        $n = sizeof($this -> notifData);
        
        $swapped;
        do {
            $swapped = false;
            for ($i = 1; $i < $n; $i++) {
                if ($this -> notifData[$i - 1]["time"] < $this -> notifData[$i]["time"]) {
                    $tmp = $this -> notifData[$i];
                    $this -> notifData[$i] = $this -> notifData[$i - 1];
                    $this -> notifData[$i - 1] = $tmp;
                    $swapped = true;
                }
            }
            $n--;
        } while ($swapped);
    }
    
    /**
     * Saves notification data to the session.
     */
    private function saveToSession() {
        $_SESSION["notifs"] = array(
            "data" => $this -> notifData,
            "filters" => $this -> filters,
            "hasUnread" => $this -> hasUnread,
            "cachedDbIds" => $this -> cachedDbIds
        );
    }
    
    /**
     * Adds a session-only notification for this user.
     * Does not show the notification if the type is disallowed by stopShowingType().
     * If you want to add a database notification for any user, use
     * NotificationManager::addDatabaseNotification().
     * 
     * @param type $html The HTML of the message to display. It will be rendered
     * as raw HTML through the template engine, without entity escaping.
     * @param type $type The type of notification.
     * @see NotificationManager::addDatabaseNotification().
     */
    public function addSessionNotification($html, $type = "") {
        if ($type != "") {
            if (!$this -> canShowType($type))
                return;
        }
        
        array_unshift($this -> notifData, array(
            "html" => $html,
            "type" => $type,
            "unread" => 1,
            "time" => time(),
            "dbid" => 0,
            "data" => array("","","","","","","")
        ));
        
        $this -> hasUnread = true;
        
        $this -> sortByTime();
        $this -> saveToSession();
    }
    
    /**
     * Adds a notification to the database.
     * 
     * @param string $html The HTML of the notification.
     * @param int $userId The user ID of the user who will receive the notification.
     * @param string $type The notification type.
     * @param array $data Optional additional data that will be stored with the notification.  Up to 7 int values can be specified in an array.
     */
    public function addDatabaseNotification($html, $userId, $type, $data = array()) {
        $db = $this -> audifan -> getDatabase();
        
        $notifData = array();
        for ($i = 0; $i < sizeof($data); $i++) {
            array_push($notifData, $data[$i]);
        }
        
        while (sizeof($notifData) < 7)
            array_push($notifData, "");
        
        $db -> prepareAndExecute("INSERT INTO Notifications(account_id,notif_text,notif_type,notif_data1,notif_data2,notif_data3,notif_data4,notif_data5,notif_data6,notif_data7,time) VALUES(?,?,?,?,?,?,?,?,?,?,?)",
                $userId, $html, $type, $notifData[0], $notifData[1], $notifData[2], $notifData[3], $notifData[4], $notifData[5], $notifData[6], time());
        
        if ($userId == $this -> audifan -> getUser() -> getId()) {
            $this -> getFromDatabase();
        }
    }
    
    /**
     * Returns an array of all notifications that can be displayed.
     * It is assumed that using this function to retrieve notifications means
     * they will immediately be displayed.  Therefore, they will all be marked as
     * read.
     */
    public function getAllNotificationsForDisplay() {
        $dbids = array(); // IDs to mark as read.
        $data = $this -> getAll();
        
        for ($i = 0; $i < sizeof($data); $i++) {
            $d = $data[$i];
            
            if ($d["dbid"] != 0)
                array_push($dbids, $d["dbid"]);
        }
        
        $this -> hasUnread = false;
        $this -> saveToSession();
        
        if (!empty($dbids)) {
            $q  = "UPDATE Notifications SET notif_unread=0 WHERE notif_id IN (";
            $q .= implode(",", $dbids);
            $q .= ")";
            $this -> audifan -> getDatabase() -> prepareAndExecute($q);
        }
        
        return $data;
    }
    
    /**
     * This will only delete it in the database if the logged in user owns it.
     * It'll always delete it from the session.
     * @param int $id The ID of the notification.
     */
    public function deleteDatabaseNotification($id) {
        $db = $this -> audifan -> getDatabase();
        $user = $this -> audifan -> getUser();
        
        $db -> prepareAndExecute("DELETE FROM Notifications WHERE notif_id=? AND account_id=?", $id, $user -> getId());
        
        // Delete from session.
        for ($i = 0; $i < sizeof($this -> notifData); $i++) {
            if ($this -> notifData[$i]["dbid"] == $id) {
                array_splice($this -> notifData, $i, 1);
                break;
            }
        }
        
        $index = array_search($id, $this -> cachedDbIds);
        
        if ($index !== FALSE)
            array_splice($this -> cachedDbIds, $index, 1);
        
        $this -> saveToSession();
    }
    
    public function getAll() {
        $data = $this -> notifData;
        
        $this -> saveToSession();
        
        return $data;
    }
    
    public function canShowType($type) {
        return !in_array($type, $this -> filters);
    }
    
    public function stopShowingType($type) {
        if ($this -> canShowType($type)) {
            array_push($this -> filters, $type);
            $this -> saveToSession();
        }
    }
    
    public function startShowingType($type) {
        if (!$this -> canShowType($type)) {
            $key = array_search($type, $this -> filters);
            array_splice($this -> filters, $key, 1);
            $this -> saveToSession();
        }
    }
    
    public function removeAllWithType($type) {
        for ($i = 0; $i < sizeof($this -> notifData); $i++) {
            $n = $this -> notifData[$i];
            if ($n["type"] == $type) {
                array_splice($this -> notifData, $i, 1);
                $i--;
            }
        }
    }
    
    /**
     * @return boolean true if there are unread notifications.5
     */
    public function hasUnreadNotifications() {
        return $this -> hasUnread;
    }
}
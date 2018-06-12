<?php

/**
 * Now represents different features of the server time (regardless of user's timezone settings),
 * such as the current week number, day number, etc.
 */
class Now {
    /**
     * The current Unix timestamp.
     * @var int
     */
    private $time;
    
    /**
     * The offset of the Audition server.
     * @var int
     */
    private $auditionTimeOffset;
    
    /**
     * The current week number of the year.
     * @var int
     */
    private $weekNumber;
    
    /**
     * The current day number of the week.  1 is Monday, 7 is Sunday.
     * @var type 
     */
    private $dayNumberOfWeek;
    
    private $weekYear;
    
    private $hour;
    
    private $minute;
    
    public function __construct($auditionTimeOffset = 0) {
        $this -> auditionTimeOffset = $auditionTimeOffset;
        $this -> time = time();
        $this -> weekNumber = (int) date("W");
        $this -> dayNumberOfWeek = (int) date("N");
        $this -> weekYear = (int) date("o");
        $this -> hour = (int) date("H");
        $this -> minute = (int) date("i");
    }
    
    /**
     * Equivalent to date("H").
     */
    public function getHour() {
        return $this -> hour;
    }
    
    /**
     * Equivalent to date("i").
     */
    public function getMinute() {
        return $this -> minute;
    }
    
    public function getTime() {
        return $this -> time;
    }
    
    /**
     * Equivalent to date("W").
     * @return int The week number of the year.
     */
    public function getWeekNumber() {
        return $this -> weekNumber;
    }
    
    /**
     * Equivalent to date("N").
     * 1 is Monday, 7 is Sunday.
     * @return int The day number of the week.
     */
    public function getDayNumberOfWeek() {
        return $this -> dayNumberOfWeek;
    }
    
    /**
     * @return int The current Audition time (Unix timestamp).
     */
    public function getAuditionTime() {
        return $this -> time + $this -> auditionTimeOffset;
    }
    
    /**
     * Equivalent to date("o").
     * @return int Returns the year, which may be changed depending on the week number.
     */
    public function getWeekYear() {
        return $this -> weekYear;
    }
}
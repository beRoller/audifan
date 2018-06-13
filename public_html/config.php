<?php

/*
 * *** NOTE ***
 * Although cron jobs also use this configuration file, they must be configured manually to
 * be able to find this file.  Configuring cron/setup.php should suffice.
 */
 
// These are server-specific configurations and should be edited server-side.
$_CONFIG = array(
    "timeOffset" => -19,
    
    "maintenanceMessage" => "",
    "maintenanceAllowedIps" => array("127.0.0.1", "::1"),
    
    "domain" => "localhost",
    
    // Where PHP views are located.
    "viewLocation" => "/home5/audition/audifan/views",
    
    // Where HTML templates for the renderer are located.
    "templateLocation" => "/home5/audition/audifan/templates",
    "templateCacheLocation" => "/home5/audition/audifan/templatecache",

    "ajaxLocation" => "/home5/audition/audifan/ajax",
    
    // Where libraries are located.
    "libsLocation" => "/home5/audition/libs",
    
    // Where cron scripts are location.
    "cronLocation" => "/home5/audition/public_html/cron",
    
    "logLocation" => "/home5/audition/audifan/logs",
    
    // Public URL of static resources.
    "staticUrl" => "/static",
    
    "localPublicLocation" => "/home5/audition/public_html",
    
    // Show super globals and context variables at the bottom of each page.
    "debug" => false,
    
    // Number of advertisements to show.
    // 1 or 2 for one or both at the bottom. 3 for both at the bottom and a side one.
    "advertisementCount" => 3,
    
    "nameChangeDays" => 15,
    
    // If new accounts are allowed to be registered.
    "allowRegistration" => true,
    
    // Database Information
    "dbUser" => "",
    "dbPassword" => "",
    "dbDatabase" => "",
    
    // Steam info
    "steamWebApiKey" => "",
    "steamAuditionAppId" => "",
    
    // Facebook info
    "facebookAppId" => "",
    "facebookAppSecret" => "",
    
    "scrollerMessage" => '',
    
    "maxFriends" => 300,
    
    // Global coin config
    "coinGainMultiplier" => 1, // Increase to add coin gains globally whenever someone gets coins added to their account. e.g. 1.2 is a 20% increase.
    "storePriceMultiplier" => 1, // Decrease to create sales.  e.g. .9 is a 10% sale.
    
    "happyBoxQPThreshold" => 1234 // The QP needed for the maximum Happy Box cooldown bonus.
);

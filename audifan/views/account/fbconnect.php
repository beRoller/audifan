<?php

/* @var $audifan Audifan */

require_once $audifan -> getConfigVar("libsLocation") . "/Facebook/autoload.php";

$db = $audifan -> getDatabase();

$getFilter = filter_input_array(INPUT_GET, array(
    "accessToken" => FILTER_DEFAULT,
    "thru" => array(
        "filter" => FILTER_VALIDATE_REGEXP,
        "options" => array(
            "regexp" => '/^\/.*$/'
        )
    )
));

$context["status"] = "badaccesstoken";

if (!is_null($getFilter["accessToken"])) {
    $token = $getFilter["accessToken"];
    $context["accessToken"] = $token;
    
    $fb = new Facebook\Facebook(array(
        "app_id" => $audifan -> getConfigVar("facebookAppId"),
        "app_secret" => $audifan -> getConfigVar("facebookAppSecret"),
        "default_graph_version" => "v2.0"
    ));
    
    $fb -> setDefaultAccessToken($token);
    
    $fbid = "0";
    try {
        $resp = $fb -> get("/me") -> getDecodedBody();
        $fbid = $resp["id"];
        $context["fbname"] = $resp["name"];
        $context["fbid"] = $fbid;
    } catch (Facebook\Exceptions\FacebookAuthenticationException $e) {}
    catch (Facebook\Exceptions\FacebookResponseException $e) {}
    
    $postFilter = filter_input_array(INPUT_POST, array(
        "submit_newaccount" => FILTER_DEFAULT,
        "nickname" => array(
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => array(
                "regexp" => "/^[A-Za-z0-9\-\_\~]{2,20}$/"
            )
        ),
        
        "submit_existingaccount" => FILTER_DEFAULT,
        "email" => FILTER_VALIDATE_EMAIL,
        "password" => FILTER_DEFAULT
    ));
    
    
    
    
    if ($fbid != "0") {
        if (!is_null($postFilter["submit_newaccount"]) && $_CONFIG["allowRegistration"]) {
            if (!is_null($postFilter["nickname"]) && $postFilter["nickname"] !== FALSE) {
                // Add new account.
                $errors = $audifan -> getUser() -> createFromFacebook($fbid, $postFilter["nickname"]);
                foreach ($errors as $e)
                    array_push($context["GLOBAL"]["messages"]["error"], $e);
            } else {
                array_push($context["GLOBAL"]["messages"]["error"], "Your nickname must be between 2 and 20 characters in length and may only contain letters, numbers, underscores ( _ ), dashes ( - ), and tildes ( ~ ).");
            }
        } elseif (!is_null($postFilter["submit_existingaccount"])) {
            // Update existing account.
            if (!is_null($postFilter["email"]) && $postFilter["email"] !== FALSE && !is_null($postFilter["password"])) {
                $res = $db -> prepareAndExecute("SELECT id, email, password FROM Accounts WHERE email=?", $postFilter["email"]) -> fetch();
                if ($res != FALSE) {
                    if ($res["password"] == $audifan -> getUser() -> hashPassword($postFilter["password"], $res["email"])) {
                        // Password is correct.
                        $db -> prepareAndExecute("UPDATE Accounts SET fbid=? WHERE id=?", $fbid, $res["id"]);
                    } else
                        array_push($context["GLOBAL"]["messages"]["error"], "The password you entered was incorrect.");
                } else
                    array_push($context["GLOBAL"]["messages"]["error"], "That account doesn't exist.");
            } else
                array_push($context["GLOBAL"]["messages"]["error"], "Please enter a valid email and password.");
        }
        
        if ($db -> prepareAndExecute("SELECT COUNT(*) FROM Accounts WHERE fbid=?", $fbid) -> fetchColumn() != 0) {
            $errors = $audifan -> getUser() -> authenticate("", "", 0, true, $fbid);
            if (empty($errors)) {
                $thru;
                if (!is_null($getFilter["thru"]) && $getFilter["thru"] !== FALSE)
                    $thru = $getFilter["thru"];
                else
                    $thru = "/";

                $audifan -> getNotificationManager() -> addSessionNotification("You have successfully logged in via Facebook.");
                header("Location: " . $thru);
                exit;
            } else {
                // Error with authentication.
                $context["status"] = "accounterror";
            }
        } else {
            $context["status"] = "noaccount";
        }
    }
}

$viewData["template"] = "account/fbconnect.twig";
$context["allowRegistration"] = $_CONFIG["allowRegistration"];
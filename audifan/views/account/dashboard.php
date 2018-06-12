<?php

$viewData["template"] = "account/dashboard.twig";

$user = $audifan->getUser();

if (!$user->isLoggedIn())
	return;



$db = $audifan->getDatabase();

$account_info = $db->prepareAndExecute("SELECT join_time FROM Accounts WHERE id=?", $user->getId())->fetch();

$context["joinTime"] = $account_info['join_time'];

$context["timeUntilNextHappyBoxSpin"] = $user->getTimeUntilNextHappyBoxSpin();






<?php

$viewData["template"] = "admin/accountsbycoins.twig";

$context["accounts"] = $audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM Accounts WHERE coin_total!=0 ORDER BY coin_total DESC") -> fetchAll();
<?php

$thru = filter_input(INPUT_GET, "thru");
if ($thru === NULL || $thru === FALSE)
    $thru = "/";

$audifan -> getUser() -> logOut();

header("Location: " . $thru);
exit;
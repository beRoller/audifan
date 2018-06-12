<?php

/*
 * This is a cron script to run the VIP Drawing (if it is the day to do it).
 * It should be run every day.  If the drawing does not happen today, nothing happens.
 */

require_once "setup.php";

/* @var $audifan Audifan */

$db = $audifan -> getDatabase();

$day = $audifan -> getNow() -> getDayNumberOfWeek();

if ($day == 5) {
    $lastSundayMonth = (int) date("n", time() - (3600 * 24 * 5));
    $lastSundayYear = (int) date("Y", time() - (3600 * 24 * 5));
    $nextSundayMonth = (int) date("n", time() + (3600 * 24 * 2));
    
    if ($nextSundayMonth != $lastSundayMonth) {
        //$pastWinners = explode(",", file_get_contents("vipwinners.txt"));
        $pastWinners = [];
        foreach ($db -> prepareAndExecute("SELECT * FROM VIPDrawingWinners ORDER BY winner_year DESC, winner_month DESC LIMIT 12") as $row) {
            $pastWinners[] = $row["winner_account_id"];
        }
        
        $drawingPool = array();
        $namesById = array();
        $ignsById = array();
        
        foreach ($db -> prepareAndExecute("SELECT AccountStuff.*, Accounts.display_name FROM AccountStuff LEFT JOIN Accounts ON AccountStuff.account_id=Accounts.id WHERE item_id=15 AND note != ''") as $row) {
            $namesById[$row["account_id"]] = $row["display_name"];
            $ignsById[$row["account_id"]] = $row["note"];

            for ($i = 0; $i <= $row["charges"]; $i++)
                array_push($drawingPool, $row["account_id"]);
        }
        
        if (!empty($drawingPool)) {
            if (sizeof($drawingPool) != 1) {
                $resultString = trim(file_get_contents(sprintf("https://www.random.org/sequences/?min=0&max=%d&col=1&format=plain&rnd=new", sizeof($drawingPool) - 1)));
                $result = explode("\n", $resultString);

                $winner;
                $i = 0;
                do {
                    $winner = $drawingPool[$result[$i]];
                    $i++;
                    if ($i >= sizeof($result)) {
                        printf("\n\nVIP Drawing: Everyone who participated has already won!");
                        exit;
                    }
                } while (in_array($winner, $pastWinners));
            } else {
                $result = array(0);
                $winner = $drawingPool[$result[0]];
            }

            $db -> beginTransaction();

            try {
                // Give VIP Drawing Prize
                $db -> prepareAndExecute("INSERT INTO AccountStuff(account_id,item_id,expire_time) VALUES (?,?,?)", $winner, 13, time() + (3600 * 24 * 14));

                // Write this drawing's results in case the winner doesn't claim their prize.
                $fh = fopen("lastvipdrawing.txt", "w");
                for ($i = 0; $i < sizeof($result); $i++)
                    fwrite($fh, $drawingPool[$result[$i]] . ",");
                fclose($fh);

                // Update past winners list.
                $db -> prepareAndExecute("INSERT INTO VIPDrawingWinners(winner_year,winner_month,winner_account_id,winner_ign) VALUES (?,?,?,?)", $lastSundayYear, $lastSundayMonth, $winner, $ignsById[$winner]);
                // array_push($pastWinners, $winner);
                // $fh = fopen("vipwinners.txt", "w");
                // fwrite($fh, implode(",", $pastWinners));
                // fclose($fh);

                // Delete VIP Drawing Entries
                $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE item_id=15");

                // Add news entry.
                $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
                $lastSundayMonthName = $months[$lastSundayMonth - 1];
                $db -> prepareAndExecute("INSERT INTO News(type, title, description, time) VALUES (?,?,?,?)", "Site Event", sprintf("%s VIP Drawing Winner", $lastSundayMonthName), sprintf('Congratulations to <a href="/community/profile/%d/">%s</a> for winning the %s VIP Drawing!', $winner, $namesById[$winner], $lastSundayMonthName), time());
                printf("\n\nVIP Drawing: %s won this month.", $namesById[$winner]);

                $db -> finishTransaction();
            } catch (PDOException $ex) {
                printf("\n\nVIP Drawing: SQL ERROR: %s", $ex -> getMessage());
                $db -> finishTransaction(false);
            }
        } else {
            print "\n\nVIP Drawing: NO VIPS!";
        }
    } else
        print "\n\nVIP Drawing: Drawing does not take place today.";
} else
    print "\n\nVIP Drawing: Drawing does not take place today.";
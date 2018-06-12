<?php

/**
 * Represents a user's inventory.
 * The user's inventory is cached on a page-by-page basis.  The first time it is accessed, it is pulled from the database and saved to this object, then that cache is used in subsequent accesses.
 * If Inventory::invalidateCache is called, the next time it is accessed, it is pulled from the database and cached again.
 * Nothing is saved to the session.
 */
class Inventory {

    // Items
    const ITEM_ADMINBADGE = 1;
    const ITEM_VIPBADGE = 2;
    const ITEM_HBCOOLDOWN10 = 3;
    const ITEM_HBCOOLDOWN25 = 4;
    const ITEM_HBCOOLDOWN40 = 5;
    const ITEM_HBCOOLDOWN100 = 6;
    const ITEM_QUESTBADGETHUMBSUP = 7;
    const ITEM_QUESTBADGEBRONZE = 8;
    const ITEM_QUESTBADGESILVER = 9;
    const ITEM_QUESTBADGEGOLD = 10;
    const ITEM_MEGAPHONETICKET = 11;
    const ITEM_BADGEVOUCHER = 12;
    const ITEM_VIPDRAWINGPRIZEVOUCHER = 13;
    const ITEM_CANDLEDAYBADGE = 14;
    const ITEM_VIPDRAWINGENTRY = 15;
    const ITEM_BADGE16 = 16; // Badges given by a badge voucher... ballroom top hat
    const ITEM_BADGE17 = 17; // ballroom flowers
    const ITEM_BADGE18 = 18; // guardian angel
    const ITEM_BADGE19 = 19; // microphone
    const ITEM_BADGE20 = 20; // dj
    const ITEM_BADGE21 = 21; // flower heart
    const ITEM_BADGE22 = 22; // bu mission
    const ITEM_BADGE23 = 23; // bu tourney medal
    const ITEM_BADGE24 = 24; // hidden mission
    const ITEM_BADGE25 = 25; // normal mission
    const ITEM_BADGE26 = 26; // couple tourney medal
    const ITEM_BADGE27 = 27; // br mission
    const ITEM_BADGE28 = 28; // one two mission
    const ITEM_BADGE29 = 29; // br tourney
    const ITEM_CABADGE = 30; // CA
    const ITEM_COINBOX = 31; // Regular Coin Box
    const ITEM_DOUBLECOINBOX = 32; // x2 Coin Box
    const ITEM_TRIPLECOINBOX = 33; // x3 Coin Box
    const ITEM_COINBONUS5PERCENT = 34; // +5% coin item
    const ITEM_COINBONUS15PERCENT = 35; // +15% coin item
    const ITEM_COINBONUS25PERCENT = 36; // +25% coin item
    const ITEM_MYSTERYCOINBOX = 37; // Mystery Coin Box
    const ITEM_BADGE38 = 38; // Guitar tourney medal
    const ITEM_JACKPOTBADGE = 39; // Happy Box Jackpot Badge
    const ITEM_COUPLESCOINBONUS = 40;
    const ITEM_NOADS = 41;
    
    const ITEMTYPE_BADGE = 1;
    const ITEMTYPE_VOUCHER = 2;
    const ITEMTYPE_OTHER = 3;
    const ITEMTYPE_COINBOX = 4;
    
    const ITEMSTACK_REPLACE = 1; // If another item is added and one already exists, remove the old one and replace it with the one being added.
    const ITEMSTACK_CHARGE = 2; // If another item is added and one already exists, the number of charges increases on the existing one instead of adding another separate item. If one does not already exist, add it normally.
    const ITEMSTACK_MULTIPLE = 3; // Multiple items can be separately added.
    const ITEMSTACK_EXTEND = 4; // If another item is added and one already exists, increase the duration of the existing item by the duration of the item that is being added.
    const ITEMSTACK_ONEONLY = 5; // If another item is added and one already exists, it is ignored.

    // Table of item information.
    // [0] is the item type, [1] is the stack method, [2] is the name.
    // [3] is the url of the image that can be displayed with it or NULL if there is no image.

    public static $ITEMINFO = array(
        /* N/A */ array(),
        /* 1   */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_ONEONLY, 'Admin Badge', '/img/badges/admin.png'),
        /* 2   */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_REPLACE, "VIP Badge", '/img/badges/vip.gif'),
        /* 3   */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "-10% Happy Box Cooldown", '/img/store/10hbcooldown.png'),
        /* 4   */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "-25% Happy Box Cooldown", '/img/store/25hbcooldown.png'),
        /* 5   */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "-40% Happy Box Cooldown", '/img/store/40hbcooldown.png'),
        /* 6   */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "-100% Happy Box Cooldown", '/img/store/100hbcooldown.png'),
        /* 7   */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_REPLACE, "Thumbs Up Quest Badge", '/img/quest/badge1.png'),
        /* 8   */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_REPLACE, "Bronze Quest Badge", '/img/quest/badge2.png'),
        /* 9   */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_REPLACE, "Silver Quest Badge", '/img/quest/badge3.gif'),
        /* 10  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_REPLACE, "Gold Quest Badge", '/img/quest/badge4.gif'),
        /* 11  */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_CHARGE, "Megaphone Ticket", '/img/store/megaticket.png'),
        /* 12  */ array(Inventory::ITEMTYPE_VOUCHER, Inventory::ITEMSTACK_MULTIPLE, "Badge Voucher", NULL),
        /* 13  */ array(Inventory::ITEMTYPE_VOUCHER, Inventory::ITEMSTACK_MULTIPLE, "VIP Drawing Prize Voucher", NULL),
        /* 14  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_REPLACE, "Candle Day Badge", '/img/badges/candle.png'),
        /* 15  */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_CHARGE, "VIP Drawing Entry", '/img/store/vipdrawingentry.png'),
        /* 16  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Ballroom Top Hat Badge", '/img/badges/16.png'),
        /* 17  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Ballroom Flowers Badge", '/img/badges/17.png'),
        /* 18  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Guardian Angel Badge", '/img/badges/18.png'),
        /* 19  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Microphone Badge", '/img/badges/19.png'),
        /* 20  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "DJ Badge", '/img/badges/20.png'),
        /* 21  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Flower Heart Badge", '/img/badges/21.png'),
        /* 22  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Beat Up Mission Badge", '/img/badges/22.png'),
        /* 23  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Beat Up Tournament Badge", '/img/badges/23.png'),
        /* 24  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Hidden Mission Badge", '/img/badges/24.png'),
        /* 25  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Normal Mission Badge", '/img/badges/25.png'),
        /* 26  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Couple Tournament Badge", '/img/badges/26.png'),
        /* 27  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Beat Rush Mission Badge", '/img/badges/27.png'),
        /* 28  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "One Two Party Mission Badge", '/img/badges/28.png'),
        /* 29  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Beat Rush Tournament Badge", '/img/badges/29.png'),
        /* 30  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_ONEONLY, "CA Badge", '/img/badges/cabadge.png'),
        /* 31  */ array(Inventory::ITEMTYPE_COINBOX, Inventory::ITEMSTACK_MULTIPLE, "Coin Box", '/img/coins/coinbox.png'),
        /* 32  */ array(Inventory::ITEMTYPE_COINBOX, Inventory::ITEMSTACK_MULTIPLE, "Double Coin Box", '/img/coins/coinbox2.png'),
        /* 33  */ array(Inventory::ITEMTYPE_COINBOX, Inventory::ITEMSTACK_MULTIPLE, "Triple Coin Box", '/img/coins/coinbox3.png'),
        /* 34  */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "+5% Coin Bonus", '/img/store/5coinbonus.png'),
        /* 35  */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "+15% Coin Bonus", '/img/store/15coinbonus.png'),
        /* 36  */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "+25% Coin Bonus", '/img/store/25coinbonus.png'),
        /* 37  */ array(Inventory::ITEMTYPE_COINBOX, Inventory::ITEMSTACK_MULTIPLE, "Mystery Coin Box", '/img/coins/mysterycoinbox.png'),
        /* 38  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_EXTEND, "Guitar Tournament Badge", '/img/badges/38.png'),
        /* 39  */ array(Inventory::ITEMTYPE_BADGE, Inventory::ITEMSTACK_ONEONLY, "Happy Box Jackpot Badge", '/img/badges/jackpotbadge.gif'),
        /* 40  */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "Couple's Coin Bonus", "/img/store/couplescoinbonus.png"),
        /* 41  */ array(Inventory::ITEMTYPE_OTHER, Inventory::ITEMSTACK_EXTEND, "No Ads", "/img/store/noads.png")
    );

    /**
     * @var Audifan
     */
    private $audifan;

    /**
     * @var int
     */
    private $userId;

    /**
     * Set to NULL when it needs to be pulled from the database.
     * @var array
     */
    private $cachedInventory;

    /**
     * Set to -1 when it needs to be pulled from the database.
     * @var int
     */
    private $coinBalance;

    /**
     * Constructs an inventory for the specified user.
     * @param Audifan $audifan The Audifan object for the currently running site.
     * @param int $userId The user ID to get inventory for.
     */
    public function __construct(Audifan $audifan, $userId) {
        $this -> audifan = $audifan;
        $this -> userId = $userId;

        $this -> invalidateCache();
    }

    /**
     * Invalidates this inventory's cache so that it is pulled from the database the next time it is accessed.
     */
    public function invalidateCache() {
        $this -> cachedInventory = NULL;
        $this -> coinBalance = -1;
    }

    /**
     * Loads inventory data from the database.
     */
    public function load() {
        $this -> cachedInventory = $this -> audifan -> getDatabase() -> prepareAndExecute("SELECT * FROM AccountStuff WHERE account_id=? AND (expire_time>? OR expire_time=-1)", $this -> userId, time()) -> fetchAll();
        $this -> coinBalance = $this -> audifan -> getDatabase() -> prepareAndExecute("SELECT coin_balance FROM Accounts WHERE id=?", $this -> userId) -> fetchColumn();
    }

    /**
     * Gets an array of items with the fields directly from the database.
     * @return array
     */
    public function getFullList() {
        if ($this -> cachedInventory == NULL)
            $this -> load();
        return $this -> cachedInventory;
    }

    /**
     * Gets an array of item IDs ONLY that are in this inventory.
     * @return array
     */
    public function getSimpleList() {
        $fullList = $this -> getFullList();

        $simpleList = array();
        foreach ($fullList as $i)
            array_push($simpleList, $i["item_id"]);

        return $simpleList;
    }

    /**
     * @param int $itemId The item to look for.
     * @return boolean true if this inventory contains at least one of the specified item.
     */
    public function hasItem($itemId) {
        return in_array($itemId, $this -> getSimpleList());
    }

    /**
     * Determines if this inventory contains at least one of the items in the list.
     * @param array $itemList An array of item IDs.
     * @return boolean
     */
    public function hasAnyItems($itemList) {
        foreach ($itemList as $i)
            if ($this -> hasItem($i))
                return true;

        return false;
    }

    /**
     * Determines if this inventory contains ALL of the items in the list.
     * @param array $itemList An array of item IDs.
     */
    public function hasAllItems($itemList) {
        foreach ($itemList as $i)
            if (!$this -> hasItem($i))
                return false;

        return true;
    }

    /**
     * The admin, CA badges
     * @return int The number of badges in this inventory.
     */
    public function numBadges() {
        $count = 0;

        foreach ($this -> getSimpleList() as $i)
            if (Inventory::$ITEMINFO[$i][0] == Inventory::ITEMTYPE_BADGE)
                $count++;

        return $count;
    }

    /**
     * @return int The number of vouchers in this inventory.
     */
    public function numVouchers() {
        $count = 0;

        foreach ($this -> getSimpleList() as $i)
            if (Inventory::$ITEMINFO[$i][0] == Inventory::ITEMTYPE_VOUCHER)
                $count++;

        return $count;
    }

    /**
     * @return int The number of items that are not badges or vouchers in this inventory.
     */
    public function numOther() {
        $count = 0;

        foreach ($this -> getSimpleList() as $i)
            if (Inventory::$ITEMINFO[$i][0] == Inventory::ITEMTYPE_OTHER)
                $count++;

        return $count;
    }

    /**
     * Adds an item to this inventory based on its rules as defined in this class.
     * @param int $itemId Item ID to add.
     * @param int $duration How long the item should stay in the inventory or -1 for indefinite.
     * @param int $charges The number of charges this item should have. Default is 0.
     * @see Inventory::$ITEMINFO
     */
    public function addItem($itemId, $duration = 0, $charges = 0) {
        $stack = Inventory::$ITEMINFO[$itemId][1];
        $db = $this -> audifan -> getDatabase();

        if ($this -> hasItem($itemId) && $stack != Inventory::ITEMSTACK_MULTIPLE) {
            switch ($stack) {
                case Inventory::ITEMSTACK_CHARGE:
                    $db -> prepareAndExecute("UPDATE AccountStuff SET charges=charges+? WHERE account_id=? AND item_id=?", $charges, $this -> userId, $itemId);
                    break;

                case Inventory::ITEMSTACK_EXTEND:
                    $db -> prepareAndExecute("UPDATE AccountStuff SET expire_time=expire_time+? WHERE account_id=? AND item_id=?", $duration, $this -> userId, $itemId);
                    break;

                case Inventory::ITEMSTACK_REPLACE:
                    $db -> beginTransaction();
                    try {
                        $db -> prepareAndExecute("DELETE FROM AccountStuff WHERE account_id=? AND item_id=?", $this -> userId, $itemId);
                        $db -> prepareAndExecute("INSERT INTO AccountStuff(account_id,item_id,expire_time,charges,in_use) VALUES(?,?,?,?,?)", $this -> userId, $itemId, ($duration == -1) ? -1 : time() + $duration, $charges, 1);
                        $db -> finishTransaction();
                    } catch (PDOException $e) {
                        $db -> finishTransaction(false);
                    }

                    break;

                // Note: Inventory::ITEMSTACK_ONEONLY is not defined because the item is ignored if one already exists.
            }
        } else {
            // Add new item.
            $db -> prepareAndExecute("INSERT INTO AccountStuff(account_id,item_id,expire_time,charges,in_use) VALUES(?,?,?,?,?)", $this -> userId, $itemId, ($duration == -1) ? -1 : time() + $duration, $charges, 1);
        }

        $this -> invalidateCache(); // Data will need to be pulled from DB the next time the inventory is accessed.
    }

    /**
     * Removes all of the specified item from this inventory.
     * @param int $itemId The item to remove. All instances will be removed.
     */
    public function removeItem($itemId) {
        $this -> audifan -> getDatabase() -> prepareAndExecute("DELETE FROM AccountStuff WHERE account_id=? AND item_id=?", $this -> userId, $itemId);
        $this -> invalidateCache();
    }

    /**
     * @return int The total number of items in this inventory.
     */
    public function totalItems() {
        return sizeof($this -> getSimpleList());
    }

    /**
     * @param int $itemId The ID of the item to count.
     * @return int The number of the specified item in this inventory.
     */
    public function numOfItem($itemId) {
        $count = 0;

        foreach ($this -> getSimpleList() as $i)
            if ($itemId == $i)
                $count++;

        return $count;
    }
    
    /**
     * @param array $itemIds The IDs of items to count.
     * @return int The sum of number of the specified items in this inventory.
     */
    public function numOfAllItems(array $itemIds) {
        $count = 0;
        
        foreach ($this -> getSimpleList() as $i) {
            if (in_array($i, $itemIds))
                $count++;
        }
        
        return $count;
    }

    // Coin functions

    /**
     * This function ALWAYS gets the value from the database.
     * @return int The balance of coins in this inventory.
     */
    public function getCoinBalance() {
        return $this -> audifan -> getDatabase() -> prepareAndExecute("SELECT coin_balance FROM Accounts WHERE id=?", $this -> userId) -> fetchColumn();
    }

    /**
     * Adds coins to this inventory and logs the source in the CoinBalance table.
     * Also applies bonuses
     * 
     * @param int $amount The amount of coins to add.
     * @param string $source [Optional] Why these coins were added.
     * @param boolean $applyBonuses [Optional] Whether or not to apply bonuses to the amount to add.  It will almost always be true.
     * @return int The actual amount of coins that were added to the user's inventory, with bonuses added.
     */
    public function addCoins($amount, $source = "", $applyBonuses = true) {
        $newAmount = $amount;

        if ($applyBonuses) {
            // Global multiplier.
            $globalMult = $this -> audifan -> getConfigVar("coinGainMultiplier");
            if (!is_null($globalMult)) {
                $newAmount *= $globalMult;
            }

            // Veteran Bonus (+10% if account is more than a year old)
            if ($this -> audifan -> getDatabase() -> prepareAndExecute("SELECT join_time FROM Accounts WHERE id=?", $this -> userId) -> fetchColumn() <= time() - (3600 * 24 * 365)) {
                $newAmount *= 1.1;
            }

            // Coin Bonus Items
            if ($this -> hasItem(Inventory::ITEM_COINBONUS5PERCENT)) {
                $newAmount *= 1.05;
            } elseif ($this -> hasItem(Inventory::ITEM_COINBONUS15PERCENT)) {
                $newAmount *= 1.15;
            } elseif ($this -> hasItem(Inventory::ITEM_COINBONUS25PERCENT)) {
                $newAmount *= 1.25;
            }
            
            // Couple's Coin Bonus
            if ($this -> hasItem(Inventory::ITEM_COUPLESCOINBONUS)) {
                $newAmount *= 1.1;
            }
        }
        
        $newAmount = floor($newAmount);

        $this -> audifan -> getDatabase() -> prepareAndExecute("UPDATE Accounts SET coin_balance=coin_balance+?, coin_total=coin_total+? WHERE id=?", $newAmount, $newAmount, $this -> userId);
        $this -> audifan -> getDatabase() -> prepareAndExecute("INSERT INTO CoinHistory(account_id,history_amount,history_bonus,history_source,history_time) VALUES(?,?,?,?,?)", $this -> userId, $newAmount, $newAmount - $amount, $source, time());

        $this -> invalidateCache();
        
        return $newAmount;
    }

    /**
     * Removes coins from this inventory and logs the source in the CoinBalance table.
     * If the user's coin balance is lower than the amount to remove, this function returns false and does nothing.
     * @param int $amount The amount of coins to remove.
     * @param string $source Why these coins were removed.
     * @return true if the coins were successfully removed, false if they were not removed.
     */
    public function removeCoins($amount, $source = "") {
        $result = false;

        if ($this -> getCoinBalance() >= $amount) {
            $this -> audifan -> getDatabase() -> prepareAndExecute("UPDATE Accounts SET coin_balance=coin_balance-? WHERE id=?", $amount, $this -> userId);
            $this -> audifan -> getDatabase() -> prepareAndExecute("INSERT INTO CoinHistory(account_id,history_amount,history_source,history_time) VALUES(?,?,?,?)", $this -> userId, -$amount, $source, time());
            $result = true;
        }

        $this -> invalidateCache();
        return $result;
    }
}
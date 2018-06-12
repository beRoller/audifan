(function ($) {
    var spinFunc = function(){};
    var spinFuncInterval = null; // Used for the spin animation.
    
    var cdFunc = function(){};
    var cdFuncInterval = null; // Used for the cooldown countdown.

    var jackpotAnimation = function () {
        var x0 = 0;
        var x1 = $("body").innerWidth() - 256;

        var y0 = $(window).scrollTop();
        var y1 = y0 + 200;

        for (var i = 0; i < 20; i++) {
            setTimeout(function () {
                var x = Math.floor(Math.random() * (x1 - x0)) + x0;
                var y = Math.floor(Math.random() * (y1 - y0)) + y0;
                $("body").append('<div class="firework" data-frame="0" style="left:' + x + 'px; top:' + y + 'px;"></div>');
            }, 250 * i);
        }
    };

    var requestSpin = function () {
        _.ajax({spin: "spin"}, null, function (d) {
            // Stop animation.
            if (spinFuncInterval) {
                clearInterval(spinFuncInterval);
                $("#happyboxballspin").removeClass().addClass("happyboxballspinstatic");
                spinFuncInterval = null;
            }

            // Handle prize display
            var prizeTexts = {
                "prize_3": "You won a -10% Cooldown Item!<br />Time between Happy Box spins reduced by 10%.<br /><br />Lasts 7 days.<br />If you already owned this item, the time has been extended by 7 days.<br /><br />Congrats!",
                "prize_4": "You won a -25% Cooldown Item!<br />Time between Happy Box spins reduced by 25%.<br /><br />Lasts 7 days.<br />If you already owned this item, the time has been extended by 7 days.<br /><br />Congrats!",
                "prize_5": "You won a -40% Cooldown Item!<br />Time between Happy Box spins reduced by 40%.<br /><br />Lasts 7 days.<br />If you already owned this item, the time has been extended by 7 days.<br /><br />Congrats!",
                "prize_11": "You won a Megaphone ticket!<br /><br />Use it by clicking the Megaphone link at the top of any page.<br /><br />Congrats!",
                "prize_12": "You won a Badge!<br /><br />Choose your new badge on the My Stuff page.<br /><br />You have 2 weeks to redeem your badge or it will disappear.<br /><br />Congrats!",
                "prize_15": "You won a VIP Drawing Entry!<br /><br />An additional entry has been added for the drawing at the end of the month.<br /><br />Congrats!",
                "prize_31": 'You won a Coin Box!<br /><br />Open it on the <a href="/account/stuff/" target="_blank">My Stuff</a> page.<br /><br />You have 2 weeks to open it or it will disappear.<br /><br />Congrats!',
                "prize_32": 'You won a Double Coin Box!<br /><br />Open it on the <a href="/account/stuff/" target="_blank">My Stuff</a> page.<br /><br />You have 2 weeks to open it or it will disappear.<br /><br />Congrats!',
                "prize_33": 'You won a Triple Coin Box!<br /><br />Open it on the <a href="/account/stuff/" target="_blank">My Stuff</a> page.<br /><br />You have 2 weeks to open it or it will disappear.<br /><br />Congrats!',
                "prize_34": "You won a +5% Coin Item!<br />Increases coin gains from all sources by 5%.<br /><br />Lasts 7 days.<br />If you already owned this item, the time has been extended by 7 days.<br /><br />Congrats!",
                "prize_35": "You won a +15% Coin Item!<br />Increases coin gains from all sources by 15%.<br /><br />Lasts 7 days.<br />If you already owned this item, the time has been extended by 7 days.<br /><br />Congrats!",
                "prize_36": "You won a +25% Coin Item!<br />Increases coin gains from all sources by 25%.<br /><br />Lasts 7 days.<br />If you already owned this item, the time has been extended by 7 days.<br /><br />Congrats!",
                "prize_37": 'You won a Mystery Coin Box!<br /><br />Open it on the <a href="/account/stuff/" target="_blank">My Stuff</a> page.<br /><br />You have 2 weeks to open it or it will disappear.<br /><br />Congrats!',
                "prize_-1": "You didn't win anything,<br />but you have received {data1} coins!<br /><br />Congrats!<br /><br /><br />{data1} coins have also been added to the Jackpot!",
                "prize_-2": "JACKPOT!!<br /><br />You won {data1} coins!<br /><br />You have also received the Jackpot badge!<br /><br />Congrats!"
            };

            var html = "Spin failed!<br />It may not be time to spin yet.";
            if (prizeTexts[d.resp])
                html = prizeTexts[d.resp].replace(/\{data1\}/g, d.data1);

            $("#happyboxprizetext").html(html);
            $("#happyboxprizetextcontainer").fadeIn("fast");

            if (d.resp === "prize_-1") {
                console.log(d);
                
                var coinCount = Math.ceil(d.data1 / 10);
                console.log(coinCount);
                
                var prizeTextOffset = $("#happyboxprizetext").offset();
                var prizeTextWidth = $("#happyboxprizetext").innerWidth();
                var prizeTextHeight = $("#happyboxprizetext").innerHeight();
                
                var numDone = 0;
                
                for (var i = 1; i <= coinCount; i++) {
                    (function() {
                        var floatingCoin = $('<div class="floatingcoin"></div>');
                        
                        floatingCoin.appendTo('body').css({
                            left: prizeTextOffset.left + (prizeTextWidth / 2),
                            top: prizeTextOffset.top + (prizeTextHeight / 2)
                        }).bezierMove([
                            [Math.random() * 1080, Math.random() * 700 + 200],
                            [Math.random() * 1080, Math.random() * 500],
                            [$(".coinbalancedisplay").offset().left, $(".coinbalancedisplay").offset().top]
                        ], Math.random() * 1000 + 1000, function() {
                            floatingCoin.fadeOut(200, function() {
                                floatingCoin.remove();
                                
                                numDone++;
                            
                                if (coinCount === numDone) {
                                    $(".coinbalancedisplay").html(d.newbalance);
                                }
                            });
                        });
                    })();
                }
                
                $("#jackpotamount").html(d.newjackpot);
            } else if (d.resp === "prize_-2") {
                jackpotAnimation();
            }

            // Update cooldown and re-enable countdown.
            $("#happyboxnextspintime").data("seconds", d.sec);
            if (!cdFuncInterval) {
                cdFuncInterval = setInterval(cdFunc, 1000);
                cdFunc();
            }
        });
    };

    spinFunc = function () {
        var anim = $(".happyboxballspinanimate");
        if (anim.length > 0) {
            var numFrames = 8;
            var frame = anim.data("frame") || 1;
            var nextFrame = (frame === numFrames) ? 1 : frame + 1;

            anim = anim.removeClass().addClass("happyboxballspinanimate happyboxballspinframe" + nextFrame);
            anim.data("frame", nextFrame);
        }
    };

    $("#happybox").on("click", ".happyboxgoavailable", function () {
        // Fade out prize text.
        $("#happyboxprizetextcontainer").fadeOut("fast");

        // Make unavailable.
        $("#happyboxgo").removeClass().addClass("happyboxgounavailable");

        // Enable animation.
        $("#happyboxballspin").removeClass().addClass("happyboxballspinanimate");
        spinFuncInterval = setInterval(spinFunc, 100);
        spinFunc();

        // Play sound.
        $("#happyboxaudio")[0].play();

        // Request spin after sound is over.
        setTimeout(requestSpin, 3000);
    });

    $("#happyboxprizetextclose").click(function () {
        $("#happyboxprizetextcontainer").fadeOut("fast");
    });

    // Firework animation function.
    var func = function () {
        $(".firework").each(function () {
            var frame = $(this).data("frame");

            if (frame >= 7) {
                var that = this;
                $(this).fadeOut(100, "swing", function () {
                    $(that).remove();
                });
                return;
            }

            frame++;
            $(this).css("background-position", "-" + (frame * 256) + "px 0px");
            $(this).data("frame", frame);
        });
    };
    setInterval(func, 100);
    func();

    // Cooldown update function.
    cdFunc = function () {
        var rem = $("#happyboxnextspintime").data("seconds");

        if (rem <= 1) {
            $("#happyboxnextspintime").html("Spin Ready!");
            $("#happyboxgo").removeClass().addClass("happyboxgoavailable");
            if (cdFuncInterval) {
                clearInterval(cdFuncInterval);
                cdFuncInterval = null;
            }
            return;
        } else {
            var seconds = rem;

            var hours = Math.floor(seconds / 3600);
            seconds -= hours * 3600;
            if (hours < 10)
                hours = "0" + hours;

            var minutes = Math.floor(seconds / 60);
            seconds -= minutes * 60;
            if (minutes < 10)
                minutes = "0" + minutes;

            if (seconds < 10)
                seconds = "0" + seconds;

            // Update big numbers.
            var chars = hours + "" + minutes;
            for (var i = 0; i <= 3; i++)
                $("#happyboxnumberslot" + (i + 1)).removeClass().addClass("happyboxnumber" + chars.charAt(i));

            $("#happyboxnextspintime").html("Next spin in " + hours + ":" + minutes + ":" + seconds);
        }

        var newRem = (rem === 1) ? 1 : rem - 1;
        $("#happyboxnextspintime").data("seconds", newRem);
    };
    cdFuncInterval = setInterval(cdFunc, 1000);
    cdFunc();

    // Flash animation.
    setInterval(function () {
        $("#happybox").toggleClass("baseflash");
    }, 75);
})(jQuery);
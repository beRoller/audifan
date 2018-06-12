var HappyBoxView = b.View.extend({
    prizeTexts: {
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
        "prize_-2": "JACKPOT!!<br /><br />You won {data1} coins!<br /><br />Congrats!"
    },
    spinFuncInterval: false,
    cdFuncInterval: false,

    prizeTextContainer: null,
    prizeText: null,
    goButton: null,
    ballSpin: null,
    nextSpinTime: null,
    audio: null,

    initialize: function () {
        this.prizeTextContainer = this.$el.find("#happyboxprizetextcontainer");
        this.prizeText = this.$el.find("#happyboxprizetext");
        this.goButton = this.$el.find("#happyboxgo");
        this.ballSpin = this.$el.find("#happyboxballspin");
        this.nextSpinTime = this.$el.find("#happyboxnextspintime");
        this.audio = this.$el.find("#happyboxaudio")[0];

        // Start fireworks animations.

        // Flash animation.
        setInterval(this.flashAnimation.bind(this), 75);
    },

    flashAnimation: function() {
        this.$el.toggleClass("baseflash");
    },

    spinFunc: function () {
        var anim = $(".happyboxballspinanimate");
        if (anim.length > 0) {
            var numFrames = 8;
            var frame = anim.data("frame") || 1;
            var nextFrame = (frame === numFrames) ? 1 : frame + 1;

            anim = anim.removeClass().addClass("happyboxballspinanimate happyboxballspinframe" + nextFrame);
            anim.data("frame", nextFrame);
        }
    },

    cdFunc: function () {

    },

    requestSpin: function () {
        var self = this;

        simpleAjax("GET", {spin: "spin"}, function (data) {
            // Stop animation.
            if (self.spinFuncInterval) {
                clearInterval(self.spinFuncInterval);
                self.ballSpin.removeClass().addClass("happyboxballspinstatic");
                self.spinFuncInterval = false;
            }

            // Handle prize display.
            var html = "Spin failed!<br />It may not be time to spin yet.";
            if (self.prizeTexts[data.resp]) {
                html = self.prizeTexts[data.resp].replace(/\{data1\}/g, data.data1);
            }

            self.prizeText.html(html);
            self.prizeTextContainer.fadeIn("fast");

            if (data.resp === "prize_-1") {
                // do coin animation and set coin balance display.
            } else if (data.resp === "prize_-2") {
                // jackpot!
            }

            // Update cooldown and re-enable countdown.
            var countdownView = self.nextSpinTime.data("countdown-view");
            countdownView.setRemaining(data.sec);
            countdownView.startInterval();
        });
    },

    events: {
        "click .happyboxgoavailable": function () {
            var self = this;

            // Fade out prize text.
            this.prizeTextContainer.fadeOut("fast");

            // Make unavailable.
            this.goButton.removeClass().addClass("happyboxgounavailable");

            // Enable animation.
            this.ballSpin.removeClass().addClass("happyboxballspinanimate");
            this.spinFuncInterval = setInterval(function () {
                self.spinFunc();
            }, 100);
            self.spinFunc();

            // Play sound.
            this.audio.play();

            // Request spin after sound is over.
            setTimeout(function () {
                self.requestSpin();
            }, 3 * 1000);
        }
    }
});

if (isLoggedIn()) {
    new HappyBoxView({
        el: $("#happybox")
    });
}
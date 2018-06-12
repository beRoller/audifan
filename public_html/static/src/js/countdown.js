var CountdownView = b.View.extend({
    remaining: 0,
    completeString: false,
    completeFunc: false,
    interval: false,
    initialize: function (obj) {
        var self = this;

        if (obj.seconds) {
            this.remaining = obj.seconds;
        }

        if (obj.completeString) {
            this.completeString = obj.completeString;
        }

        if (obj.completeFunction && window[ obj.completeFunction ]) {
            this.completeFunc = window[ obj.completeFunction ];
        }

        this.startInterval();
        this.tick();
    },
    startInterval: function() {
        var self = this;

        this.interval = setInterval(function () {
            self.tick();
        }, 1000);
    },
    setRemaining: function(newRemaining) {
        this.remaining = newRemaining;
    },
    render: function () {
        var html = "";

        if (this.remaining <= 0) {
            if (this.completeFunc) {
                this.completeFunc();
            } else if (this.completeString) {
                html = this.completeString;
            } else {
                html = "0:00:00";
            }
        } else {
            var seconds = this.remaining;

            var hours = Math.floor(seconds / 3600);
            seconds -= hours * 3600;

            var minutes = Math.floor(seconds / 60);
            seconds -= minutes * 60;

            html += (hours < 10) ? "0" + hours : hours;
            html += ":";
            html += (minutes < 10) ? "0" + minutes : minutes;
            html += ":";
            html += (seconds < 10) ? "0" + seconds : seconds;
        }

        $(this.el).html(html);
    },
    tick: function () {
        this.remaining--;

        if (this.remaining <= 0 && this.interval) {
            clearInterval(this.interval);
            this.interval = false;
        }

        this.render();
    }
});

$("[data-countdown]").each(function (e) {
    var el = $(this);

    var view = new CountdownView({
        el: this,
        seconds: el.data("countdown"),
        completeString: el.data("countdown-complete-string"),
        completeFunction: el.data("countdown-complete-function")
    });

    el.data("countdown-view", view);
});
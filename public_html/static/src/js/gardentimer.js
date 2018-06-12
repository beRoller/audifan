/**
 * Individual timer.
 */
var GardenTimerView = b.View.extend({
    group: false,

    type: false,

    timeRemaining: 0,

    // elements
    startContainer: false,
    secretContainer: false,
    stopContainer: false,
    timeDisplay: false,

    initialize: function(obj) {
        console.log('initialize timer');

        var view = this;

        this.group = obj.group;
        this.type = this.$el.data("type");

        this.startContainer = this.$el.find(".timerstart");
        this.secretContainer = this.$el.find(".timersecretstart");
        this.stopContainer = this.$el.find(".timerstop");
        this.timeDisplay = this.$el.find(".timerdisplay");
    },

    getFormattedTimeRemaining: function() {
        var hoursRemainder = this.timeRemaining % 3600;

        var h = Math.floor(this.timeRemaining / 3600);
        var m = Math.floor(hoursRemainder / 60);
        var s = hoursRemainder % 60;

        return h + ":" + (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
    },

    showSecretPrompt: function() {
        this.hideAllControls();
        this.secretContainer.show();
    },

    start: function() {
        this.hideAllControls();
        this.group.mainView.send(this.type, {
            timer: this.group.id
        });
    },

    stop: function() {
        this.hideAllControls();
        this.group.mainView.send(this.type, {
            timer: this.group.id,
            stop: true
        });
    },

    hideAllControls: function() {
        this.startContainer.hide();
        this.secretContainer.hide();
        this.stopContainer.hide();
    },

    updateControls: function() {
        this.hideAllControls();

        if (this.timeRemaining > 0) {
            this.stopContainer.show();
        } else {
            this.startContainer.show();
        }
    },

    render: function() {
        this.timeDisplay.html(this.getFormattedTimeRemaining());
    },

    events: {
        "click .timerstart": function() {
            if ("water" == this.type || "dust" == this.type) {
                this.showSecretPrompt();
            } else {
                this.start();
            }
        },
        "click .timerstop": function() {
            this.stop();
        },
        "click .secretcancelbutton": function() {
            this.updateControls();
        }
    }
});



/**
 * A set of timers.
 */
var GardenTimerGroupView = b.View.extend({
    mainView: false,
    id: false,
    timersByType: false,

    initialize: function(obj) {
        console.log('initialize timer group');

        var view = this;

        this.mainView = obj.mainView;
        this.id = this.$el.data("id");

        this.timersByType = {};

        this.$el.find(".timerlabel").each(function() {
            var newTimer = new GardenTimerView({
                el: $(this),
                group: view
            });

            view.timersByType[ newTimer.type ] = newTimer;
        });
    },

    render: function() {
        for (var i in this.timersByType) {
            this.timersByType[i].render();
        }
    },

    events: {
        '.timername a': function(e) {
            this.$el.addClass("edit-name");

            e.preventDefault();
            return false;
        }
    }
});




/**
 * Main container for all garden timers.
 */
var GardenTimerMainView = b.View.extend({
    groupsById: {},

    startTime: false,
    totalSeconds: 0,

    message: false,

    initialize: function(obj) {
        console.log('initialize main view');

        var view = this;

        this.message = this.$el.find("#timermessage");

        this.$el.find("#timers .timercontainer").each(function() {
            var newGroup = new GardenTimerGroupView({
                el: $(this),
                mainView: view
            });

            view.groupsById[ newGroup.id ] = newGroup;
        });

        this.send("get", {}, (function(data) {
            this.response(data);

            this.startTime = new Date();

            window.requestAnimationFrame(this.render.bind(this));

            view.setMessage("");
        }).bind(this));
    },

    /**
     * Send an action to the server.
     */
    send: function(action, additionalData, respFunction) {
        var view = this;

        var data = additionalData || {};
        data["action"] = action;

        $.ajax({
            data: data,
            dataType: "json",
            success: respFunction || this.response.bind(this),
            error: function(x, s, e) {
                // Change message.
                view.setMessage('Something went wrong while trying to connect to the server.  Please try <a href="/community/gardentimer/">reloading</a>.');
            }
        });
    },

    updateTimerData: function(data) {

    },

    response: function(data) {
        console.log("response: ", data);

        if (data.timers) {
            for (var i in data.timers) {
                var curr = data.timers[i];

                var group = this.groupsById[ curr.id ];

                if (group) {
                    for (var t in group.timersByType) {
                        group.timersByType[t].timeRemaining = curr[t] ? curr[t] : 0;
                        group.timersByType[t].updateControls();
                    }
                }
            }
        }
    },

    setMessage: function(message) {
        this.message.html(message);

        if (message) {
            this.message.slideDown("fast");
        } else {
            this.message.slideUp("fast");
        }
    },

    /**
     * Call a function for each timer.
     * @param  function func   The value of 'this' is the GardenTimerView object.
     */
    eachTimer: function(func) {
        for (var i in this.groupsById) {
            for (var t in this.groupsById[i].timersByType) {
                func.call(this, this.groupsById[i].timersByType[t]);
            }
        }
    },

    render: function() {
        var newTotalSeconds = Math.floor((new Date().getTime() - this.startTime.getTime()) / 1000);

        if (newTotalSeconds != this.totalSeconds) {
            var diff = newTotalSeconds - this.totalSeconds;

            this.eachTimer(function(timer) {
                if (timer.timeRemaining > 0) {
                    if (timer.timeRemaining - diff <= 0) {
                        timer.timeRemaining = 0;
                        // Notify.
                    } else {
                        timer.timeRemaining -= diff;
                    }
                }
            });

            this.totalSeconds = newTotalSeconds;

            for (var i in this.groupsById) {
                this.groupsById[i].render();
            }
        }

        window.requestAnimationFrame(this.render.bind(this));
    },

    events: {

    }
});




if (1 === $(".garden-timer-container").length) {
    new GardenTimerMainView({
        el: $(".garden-timer-container")
    });
}
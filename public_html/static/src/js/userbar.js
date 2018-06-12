var UserBarView = b.View.extend({
    userBarContent: false,
    fb: false,
    initialize: function () {
        this.userBarContent = this.$el.find(".userbarcontent");
        
        var view = this;

        // Load the Facebook SDK.
        window.fbAsyncInit = function () {
            FB.init({
                appId: (window.location.hostname === "localhost") ? "545000688871283" : "366262623446900",
                channelUrl: "//" + window.location.hostname + "/static/channel.html",
                status: true,
                cookie: true,
                xfbml: true
            });

            view.fb = true;
        };

        (function (d) {
            var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement('script');
            js.id = id;
            js.async = true;
            js.src = "//connect.facebook.net/en_US/all.js";
            ref.parentNode.insertBefore(js, ref);
        })(document);
    },
    toggleMobileLogin: function() {
        this.userBarContent.toggleClass("mobile-login-visible");
    },
    events: {
        "click #mobile-login-toggle": function(e) {
            this.toggleMobileLogin();
            e.preventDefault();
            return false;
        },
        "click .login-cancel-link": function(e) {
            this.toggleMobileLogin();
            e.preventDefault();
            return false;
        },
        "click #facebooklogin": function (e) {
            if (this.fb) {
                FB.login(function (resp) {
                    if (resp.authResponse) {
                        var loc = "/account/fbconnect/?accessToken=" + resp.authResponse.accessToken;
                        if (window.location.pathname)
                            loc += "&thru=" + window.location.pathname;
                        window.location = loc;
                    }
                }, {});
            }

            e.preventDefault();
            return false;
        },
        "click #mobile-menu-toggle": function (e) {
            var links = this.$el.find(".mobile-links");
            var glyph = this.$el.find("#mobile-menu-toggle .glyphicon");
            var newDir = links.is(":visible") ? "down" : "up";

            glyph.removeClass().addClass("glyphicon glyphicon-chevron-" + newDir);
            links.slideToggle("fast");

            e.preventDefault();
            return false;
        }
    }
});

new UserBarView({
    el: document.getElementById("userbar")
});
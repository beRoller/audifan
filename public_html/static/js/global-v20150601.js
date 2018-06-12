(function(w) {
    
    
    w._ = new function() {
        var self = this;
        
        this.init = function() {
            // Initialize menu
            $("#menuitemwrapper").bind({
                "mouseenter": function() {
                    $("#submenuwrapper").show();
                },
                "mouseleave": function() {
                    $("#submenuwrapper").hide();
                }
            });
            
            $("#menutoggle").bind("click", function() {
                $("#submenuwrapper").toggle();
            });
            
            // Happy Box menu link flash effect
            setInterval(function() {
                var color = window.happyBoxYellow ? "#FFF254" : "#EA0F5A";
                window.happyBoxYellow = !window.happyBoxYellow;
                
                if ($("#happyboxlink").hasClass("hbflash1"))
                    $("#happyboxlink").removeClass("hbflash1").addClass("hbflash2");
                else
                    $("#happyboxlink").removeClass("hbflash2").addClass("hbflash1");
            }, 100);
            
            // More info status bar
            $("#userbarmoreinfoopenlink").click(function() {
                $("#userbarstatusbar").css("opacity", 0);
                $("#userbarmoreinfowrapper").slideDown("fast");
            });
            
            $("#userbarmoreinfocloselink").click(function() {
                $("#userbarmoreinfowrapper").slideUp("fast", "swing", function() {
                    $("#userbarstatusbar").animate({
                        "opacity": 1
                    }, "fast");
                });
            });
            
            // Initialize Jump To Top
            $(document).scroll(function(e) {
                var scrollTop = $(document).scrollTop();
                
                // Fade in and out between 500 and 1000 px.
                $("#topjumpwrapper a").css("opacity", ((1/500)*scrollTop)-1);
            });
            $("#topjumpwrapper a").click(function(e) {
                $("body, html").animate({
                    "scrollTop": 0
                }, "fast");
            });
            
            // Scroller
            var scrollFunc;
            scrollFunc = function() {
                var SPEED = 40; // pixels per second
                var containerWidth = $("#scrollerwrapper").width();
                var textWidth = $("#scrollermessage").innerWidth();
                
                if (textWidth > containerWidth) {
                    // Scroll.
                    $("#scrollermessage").css({
                        "left": containerWidth + "px"
                    });
                    $("#scrollermessage").animate({
                        "left": -textWidth + "px"
                    }, (containerWidth/SPEED)*1000, "linear", scrollFunc);
                } else {
                    // Manually center.
                    $("#scrollermessage").css({
                        "left": ((containerWidth/2) - (textWidth/2)) + "px"
                    });
                }
                
                $("#scrollerwrapper").css("visibility", "visible");
            };
            scrollFunc();
            
            // Notifications
            $("#notificationclose").click(function() {
                $("#notificationlistwrapper").slideUp("fast");
            });
            $("#notificationbutton").click(function() {
                $("#notificationlistwrapper").slideToggle("fast");
            });
            
            // Initialize countdowns.
            $("[data-countdown]").each(function(i, e) {
                var interval;
                var func = function() {
                    var secLeft = $(e).data("countdown");
                    
                    if (secLeft <= 1) {
                        $(e).html($(e).data("countdowndone"));
                        clearInterval(interval);
                        return;
                    }
                    
                    var subSec = secLeft;
                    
                    var h = Math.floor(secLeft / 3600);
                    secLeft -= (h * 3600);
                    var m = Math.floor(secLeft / 60);
                    secLeft -= (m * 60);
                    
                    var s = h + ":";
                    s += (m < 10) ? "0" + m : m;
                    s += ":";
                    s += (secLeft < 10) ? "0" + secLeft : secLeft;
                    
                    $(e).html(s);
                    $(e).data("countdown", subSec - 1);
                };
                interval = setInterval(func, 1000);
                func();
            });
            
            // User card animations.
            $(".usercardexpand").click(function(e) {
                var container = $(this).parent(".usercardcontainer");
                var moreInfo = container.find(".usercardmoreinfo");
                var expand = container.find(".usercardexpand > i");
                
                if (moreInfo.is(":visible")) {
                    moreInfo.slideUp("fast");
                    container.removeClass("active");
                    expand.removeClass().addClass("fa fa-sort-down");
                } else {
                    moreInfo.slideDown("fast");
                    container.addClass("active");
                    expand.removeClass().addClass("fa fa-sort-up");
                }
            });
            
            // Load the Facebook SDK.
            window.fbAsyncInit = function() {
                FB.init({
                    appId:      (window.location.hostname === "localhost") ? "545000688871283" : "366262623446900",
                    channelUrl: "//" + window.location.hostname + "/static/channel.html",
                    status:     true,
                    cookie:     true,
                    xfbml:      true
                });
            };
            (function(d) {
                var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement('script'); js.id = id; js.async = true;
                js.src = "//connect.facebook.net/en_US/all.js";
                ref.parentNode.insertBefore(js, ref);
            })(document);
        };
        
        this.ajax = function(data, beforeCB, afterCB) {
            $.ajax({
                "beforeSend": beforeCB,
                "cache": false,
                "data": data,
                "dataType": "json",
                "error": function(x, s, e) {
                    console.log("Error: " + s + " - " + e);
                },
                "success": afterCB
            });
        };
        
        this.alert = function(text, okCB) {
            $("#alerttext").html(text);
            
            var func;
            func = function() {
                if (okCB)
                    okCB();
                
                $("#alertwrapper").hide();
                $("#alertokbtn").unbind("click", func);
            };
            $("#alertokbtn").click(func);
            
            $("#alertwrapper").fadeIn("fast");
        };
        
        this.round = function(number, places) {
            var multiple = Math.pow(10, places);
            return Math.round(number * multiple) / multiple;
        };
        
        this.logInWithFacebook = function() {
            FB.login(function(resp) {
                if (resp.authResponse) {
                    var loc = "/account/fbconnect/?accessToken=" + resp.authResponse.accessToken;
                    if (window.location.pathname)
                        loc += "&thru=" + window.location.pathname;
                    window.location = loc;
                }
            }, {});
        };
    };
    
    $(_.init);
})(window);
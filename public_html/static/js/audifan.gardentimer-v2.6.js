// Requires jQuery

(function(w) {
    w.GardenTimer = new function() {
        this.NOTIFTYPE = "alert";
        this.SOUND = true;
        this.SOUNDVOLUME = 1;
        this.RESYNCHINTERVAL = 5 * 60 * 1000;
        
        var timerData       = [], // elems {id, name, water, dust, fertilize, rosemary, spearmint, peppermint}
            tickElems       = ["water", "dust", "fertilize", "rosemary", "spearmint", "peppermint"],
            currentTime     = "",
            documentTitle   = "",
            inited          = false,
            startTime       = null,
            totalSeconds    = 0;
        
        var extractTimerId = function(element) {
            var fullid = $(element).attr("id");
            return fullid.substr(fullid.indexOf("_") + 1);
        };
        
        this.init = function(notif, sound, soundVolume) {
            if (inited)
                return;
            
            GardenTimer.NOTIFTYPE = notif;
            GardenTimer.SOUND = sound;
            GardenTimer.SOUNDVOLUME = soundVolume;
            
            documentTitle = document.title;
            
            this.send("get", {}, function(d) {
                GardenTimer.response(d);
                
                for (var i = 0; i < timerData.length; i++) {
                    var curr = timerData[i];
                    for (var eIndex in tickElems) {
                        var e = tickElems[eIndex];
                        if (curr[e] == 0)
                            $("#timerstart" + e + "_" + curr["id"]).show();
                        else
                            $("#timerstop" + e + "_" + curr["id"]).show();
                    }
                }
                
                startTime = new Date();
                
                setInterval(GardenTimer.tick, 1000);
                GardenTimer.tick();
                
                setInterval(function() {
                    GardenTimer.changeMessage('Attempting to resynch... <img src="/static/img/timerload.gif" />');
                    GardenTimer.send("get", {}, function(d) {
                        GardenTimer.response(d);
                        GardenTimer.changeMessage();
                    });
                }, GardenTimer.RESYNCHINTERVAL);
                
                var closeMessage = true;
                
                if (GardenTimer.NOTIFTYPE === "desktop") {
                    if (window.Notification) {
                        if (Notification.permission !== "granted") {
                            closeMessage = false;
                            GardenTimer.changeMessage('Warning: You have Desktop Notifications enabled,<br />but they are currently disallowed.<br /><a href="javascript:;" onclick="checkNotificationAvailability();">Click here to check settings</a>');
                        }
                    }
                    else {
                        closeMessage = false;
                        GardenTimer.changeMessage("Warning: You have Desktop Notifications on,<br />but your current browser doesn't support them!<br />Change your notification options below.");
                    }
                }
                
                if (closeMessage)
                    GardenTimer.changeMessage();
            });
            
            // Water
            $(".waterstartbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartwater_" + id).show();
                $("#timerstartwater_" + id).hide();
            });
            $(".watersecretstartbuttonyes").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartwater_" + id).hide();
                GardenTimer.send("water", {secret:true, timer:id});
            });
            $(".watersecretstartbuttonno").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartwater_" + id).hide();
                GardenTimer.send("water", {timer:id});
            });
            $(".watersecretstartbuttoncancel").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartwater_" + id).hide();
                $("#timerstartwater_" + id).show();
            });
            $(".waterstopbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstopwater_" + id).hide();
                GardenTimer.send("water", {stop:true, timer:id}, function(d) {
                    GardenTimer.response(d);
                    $("#timerstartwater_" + id).show();
                });
            });
            
            // Dust
            $(".duststartbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartdust_" + id).show();
                $("#timerstartdust_" + id).hide();
            });
            $(".dustsecretstartbuttonyes").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartdust_" + id).hide();
                GardenTimer.send("dust", {secret:true, timer:id});
            });
            $(".dustsecretstartbuttonno").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartdust_" + id).hide();
                GardenTimer.send("dust", {timer:id});
            });
            $(".dustsecretstartbuttoncancel").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timersecretstartdust_" + id).hide();
                $("#timerstartdust_" + id).show();
            });
            $(".duststopbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstopdust_" + id).hide();
                GardenTimer.send("dust", {stop:true, timer:id}, function(d) {
                    GardenTimer.response(d);
                    $("#timerstartdust_" + id).show();
                });
            });
            
            // Fertilize
            $(".fertilizestartbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstartfertilize_" + id).hide();
                GardenTimer.send("fertilize", {timer:id});
            });
            $(".fertilizestopbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstopfertilize_" + id).hide();
                GardenTimer.send("fertilize", {stop:true, timer:id}, function(d) {
                    GardenTimer.response(d);
                    $("#timerstartfertilize_" + id).show();
                });
            });
            
            // Rosemary
            $(".rosemarystartbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstartrosemary_" + id).hide();
                GardenTimer.send("rosemary", {timer:id});
            });
            $(".rosemarystopbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstoprosemary_" + id).hide();
                GardenTimer.send("rosemary", {stop:true, timer:id}, function(d) {
                    GardenTimer.response(d);
                    $("#timerstartrosemary_" + id).show();
                });
            });
            
            // Spearmint
            $(".spearmintstartbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstartspearmint_" + id).hide();
                GardenTimer.send("spearmint", {timer:id});
            });
            $(".spearmintstopbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstopspearmint_" + id).hide();
                GardenTimer.send("spearmint", {stop:true, timer:id}, function(d) {
                    GardenTimer.response(d);
                    $("#timerstartspearmint_" + id).show();
                });
            });
            
            // Peppermint
            $(".peppermintstartbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstartpeppermint_" + id).hide();
                GardenTimer.send("peppermint", {timer:id});
            });
            $(".peppermintstopbutton").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timerstoppeppermint_" + id).hide();
                GardenTimer.send("peppermint", {stop:true, timer:id}, function(d) {
                    GardenTimer.response(d);
                    $("#timerstartpeppermint_" + id).show();
                });
            });
            
            // Name Edit
            $(".timernameeditpencil").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timernameedit_" + id).show();
                $("#timername_" + id).hide();
            });
            $(".timernameeditcancel").bind("click", function(e) {
                var id = extractTimerId(this);
                $("#timernameedit_" + id).hide();
                $("#timername_" + id).show();
                
                var val = $("#timernamedisplay_" + id).html();
                $("#timernameeditinput_" + id).val( (val === "(Unnamed)" ? "" : val) );
            });
            
            swfobject.embedSWF('/static/swf/gardentimersound-v2.5.swf', 'gardentimersoundcontainer', '5', '5', '8', null, null, {
                wmode: 'opaque',
                allowScriptAccess: 'sameDomain',
                bgcolor: '#000000',
                menu: 'false',
                flashvars: ''
            }, {id:'gardentimersound'});
            
            inited = true;
        }
        
        this.getIndexFromId = function(id) {
            for (var i = 0; i < timerData; i++)
                if (timerData[i]["id"] == id)
                    return i;
            return -1;
        }
        
        this.parseSeconds = function(total) {
            var total = total || 0;
            
            var hours = Math.floor(total / 3600);
            total -= (hours * 3600);
            var minutes = Math.floor(total / 60);
            total -= (minutes * 60);
            var seconds = total;
            
            return hours + ":" + ((minutes < 10) ? "0" + minutes : minutes) + ":" + ((seconds < 10) ? "0" + seconds : seconds);
        }
        
        this.notify = function(image, sound, title, str) {
            if (GardenTimer.SOUND) {
                var sound = document.getElementById("gardentimersound-" + sound);
                sound.volume = GardenTimer.SOUNDVOLUME;
                sound.play();
            }
            
            if (GardenTimer.NOTIFTYPE === "alert") {
                var before = new Date();
                alert(title + "\n" + str);
                var after = new Date();
                GardenTimer.increaseCurrentTime(Math.ceil((after.getTime() - before.getTime()) / 1000), false);
            }
            else if (GardenTimer.NOTIFTYPE === "desktop") {
                try {
                    var dn = new Notification(title, {
                        "body": str,
                        "icon": image
                    });
                    dn.onclick = function() {
                        window.focus();
                    };
                    dn.show();
                }
                catch (e) {}
            }
        }
        
        this.changeMessage = function(str) {
            var ele = $("#timermessage");
            if (!str) {
                ele.slideUp("fast", "swing", function() {
                    $("#timermessage").html("");
                });
            }
            else {
                ele.html(str);
                ele.slideDown("fast");
            }
        }
        
        this.send = function(action, additionalData, respFunction) {
            $.ajax({
                data: $.extend({
                    action: action
                }, additionalData || {}),
                dataType: "json",
                success: respFunction || GardenTimer.response,
                error: function(x, s, e) {
                    GardenTimer.changeMessage('Something went wrong while trying to connect the server. Please try <a href="javascript:;" onclick="window.location.reload();">reloading</a>.');
                }
            });
        }
        
        this.response = function(d) {
            if (d.currentTime)
                currentTime = d.currentTime;
            if (d.timers)
                timerData = d.timers;
        }
        
        this.increaseCurrentTime = function(seconds, fromTick) {
            var seconds = seconds || 1;
            var fromTick = fromTick || true;
            
            // Current time isn't ready yet.
            if (currentTime.indexOf(":") === -1)
                return;
            
            var timeArray = currentTime.split(":");
            for (var i in timeArray)
                timeArray[i] = parseInt(timeArray[i]);
            timeArray[2] += seconds;
            totalSeconds += seconds;
            if (timeArray[2] >= 60) {
                var over = timeArray[2] - 60;
                timeArray[1] += (1 + Math.floor(over / 60));
                timeArray[2] = (over % 60);
            }
            if (timeArray[1] >= 60) {
                var over = timeArray[1] - 60;
                timeArray[0] += (1 + Math.floor(over / 60));
                timeArray[1] = (over % 60);
            }
            if (timeArray[0] >= 13)
                timeArray[0] %= 12;
            
            currentTime = timeArray[0] + ":" + ((timeArray[1] < 10) ? "0" + timeArray[1] : timeArray[1]) + ":" + ((timeArray[2] < 10) ? "0" + timeArray[2] : timeArray[2]);
            var ele = $("#currenttime");
            if (timeArray[1] % 10 <= 4)
                ele.css("color", "#AAFFAA");
            else
                ele.css("color", "#FFAAAA");

            if (fromTick) {
                var soonest = {
                    time: -1,
                    name: "",
                    which: ""
                };
                
                for (var i = 0; i < timerData.length; i++) {
                    var curr = timerData[i];

                    for (var eIndex in tickElems) {
                        var e = tickElems[eIndex];
                        if (curr[e] > 0) {
                            timerData[i][e] -= seconds;
                            if (soonest.time === -1 || timerData[i][e] < soonest.time) {
                                soonest = {
                                    time: timerData[i][e],
                                    name: curr["name"],
                                    which: e
                                };
                            }
                            
                            if (timerData[i][e] < 0)
                                timerData[i][e] = 0;
                            if (timerData[i][e] == 0) {
                                var title = "";
                                if (curr["name"] !== "")
                                    title += curr["name"] + " Timer: ";
                                title += e.toUpperCase() + " READY";
                                
                                var sound = null;
                                
                                var str = "";
                                if (e === "water" || e === "dust") {
                                    if (curr["name"] === "")
                                        str = "Your flowers are ready to be " + e + "ed.";
                                    else
                                        str = "Your flowers for the " + curr["name"] + " timer are ready to be " + e + "ed.";
                                    
                                    sound = e;
                                }
                                else if (e === "fertilize") {
                                    if (curr["name"] === "")
                                        str = "Your flowers are ready to be fertilized.";
                                    else
                                        str = "Your flowers for the " + curr["name"] + " timer are ready to be fertilized.";
                                    sound = e;
                                }
                                else {
                                    if (curr["name"] === "")
                                        str = "Your " + e + " is ready to be harvested.";
                                    else
                                        str = "Your " + e + " for the " + curr["name"] + " timer is ready to be harvested.";
                                    sound = "secret";
                                }

                                GardenTimer.notify("/static/img/couplegarden/" + e + ".png", sound, title, str);

                                $("#timerstart" + e + "_" + curr["id"]).show();
                                $("#timerstop" + e + "_" + curr["id"]).hide();
                            }
                            else {
                                $("#timerstop" + e + "_" + curr["id"]).show();
                                $("#timerstart" + e + "_" + curr["id"]).hide();
                            }
                        }
                    }
                }
                
                if (soonest.time != -1)
                    document.title = "[" + GardenTimer.parseSeconds(soonest.time) + " until " + soonest.which + (soonest.name != "" ? " on " + soonest.name : "") + "] " + documentTitle;
                else
                    document.title = documentTitle;
            }
        }
        
        this.tick = function() {
            GardenTimer.increaseCurrentTime(Math.round((new Date().getTime() - startTime.getTime()) / 1000) - totalSeconds);
            $("#currenttime").html(currentTime);
            
            for (var i = 0; i < timerData.length; i++) {
                var curr = timerData[i];
                
                for (var eIndex in tickElems) {
                    var e = tickElems[eIndex];
                    $("#timerdisplay" + e + "_" + curr["id"]).html(GardenTimer.parseSeconds(curr[e]));
                }
            }
        }
    };
})(window);
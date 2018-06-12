$("#canvascontainer").contextmenu(function(e) {
            e.preventDefault();
            return false;
        });
        
        var ctx = document.getElementById("webpractice").getContext("2d");
        
        ctx.font = "26px sans-serif";
        ctx.fillStyle = "#ffffff";
        ctx.fillText("Loading...", 450, 250);
        
        var ytplayer = false;
        
        var keysDown = {};
        for (var i = 0; i <= 255; i++)
            keysDown[i] = false;
        
        $(document).bind("keydown", function(e) {
            keysDown[e.keyCode] = true;
            e.preventDefault();
            return false;
        });
        
        $(document).bind("keyup", function (e) {
            keysDown[e.keyCode] = false;
            e.preventDefault();
            return false;
        });
        
        function isKeyPressed(key) {
            return keysDown[key];
        }
        
        // Object Data
        var od = {
            "rect": {x: 0, y: 0},
            "beatbar": {
                startX: 450,
                endX: 600,
                y: 250
            }
        };
        
        /**
         * Synchs and calculates accurate BPM for a YouTube player.
         */
        var YTBPM = function() {
            var startTime = 0; // The time when the video started playing.
            var lastApiTime = 0; // The last time update that was sent by the YouTube API.
            
            var measure = 0;
            var measureBeat = 0;
            
            var songParams = {
                bpm: 1, // Beats per minute.
                offset: 0, // Offset of when the song starts in the video (in seconds).
                bps: 1, // Beats per second.
                startMeasure: 0 // The measure when arrow inputs begin.
            };
            
            this.setSongParams = function(params) {
                songParams = {
                    bpm: params.bpm || 1,
                    offset: params.offset || 0,
                };
                
                songParams["bps"] = songParams.bpm / 60;
            };
            
            /**
             * Indicate that the video started.
             */
            this.start = function(time) {
                startTime = time;
            };
            
            this.update = function(ytplayer, time) {
                // Resynch with the video player's current time, if it was updated.
                var apiTime = ytplayer.getCurrentTime();
                if (apiTime !== lastApiTime) {
                    // Adjust the current time and save it as the adjusted start time.
                    startTime = time - (apiTime * 1000);
                    lastApiTime = apiTime;
                }
                
                var currentVideoTime = (time - startTime) / 1000;
                
                // Beats since the beginning.
                var beat = ((currentVideoTime + songParams.offset) * bps);
                var beatIntegerPart = Math.floor(beat);
                var beatDecimalPart = beat - beatIntegerPart;
                
                // Beat in the current measure.
                var measureBeatIntegerPart = (beatIntegerPart % 4);
                var measureBeat = measureBeatIntegerPart + beatDecimalPart;
                
                if (measureBeatIntegerPart < 0)
                    measureBeatIntegerPart = (measureBeatIntegerPart + 4) % 4;
            };
        };
        
        var currentMeasure = 1;
        var lastTime;
        var ytStartTime = 0;
        var lastYtApiTime = 0;
        var lastYtState = -5;
        var draw = function(time) {
            var delta = time - lastTime;
            lastTime = time;
            
            ctx.clearRect(0, 0, 900, 500);
            
            ctx.fillStyle = "#ffffff";
            
            var state = ytplayer.getPlayerState();
            
            if (state !== lastYtState) {
                if (state === 1) {
                    // Video is now playing.
                    ytStartTime = time;
                }
                
                lastYtState = state;
            } else {
                if (state === 1) {
                    
                    
                    // Resynch with the video player's current time,
                    // if it was updated.
                    var ytCurrentTime = (time - ytStartTime) / 1000;
                    
                    var ytApiTime = ytplayer.getCurrentTime();
                    if (ytApiTime !== lastYtApiTime) {
                        // Adjust the current time and save it as the
                        // adjusted start time.
                        ytStartTime = time - (ytApiTime * 1000);
                        
                        lastYtApiTime = ytApiTime;
                    }
                    
                    ctx.font = "26px sans-serif";
                    ctx.fillStyle = "#ffffff";
                    ctx.fillText(ytCurrentTime, 150, 150);
                    ctx.fillText(ytApiTime, 150, 200);
                    
                    var offset = -6.8;
                    var bpm = 135;
                    var bps = bpm / 60; // beats per second
                    
                    var beat = ((ytCurrentTime + offset) * bps);
                    var beatIntegerPart = Math.floor(beat);
                    var beatDecimalPart = beat - beatIntegerPart;
                    
                    var measureBeatIntegerPart = (beatIntegerPart % 4);
                    var measureBeat = measureBeatIntegerPart + beatDecimalPart;
                    
                    if (measureBeatIntegerPart < 0)
                        measureBeatIntegerPart = (measureBeatIntegerPart + 4) % 4;
                    
                    ctx.font = "26px sans-serif";
                    ctx.fillStyle = "#ffffff";
                    ctx.fillText(measureBeatIntegerPart, 100, 100);
                    
                    ctx.font = "26px sans-serif";
                    ctx.fillStyle = "#ffffff";
                    ctx.fillText(beat, 50, 50);
                    
                    // Adjust the measure beat so that the 1st beat is at the 3rd position on the beat bar.
                    var adjustedMeasureBeat = ((measureBeatIntegerPart + 3) % 4) + beatDecimalPart;
                    ctx.fillRect(od.beatbar.startX + (adjustedMeasureBeat * .25 * (od.beatbar.endX - od.beatbar.startX)), od.beatbar.y, 25, 25);
                }
            }
            
            window.requestAnimationFrame(draw);
        };
        
        
        
        // Load the YT iframe player.
        var tag = document.createElement("script");
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName("script")[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        
        function onYouTubeIframeAPIReady() {
            ytplayer = new YT.Player("ytplayer", {
                height: "390",
                width: "640",
                videoId: "jtdLporD2HM",
                events: {
                    "onReady": function(e) {
                        // Get the first requested time, then start the render loop.
                        window.requestAnimationFrame(function(time) {
                            lastTime = time;
                            window.requestAnimationFrame(draw);
                        });
                    },
                    "onStateChange": function(e) {
                        
                    }
                }
            });
        }
/**
 * THIS SCRIPT MUST BE MINIFIED BEFORE FULL DEPLOYMENT.
 * Requires jQuery and Pixi.
 */

(function($, p) {
    // Disable console message.
    p.utils._saidHello = true;

    // Set up the music player.
    var musicPlayer = new function() {
        // Audio object and loading.
        var audio = false;
        var loading = false;
        var self = this;
        
        // Static song data.
        var bpm = 1; // beats per minute
        var bps = 1 / 60; // beats per second
        var spb = 1 / bps; // seconds per beat
        var offset = 0; // offset of the first beat of the first measure, in seconds
        var startMeasure = 0; // measure when arrow inputs begin ("come on!!"/"go straight!!")
        var artist = "";
        var title = "";
        
        // Dynamic song data.
        var beat = 0; // Beat overall
        var measure = 0;
        var measureBeat = 0; // Beat in the measure.
        
        this.resetSongData = function() {
            bpm = 1;
            bps = bpm / 60;
            spb = 1 / bps;
            offset = 0;
            startMeasure = 0;
            artist = "";
            title = "";
        };
        
        this.load = function(url, songData) {
            var songData = songData || {};
            
            loading = true;
            
            self.resetSongData();
            bpm = songData.bpm || 1;
            bps = bpm / 60;
            spb = 1 / bps;
            offset = songData.offset || 0;
            startMeasure = songData.startMeasure || 0;
            artist = songData.artist || "";
            title = songData.title || "";
            
            audio = new Audio();
            audio.oncanplaythrough = function() {
                console.log("Loaded song " + artist + " " + title);
                console.log(audio.duration);
                loading = false;
                self.onReady();
            };
            audio.src = url;
        };
        
        this.ready = function() {
            return (audio && !loading);
        };
        
        this.onReady = function(){};
        
        this.playing = function() {
            return (audio.currentTime > 0 && !audio.paused);
        };
        
        this.start = function() {
            // Starts the music and beat tracking.
            audio.play();
        };
        
        this.update = function() {
            // Should be called once per frame.
            // Updates the current beat data for the song.
            if (self.playing()) {
                var t = audio.currentTime;
                
                beat = (t - offset) * bps;
                measure = Math.floor(beat / 4);
                measureBeat = beat - (measure * 4);
            }
        };
        
        this.getCurrentTime = function() {
            return audio.currentTime;
        };
        
        this.getBPM = function() {
            return bpm;
        };
        
        this.getBeat = function() {
            return beat;
        };
        
        this.getMeasure = function() {
            return measure;
        };
        
        this.getMeasureBeat = function() {
            return measureBeat;
        };
        
        this.getStartMeasure = function() {
            return startMeasure;
        };
        
        this.getLastMeasureEndTime = function() {
            if (measure > 0) {
                return offset + (spb * measure * 4);
            } else {
                return offset;
            }
        };
        
        this.getCurrentMeasureEndTime = function() {
            return self.getLastMeasureEndTime() + spb;
        };
    };
    
    // Set up Pixi renderer, stage, and screen list.
    var renderer = p.autoDetectRenderer(900, 500, {
        backgroundColor: 0x000000
    });
    $("#canvascontainer").append(renderer.view).on("contextmenu", function(e) {
        e.preventDefault();
        return false;
    });
    
    var stage = new p.Container();
    
    /* Screens */
    var screens = {
        "loading": new p.Container(),
        "selection": new p.Container(),
        "play": new p.Container(),
        "results": new p.Container()
    };
    
    var currentScreen = false;
    
    function hideAllScreens() {
        for (var i in screens) {
            screens[i].visible = false;
        }
    }
    
    function switchToScreen(name) {
        if (screens[name]) {
            hideAllScreens();
            screens[name].visible = true;
            currentScreen = name;
        }
    }
    
    // Add all screens to the stage.
    for (var i in screens) {
        stage.addChild(screens[i]);
    }
    
    hideAllScreens();
    
    
    
    
    // Song List
    var songDirectory = '/static/audio/webpractice/songs/';
    var songList = [
        {
            artist: 'Deorro',
            title: 'Perdóname (feat. Adrian Delgado & DyCy)',
            file: 'perdoname.mp3',
            bpm: 128,
            offset: 2.64,
            startMeasure: 7
        },
        {
            artist: 'Tristam & Braken',
            title: 'Flight',
            file: 'flight.mp3',
            bpm: 87.5,
            offset: 1.25,
            startMeasure: 7
        }
    ];

    
    
    
    var loading = new p.Text("Loading...", {
        fill: "#ffffff"
    });
    loading.x = 50;
    loading.y = 50;
    loading.interactive = true;
    loading.on('click', function(e) {
        switchToScreen("selection");
    });
    screens["loading"].addChild(loading);
    
    
    
    // Selection screen.
    var start = new p.Text("START", {
        fill: '#ffffff'
    });
    start.x = 50;
    start.y = 50;
    start.interactive = true;
    start.on('click', function(e) {
        var selectedSong = songList[0];
        musicPlayer.onReady = function() {
            switchToScreen("play");
            musicPlayer.start();
        };
        musicPlayer.load(songDirectory + selectedSong.file, selectedSong);
    });
    screens["selection"].addChild(start);
    
    
    
    
    // Play screen.
    var beatBarBackground = new p.Sprite(p.Texture.fromImage('/static/img/webpractice/beatbarbkg.png'));
    beatBarBackground.position.x = 488;
    beatBarBackground.position.y = 231;
    screens["play"].addChild(beatBarBackground);
    
    var beatFlash = new p.Sprite(p.Texture.fromImage('/static/img/webpractice/beatflash.png'));
    beatFlash.position.x = 558;
    beatFlash.position.y = 239;
    screens["play"].addChild(beatFlash);
    
    var beatBall = new p.Sprite(p.Texture.fromImage('/static/img/webpractice/beatball.png'));
    beatBall.position.x = 490;
    beatBall.position.y = 230;
    screens["play"].addChild(beatBall);
    
    // Tracks keynote inputs.
    var keynoteTracker = new function() {
        var currentMove = "742141189";
        var completed = ""; // Notes that have been correctly entered.
        
        var keyCodeToArrow = {
            97: "1",
            98: "2",
            39: "2",
            99: "3",
            100: "4",
            37: "4",
            101: "5",
            102: "6",
            40: "6",
            103: "7",
            104: "8",
            39: "8",
            105: "9"
        };
        
        var self = this;
        
        this.setMove = function(newMove) {
            currentMove = newMove;
        };
        
        this.hasMove = function() {
            return (currentMove !== "");
        };
        
        this.reset = function() {
            currentMove = "";
            completed = "";
        };
        
        this.isComplete = function() {
            return (currentMove === completed);
        };
        
        this.restartCompleted = function() {
            completed = "";
        };
        
        this.keyPress = function(e) {
            var preventDefault = true;
            
            if (!self.isComplete()) {
                var nextNote = currentMove.charAt(completed.length);
                
                if (keyCodeToArrow[e.keyCode]) {
                    if (keyCodeToArrow[e.keyCode] === nextNote) {
                        completed += nextNote;
                        console.log(completed);
                    } else {
                        self.restartCompleted();
                        console.log(completed);
                    }
                } else {
                    preventDefault = false;
                }
            }
            
            if (preventDefault) {
                e.preventDefault();
                return false;
            }
        };
    };
    
    $(document).keydown(keynoteTracker.keyPress);
    
    
    
    
    var timingJudge = new function() {
        // exactTime is if they hit space right on the nose.
        // actualTime is the time they actually hit space.
        // This assumes that the move is complete.
        // return val: 0 is miss, 1 is bad, 2 cool, 3 great, 4 perfect, 5 insane perfect
        this.judge = function(exactTime, actualTime, bpm) {
            var spb = 60 / bpm; // seconds per beat.
            var diff = Math.abs(exactTime - actualTime);
            
            if (diff > (spb * .6)) {
                return 0;
            } else {
                return 1;
            }
        };
    };
    
    // Handle spacebar/ctrl press.
    $(document).keydown(function(e) {
        if (keynoteTracker.hasMove() && e.keyCode === 32 || e.keyCode === 17) { // there is a move on the screen and they pressed space or ctrl
            if (keynoteTracker.isComplete()) { // the move is complete.
                var exactTime = ((musicPlayer.getMeasureBeat() < 1) ? musicPlayer.getLastMeasureEndTime : musicPlayer.getCurrentMeasureEndTime)();
                
                console.log(exactTime + " / " + musicPlayer.getCurrentTime());
                console.log(timingJudge.judge(exactTime, musicPlayer.getCurrentTime(), musicPlayer.getBPM()));
            } else { // the move is incomplete.
                console.log(0);
            }
            
            e.preventDefault();
            return false;
        }
    });
    
    
    
    // Tracking vars.
    var lastMeasure = 0;
    
    function animate() {
        // Changes go here.
        switch (currentScreen) {
            case "play":
                musicPlayer.update();
                if (musicPlayer.playing()) {
                    var measureBeat = musicPlayer.getMeasureBeat();
                    var measureBeatIntegerPart = Math.floor(measureBeat);
                    var measureBeatDecimalPart = measureBeat - measureBeatIntegerPart;
                    var adjustedMeasureBeat = ((measureBeatIntegerPart + 3) % 4) + measureBeatDecimalPart;
                    //console.log(musicPlayer.getMeasure() % 4);
                    
                    beatBall.position.x = 490 + (adjustedMeasureBeat * .25 * 150);
                    
                    var a,b;
                    if (measureBeat >= 0.5 && measureBeat <= 3.5) {
                        a = .45;
                        b = 1.14;
                    } else {
                        //a = 1.6;
                        //b = 1.5;
                        a = 1;
                        b = 1.31;
                    }
                    
                    // Don't ask me why this works.
                    beatFlash.scale.x = (a * (Math.sin((measureBeat - 0.75) * 2 * Math.PI) / Math.PI)) + b;
                    beatFlash.position.x = 558 - (((128 * beatFlash.scale.x) - 128) / 2);
                }
                
                if (keynoteTracker.hasMove()) {
                    
                }
                break;
        }
        
        musicPlayer.update();
        if (musicPlayer.playing()) {
            var currMeasure = musicPlayer.getMeasure();
            //test.text = musicPlayer.getCurrentTime() + "\n" + musicPlayer.getBeat() + "\n" + currMeasure + "\n" + musicPlayer.getMeasureBeat();
            if (lastMeasure !== currMeasure) {
                if (currMeasure === musicPlayer.getStartMeasure() - 1) {
                    console.log('Alright now!');
                } else if (currMeasure === musicPlayer.getStartMeasure()) {
                    console.log('Come on!!');
                }
                lastMeasure = currMeasure;
            }
        }
        
        // Render the screen.
        
        renderer.render(stage);
        requestAnimationFrame(animate);
    }
    
    function doneLoadingAssets() {
        console.log("done loading");
        animate();
    }
    
    switchToScreen("selection");
    
    // Load assets.
    
    p.loader
            .add('/static/img/webpractice/beatbarbkg.png')
            .add('/static/img/webpractice/beatball.png')
            .add('/static/img/webpractice/beatflash.png')
            .once('complete', doneLoadingAssets);
    
    console.log(p.loader);
    
    doneLoadingAssets();
})(jQuery, PIXI);
// Requires jQuery.

(function($) {
    /**
     * Animates the top and left position of the element with a Bezier curve.
     * The element MUST be absolutely positioned.
     * 
     * @param array ctrlPts Control point matrix WITHOUT the initial position (3 rows).  The initial position is determined by this function..
     * @param Number duration How long the animation should take in milliseconds.
     * @param function finishedCallback An optional callback for when the move finishes.
     */
    $.fn.bezierMove = function(ctrlPts, duration, finishedCallback) {
        var elem = this;
        
        var x0 = elem.css("left").slice(0, -2),
            y0 = elem.css("top").slice(0, -2);
    
        if (x0 === "auto" || y0 === "auto")
            return;
        
        ctrlPts.unshift([parseFloat(x0), parseFloat(y0)]);
        
        var animIntervalDelay = 50;
        var deltaT = animIntervalDelay / duration;
        var t = 0;
        
        var animInterval = null;
        var animFunction = function() {
            if (t >= 1.0) {
                // Finish animation.
                clearInterval(animInterval);
                
                // Move to final position.
                elem.css({
                    "left": ctrlPts[3][0] + "px",
                    "top": ctrlPts[3][1] + "px"
                });
                
                // Callback.
                if (finishedCallback)
                    finishedCallback();
                
                return;
            } else {
                var left = (t*t*t * ((-ctrlPts[0][0]) + (3*ctrlPts[1][0]) - (3*ctrlPts[2][0]) + ctrlPts[3][0])) + (t*t * ((3*ctrlPts[0][0]) - (6*ctrlPts[1][0]) + (3*ctrlPts[2][0]))) + (t * ((-3*ctrlPts[0][0]) + (3*ctrlPts[1][0]))) + ctrlPts[0][0];
                var top = (t*t*t * ((-ctrlPts[0][1]) + (3*ctrlPts[1][1]) - (3*ctrlPts[2][1]) + ctrlPts[3][1])) + (t*t * ((3*ctrlPts[0][1]) - (6*ctrlPts[1][1]) + (3*ctrlPts[2][1]))) + (t * ((-3*ctrlPts[0][1]) + (3*ctrlPts[1][1]))) + ctrlPts[0][1];
            
                elem.css({
                    "left": left + "px",
                    "top": top + "px"
                });
            }
            
            t += deltaT;
        };
        
        animInterval = setInterval(animFunction, animIntervalDelay);
        animFunction();
    };
})(jQuery);
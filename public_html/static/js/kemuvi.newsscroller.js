// kemuvi.newsscroller.js
// Requires jQuery

(function($) {
    $.newsscroller_currentitem = 0;
    $.newsscroller_width = 0;
    $.newsscroller_interval = null;
    
    $.newsscrollertick = function() {
        var totalItems = $(".newsscrolleritem").length;
        
        var currentItem = $("#newsscrolleritem_" + $.newsscroller_currentitem);
        
        if ($.newsscroller_currentitem === totalItems - 1)
            $.newsscroller_currentitem = 0;
        else
            $.newsscroller_currentitem++;
        
        var nextItem = $("#newsscrolleritem_" + $.newsscroller_currentitem);
        
        //alert(currentItem.selector);
        //alert(nextItem.selector);
        
        currentItem.animate({
            "left": (-$.newsscroller_width) + "px"
        }, 1000, "swing", function() {
            $("#newsscrolleritem_" + (($.newsscroller_currentitem === 0 ? $(".newsscrolleritem").length - 1 : $.newsscroller_currentitem - 1))).css({
                "left": $.newsscroller_width + "px"
            });
        });
        nextItem.animate({
           "left": "0px"
        }, 1000, "swing", function() {
            $("#newsscrolleritem_" + (($.newsscroller_currentitem === 0 ? $(".newsscrolleritem").length - 1 : $.newsscroller_currentitem - 1))).css({
                "left": $.newsscroller_width + "px"
            });
        });
    };
    
    $.fn.newsscroller = function(info) {
        this.append('<div id="newsscrolleritemcontainer"></div>');
        
        var currentItemIndex = 0;
        
        for (var i in info.items) {
            if (!info.items[i].image)
                continue;
            
            var toAppend = '<img src="' + info.items[i].image + '" style="border:0px;" />';
            if (info.items[i].link)
                toAppend = '<a href="' + info.items[i].link + '">' + toAppend + '</a>';
            
            $("#newsscrolleritemcontainer").append('<div class="newsscrolleritem" id="newsscrolleritem_' + currentItemIndex + '">' + toAppend + '</div>');
            currentItemIndex++;
        }
        
        if ($(".newsscrolleritem").length < 1)
            return;
        
        $.newsscroller_width = info.width;
        
        $("#newsscrolleritemcontainer").css({
            "position": "relative",
            "left": "0px",
            "top": "0px",
            "width": info.width + "px",
            "height": info.height + "px",
            "text-align": "center",
            "margin-bottom": "5px",
            "overflow": "hidden"
        });
        
        $(".newsscrolleritem").css({
            "position": "absolute",
            "left": "416px",
            "top": "inherit"
        });
        
        $("#newsscrolleritem_0").hide();
        $("#newsscrolleritem_0").css({
            "left": "0px"
        });
        $("#newsscrolleritem_0").fadeIn(1000);
        
        $.newsscroller_interval = setInterval($.newsscrollertick, info.changeDelay + 1000);
    };
})(jQuery);
$("#jump-to-top").click(function(e) {
    console.log('click');
    $("body, html").animate({
        scrollTop: 0
    }, 'fast');
    
    e.preventDefault();
    return false;
});
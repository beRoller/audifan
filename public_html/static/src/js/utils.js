function isLoggedIn() {
    return $("body").hasClass("logged-in");
}

/**
 * Perform an ajax request where the url is the current page.
 * @param  string    method POST or GET.
 * @param  object    data   The data to pass to it.
 * @param  function  func   The success callback.
 */
function simpleAjax(method, data, func) {
    $.ajax({
        method: method,
        data: data,
        success: func,
        cache: false,
        dataType: "json"
    });
}

/**
 * Perform an ajax request (POST) where the url is the global ajax endpoint (/ajax/).
 */
function globalAjax(ajaxDo, additionalData, successFunc, completeFunc) {
	var data = additionalData || {};
	data["do"] = ajaxDo;

	$.ajax({
		url: "/ajax/",
		method: "POST",
		data: data,
		success: successFunc,
		complete: completeFunc,
		error: function(x, s, e) {
			console.log("AJAX Error: ", s, e);
		},
		cache: false,
		dataType: "json"
	});
}
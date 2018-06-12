/**
	options:
	{
		title,
		message,
		cancel,
		confirm,
		closeOnConfirm,
		cancelCallback,
		confirmCallback
	}
*/
$.confirmModal = function(options) {
	var cancelCallback, confirmCallback, removeModalListeners;

	removeModalListeners = function(e) {
		$("#confirm-modal").off("hide.bs.modal", cancelCallback);
		$("#confirm-modal .modal-confirm").off("click", confirmCallback);
	};

	cancelCallback = function(e) {
		console.log('cancel');

		if (options.cancelCallback) {
			options.cancelCallback(e);
		}

		removeModalListeners(e);
	};

	confirmCallback = function(e) {
		console.log('confirm');

		if (options.closeOnConfirm) {
			$("#confirm-modal").modal('hide');
		}

		if (options.confirmCallback) {
			options.confirmCallback(e);
		}

		removeModalListeners(e);
	};

	$("#confirm-modal .modal-title").html(options.title || "");
	$("#confirm-modal .modal-body").html(options.message || "");
	$("#confirm-modal .modal-cancel").html(options.cancel || "Cancel");
	$("#confirm-modal .modal-confirm").html(options.confirm || "OK").on("click", confirmCallback);

	$("#confirm-modal").on("hide.bs.modal", cancelCallback);
	$("#confirm-modal").modal();
}

// $("#jump-to-top").click(function() {
// 	confirmModal({
// 		title: "Confirm Title",
// 		message: '<b>HTML</b>',
// 		confirmCallback: function() {
// 			console.log('I confirm!');
// 		},
// 		closeOnConfirm: true
// 	});
// });
var MyAccountView = b.View.extend({
	initialize: function() {

	},

	events: {
		'click .cancel-change': function(e) {
			$(e.currentTarget).closest('.panel').removeClass('edit');
		}
	}
});

if ($(".my-account").length) {
	new MyAccountView({
		el: $(".my-account")
	});
}
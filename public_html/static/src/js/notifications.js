var NotificationsView = b.View.extend({
    initialize: function() {
        
    },
    
    isPopupOpen: function() {
        return (this.$el.hasClass("open"));
    },
    
    openPopup: function() {
        this.$el.addClass("open");
    },
    
    closePopup: function() {
        this.$el.removeClass("open");
    },
    
    togglePopup: function() {
        if (this.isPopupOpen()) {
            this.closePopup();
        } else {
            this.openPopup();
        }
    },
    
    events: {
        'click #notification-button': function(e) {
            this.togglePopup();
        },
        
        'click .popup-close': function(e) {
            this.closePopup();
        },

        'click .notification-list-item-delete': function(e) {
            var item = $(e.currentTarget).closest(".notification-list-item");
            var id = item.data("dbid");

            $(e.currentTarget).find("i").removeClass().addClass("fa fa-spinner fa-spin");

            console.log(id);

            if (id) {
                globalAjax("notification-delete", {"id":id}, function(data) {
                    item.slideUp("fast", function() {
                        item.remove();
                    });
                    console.log(data);
                });
            }
        }
    }
});

new NotificationsView({
    el: $("#notification-container")
});
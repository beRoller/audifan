var SongRowView = b.View.extend({
    id: null,
    added: false,
    waiting: false, // waiting for ajax response
    favorite: null,
    link: null,
    initialize: function () {
        this.id = this.$el.data("song-id");
        this.favorite = this.$el.find(".favorite");
        this.link = this.$el.find(".favorite a");
        this.added = (this.link.html() === "Remove");
    },
    addOrRemove: function () {
        console.log("addorremove", this.id, this.added);
        var self = this;

        if (!this.waiting) {
            this.waiting = true;

            this.favorite.html('<i class="fa fa-spin fa-spinner"></i>');

            simpleAjax("GET", {favorite: this.id}, function () {
                if (self.added) {
                    // It's now removed.
                    self.favorite.html('<a href="#">Add</a>');
                    self.added = false;
                } else {
                    // It's now added.
                    self.favorite.html('<a href="#">Remove</a>');
                    self.added = true;
                }
                self.waiting = false;
            });
        }
    },
    events: {
        "click .favorite a": function (e) {
            this.addOrRemove();
            e.preventDefault();
            return false;
        }
    }
});

if (isLoggedIn()) {
    $("tr[data-song-id]").each(function (i) {
        new SongRowView({
            el: this
        });
    });
}
(function(p, _, assets) {
    var groups = {};
    
    var game = new p.Game(1024, 768, p.AUTO, "canvas", {
        preload: function() {
            for (var groupName in assets.sprites) {
                groups[groupName] = game.add.group();
                
                for (var spriteName in assets.sprites[groupName]) {
                    var curr = assets.sprites[groupName][spriteName];
                    
                    var x = curr.x ? curr.x : 0,
                        y = curr.y ? curr.y : 0;
                        
                    groups[groupName].addChild(game.add.sprite(x, y, spriteName));
                }
            }
            
            //game.load.image("login-base", "img/login/logbase.png");
            //game.load.image("login-check", "img/login/jpn_check.png");
        },
        
        create: function() {
            for (var groupName in assets.sprites) {
                groups[groupName] = game.add.group();
                
                for (var spriteName in assets.sprites[groupName]) {
                    var curr = assets.sprites[groupName][spriteName];
                    
                    var x = curr.x ? curr.x : 0,
                        y = curr.y ? curr.y : 0;
                        
                    groups[groupName].addChild(game.add.sprite(x, y, spriteName));
                }
            }
        },
        
        update: function() {
            
        }
    });
})(Phaser, _, {
    "sprites": sprites
});
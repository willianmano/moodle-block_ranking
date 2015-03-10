M.block_ranking = {};

M.block_ranking.init_tabview = function(Y) {
    Y.use("tabview", function(Y) {
        var tabview = new Y.TabView({srcNode:'#ranking-tabs'});
        tabview.render();
    });
};
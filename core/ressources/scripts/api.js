var forge_api = {
    collections : {
        ressourceToList : function(response) {
            var list = [];
            for(var i = 0; i < response.meta.count; i++) {
                list.push('[' + response.items[0].slug + '] ' + response.items[0].name);
            }
            return list;
        }
    }
};
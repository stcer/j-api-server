var exports;
var SyncComponent = {
    loaded : {},
    src : "/component/",

    create : function(name){
        if(this.loaded[name]){
            return this.loaded[name];
        }

        var that = this;
        Vue.component(name, function(resolve) {
            var scriptUrl = that.src + name + ".js?" + Math.random();
            $.get(scriptUrl, function(script) {
                if(!/html$/.test(exports.template)) {
                    resolve(exports);
                } else {
                    var tplUrl = that.src + "template/" + name + ".html?" + Math.random() ;
                    $.get(tplUrl, function(template) {
                        exports.template = '<div>' + template + '</div>';
                        resolve(exports);
                    }, 'text');
                }
            }, 'script');
        });

        this.loaded[name] = Vue.component(name);
        return this.loaded[name];
    }
};

// 定义路由
var routes = [
    {path : '/', component: SyncComponent.create('welcome')},
    {path : '/api/:id', component: SyncComponent.create('api')}
];

var router = new VueRouter({
    routes : routes
});

var app = new Vue({
    router : router,
    data: {
        apis: [],
    },
    created : function(){
        var that = this;
        PApi.get('document.getApiList').done(function(rs){
            that.apis = rs;
        }).fail(function(){
        });
    }
}).$mount('#main');

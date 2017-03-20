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
                        exports.template = template;
                        exports.template = '<div>' + exports.template + '</div>';
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

SyncComponent.create('testForm');
SyncComponent.create('initParams');

/**
 * 中心组件组织数据给各个组件
 * 子组件完成部分功能的渲染与数据计算
 * @type {Vue}
 */
var demo = new Vue({
    router : router,

    el: '#main',
    data: {
        // api列表
        apis: [],

        // 显示api所有信息
        api : '',
        apiTmp : '',

        // 测试接口
        currentFunc : {
            name : '',
            args : [],
            doc : ''
        },

        // 初始化参数
        initParams : []
    },

    methods : {
        show : function(api){
            var that = this;
            this.initParams = [];
            this.apiTmp = api;

            PApi.get('document.getInitParams', {api : api}).done(function(rs){
                if(rs.length > 0){
                    that.initParams = rs;
                    $('#modalInitPrams').modal();
                } else {
                    that.api = api;
                }
            }).fail(function(){
            });
        },

        onTest : function(func){
            this.$set('currentFunc', func);
            //$('#myModal').modal();
        },

        onInit : function(params){
            this.$set('api', this.apiTmp);
            this.$set('initParams', params);
        }
    },

    created : function(){
        var that = this;

        PApi.get('document.getApiList').done(function(rs){
            that.apis = rs;
        }).fail(function(){

        });
    }
});

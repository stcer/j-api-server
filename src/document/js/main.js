Vue.component('welcome', {
    template: '#welcomeTemplate'
});

/**
 * 中心组件组织数据给各个组件
 * 子组件完成部分功能的渲染与数据计算
 * @type {Vue}
 */
var demo = new Vue({
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

    computed: {
        welcome : function(){
            return this.api.length == 0;
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

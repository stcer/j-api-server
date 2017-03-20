Vue.filter('annotation', function (value) {
    return "    " +  value;
});


var exports =  {
    template: 'api.html',
    data : function(){
        return {
            name : null,
            summary : '',

            currentFunc : {
                name : '',
                args : [],
                doc : ''
            },

            isInit : false,
            funcs : [],
            params : []
        }
    },

    methods : {
        toggle: function (key) {
            this.funcs[key].isShow = !this.funcs[key].isShow;
        },

        test : function(key){
            this.currentFunc = this.funcs[key];
        },

        update : function(){
            if(!this.name){
                return;
            }


            if(!this.isInit){
                // todo, init params
                this.isInit = true;
            }

            var that = this;
            var args = { name : this.name, params : this.params}
            PApi.get('document.getApiDocument', args)
                .done(function(rs){
                    rs.method.forEach(function(value, index){
                        rs.method[index].isShow = false;
                    });
                    that.funcs = rs.method;
                    that.summary = rs.document;
                }).fail(function(){

                });
        }
    },


    beforeRouteEnter : function(to, from, next){
        next();
    },

    created : function(){
        this.name = this.$route.params.id;
        this.update();
    },

    computed: {
        hasSummary : function(){
            return (this.summary + '').length > 0;
        }
    },

    watch : {
        // 如果路由有变化，会再次执行该方法
        '$route': function() {
            this.name = this.$route.params.id;
        }
    }
};
Vue.filter('annotation', function (value) {
    return "    " +  value;
});

Vue.component('api', {
    template: '#apiTemplate',
    props : ['name', 'params', 'on-test'],
    data : {
        name : null,
        funcs : [],
        summary : ''
    },

    methods : {
        toggle: function (key) {
            console.log(this.funcs[key].isShow);
            this.$set('funcs[' + key + '].isShow', !this.funcs[key].isShow);
        },

        test : function(key){
            this.onTest(this.funcs[key]);
        },

        update : function(){
            if(!this.name){
                return;
            }

            var that = this;
            var args = { name : this.name, params : this.params}
            PApi.get('document.getApiDocument', args)
                .done(function(rs){
                    that.$set('funcs', rs.method) ;
                    that.$set("summary", rs.document) ;
                    })
                .fail(function(){

                });
        }
    },

    created : function(){
        this.update();
    },

    computed: {
        hasSummary : function(){
            return this.summary.length > 0;
        }
    },

    watch : {
        name : function(val, oldVal){
            if(val == oldVal){
                return val;
            }

            this.update();
        },

        funcs : {
            deep : true
        }
    }
});
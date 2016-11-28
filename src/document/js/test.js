/**
 * gen test quest url
 * @depend api, func.args, initPrams
 */
Vue.component('testForm', {
    template: '#TestFormTemplate',
    props : ['func', 'api', 'params'],

    methods : {
    },

    computed: {
        isWelcome : function(){
            return this.api.length > 0;
        },

        hasDoc : function(){
            return this.func.doc.length > 0;
        },

        url : function(){
            var api = this.api;

            if(this.func.name != 'execute' && this.func.name.length > 0){
                api = api +  '.' + this.func.name;
            }

            var url = PApi.testUrl(api);
            if(url.indexOf('?')){
                url += '&';
            } else {
                url += '?';
            }

            this.func.args.forEach(function(arg){
                url += '&args[' + arg.name + ']=' + (arg.value == undefined ? '' : arg.value);
            });

            if(this.params){
                for(var k in this.params){
                    url += '&init[' + k + ']=' + (this.params[k] == undefined ? '' : this.params[k]);
                }
            }

            return url;
        }
    },

    watch : {
        api : {
            deep: true
        }
    }
});
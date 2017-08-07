/**
 * gen test quest url
 * @depend api, func.args, initPrams
 */
var exports = {
    template: 'testForm.html',
    props : ['func', 'api', 'params'],

    methods : {
    },

    computed: {
        hasDoc : function(){
            return this.func.doc.length > 0;
        },

        args : function(){
            return this.func.args;
        },

        url : function(){
            var api = this.api;

            if(this.func.name != 'execute' && this.func.name.length > 0){
                api = api +  '.' + this.func.name;
            }

            var url = PApi.testUrl(api);
            if(url.indexOf('?') !== -1){
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
    }
};
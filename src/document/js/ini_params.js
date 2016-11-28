
Vue.component('initParams', {
    template: '#InitPramsTemplate',
    props : ['params', 'api', 'on-init'],

    methods : {
        submit : function() {
            $('#modalInitPrams').modal('hide');
            var initParams = {};
            this.params.forEach(function(v){
                initParams[v.name] = v.value;
            });
            console.log(initParams);
            this.onInit(initParams);
        }
    }
});
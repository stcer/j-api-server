var PApi = {
    codeSucc : 200,
    apiUrl :  '/api/%action%/',
    testUrlPattern :  '/api/%action%/',

    isSucc : function(response){
        return response.code == this.codeSucc;
    },

    isError : function(response){
        return response.code != this.codeSucc;
    },

    /**
     *
     * @param api
     * @returns {string}
     */
    url : function(api) {
        return this.apiUrl.replace(/%action%/, api.replace(/\./g, '/'));
    },

    testUrl : function(api){
        return this.testUrlPattern.replace(/%action%/, api.replace(/\./g, '/'));
    },

    /**
     *
     * @param {string} api
     * @param {Array} args
     * @param {Array} request
     * @returns {*}
     */
    get : function(api, args, request){
        if(!request){
            request = {};
        }

        if(args){
            request.args = args;
        }

        var that = this;
        var deferred = $.Deferred();
        var url = this.url(api) + '?callback=?';

        $.getJSON(url, request, function(response){
            if(that.isSucc(response)){
                deferred.resolve(response.data);
            } else {
                deferred.reject(response);
            }
        });
        return deferred;
    }
}

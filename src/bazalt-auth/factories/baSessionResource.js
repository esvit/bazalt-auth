define('bazalt-auth/factories/baSessionResource', ['bazalt-auth/app'], function (app) {

    app.factory('baSessionResource', ['$resource', '$q', 'baConfig', '$injector',
        function ($resource, $q, baConfig, $injector) {
            var res = $resource(baConfig.apiEndpoint() + '/session', {}, {
                login: { method: 'POST' },
                logout: { method: 'DELETE' }
            });
            res.prototype.logout = function (success, error) {
                var baAcl = $injector.get('baAcl');
                baAcl.logout(success, error);
            };
            return res;
        }]);

});
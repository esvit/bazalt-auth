define('bazalt-auth/factories/baSessionResource', ['bazalt-auth/app'], function(module) {

    module.factory('baSessionResource', ['$resource', '$q', 'baConfig', function ($resource, $q, baConfig) {
        return $resource(baConfig.apiEndpoint() + '/sessions', {}, {
            login: { method: 'POST' },
            logout: { method: 'DELETE' }
        });
    }]);

});
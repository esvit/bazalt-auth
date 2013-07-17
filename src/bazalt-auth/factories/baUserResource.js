define('bazalt-auth/factories/baUserResource', ['bazalt-auth/app'], function(module) {

    module.factory('baUserResource', ['$resource', '$q', 'baConfig', function ($resource, $q, baConfig) {
        return $resource(baConfig.apiEndpoint(), {}, {
            login: { method: 'POST' },
            logout: { method: 'DELETE' },
            checkEmail: { method: 'GET', params: { 'action': 'checkEmail' } },
            register: { method: 'PUT' }
        });
    }]);

});
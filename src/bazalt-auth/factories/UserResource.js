define('bazalt-auth/factories/UserResource', ['bazalt-auth/app'], function(module) {

    module.factory('UserResource', ['$resource', '$q', 'blConfig', function ($resource, $q, blConfig) {
        return $resource(blConfig.apiEndpoint(), {}, {
            login: { method: 'POST' },
            checkEmail: { method: 'GET', params: { 'action': 'checkEmail' } },
            register: { method: 'PUT' }
        });
    }]);

});
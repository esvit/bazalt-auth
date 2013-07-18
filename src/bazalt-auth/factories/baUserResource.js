define('bazalt-auth/factories/baUserResource', ['bazalt-auth/app'], function(module) {

    module.factory('baUserResource', ['$resource', '$q', 'baConfig', function ($resource, $q, baConfig) {
        return $resource(baConfig.apiEndpoint() + '/users/:id', { 'id': '@id' }, {
            'checkEmail': { method: 'GET', params: { 'action': 'checkEmail' } },
            'delete': { method: 'DELETE' }
        });
    }]);

});
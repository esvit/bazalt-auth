define('bazalt-auth/factories/baRoleResource', ['bazalt-auth/app'], function(module) {

    module.factory('baRoleResource', ['$resource', '$q', 'baConfig', function ($resource, $q, baConfig) {
        return $resource(baConfig.apiEndpoint() + '/role', {}, {
        });
    }]);

});
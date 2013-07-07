define('bazalt-login/factories/UserResource', ['bazalt-login/app'], function(module) {

    module.factory('UserResource', ['$resource', '$q', function ($resource, $q) {
        return $resource('/rest.php/user', {}, {
            login: { method: 'POST' },
            checkEmail: { method: 'GET', params: { 'action': 'checkEmail' } },
            register: { method: 'PUT' }
        });
    }]);

});
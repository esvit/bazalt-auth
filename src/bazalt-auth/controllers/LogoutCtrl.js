define('bazalt-auth/controllers/LogoutCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('LogoutCtrl', ['$scope', '$location', 'blAcl',
                            function($scope,   $location,   blAcl) {
        blAcl.logout(function() {
            $location.path('/');
        });
    }]);

});
define('bazalt-auth/controllers/baLogoutCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('baLogoutCtrl', ['$scope', '$location', 'baAcl',
                               function($scope,   $location,   baAcl) {
        baAcl.logout(function() {
            $location.path('/');
        });
    }]);

});
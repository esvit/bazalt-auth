define('bazalt-auth/controllers/baLoginCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('baLoginCtrl', ['$scope', '$location', 'baAcl',
                              function($scope,   $location,   baAcl) {
        $scope.form = {};
        if (baAcl.isLoggedIn()) {
            $location.path('/user/profile');
        }

       $scope.loginUser = function () {
            var data = $scope.form;
            baAcl.login(data, function(user) {
                $location.path('/user/profile');
            }, function(res) {
                if (res.status == 400) $scope.login.invalidForm(res.data);
            });
        };
    }]);

});
define('bazalt-auth/controllers/LoginCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('LoginCtrl', ['$scope', '$location', 'blAcl',
                            function($scope,   $location,   blAcl) {
        $scope.form = {};
        if (blAcl.isLoggedIn()) {
            $location.path('/user/profile');
        }

       $scope.loginUser = function () {
            var data = $scope.form;
            blAcl.login(data, function(user) {
                $location.path('/user/profile');
            }, function(res) {
                if (res.status == 400) $scope.login.invalidForm(res.data);
            });
        };
    }]);

});
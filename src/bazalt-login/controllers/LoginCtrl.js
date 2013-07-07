define('bazalt-login/controllers/LoginCtrl', ['bazalt-login/app'], function(module) {

    module.controller('LoginCtrl', ['$scope', '$location', 'UserResource', function($scope, $location, UserResource) {
        $scope.form = {};
        $scope.loginUser = function () {
            var data = $scope.form;
            UserResource.login(data, function(res) {
                //$location.path('/');
                //console.info(res);
            }, function(res) {
                if (res.status == 400) $scope.login.invalidForm(res.data);
            });
        };
    }]);

});
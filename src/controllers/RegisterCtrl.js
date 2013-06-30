define(['main'], function(module) {

    module.controller('RegisterCtrl', ['$scope', '$location', 'UserResource', 'bazaltLogin', function($scope, $location, UserResource, bazaltLogin) {
        $scope.form = {};
        $scope.registerUser = function () {
            var user = new UserResource($scope.form);
            user.$register(function(res) {
                $location.path(bazaltLogin.baseUrl() + '/activationSent');
            }, function(res) {
                if (res.status == 400) $scope.register.invalidForm(res.data);
            });
        };
        $scope.resendActivation = function () {
            $http.post('/account/resendActivation', $scope.form)
            .success(function(data) {
                $location.path('/activationResent');
            })
            .error(function(data, status, headers, config) {
                $scope.error = data.error.message;
            });
        };

    }]);

});
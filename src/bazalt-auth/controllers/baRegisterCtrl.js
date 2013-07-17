define('bazalt-auth/controllers/baRegisterCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('baRegisterCtrl', ['$scope', '$location', 'baUserResource', 'baConfig', '$q',
                                 function($scope,   $location,   baUserResource,   baConfig,   $q) {
        $scope.user = {};
        $scope.registerUser = function () {
            var user = new baUserResource($scope.user);
            $scope.loading = true;
            user.$register(function(res) {
                $scope.loading = false;
                $location.path(baConfig.baseUrl() + '/activationSent');
            }, function(res) {
                if (res.status == 400) $scope.register.invalidForm(res.data);
            });
        };
        $scope.checkEmail = function(email) {
            var d = $q.defer();
            baUserResource.checkEmail({ 'email': email }, function(data) {
                d.resolve(data.valid);
            }, function(error) {
                d.reject(error);
            });
            return d.promise;
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
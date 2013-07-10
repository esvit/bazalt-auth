define('bazalt-auth/directives/blAccessLevel', ['bazalt-auth/app'], function(module) {

    module.directive('blAccessLevel', ['blAcl', function(blAcl) {
        return {
            restrict: 'A',
            scope: {
                'accessLevel': '=blAccessLevel'
            },
            link: function($scope, element, attrs) {
                $scope.user = blAcl.user();
                $scope.$watch('user', function(user) {
                    updateCSS();
                }, true);
                $scope.$watch('accessLevel', function(al) {
                    updateCSS();
                }, true);

                function updateCSS() {
                    if ($scope.user && $scope.accessLevel) {
                        $(element).toggle(blAcl.authorize($scope.accessLevel, $scope.user.role));
                    }
                }
            }
        };
    }]);

});
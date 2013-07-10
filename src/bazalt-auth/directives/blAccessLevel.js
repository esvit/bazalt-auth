define('bazalt-auth/directives/blAccessLevel', ['bazalt-auth/app'], function(module) {

    module.directive('blAccessLevel', ['blAcl', '$rootScope', function(blAcl, $rootScope) {
        return {
            restrict: 'A',
            scope: {
                'accessLevel': '=blAccessLevel'
            },
            link: function($scope, element, attrs) {
                $scope.user = blAcl.user();
                $rootScope.$watch('user', function(user) {
                    updateCSS();
                }, true);
                $scope.$watch('accessLevel', function(al) {
                    updateCSS();
                }, true);

                function updateCSS() {
                    if ($scope.user && $scope.accessLevel) {
                        $(element).toggle(blAcl.authorize($scope.accessLevel, blAcl.user().role) >= 1);
                    }
                }
            }
        };
    }]);

});
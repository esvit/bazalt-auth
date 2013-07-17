define('bazalt-auth/directives/baAccessLevel', ['bazalt-auth/app'], function(module) {

    module.directive('baAccessLevel', ['baAcl', '$rootScope',
                               function(baAcl,   $rootScope) {
        return {
            restrict: 'A',
            scope: {
                'accessLevel': '=baAccessLevel'
            },
            link: function($scope, element, attrs) {
                console.info(attrs.baAccessLevel, $scope.accessLevel)
                $scope.user = baAcl.user();
                $rootScope.$watch('user', function(user) {
                    updateCSS();
                }, true);
                $scope.$watch('accessLevel', function(al) {
                    updateCSS();
                }, true);
                $rootScope.$on('baUserLogin', function(e, args) {
                    $scope.user = baAcl.user();
                    updateCSS();
                    console.info($scope.user,
                        $scope.accessLevel );
                });

                function updateCSS() {
                    if ($scope.user && $scope.accessLevel) {
                        $(element).toggle(baAcl.authorize($scope.accessLevel, baAcl.user().role) >= 1);
                    }
                }
            }
        };
    }]);

});
define('bazalt-auth/directives/baAccessLevel', ['bazalt-auth/app'], function(module) {

    module.directive('baAccessLevel', ['baAcl', '$rootScope',
                               function(baAcl,   $rootScope) {
        return {
            restrict: 'A',
            scope: false,
            link: function($scope, element, attrs) {
                $scope.user = baAcl.user();
                $rootScope.$watch('user', function(user) {
                    updateCSS();
                }, true);
                $rootScope.$on('baUserLogin', function(e, args) {
                    $scope.user = baAcl.user();
                    updateCSS();
                });

                function updateCSS() {
                    if ($scope.user) {
                        $(element).toggle(baAcl.hasRight(attrs.baAccessLevel, baAcl.user().acl));
                    }
                }
            }
        };
    }]);

});
define('bazalt-auth/controllers/ModalCtrl', ['bazalt-auth/app'], function(module) {

    module.controller('ModalCtrl', ['$scope', function($scope) {
        this.setModel = function(data) {
            $scope.$apply( function() {
                $scope.data = data;
            });
        };
        $scope.setModel = this.setModel;
    }]);

});
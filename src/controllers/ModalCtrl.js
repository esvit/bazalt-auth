define(['bazalt-login'], function(module) {

    module.controller('ModalCtrl', ['$scope', function($scope) {
        this.setModel = function(data) {
            $scope.$apply( function() {
                $scope.data = data;
            });
        };
        $scope.setModel = this.setModel;
    }]);

});
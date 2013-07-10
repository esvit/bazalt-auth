define('bazalt-auth/directives/ngUnique', ['bazalt-auth/app'], function(module) {

    module.directive('ngUnique', ['$parse', function ($parse) {
        return {
            require: 'ngModel',
            scope: false,
            link: function (scope, elem, attrs, ctrl) {
                var callback = $parse(attrs.ngUnique);
                elem.on('blur', function (evt) {
                    scope.$apply(function () {
                        callback(scope, {'$value': elem.val()}).then(function(data){
                            ctrl.$setValidity('unique', data);
                            if (!data) {
                                ctrl.$setViewValue(undefined);
                            }
                        });
                    });
                });
            }
        }
    }
    ]);

});
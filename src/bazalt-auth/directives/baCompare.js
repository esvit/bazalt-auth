define('bazalt-auth/directives/baCompare', ['bazalt-auth/app'], function(module) {

    module.directive('baCompare', [function () {
        return {
            require: 'ngModel',
            scope: {
                compareField: '=baCompare'
            },
            link: function (scope, elem, attrs, ctrl) {
                // add a parser that will process each time the value is
                // parsed into the model when the user updates it.
                ctrl.$parsers.unshift(function(value) {
                    // test and set the validity after update.
                    var valid = value == scope.compareField;
                    ctrl.$setValidity('baCompare', valid);

                    // if it's valid, return the value to the model,
                    // otherwise return undefined.
                    return valid ? value : undefined;
                });

                // add a formatter that will process each time the value
                // is updated on the DOM element.
                ctrl.$formatters.unshift(function(value) {
                    // validate.
                    ctrl.$setValidity('baCompare', value == scope.compareField);

                    // return the value or nothing will be written to the DOM.
                    return value;
                });
            }
        }
    }]);

});
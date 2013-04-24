bazaltCMS.directive('regexValidate', function() {
    return {
        // restrict to an attribute type.
        restrict: 'A',
        
        // element must have ng-model attribute.
        require: 'ngModel',
        
        // scope = the parent scope
        // elem = the element the directive is on
        // attr = a dictionary of attributes on the element
        // ctrl = the controller for ngModel.
        link: function(scope, elem, attr, ctrl) {
            
            //get the regex flags from the regex-validate-flags="" attribute (optional)
            var flags = attr.regexValidateFlags || '';
            
            // create the regex obj.
            var regex = new RegExp(attr.regexValidate, flags);            
                        
            // add a parser that will process each time the value is 
            // parsed into the model when the user updates it.
            ctrl.$parsers.unshift(function(value) {
                // test and set the validity after update.
                var valid = regex.test(value);
                ctrl.$setValidity('regexValidate', valid);
                
                // if it's valid, return the value to the model, 
                // otherwise return undefined.
                return valid ? value : undefined;
            });
            
            // add a formatter that will process each time the value 
            // is updated on the DOM element.
            ctrl.$formatters.unshift(function(value) {
                // validate.
                ctrl.$setValidity('regexValidate', regex.test(value));
                
                // return the value or nothing will be written to the DOM.
                return value;
            });
        }
    };
});
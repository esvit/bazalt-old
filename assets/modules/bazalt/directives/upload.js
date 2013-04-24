bazaltCMS.directive('ngUpload', function($parse) {
    var counter = 0;

    return {
        restrict: 'A',
        'scope':false,
        link: function(scope, element, attrs) {
            var name = attrs['name'];
            var target = attrs['ngUpload'];
            var model = attrs['model'];

            scope.$watch(function() {
                return attrs['ngUpload'];
            }, function(value) {
                target = value;
            });
            var onChange = function(e) {
                var form = angular.element('<form style="display:none;"></form>');
                //var file = e.target.files !== undefined ? e.target.files[0] : (e.target.value ? { name: e.target.value.replace(/^.+\\/, '') } : null)

                // build iframe
                var iframe = angular.element(
                                '<iframe src="javascript:false;" name="iframe-transport-' +
                                    (counter += 1) + '"></iframe>'
                            )// add iframe to app
                form
                    .attr('accept-charset', 'UTF-8')
                    .prop('target', iframe.prop('name'))
                    .prop('action', target)
                    .prop('method', 'POST');

                // attach function to load event
                iframe.bind('load', function() {
                    var response;
                    // Wrap in a try/catch block to catch exceptions thrown
                    // when trying to access cross-domain iframe contents:
                    try {
                        response = iframe.contents();
                        // Google Chrome and Firefox do not throw an
                        // exception when calling iframe.contents() on
                        // cross-domain requests, so we unify the response:
                        if (!response.length || !response[0].firstChild) {
                            throw new Error();
                        }
                        response = JSON.parse($(response[0].body).text());
                    } catch (e) {
                        response = undefined;
                    }
                    if (!response) {
                        return;
                    }
                    var result = response[name];
                    if (model) {
                        scope.$apply(function() {
                            var fn = $parse(model);
                            fn.assign(scope, result);
                        });
                    }
                    // Fix for IE endless progress bar activity bug
                    // (happens on form submits to iframe targets):
                    frame = angular.element('<iframe src="javascript:false;"></iframe>');
                    form.append(frame);
                    form.remove();
                });

                var clone = element.clone();
                    clone.bind('change', onChange);
                    element.replaceWith(clone);

                form.append(element)
                    .prop('enctype', 'multipart/form-data')
                    // enctype must be set as encoding for IE:
                    .prop('encoding', 'multipart/form-data')
                    .append(iframe);

                element = clone;

                form.appendTo('body');
                form.submit();
            };
            element.bind('change', onChange);
        }
    }
});
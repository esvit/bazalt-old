'use strict';

bazaltCMS.directive('fontpicker', function() {

    var fonts = new Array(
        'Arial, Arial, Helvetica,sans-serif',
        'Arial Black, Arial Black, Gadget,sans-serif',
        'Comic Sans MS, Comic Sans MS, cursive',
        'Courier New, Courier New, Courier, monospace',
        'Georgia,Georgia,serif',
        'Impact,Charcoal,sans-serif',
        'Lucida Console,Monaco,monospace',
        'Lucida Sans Unicode,Lucida Grande,sans-serif',
        'Palatino Linotype,Book Antiqua,Palatino,serif',
        'Tahoma,Geneva,sans-serif',
        'Times New Roman,Times,serif',
        'Trebuchet MS,Helvetica,sans-serif',
        'Verdana,Geneva,sans-serif'
    );

    return {
        require: '?ngModel',
        link: function(scope, element, attrs, controller) {
            // Add a ul to hold fonts
            var ul = $('<ul class="fontselector"></ul>');
            $('body').prepend(ul);
            $(ul).hide();

            jQuery.each(fonts, function(i, item) {

                $(ul).append('<li><a href="javascript:;" class="font_' + i + '" style="font-family: ' + item + '">' + item.split(',')[0] + '</a></li>');

                // Prevent real select from working
                element.focus(function(ev) {

                    ev.preventDefault();

                    // Show font list
                    $(ul).show();

                    // Position font list
                    $(ul).css({
                        top:  element.offset().top + element.outerHeight() - 1,
                        left: element.offset().left
                    });

                    // Blur field
                    $(this).blur();
                    return false;
                });


                $(ul).find('a').click(function() {
                    var font = fonts[$(this).attr('class').split('_')[1]];
                    element.val(font);
                    ul.hide();
                    scope.$apply(function() {
                        return controller.$setViewValue(font);
                    });
                    return false;
                });
            });


            if(controller != null) {
                controller.$render = function() {
                    if (controller.$viewValue != null) {
                        element.val(controller.$viewValue);
                    }
                };
            }
        }
    };
});


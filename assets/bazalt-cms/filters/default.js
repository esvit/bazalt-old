bazaltCMS.filter('default', function() {
    return function(value, defaultValue) {
        return value ? value : defaultValue;
    }
});
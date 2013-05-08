bazaltCMS
.value('languages', {
        all: [
            {
                id: 'en',
                title: 'English'
            }
        ],
        current: 'en'
    })
.run(['languages', '$rootScope', 'LanguageService', function(languages, $rootScope, LanguageService) {
    $rootScope.languages = languages.all;
    LanguageService.query(function(result) {
        angular.forEach(result, function(item) {
            if (item.is_default) {
                languages.current = item.id;
            }
        });
        languages.all = result;
    });

    $(document).keydown(function(e) {
        var shiftNums = {
            "1": "!", "2": "@", "3": "#", "4": "$", "5": "%", "6": "^", "7": "&", "8": "*", "9": "(", "0": ")"
        }
    
        if(e.ctrlKey && e.altKey) {
            var character = parseInt(String.fromCharCode(e.which).toLowerCase());
            if (character > 0 && character <= languages.all.length) {
                e.preventDefault();
                languages.current = languages.all[character - 1].id;
                if (!$rootScope.$$phase) {
                    $rootScope.$apply();
                }
            }
        }
    });
}])
.filter('language', ['languages', function(languages) {
    return function(value, language) {
        if (typeof value == 'undefined' || value == null) {
            return value;
        }
        language = language || languages.current;
        if (!value[language] && value['orig']) {
            return value[value['orig']] + " (" + value['orig'] + ")";
        }
        return value[language];
    }
}]);
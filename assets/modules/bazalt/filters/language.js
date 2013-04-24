bazaltCMS
.value('languages', {
        all: [
            {
                title: 'English',
                alias: 'en'
            }/*,
            {
                title: 'UA',
                alias: 'ukr'
            }*/
        ],
        current: 'en'
    })
.run(function(languages, $rootScope) {
    $(document).keydown(function(e) {
        var shiftNums = {
            "1": "!", "2": "@", "3": "#", "4": "$", "5": "%", "6": "^", "7": "&", "8": "*", "9": "(", "0": ")"
        }
    
      if(e.ctrlKey && e.altKey) {
        var character = parseInt(String.fromCharCode(e.which).toLowerCase());
        if (character > 0 && character <= languages.all.length) {
            e.preventDefault();
            languages.current = languages.all[character - 1].alias;
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }
        }
      }
    });
})
.filter('language', function(languages) {
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
});
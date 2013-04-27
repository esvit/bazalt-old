module.exports = function(grunt) {

    var isDev = true;

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            options: {
                compress: !isDev,
                beautify: isDev,
                preserveComments: isDev ? 'all' : false,
                mangle: !isDev
            },
            'require.js': {
                src: [
                    'assets/components/requirejs/require.js',
                    'assets/components/jquery/jquery.js',
                    'assets/components/jquery/jquery-migrate.js',
                    'assets/components/angularjs-bower/angular-loader.js',
                    'assets/config.js'
                ],
                dest: 'assets/require.js'
            },
            'angular.js': {
                src: [
                    'assets/components/angularjs-bower/angular.js',
                    'assets/components/angularjs-bower/angular-resource.js'
                ],
                dest: 'assets/components/angular.js'
            },
            'jquery-ui.js': {
                src: [
                    'assets/components/jquery-ui/ui/*.js',
                    //'assets/components/jquery-ui/ui/i18n/*.js'
                ],
                dest: 'assets/components/jquery-ui.js'
            },
            'bazalt-cms.js': {
                src: [
                    'assets/bazalt-cms/bazaltCMS.js',
                    'assets/bazalt-cms/factories/*',
                    'assets/bazalt-cms/directives/*',
                    'assets/bazalt-cms/filters/*'
                ],
                dest: 'assets/components/bazalt-cms.js'
            },
            'bootstrap.js': {
                src: [
                    'assets/components/bootstrap/js/bootstrap-transition.js',
                    'assets/components/bootstrap/js/bootstrap-alert.js',
                    'assets/components/bootstrap/js/bootstrap-button.js',
                    'assets/components/bootstrap/js/bootstrap-carousel.js',
                    'assets/components/bootstrap/js/bootstrap-collapse.js',
                    'assets/components/bootstrap/js/bootstrap-dropdown.js',
                    'assets/components/bootstrap/js/bootstrap-modal.js',
                    'assets/components/bootstrap/js/bootstrap-tooltip.js',
                    'assets/components/bootstrap/js/bootstrap-popover.js',
                    'assets/components/bootstrap/js/bootstrap-scrollspy.js',
                    'assets/components/bootstrap/js/bootstrap-tab.js',
                    'assets/components/bootstrap/js/bootstrap-typeahead.js',
                    'assets/components/bootstrap/js/bootstrap-affix.js'
                ],
                dest: 'assets/components/bootstrap.js'
            },
            'ng-finder.js': {
                src: [
                    'assets/components/ng-finder/js/ng-finder.js'
                ],
                dest: 'assets/components/ng-finder.js'
            },
            'ng-table.js': {
                src: [
                    'assets/components/ng-table/ng-table.js'
                ],
                dest: 'assets/components/ng-table.js'
            },
            'ng-editable-tree.js': {
                src: [
                    'assets/components/ng-editable-tree/ng-editable-tree.js'
                ],
                dest: 'assets/components/ng-editable-tree.js'
            },
            'jquery.ui.touch-punch.js': {
                src: [
                    'assets/components/jquery-ui-touch-punch/jquery.ui.touch-punch.js'
                ],
                dest: 'assets/components/jquery.ui.touch-punch.js'
            },
            'jquery.ui.nestedSortable.js': {
                src: [
                    'assets/components/nestedSortable/jquery.ui.nestedSortable.js'
                ],
                dest: 'assets/components/jquery.ui.nestedSortable.js'
            },
        },
        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: 'installer.zip'
                },
                expand: true,
                files: [
                    {
                        src: [
                            'App/**',
                            'assets/components/*.js',
                            'assets/components/ckeditor/**',
                            'assets/modules/**',
                            'assets/*.js',
                            'Components/**',
                            'Framework/**',
                            'install/**',
                            'static/empty',
                            'uploads/empty',
                            'tmp/empty',
                            'themes/default/**',
                            '.htaccess',
                            'admin.php',
                            'bootstrap.php',
                            'index.php',
                            'less.php',
                            'rest.php',
                            'thumb.php',
                            'install.php'
                        ]
                    }
                    //{src: ['path/**'], dest: 'internal_folder2/'}, // includes files in path and its subdirs
                    //{expand: true, cwd: 'path/', src: ['**'], dest: 'internal_folder3/'}, // makes all src relative to cwd
                    //{flatten: true, src: ['path/**'], dest: 'internal_folder4/', filter: 'isFile'} // flattens results to a single level
                ]
            }
        }
    });

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');

    // Default task(s).
    grunt.registerTask('default', [
        'uglify',
        'compress'
    ]);

};
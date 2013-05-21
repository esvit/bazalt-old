module.exports = function(grunt) {

    var isDev = true;

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        php: {
            dist: {
                options: {
                    port: 8000,
                    base: '.',
                    open: true,
                    keepalive: true
                }
            }
        },
        phpunit: {
            unit: {
                configuration: 'Tests/travis/mysql.xml'
            },
            options: {
                coverageHtml: 'Tests/coverage/',
                bin: 'phpunit',
                bootstrap: 'tests/Bootstrap.php',
                colors: true,
                testdox: true
            }
        },
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
                    'assets/components/angular/angular-loader.js',
                    'assets/config.js'
                ],
                dest: 'assets/require.js'
            },
            'angular.js': {
                src: [
                    'assets/components/angular/angular.js',
                    'assets/components/angular/angular-resource.js'
                ],
                dest: 'assets/components/angular.js'
            },
            'angular-cookies.js': {
                src: [
                    'assets/components/angular/angular-cookies.js'
                ],
                dest: 'assets/components/angular-cookies.js'
            },
            'jquery-ui.js': {
                src: [
                    'assets/components/jquery-ui/ui/jquery-ui.js'
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
                    'assets/modules/nestedSortable/jquery.mjs.nestedSortable.js',
                    'assets/components/ng-editable-tree/ng-editable-tree.js'
                ],
                dest: 'assets/components/ng-editable-tree.js'
            },
            'bz-switcher.js': {
                src: [
                    'assets/modules/bz-switcher/bootstrapSwitch.js',
                    'assets/modules/bz-switcher/bz-switcher.js'
                ],
                dest: 'assets/components/bz-switcher.js'
            },
            'colorpicker.js': {
                src: [
                    'assets/components/bootstrap-colorpicker/js/bootstrap-colorpicker.js'
                ],
                dest: 'assets/components/colorpicker.js'
            },
            'ng-ace.js': {
                src: [
                    'assets/modules/angular-ace/angular-ace.js'
                ],
                dest: 'assets/components/ng-ace.js'
            },
            'jquery.ui.touch-punch.js': {
                src: [
                    'assets/components/jquery-ui-touch-punch/jquery.ui.touch-punch.js'
                ],
                dest: 'assets/components/jquery.ui.touch-punch.js'
            },
            'ng-infinite-scroll.js': {
                src: [
                    'assets/components/ngInfiniteScroll/ng-infinite-scroll.js'
                ],
                dest: 'assets/components/ng-infinite-scroll.js'
            },
            'uploader.js': {
                src: [
                    'assets/components/plupload/js/plupload.full.js',
                    'assets/modules/uploadkit/uploadkit.js',
                    'assets/modules/uploadkit/directive.js'
                ],
                dest: 'assets/components/uploader.js'
            },
            'site.js': {
                src: [
                    'App/Site/assets/js/app.js'
                ],
                dest: 'assets/components/site.js'
            },
            'themes/default': {
                src: [
                    'assets/components/bootstrap/js/*.js'
                ],
                dest: 'themes/default/assets/js/bootstrap.js'
            }
        },
        copy: {
            "ckeditor/bazalt-cms": {
                files: [
                    { flatten: true, expand: true, src: ["assets/bazalt-cms/ckeditor/bazalt-cms/*"], dest: "assets/components/ckeditor/plugins/bazalt-cms/", filter: 'isFile'}
                ]
            },
            "components/ace": {
                files: [
                    { flatten: true, expand: true, src: ["assets/components/ace-builds/src-min-noconflict/*"], dest: "assets/components/ace/", filter: 'isFile'}
                ]
            },
            "modules/angular-ace/mode-twig": {
                files: [
                    { flatten: true, expand: true, src: ["assets/modules/angular-ace/mode-twig.js"], dest: "assets/components/ace/", filter: 'isFile'}
                ]
            },
            "themes/default": {
                files: [
                    { flatten: true, expand: true, src: ["assets/components/bootstrap/img/*"], dest: "themes/default/assets/img/", filter: 'isFile'}
                ]
            }
        },
        less: {
            "themes/default": {
                options: {
                    paths: [
                        "assets/components/bootstrap/less",
                        "themes/default/assets/less"
                    ],
                    yuicompress: !isDev
                },
                files: {
                    "themes/default/assets/css/theme.css": "themes/default/assets/less/theme.less"
                }
            }
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
                            'assets/components/ace/**',
                            'assets/components/ckeditor/**',
                            'assets/components/bootstrap/less/*',
                            'assets/modules/**',
                            'assets/*.js',
                            'Components/**',
                            'Framework/**',
                            'install/**',
                            'Widgets/**',
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
    grunt.loadNpmTasks('grunt-php');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-compress');

    // Default task(s).
    grunt.registerTask('default', [
        'copy',
        'less',
        'uglify',
        'compress'
    ]);

};
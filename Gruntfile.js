module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
            },
            build: {
                src: 'src/<%= pkg.name %>.js',
                dest: 'build/<%= pkg.name %>.min.js'
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
                            'assets/**',
                            'Components/**',
                            'Framework/**',
                            'install/**',
                            '.htaccess',
                            'admin.php',
                            'bootstrap.php',
                            'index.php',
                            'less.php',
                            'rest.php',
                            'thumb.php'
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
    //grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-compress');

    // Default task(s).
    grunt.registerTask('default', ['compress']);

};
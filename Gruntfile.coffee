path = require 'path'

# Build configurations.
module.exports = (grunt) ->
  grunt.initConfig
  # Deletes built file and temp directories.
    clean:
      working:
        src: [
          'bazalt-auth.*'
        ]

    uglify:
    # concat js files before minification
      js:
        src: ['bazalt-auth.js']
        dest: 'bazalt-auth.min.js'
        options:
          sourceMap: (fileName) ->
            fileName.replace /\.js$/, '.map'

    requirejs:
      dist:
        options:
          baseUrl: 'src'
          optimize: 'none'
          preserveLicenseComments: false
          useStrict: true
          wrap: true
          mainConfigFile: 'src/config.js'
          name: 'bazalt-auth'
          include: ['bazalt-auth']
          exclude: ['jquery','angular','angular-resource']
          out: 'bazalt-auth.js'

  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-copy'
  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-contrib-concat'
  grunt.loadNpmTasks 'grunt-contrib-requirejs'

  grunt.registerTask 'default', [
    'clean'
    'requirejs'
    'uglify'
  ]

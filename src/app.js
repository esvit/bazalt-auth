require.config({
    shim: {
        angular: {
            exports: 'angular'
        },
        'angular-resource': {
            exports: 'angular'
        },
        'views': {
            deps: ['main']
        }
    },
    paths: {
        'jquery': '../bower_components/jquery/jquery.min',
        'angular': '../bower_components/angular/angular.min',
        'angular-resource': '../bower_components/angular-resource/angular-resource.min',
        'angular-loader': '../bower_components/angular-loader/angular-loader.min',
        'elasticjs': '../bower_components/elastic.js/dist/elastic.min',
        'angular-elastic': '../bower_components/elastic.js/dist/elastic-angular-client.min',
        'social-likes': '../bower_components/social-likes/social-likes.min',
        'jquery-timeago': '../bower_components/jquery-timeago/jquery.timeago',
        'ng-table': '../bower_components/ng-table/ng-table'
    }
});
require(['bazalt-login']);
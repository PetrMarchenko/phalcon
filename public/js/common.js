requirejs.config({
    paths: {
        'jquery': 'lib/jquery-1.11.1.min',
        'jqueryui': 'lib/jquery-ui.min',
        'fuelux': '/../fuelux',
        'bootstrap': '/bootstrap/js/bootstrap',
        'datetimepicker': 'lib/jquery-ui-timepicker-addon'
    },
    baseUrl: '/js'
});

require(['jquery', 'jqueryui', 'lib/grid'], function ($,  ui, grid) {

    /**
     * Messages
     */
    var messages = $("#messages");
    messages.children('.alert-success').each(function(i){
        $(this).delay(4000 + (i * 1000)).fadeOut();
    });
    messages.children('.alert-info').each(function(i){
        $(this).delay(4000 + (i * 1000)).fadeOut();
    });
    messages.children('.alert-warning').each(function(i){
        $(this).delay(4000 + (i * 1000)).fadeOut();
    });
    messages.children('.alert-danger').each(function(i){
        $(this).delay(4000 + (i * 1000)).fadeOut();
    });

    /**
     * Assets
     */
    require(['controllers/' + $('body').data('path') ])
});
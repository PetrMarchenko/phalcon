define(['jquery', 'alert', 'ckeditor'], function($, alert) {
    $(function(){
        CKEDITOR.config.height = 150;
        CKEDITOR.config.width = 'auto';
        CKEDITOR.replace( 'mail_templates_text_area' );
    });
});
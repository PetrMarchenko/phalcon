define(['jquery'], function ($) {

    var messages = $("#messages");

    var alert = function(type, message)
    {
        return $('\
            <div class="alert alert-'+type+'">\
                <p>'+message+'</p>\
            </div>').appendTo(messages).fadeIn().delay(4000).fadeOut();
    }

    return alert;
})
define(['jquery', 'alert'], function($, alert) {
    $(function(){
        $('body').on('change', '.resources', function () {
            var isChecked = ($(this).is( ":checked" )) ? 1 : 0;
            var resourcesKey = $(this).data( "name" );
            var action = $(this).data( "action" );
            var roleId = $(this).data( "role_id" );
            var url = $('body table.resources_form').data('url');

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    "resourcesKey" : resourcesKey,
                    "action" : action,
                    "roleId" : roleId,
                    "isChecked" : isChecked
                },
                success: function(data){
                    if(data.status) {
                        alert('success', "Permition has already saved")
                    } else {
                        alert('warning', "Error happened")
                    }
                },
                dataType: 'json'
            });
        });

    });
});
<h2>{{ grid.getTitle() }}</h2>
<br />
<br />


{% include "../../../views/templates/grid.volt" %}

<script>
    var grid = {
        columns: {{ grid.getColumns()|json_encode }},
        options: {{ grid.getOptions()|json_encode }},
        url: '{{ url(["for": "mail_template_show"]) }}'
    };
</script>
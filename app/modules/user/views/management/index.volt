<h2>{{ grid.getTitle() }}</h2>

{{ link_to(["for": "user_create"], "Add New ", "class": "btn btn-success") }}

<br />
<br />


{% include "../../../views/templates/grid.volt" %}

<script>
    var grid = {
        columns: {{ grid.getColumns()|json_encode }},
        options: {{ grid.getOptions()|json_encode }},
        url: '{{ url(["for": "user_show"]) }}'
    };
</script>
<h2>Rbac manager</h2>

<h2>{{ name }}</h2>

<table class="table table-bordered resources_form" data-url = {{ url(['for': 'rbac_save']) }}>
    <tr>
        <th>#</th>
        <th>Resources</th>
        <th>Action</th>
        <th>URL</th>
        {% for role in roles %}
            <th>{{role.key}}</th>
        {% endfor %}
     </tr>
    {% for key, row in table %}
    <tr>
    <td>{{ key }}</td>
    <td>{{ row['resources']['name'] }}</td>
    <td>{{ row['resources']['action'] }}</td>
    <td>{{(row['resources']['url'])}}</td>
    {% for value in row['resources']['roles'] %}
        <td>
            <input type="checkbox"
                class="resources"
                data-role_id = "{{value['role'].id}}"
                data-name = "{{row['resources']['name']}}"
                data-action = "{{row['resources']['action']}}"
                "{% if (value['isAllow']) %} checked {% endif %}" >
        </td>
    {% endfor %}

    </tr>
    {% endfor %}
</table>
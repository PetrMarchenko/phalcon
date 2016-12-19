<table id="{% if gridId is defined %}{{ gridId }}{% else %}grid{% endif %}" class="table table-bordered datagrid">
    <thead>
    </thead>

    {% set pageSizes = (grid.pageSizes) ? grid.pageSizes : grid['pageSizes'] %}

    <tfoot {% if pageSizes|length == 1 %} style="display:none;" {% endif %}>
        <tr>
            <th>
                <div class="datagrid-footer-left" style="display:none;">
                    <div class="grid-controls">
                        <span>
                            <span class="grid-start"></span> -
                            <span class="grid-end"></span> of
                            <span class="grid-count"></span>
                        </span>

                        <div class="select grid-pagesize" data-resize="auto">
                            <button type="button" data-toggle="dropdown" class="btn dropdown-toggle">
                                <span class="dropdown-label"></span>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {% for selected, pageSize in pageSizes %}
                                    <li data-value="{{ pageSize }}" {% if 'selected' ===  selected %} data-selected="true" {% endif %} >
                                        <a href="#">{{ pageSize }}</a>
                                    </li>
                                {% endfor %}
                            </ul>
                        </div>
                        <span>Per Page</span>
                    </div>
                </div>
                <div class="datagrid-footer-right" style="display:none;">
                    <div class="grid-pager">
                        <button type="button" class="btn grid-prevpage"><i class="icon-chevron-left"></i></button>
                        <span>Page</span>

                        <div class="input-append dropdown combobox">
                            <input class="input-small" type="text">
                            <button type="button" class="btn" data-toggle="dropdown"><i class="caret"></i></button>
                            <ul class="dropdown-menu"></ul>
                        </div>
                        <span>of <span class="grid-pages"></span></span>
                        <button type="button" class="btn grid-nextpage"><i class="icon-chevron-right"></i></button>
                    </div>
                </div>
            </th>
        </tr>
    </tfoot>
</table>
define(['fuelux/all', 'jquery', 'alert'], function (StaticDataSource, $, alert) {

    function getURLParameter(name) {
        return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
    }

    $.fn.datagrid.Constructor.prototype.renderColumns = function () {
        var self = this;

        this.$footer.attr('colspan', this.columns.length);
        this.$topheader.attr('colspan', this.columns.length);

        var colHTML = '';

        $.each(this.columns, function (index, column) {
            var classes = column.cssClass,
                label = column.label;
            if (classes == 'title-width') {
                label = "<div class='"+classes+"'> "+ column.label+" </div>"
            }
            if (column.sortable) {
                classes += " sortable";
            }
            colHTML += '<th data-property="' + column.property + '" class="' + classes + '">' + label + '</th>';
        });
        self.$colheader.append(colHTML);

        var colSearchHTML = '<tr class="search-header">';
        var hasSearchable = false;

        $.each(this.columns, function (index, column) {
            colSearchHTML += '<th>';

            if (column['searchable']) {
                hasSearchable = true;

                var classes = 'datagrid-search input-small';
                if (column['date'] || column['datetime']) {
                    classes += ' datepicker';
                }
                var urlValue = getURLParameter(column.property) || '';

                if ($.isPlainObject(column.searchable) || $.isArray(column.searchable)) {

                    var options = '<option></option>';
                    for (var i in column.searchable) {
                        var selected = '';
                        if (urlValue == i) {
                            selected = ' selected'
                        }
                        options += '<option value="' + i +'"' + selected + '>' + column.searchable[i] + '</option>';
                    }
                    colSearchHTML += '<select class="' + classes + '" name="' + column.property + '">' + options + '</select>';
                } else {
                    var pattern = '';
                    if (column.pattern) {
                        pattern = ' pattern="' + column.pattern + '"';
                    }
                    colSearchHTML += '<input class="' + classes + '" ' + pattern + ' name="' + column.property
                        + '" data-val="' + urlValue + '" value="' + urlValue + '"/>';
                }
            }

            if (column['checkbox']) {
                colSearchHTML += column.checkbox;
                hasSearchable = true;
            }


            colSearchHTML += '</th>';
        });
        colSearchHTML += '</tr>';

        if (hasSearchable) {
            this.$thead.append(colSearchHTML);
        }

        var hasCategory = false;
        var colCategories = [];

        $.each(this.columns, function (index, column) {
            var category = {'title': '', 'colspan': 1}
            if (column['category']) {
                var last = colCategories.length -1;

                if (last > 0 && colCategories[last].title == column['category']) {
                    colCategories[last]['colspan']++;
                } else {
                    category.title = column['category']
                    colCategories.push(category);
                }
                hasCategory = true;
            } else {
                colCategories.push(category);
            }
        });
        if (hasCategory) {
            var colCategoryHTML = '<tr class="search-header">';
            for (var i in colCategories) {
                colCategoryHTML += '<th colspan="' + colCategories[i].colspan + '">' + colCategories[i].title + '</th>';
            }
            colCategoryHTML += '</tr>';

            this.$thead.prepend(colCategoryHTML);
        };

        $('input.datagrid-search').blur(function () {
            if ($(this).data('val') != this.value) {
                $(this).closest('.datagrid').datagrid('reload');
            }
            $(this).data('val', this.value);
        }).keypress(function (e) {
            if (e.which === 13) {
                $(this).blur();
            }
        });

        this.$thead.find('select.datagrid-search').change(function () {
            $(this).closest('.datagrid').datagrid('reload');
        });

        if (!this.options.dataSource.options ||  "0" !== this.options.dataSource.options.exportlimit) {
            var colExportHTML = '<tr><td colspan="'+this.columns.length+'" class="grid-export"></td></tr>';
            this.$thead.prepend(colExportHTML);
        }
        //update sorted column
        this._updateColumns(this.$thead, this.$thead.find('th[data-property="'+self.options.dataOptions.sortProperty+'"]').first(), self.options.dataOptions.sortDirection);
    };

    $.fn.datagrid.Constructor.prototype.getRows = function () {
        return this.$tbody.find('tr')
    }

    $(function() {
        $('.datagrid').each(function(key, element) {
            if (window[element.id]) {
                var $this = $(element);
                var grid = window[element.id];
                var dataSource = {
                    columns: function () {
                        return grid.columns;
                    },
                    data: function (options, callback) {
                        options['search'] = {};
                        options['filter'] = {};

                        var isValid = true;
                        $this.find('.datagrid-search').each(function() {
                            if (!this.checkValidity()) {
                                isValid = false;
                            }
                            options.search[this.name] = this.value;
                        });
                        $('.datagrid-filter-' + element.id).each(function() {
                            if (!this.checkValidity()) {
                                isValid = false;
                            }
                            options[this.name] = this.value;
                        });

                        if (!isValid) {
                            new alert('error', 'Invalid value specified');
                            return;
                        }
                        $.getJSON(grid.url, options, function(response) {
                            if ($.isFunction(grid.onReloadCallback)) {
                                grid.onReloadCallback.call(this, response);
                            }
                            var exportCSV = $this.find('.grid-export');
                            if (exportCSV.length) {

                                var exportlimit =  parseInt(response.options.exportlimit);
                                var exportUrl = '?export=1&start=';

                                var colExportHTML = '<div class="btn-group"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Export to csv<span class="caret"></span></a><ul class="dropdown-menu">';
                                for (var i = 0; i < response.count; i = i + exportlimit) {

                                    var ii = (i + exportlimit > response.count )? response.count : i + exportlimit ;
                                    colExportHTML = colExportHTML + '<li><a href="'+exportUrl+i+'">'+i+'-'+ii+'</a></li>';
                                }
                                colExportHTML = colExportHTML + '</ul></div>'
                                exportCSV.html(colExportHTML);
                            }
                            return callback(response);
                        });
                    },
                    options: grid.options || {}
                };

                $this.datagrid({
                    dataSource: dataSource,
                    dataOptions: {
                        pageSize: grid.options.pageSize || 100,
                        sortProperty: grid.options.sortBy,
                        sortDirection: grid.options.sortDir
                    }
                });

                if ($.isFunction(grid.onInitCallback)) {
                    grid.onInitCallback.call($this);
                }
            }
        }).on('loaded', function (e){
            $('input.datepicker').datepicker({
                dateFormat: 'dd-mm-y',
                showOn: "button",
                buttonImage: "/js/lib/calendar.gif",
                buttonImageOnly: true,
                onClose: function() { $(this).blur() }
            });
        });
    });
});

var pckgHtmlbuilderSelect = Vue.component('pckg-htmlbuilder-select', {
    template: '#pckg-htmlbuilder-select',
    props: {
        url: null,
        refreshUrl: null,
        initialOptions: {
            default: []
        }
    },
    data: function () {
        return {
            options: this.initialOptions
        };
    },
    methods: {
        refreshList: function () {
            http.getJSON(this.refreshUrl, function (data) {
                var $select = $(this.$el).next().find('select');
                var value = $select.val();
                $select.html('');
                $.each(data.records, function (key, val) {
                    if (typeof val == 'object' || typeof val == 'array') {
                        var optgroup = '<optgroup label="' + key + '">';
                        $.each(val, function (k, v) {
                            optgroup += '<option value="' + (k === 0 ? '' : k) + '">' + v + '</option>';
                        });
                        optgroup += '</optgroup>';
                        $select.append(optgroup);
                    } else {
                        $select.append('<option value="' + (key === 0 ? '' : key) + '">' + val + '</option>');
                    }
                });
                $select.val(value);
                $select.selectpicker('refresh');
            }.bind(this));
        }
    }
});
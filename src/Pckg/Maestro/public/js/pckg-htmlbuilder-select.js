var pckgHtmlbuilderSelect = Vue.component('pckg-htmlbuilder-select', {
    template: '#pckg-htmlbuilder-select',
    props: {
        url: null,
        viewUrl: null,
        refreshUrl: null,
        initialOptions: {
            default: function () {
                return [];
            }
        }
    },
    data: function () {
        return {
            options: this.initialOptions,
            v: null
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
                this.v = value;
                $select.selectpicker('refresh');
            }.bind(this));
        }
    },
    mounted: function () {
        var $t = this;
        $(this.$el).parent().find('select').on('change', function () {
            $t.v = $(this).val();
        });
        this.v = $(this.$el).parent().find('select').val() || null;
    },
    computed: {
        realViewUrl: function () {
            return utils.url(this.viewUrl, {record: this.v});
        }
    }
});
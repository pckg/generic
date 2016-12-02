var pckgHtmlbuilderSelect = Vue.component('pckg-htmlbuilder-select', {
    template: '#pckg-htmlbuilder-select',
    props: {
        url: null,
        refreshUrl: null
    },
    data: function () {
        return {};
    },
    methods: {
        refreshList: function () {
            http.get(this.refreshUrl, function (data) {
                var $select = $(this.$el).next().find('select');
                var value = $select.val();
                $select.html('');
                $.each(data.records, function(key, val){
                    $select.append('<option value="' + key + '">' + val + '</option>');
                });
                $select.val(value);
                $select.selectpicker('refresh');
            }.bind(this));
        }
    }
});
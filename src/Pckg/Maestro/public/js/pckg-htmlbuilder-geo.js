var pckgHtmlbuilderGeo = Vue.component('pckg-htmlbuilder-geo', {
    template: '#pckg-htmlbuilder-geo',
    props: {},
    data: function () {
        return {
            search: null
        };
    },
    methods: {
        openMap: function () {
            var value = $(this.$el).parent().find('input.geo').val();
            if (value) {
                this.$refs.gmap.setCenter(value);
            }
            this.$refs.gmap.singletonMap();
            $('#pckgHtmlbuilderGeoModal').modal('show');
        },
        selectAndClose: function () {
            $(this.$el).parent().find('input.geo').val(this.$refs.gmap.getLocation().geo.join(';'));
        }
    },
    ready: function () {
        $(this.$el).parent().find('input.geo').on('focus', this.openMap);
    }
});
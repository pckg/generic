var pckgMaestroTableComponent = Vue.component('pckg-maestro-table', {
    name: 'pckg-maestro-table', // recursive
    template: '#pckg-maestro-table',
    props: {
        fields: [],
        depth: 0,
        records: [],
        groups: [],
        first: false,
        ids: [],
        allChecked: false,
        search: null,
        entityactions: [],
        table: null,
        paginator: {}
    },
    data: function () {
        return {
            _searchTimeout: null
        };
    },
    methods: {
        recordAction: function (record, action) {
            this.$parent.recordAction(record, action);
        },
        checkAll: function () {
            if (!this.allChecked) {
                $.each(this.records, function (i, record) {
                    this.ids.push(record.id);
                }.bind(this));

                this.ids = Array.from(new Set(this.ids));
            } else {
                this.ids = [];
            }
        }
    }
});
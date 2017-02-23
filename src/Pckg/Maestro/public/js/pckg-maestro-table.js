var pckgMaestroTableComponent = Vue.component('pckg-maestro-table', {
    name: 'pckg-maestro-table', // recursive
    template: '#pckg-maestro-table',
    props: {
        fields: [],
        depth: 0,
        records: [],
        groups: [],
        first: false,
        ids: {
            default: function () {
                return [];
            },
            type: Array
        },
        allChecked: false,
        search: null,
        entityactions: [],
        table: null,
        paginator: {
            default: function () {
                return {
                    perPage: 50,
                    page: 1,
                    total: 0,
                    url: null
                };
            },
            type: Object
        },
        recordactionhandler: null,
        togglefield: null,
        resetpaginatorurl: null,
        sort: {
            default: function () {
                return {
                    field: '',
                    dir: 'up'
                };
            },
            type: Object
        }
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
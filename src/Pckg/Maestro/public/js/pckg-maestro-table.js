var pckgMaestroTableComponent = Vue.component('pckg-maestro-table', {
    name: 'pckg-maestro-table', // recursive
    template: '#pckg-maestro-table',
    props: {
        fields: {
            default: function () {
                return [];
            }
        },
        depth: 0,
        records: {
            default: function () {
                return [];
            }
        },
        groups: {
            default: function () {
                return [];
            }
        },
        first: false,
        ids: {
            default: function () {
                return [];
            }/*,
             type: Array*/
        },
        allChecked: false,
        search: null,
        entityactions: {},
        table: null,
        paginator: {
            default: function () {
                return {
                    perPage: 50,
                    page: 1,
                    total: 0,
                    url: null
                };
            }/*,
             type: Object*/
        },
        /*recordactionhandler: {
            default: function () {
                console.log("No record action handler");
            },
            type: Function
        },
        togglefield: null,*/
        resetpaginatorurl: null,
        sort: {
            default: function () {
                return {
                    field: '',
                    dir: 'up'
                };
            }/*,
             type: Object*/
        }
    },
    data: function () {
        return {
            _searchTimeout: null,
            _sortTimeout: null
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
    },
    computed: {
        filteredRecords: function () {
            var self = this;
            var searchRegex = new RegExp(self.search, 'i');

            if (!self.search || self.search.length < 1 || true) {
                return self.records;
            }

            return self.records.filter(function (record) {
                return record.filter(function (value) {
                    return searchRegex.test(value);
                });
            });
        }
    }
});
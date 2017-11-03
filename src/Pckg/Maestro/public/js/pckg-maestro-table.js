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
        initialRecords: {
            default: function () {
                return [];
            }
        },
        groups: {
            default: function () {
                return [];
            }
        },
        first: {
            default: false
        },
        initialIds: {
            default: function () {
                return [];
            }/*,
             type: Array*/
        },
        search: {
            default: '',
            type: String
        },
        entityactions: {},
        table: {
            type: Object,
            default: function(){
                return {};
            }
        },
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
            },
            type: Function
        },
        togglefield: null,*/
        resetpaginatorurl: {
            type: String,
            default: ''
        },
        sort: {
            default: function () {
                return {
                    field: '',
                    dir: 'up'
                };
            }/*,
             type: Object*/
        },
        total: {
            default: 0,
            type: Number
        },
        listed: {
            default: 0,
            type: Number
        },
        loading: {
            default: false,
            type: Boolean
        }
    },
    data: function () {
        return {
            _searchTimeout: null,
            _sortTimeout: null,
            records: this.initialRecords,
            ids: this.initialIds,
            emitted: 0,
            allChecked: false
        };
    },
    methods: {
        recordAction: function (record, action) {
            this.$parent.recordAction(record, action);
        },
        checkAll: function () {
            if (this.allChecked) {
                $.each(this.records, function (i, record) {
                    this.ids.push(record.id);
                }.bind(this));

                this.ids = Array.from(new Set(this.ids));
            } else {
                this.ids = [];
            }
        },
        computed: function(val){
            return val;
        }
    }/*,
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
    }*/
});
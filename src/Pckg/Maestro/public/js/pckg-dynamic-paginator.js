Vue.component('pckg-dynamic-paginator', {
    template: '#pckg-dynamic-paginator',
    props: {
        perPage: 0,
        page: 0,
        total: 0,
        url: null,
        resetpaginatorurl: null,
        records: [],
        groups: []
    },
    data: function () {
        return {
            margin: 5
        };
    },
    computed: {
        pages: function () {
            var is = [1];
            var lastPage = Math.ceil(this.total / this.perPage);

            if (this.page > this.margin) {
                is.push('...')
            }

            for (var i = this.page - this.margin; i < this.page + this.margin; i++) {
                if (i < 2 || i > lastPage) {
                    continue;
                }

                is.push(i);
            }

            if (is.indexOf(lastPage) == -1) {
                if (is.indexOf(lastPage - 1) == -1) {
                    is.push('...');
                }
                is.push(lastPage);
            }

            return is;
        }
    },
    methods: {
        prev: function () {
            this.navigate(this.page - 1);
        },
        next: function () {
            this.navigate(this.page + 1);
        },
        navigate: function (page) {
            if (page < 1 || page > Math.ceil(this.total / this.perPage)) {
                return;
            }

            this.page = page;

            this.resetpaginatorurl({
                page: page
            });

            Vue.nextTick(function () {
                http.getJSON(this.url, function (data) {
                    this.records = data.records;
                    this.groups = data.groups;

                }.bind(this));
            }.bind(this));
        }
    }
});
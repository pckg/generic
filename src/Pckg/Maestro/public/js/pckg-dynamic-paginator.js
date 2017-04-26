Vue.component('pckg-dynamic-paginator', {
    template: '#pckg-dynamic-paginator',
    mixins: [pckgDelimiters],
    props: {
        initialPerPage: 0,
        initialPage: 0,
        total: 0,
        url: null,
        resetpaginatorurl: null,
        initialRecords: {
            default: function(){
                return [];
            }
        },
        initialGroups: {
            default: function(){
                return [];
            }
        }
    },
    data: function () {
        return {
            margin: 5,
            limits: [50, 100, 250, 500, 1000],
            page: this.initialPage,
            perPage: this.initialPerPage,
            groups: this.initialGroups,
            records: this.initialRecords
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
        },
        setPerPage: function (perPage) {
            this.perPage = perPage;

            this.resetpaginatorurl({
                perPage: perPage
            });
        }
    }
});
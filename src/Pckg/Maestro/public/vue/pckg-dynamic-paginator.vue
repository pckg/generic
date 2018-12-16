<template>
    <div class="pckg-dynamic-paginator">
        <div class="pull-left">
            <div class="btn-group btn-group-md dropup" style="display: table; margin: 0 auto;">
                <div class="btn btn-default">
                    <a title="view" href="#">
                        Show: {{ perPage }} items
                    </a>
                </div>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a v-for="limit in limits" href="#" @click.prevent="setPerPage(limit)">{{ limit }} per page</a>
                        <a href="#" @click.prevent="setPerPage('all')">all</a>
                    </li>
                </ul>
            </div>
        </div>

        <div>
            <nav class="text-center" v-if="perPage != 'all' && total > perPage">
                <ul class="pagination pagination-sm">
                    <li>
                        <a :class="{ disabled: page == 1 }" href="#" aria-label="Previous"
                           @click.prevent="prev">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                    </li>
                    <li v-for="(p,i) in pages" :key="i" :class="{ active: p == page }">
                        <a :class="{ disabled: page == '...' }" href="#" @click.prevent="navigate(p)">{{ p
                            }}</a>
                    </li>
                    <li>
                        <a :class="{ disabled: page == Math.ceil(total / perPage) }" href="#" aria-label="Next"
                           @click.prevent="next">
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="clearfix"></div>
    </div>
</template>

<script>
    export default {
        props: {
            initialPerPage: 0,
            initialPage: 0,
            total: 0,
            url: null,
            resetpaginatorurl: null,
            initialRecords: {
                default: function () {
                    return [];
                }
            },
            initialGroups: {
                default: function () {
                    return [];
                }
            }
        },
        data: function () {
            return {
                margin: 5,
                limits: [25, 50, 100, 250, 500, 1000, 5000],
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
    }
</script>
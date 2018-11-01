<template>
    <div class="btn-group btn-group-sm">
        <a type="button" class="entity-action-view dropdown-toggle" href="#"
           title="" data-toggle="dropdown"
           aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-bars" aria-hidden="true"></i> More
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
            <li><a href="#"><i class="fa fa-download"></i> Export</a></li>
            <li><a href="#"><i class="fa fa-upload"></i> Import</a></li>
            <li v-for="action in actions">
                <a href="#" @click.prevent="entityAction(action.event)">
                    <i class="fa" :class="'fa-' + action.icon"></i>
                    {{ action.title }}</a>
            </li>
        </ul>
    </div>

</template>

<script>
    export default {
        name: 'pckg-maestro-table-actions',
        props: {
            table: {
                required: true
            },
            actions: {
                type: Array
            }
        },
        data: function () {
            return {
                //templateRender: null
            };
        },
        /*watch: {
            table: {
                immediate: true,
                handler: function (newTable, oldTable) {
                    console.log(newTable, oldTable);
                    http.get('/api/vue/dynamic/table/' + 1 + '/actions', function (data) {

                        let res = Vue.compile(data.template);

                        this.templateRender = res.render;
                        this.$options.staticRenderFns = [];
                        this._staticTrees = [];
                        if (res.staticRenderFns) {
                            for (var i in res.staticRenderFns) {
                                this.$options.staticRenderFns.push(res.staticRenderFns[i]);
                            }
                        }
                    }.bind(this));
                }
            }
        },
        render: function (h) {
            if (!this.templateRender) {
                return h('div', 'Loading ...');
            }

            return this.templateRender();
        },*/
        methods: {
            entityAction: function (action) {
                return;
                $dispatcher.$emit('entity-action:' + action);
            }
        }
    }
</script>
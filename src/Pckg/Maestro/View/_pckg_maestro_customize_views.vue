<template>
    <div class="c-maestro-customize-views">
        <pckg-select :initial-options="savedViews" :initial-multiple="false" @change="selectView"
                     :flat="true"></pckg-select>
        <!--<h5>
            System views
            <pckg-tooltip icon="question-circle"
                          :content="'You can save custom build views with selected fields and filters for quick access'"></pckg-tooltip>
        </h5>

        <div v-for="view in systemViews">
            <a href="#" @click.prevent="loadView(view)">{{ view.title }}</a>
        </div>

        <h5>
            Saved views
            <pckg-tooltip icon="question-circle"
                          :content="'You can save custom build views with selected fields and filters for quick access'"></pckg-tooltip>
        </h5>

        <div v-for="view in savedViews">
            <a href="#" @click.prevent="loadView(view)">{{ view.title }}</a>
        </div>-->

    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-customize-views',
        props: {
            views: {
                type: Array
            }
        },
        data: function () {
            return {};
        },
        methods: {
            selectView: function (viewId) {
                this.$emit('set-view', this.views[viewId].settings);
            }
        },
        computed: {
            savedViews: function () {
                return this.views.filter(function (view) {
                    return true;
                    return view.type == 'saved';
                }).map(function (view) {
                    return view.title;
                });
            },
            systemViews: function () {
                return this.views.filter(function (view) {
                    return true;
                    return view.type == 'system';
                }).map(function (view) {
                    return view.title;
                });
            }
        }
    }
</script>
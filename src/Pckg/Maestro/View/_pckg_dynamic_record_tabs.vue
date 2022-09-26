<template>
    <div class="pckg-dynamic-record-tabs">
        <div class="display-block">

            <h1 class="h-page-title">
                <router-link to="/maestro" class="__maestro-back-button" title="Go to Dashboard">
                    <i class="fal fa-chevron-left"></i>
                </router-link>

                {{ table.title }}

                <router-link to="/maestro" class="__maestro-back-button" title="Open list">
                    <i class="fal fa-chevron-left"></i>
                </router-link>

                {{ recordIdentifier }}

                <pckg-maestro-actions :record="record"
                                      :actions="actions"
                                      :identifier="table.table"></pckg-maestro-actions>
            </h1>

            <ul class="nav nav-tabs">
                <li>
                    <router-link :to="'/dynamic/records/' + table.id +'/' + record.id + '/' + viewOrEdit" active-class="active">General</router-link>
                </li>
                <li v-for="tab in tabs">
                    <router-link :to="'/dynamic/records/' + table.id +'/' + record.id + '/tab/' + tab.id" active-class="active">
                        {{ tab.title }}
                    </router-link>
                </li>
            </ul>
        </div>

        <div class="clearfix"></div>

        <div class="tab-content">

            <div role="tabpanel" class="tab-pane active">

                <keep-alive>
                    <router-view :key="$route.fullPath"></router-view>
                </keep-alive>

            </div>
        </div>

        <!-- additional components -->
        <component :is="component" v-for="component in uniqueActions" :key="component"></component>

    </div>
</template>

<script>
export default {
    name: 'pckg-dynamic-record-tabs',
    data: function () {
        return {
            localBus: new Vue(), // @deprecated
            viewOrEdit: this.$route.name.indexOf('.edit') > 0 ? 'edit' : 'view',
        };
    },
    computed: {
        table: function () {
            return this.$route.meta.resolved.table;
        },
        record: function () {
            return this.$route.meta.resolved.mappedRecord || this.$route.meta.resolved.record;
        },
        actions: function () {
            return this.$route.meta.resolved.actions;
        },
        tabs: function () {
            return this.$route.meta.resolved.tabs;
        },
        relations: function () {
            return this.$route.meta.resolved.relations;
        },
        mode: function () {
            return this.$route.meta.resolved.mode;
        },
        tabRelations: function () {
            return this.relations.filter((relation) => relation.dynamic_table_tab_id > 0);
        },
        uniqueActions: function () {
            let components = {};
            $.each(this.actions.entity, function (i, action) {
                if (!action.component) {
                    return;
                }
                components[action.component] = action.component;
            });
            $.each(this.actions.record, function (i, action) {
                if (!action.component) {
                    return;
                }
                components[action.component] = action.component;
            });
            return components;
        },
        recordIdentifier: function () {
            let identifier = this.record.id || null;
            $.each(['email', 'title', 'slug', 'identifier', 'num', 'id'], (i, prop) => {
                if (this.record[prop]) {
                    identifier = this.record[prop];
                    return false;
                }
            })

            return identifier;
        }
    }
}
</script>

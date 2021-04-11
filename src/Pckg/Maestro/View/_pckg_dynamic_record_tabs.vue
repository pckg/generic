<template>
    <pckg-loader v-if="loading"></pckg-loader>
    <div v-else class="pckg-dynamic-record-tabs">
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
                <li :class="!selectedTab ? 'active' : ''">
                    <router-link :to="'/dynamic/records/' + table.id +'/' + record.id + '/view'" @click.native="selectTab(null)">General</router-link>
                </li>
                <li v-for="tab in tabs" :class="selectedTab === tab.id ? 'active' : ''">
                    <router-link :to="'/dynamic/records/' + table.id +'/' + record.id + '/tab/' + tab.id" @click.native="selectTab(tab.id)">
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
        <component :is="component" v-for="component in uniqueActions" :key="component"
                   @tab:refresh="selectTab"></component>

    </div>
</template>

<script>
export default {
    name: 'pckg-dynamic-record-tabs',
    data: function () {
        return {
            loading: false,
            selectedTab: null,
            localBus: new Vue()
        };
    },
    watch: {
        '$route': {
            deep: true,
            immediate: true,
            handler: function () {
                this.initialFetch();
            }
        }
    },
    methods: {
        initialFetch: function () {
            //http.get('/api/dynamic/table/' + this.$route.params.table, (data) => {
            //  this.actions = data.actions;
            //this.tabs = data.tabs;
            this.loading = false;
            //});
        },
        selectTab: function (tabId) {
            this.selectedTab = tabId;
            /**
             * Destroy and re-init component?
             */
            /*$dispatcher.$emit('dynamic-tab-' + tabId + ':refresh', {
                tabId: tabId, callback: function (data) {
                    if (data.functionizes) {
                        $(this.$el).find('.tab-functionize').html(data.functionizes.join(''));
                    }
                }.bind(this)
            });*/
        },
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

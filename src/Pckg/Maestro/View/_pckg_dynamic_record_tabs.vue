<template>
    <pckg-loader v-if="loading"></pckg-loader>
    <div v-else class="pckg-dynamic-record-tabs">
        <div class="display-block">
            <h2 class="h-page-title">
                <h2>{{ mode }} {{ table.title }}</h2>

                <pckg-maestro-actions :record="record"
                                      :actions="actions"
                                      :identifier="table.table"></pckg-maestro-actions>
            </h2>

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" :class="!selectedTab ? 'active' : ''">
                    <a href="#home" aria-controls="home" role="tab" data-toggle="tab" @click.prevent="selectTab(null)">General</a>
                </li>
                <li role="presentation" v-for="tab in tabs" :class="selectedTab === tab.id ? 'active' : ''">
                    <a :href="'#dynamic-tab-' + tab.id" :aria-controls="'dynamic-tab-' + tab.id" role="tab"
                       data-toggle="tab"
                       @click.prevent="selectTab(tab.id)">{{ tab.title }}</a>
                </li>
            </ul>
        </div>

        <div class="clearfix"></div>

        <div class="tab-content">

            <div role="tabpanel" class="tab-pane active">

                <div v-if="!selectedTab">
                    <keep-alive>
                        <pckg-maestro-form :table-id="$route.params.table"
                                           :form-model="record"
                                           :mode="mode"></pckg-maestro-form>
                    </keep-alive>
                </div>
                <div v-else>
                    <keep-alive>
                        <div v-for="relation in tabRelations">
                            <pckg-maestro-table :table-id="relation.show_table_id"
                                                :relation-id="relation.id"
                                                :record-id="record.id"></pckg-maestro-table>
                        </div>
                    </keep-alive>
                </div>

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
        }
    },
    computed: {
        table: function () {
            return this.$store.state.generic.metadata.router.table || {};
        },
        record: function () {
            return this.$store.state.generic.metadata.router.record || {};
        },
        actions: function () {
            return this.$store.state.generic.metadata.router.actions || {};
        },
        tabs: function () {
            return this.$store.state.generic.metadata.router.tabs || {};
        },
        relations: function () {
            return this.$store.state.generic.metadata.router.relations || {};
        },
        mode: function () {
            return this.$store.state.generic.metadata.router.mode || {};
        },
        tabRelations: function () {
            return this.relations.filter((relation) => relation.dynamic_table_tab_id == this.selectedTab);
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
        }
    }
}

/*{% for tab in tabs %}
Vue.component('dynamic-tab-{{ tab.id }}', function (resolve) {
    http.getJSON('{{ tabelize.getTabUrl(tab) }}?html=1', function (data) {
        $('body').append(data.vue);

        resolve({
            template: '<div>' + data.html + '</div>',
            mixins: [pckgDelimiters],
            mounted: function () {
                $(this.$el).closest('.tab-pane').find('> .fal').detach();
            }
        });
    });
});
{% endfor %}*/
</script>
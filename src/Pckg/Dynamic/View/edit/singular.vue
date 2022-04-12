<template>

    <div>
        <div class="display-block">

            <h1 class="h-page-title">
                <router-link :to="$root.tenantUrl()" class="__maestro-back-button" title="Go to Dashboard">
                    <i class="fal fa-chevron-left"></i>
                </router-link>

                Add {{ table.titleSingular | lcfirst }}
            </h1>
        </div>

        <pckg-maestro-form :table-id="$route.params.table"></pckg-maestro-form>
    </div>

</template>

<script>

export default {
    methods: {
        initialFetch: function () {
            return;
            http.get('/api/dynamic/table/' + this.$route.params.table, (data) => {
                this.table = data.table;
                this.actions = data.actions;
                this.loading = false;
            });

            if (this.$route.params.record) {
                http.get('/api/dynamic/form/' + this.$route.params.table + '/' + this.$route.params.record, (data) => {

                });
            }
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
    }
}
</script>

<template>
    <div></div>
</template>

<script type="text/javascript">
    export default {
        mixins: [dynamicEvents],
        name: 'pckg-generic-routes-importexport',
        data: function () {
            return {
                record: {},
                structure: '',
                triggers: {
                    importExport: 'record:importExport'
                }
            };
        },
        methods: {
            exportRoute: function () {
                http.getJSON(utils.url('@pckg.generic.pageStructure.routeExport', {route: this.record.id}), function (data) {
                    this.structure = JSON.stringify(data.export);
                    $dispatcher.$emit('notification:success', 'Route exported');
                }.bind(this));
            },
            importRoute: function () {
                http.post(utils.url('@pckg.generic.pageStructure.routeImport', {route: this.record.id}), {'export': this.structure}, function (data) {
                    $dispatcher.$emit('notification:success', 'Route imported');
                }.bind(this));
            },
            importExport: function (record) {
                this.record = record;

                $('#importExportModal').modal('show');
            }
        }
    }
</script>

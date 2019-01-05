<template>
    <div class="pckg-dynamic-delete">

        <pckg-bootstrap-modal :visible="modal == 'delete'" @close="modal == 'visible' ? modal = null : null"
                              class="danger">
            <div class="header">
                Delete record
            </div>
            <div class="body">
                <p>Do you really want to delete #{{ recordIds }}?</p>
                <p><a @click.prevent="deleteRecord" href="#" class="btn btn-danger">Yes, delete record</a></p>
            </div>
        </pckg-bootstrap-modal>

        <pckg-bootstrap-modal :visible="modal == 'delete'" @close="modal == 'visible' ? modal = null : null"
                              class="danger">
            <div class="header">
                Delete translation
            </div>
            <div class="body">
                <p>Do you really want to translation #{{ recordIds }}?</p>
                <p><a @click.prevent="deleteRecordTranslation" href="#" class="btn btn-danger">Yes, delete
                    translation</a></p>
            </div>
        </pckg-bootstrap-modal>
    </div>
</template>

<script>
    var pckgDynamicDelete = Vue.component('pckg-dynamic-delete', {
        mixins: [pckgDelimiters],
        name: 'pckg-dynamic-delete',
        template: '#pckg-dynamic-delete',
        data: function () {
            return {
                records: [],
                identifier: null,
                modal: null
            };
        },
        methods: {
            checkDeleteRecords: function (records, recordIds, identifier) {
                this.identifier = identifier;
                this.records = utils.collect(records);
                this.modal = 'delete';
            },
            checkDeleteRecordTranslation: function (records) {
                this.records = utils.collect(records);
                this.modal = 'deleteTranslation';
            },
            deleteRecord: function () {
                this.modal = null;

                var deleteTimeout = null;
                $.each(this.records, function (i, record) {
                    http.deleteJSON(record.deleteUrl, function () {
                        clearTimeout(deleteTimeout);
                        deleteTimeout = setTimeout(function () {
                            console.log('emiting', 'pckg-maestro-table-' + this.identifier + ':refresh', 'dynamic-tab-' + this.identifier + ':refresh');
                            $dispatcher.$emit('pckg-maestro-table-' + this.identifier + ':refresh');
                            $dispatcher.$emit('dynamic-tab-' + this.identifier + ':refresh');
                        }.bind(this), 333);
                    }.bind(this), function () {
                        $dispatcher.$emit('notification:error', 'Error deleting record');
                    });
                }.bind(this));
            },
            deleteRecordTranslation: function () {
                this.modal = null;

                $.each(this.records, function (i, record) {
                    http.deleteJSON(record.deleteTranslationUrl, function () {
                        $dispatcher.$emit('notification:error', 'Translation deleted');
                    }, function () {
                        $dispatcher.$emit('notification:error', 'Error deleting translation');
                    });
                });
            }
        },
        computed: {
            recordIds: function () {
                return this.records.map(function (item) {
                    return item.id;
                }).join(', ');
            }
        },
        created: function () {
            $dispatcher.$on('record:checkDeleteRecord', this.checkDeleteRecords);
            $dispatcher.$on('record:checkDeleteRecordTranslation', this.checkDeleteRecordTranslation);
            $dispatcher.$on('entity:checkDeleteRecords', this.checkDeleteRecords);
        },
        beforeDestroy: function () {
            $dispatcher.$off('record:checkDeleteRecord', this.checkDeleteRecords);
            $dispatcher.$off('record:checkDeleteRecordTranslation', this.checkDeleteRecordTranslation);
            $dispatcher.$off('entity:checkDeleteRecords', this.checkDeleteRecords);
        }
    });
</script>

<pckg-dynamic-delete></pckg-dynamic-delete>
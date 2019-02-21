<template>
    <div class="pckg-dynamic-delete">

        <pckg-bootstrap-modal :visible="modal == 'delete'" @close="modal = modal == 'delete' ? null : modal"
                              class="danger">
            <div slot="header">
                Delete record
            </div>
            <div slot="body">
                <p>Do you really want to delete #{{ recordIds }}?</p>
                <p><a @click.prevent="deleteRecord" href="#" class="btn btn-danger">Yes, delete record</a></p>
            </div>
        </pckg-bootstrap-modal>

        <pckg-bootstrap-modal :visible="modal == 'deleteTranslation'"
                              @close="modal == 'deleteTranslation' ? modal = null : null"
                              class="danger">
            <div slot="header">
                Delete translation
            </div>
            <div slot="body">
                <p>Do you really want to translation #{{ recordIds }}?</p>
                <p><a @click.prevent="deleteRecordTranslation" href="#" class="btn btn-danger">Yes, delete
                    translation</a></p>
            </div>
        </pckg-bootstrap-modal>
    </div>
</template>

<script>
    export default {
        name: 'pckg-dynamic-delete',
        mixins: [dynamicEvents],
        data: function () {
            return {
                records: [],
                identifier: null,
                modal: null,
                triggers: {
                    checkDeleteRecords: ['record:checkDeleteRecord', 'entity:checkDeleteRecords'],
                    checkDeleteRecordTranslation: 'record:checkDeleteRecordTranslation'
                }
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
                    }.bind(this), function () {
                        $dispatcher.$emit('notification:error', 'Error deleting record');
                    });
                }.bind(this));

                this.$emit('table:refresh');
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
        }
    }
</script>
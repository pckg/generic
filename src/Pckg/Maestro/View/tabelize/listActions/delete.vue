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
            deleteRecord: async function () {
                this.modal = null;

                $dispatcher.$emit('page:percentage', 1);
                let hasSuccess = false;
                let hasError = false;
                let i = 0;
                for (const record of this.records) {
                    await new Promise(function (resolve, reject) {
                        http.deleteJSON(record.deleteUrl, function (data) {
                            hasSuccess = true;
                            resolve(data);
                        }, function () {
                            hasError = true;
                            resolve(false); // resolve on error
                            $dispatcher.$emit('notification:error', 'Error deleting record');
                        });
                    });
                    i++;
                    $dispatcher.$emit('page:percentage', i / this.records.length * 100);
                }

                if (hasSuccess) {
                    if (hasError) {
                        $dispatcher.$emit('notification:warning', 'Some records were deleted, some were not. Please refresh the page.');
                    } else {
                        $dispatcher.$emit('notification:success', this.records.length > 1 ? 'Records deleted' : 'Record deleted');
                    }
                } else {
                    if (hasError) {
                        $dispatcher.$emit('notification:error', this.records.length > 1 ? 'Error deleting records' : 'Error deleting record');
                    } else {
                        $dispatcher.$emit('notification:info', 'Something weird has happened');
                    }
                }

                $dispatcher.$emit('page:loaded');
                this.$emit('table:refresh');
            },
            deleteRecordTranslation: async function () {
                this.modal = null;

                $dispatcher.$emit('page:percentage', 1);
                let hasSuccess = false;
                let hasError = false;
                let i = 0;
                for (const record of this.records) {
                    await new Promise(function(resolve, reject) {
                        http.deleteJSON(record.deleteTranslationUrl, function (data) {
                            hasSuccess = true;
                            resolve(data);
                        }, function () {
                            hasError = true;
                            resolve(false); // resolve on error
                            $dispatcher.$emit('notification:error', 'Error deleting translation');
                        });
                    });
                    i++;
                    $dispatcher.$emit('page:percentage', i / this.records.length * 100);
                }

                if (hasSuccess) {
                    if (hasError) {
                        $dispatcher.$emit('notification:warning', 'Some translations were deleted, some were not. Please refresh the page.');
                    } else {
                        $dispatcher.$emit('notification:success', this.records.length > 1 ? 'Translations deleted' : 'Translation deleted');
                    }
                } else {
                    if (hasError) {
                        $dispatcher.$emit('notification:error', this.records.length > 1 ? 'Error deleting translations' : 'Error deleting translation');
                    } else {
                        $dispatcher.$emit('notification:info', 'Something weird has happened');
                    }
                }

                $dispatcher.$emit('page:loaded');
                this.$emit('table:refresh');
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

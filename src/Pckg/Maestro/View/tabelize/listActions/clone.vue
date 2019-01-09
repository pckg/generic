<template>
    <div>

        <pckg-bootstrap-modal :visible="modal == 'clone'" @close="modal = modal == 'clone' ? null : modal">
            <div slot="header">
                Clone record
            </div>
            <div slot="body">
                <p>Do you really want to clone #{{ record.id }}?</p>

                <p><b>Number of clones: <input type="number" min="1" max="99" step="1" v-model="clones"
                                               class="form-control narrow"/></b></p>

                <p><a @click.prevent="cloneRecord" href="#" class="btn btn-danger">Yes, clone record</a></p>
            </div>
        </pckg-bootstrap-modal>

        <pckg-bootstrap-modal :visible="modal == 'cloned'" @close="modal = modal == 'cloned' ? null : modal">
            <div slot="header">
                Record cloned
            </div>
            <div slot="body">
                <p>#{{ record.id }} was cloned.</p>
                <p><a :href="clonedUrl" class="btn btn-success">Open it</a></p>
            </div>
        </pckg-bootstrap-modal>

    </div>
</template>

<script>
    export default {
        name: 'pckg-dynamic-clone',
        data: function () {
            return {
                record: {},
                clonedUrl: null,
                relations: [],
                clones: 1,
                modal: null
            };
        },
        methods: {
            checkCloneRecord: function (record) {
                this.record = record;
                this.modal = 'clone';
            },
            cloneRecord: function () {
                this.modal = null;

                http.post(this.record.cloneUrl, {clones: this.clones}, function (data) {
                    this.clonedUrl = data.clonedUrl;
                    this.modal = 'cloned';
                }.bind(this));
            }
        },
        created: function () {
            $dispatcher.$on('record:checkCloneRecord', this.checkCloneRecord);
        },
        beforeDestroy: function () {
            $dispatcher.$off('record:checkCloneRecord', this.checkCloneRecord);
        }
    }
</script>
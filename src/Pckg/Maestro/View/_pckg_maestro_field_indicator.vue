<template>
    <i class="pckg-maestro-field-indicator" v-if="visible && iconClass" :class="iconClass"></i>
</template>

<script>
    export default {
        name: 'pckg-maestro-field-indicator',
        props: {
            field: {},
            record: {},
            dbField: {},
        },
        computed: {
            value: function(){
               return this.record[this.field.field];
            },
            visible: function () {
                if (!this.dbField || !this.dbField.fieldType || this.dbField.fieldType.slug != 'select') {
                    return false;
                }

                if (!this.value) {
                    return false;
                }

                return true;
            },
            iconClass: function () {
                let visible = this.visible;
                if (!visible) {
                    return;
                }

                let value = this.value;
                if (['confirmed', 'payed', 'published', 'taken', 'delivered', 'resolving'].indexOf(value) >= 0) {
                    return 'fas fa-circle clr-success';
                }

                if (['reserved', 'sent', 'shipped', 'closed'].indexOf(value) >= 0) {
                    return 'fal fa-circle clr-success';
                }

                if (['submitted', 'allocated'].indexOf(value) >= 0) {
                    return 'fal fa-circle clr-info';
                }

                if (['canceled', 'rejected', 'expired', 'quo'].indexOf(value) >= 0) {
                    return 'fal fa-circle clr-error';
                }

                if (['returned', 'opened', 'high'].indexOf(value) >= 0) {
                    return 'fas fa-circle clr-error';
                }

                if (['basket', 'split', 'none', 'created'].indexOf(value) >= 0) {
                    return 'fal fa-circle';
                }

                return null;
            }
        }
    }
</script>
<i class="fal fa-circle clr-success"></i> Waiting<br/>
<i class="fas fa-circle clr-error"></i> Overdue<br/>
<i class="fal fa-circle"></i> None<br/>
<i class="fas fa-circle clr-success"></i> Confirmed<br/>
<i class="fal fa-circle clr-success"></i> Submitted<br/>
<i class="fal fa-circle"></i> Basket<br/>
<i class="fal fa-circle clr-error"></i> Canceled<br/>
<i class="fal fa-circle clr-error"></i> Rejected<br/>
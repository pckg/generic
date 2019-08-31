<template>
    <div>

        <form-group label="Icon"
                    name="icon"
                    type="select:single"
                    :options="{options: initialOptions.availableIcons}"
                    v-model="iconModel"></form-group>

        <form-group label="Style"
                    name="iconStyle"
                    type="select:single"
                    :options="{options: initialOptions.availableStyles}"
                    v-model="iconStyleModel"></form-group>

    </div>
</template>

<script>
    export default {
        mixins: [pckgActionbuilderSettings],
        name: 'pckg-icon-pagebuilder-action',
        data: function () {
            return {
                initialOptions: {
                    availableIcons: this.config('pckg.generic.actions.derive-content-icon.availableIcons'),
                    availableStyles: this.config('pckg.generic.actions.derive-content-icon.availableStyles'),
                }
            };
        },
        computed: {
            iconModel: pckgComputedSetting('icon'),
            iconStyleModel: pckgComputedSetting('iconStyle'),
        }
    }
</script>
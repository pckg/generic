<template>
    <div>

        <h3 class="__component-title margin-bottom-xs">General</h3>

        <form-group label="Style"
                    name="iconStyle"
                    type="select:single"
                    :options="{options: initialOptions.availableStyles}"
                    help="Select one of font styles"
                    v-model="iconStyleModel"></form-group>

        <form-group label="Icon"
                    name="icon"
                    type="text"
                    help="Enter any icon from Font Awesome"
                    v-model="iconModel"></form-group>

        <h3 class="__component-title margin-bottom-xs">Advanced</h3>

        <form-group label="Fixed width"
                    name="border"
                    type="checkbox"
                    v-model="iconWidthModel"
                    help="Apply font width class .fa-fw"></form-group>

        <form-group label="Border"
                    name="border"
                    type="checkbox"
                    v-model="iconBorderModel"
                    help="Apply default border class .fa-border"></form-group>

        <form-group label="Circle"
                    name="circle"
                    type="checkbox"
                    v-model="iconCircleModel"
                    help="Add 50% border radius and hides overflow - combine this with padding"></form-group>

        <form-group label="Rotate"
                    name="iconRotate"
                    type="select:single"
                    :options="{options: initialOptions.availableRotates}"
                    help="Rotate will not work for inline icons (use inline-block display instead)"
                    v-model="iconRotateModel"></form-group>

        <form-group label="Flip"
                    name="iconFlip"
                    type="select:single"
                    :options="{options: initialOptions.availableFlips}"
                    help="Flip will not work for inline icons (use inline-block display instead)"
                    v-model="iconFlipModel"></form-group>

    </div>
</template>

<script>
    export default {
        mixins: [pckgActionbuilderSettings, pckgScopeManager],
        name: 'pckg-icon-pagebuilder-action',
        data: function () {
            return {
                initialOptions: {
                    availableIcons: this.config('pckg.generic.actions.derive-content-icon.availableIcons'),
                    availableStyles: this.config('pckg.generic.actions.derive-content-icon.availableStyles'),
                    availableRotates: {
                        '': 'Default (no)',
                        'fa-rotate-90': 'Rotate 90 degrees',
                        'fa-rotate-180': 'Rotate 180 degrees',
                        'fa-rotate-270': 'Rotate 270 degrees',
                    },
                    availableFlips: {
                        '': 'Default (no)',
                        'fa-flip-horizontal': 'Flip horizontal',
                        'fa-flip-vertical': 'Flip vertical',
                        'fa-flip-both': 'Flip both',
                    }
                }
            };
        },
        computed: {
            iconModel: pckgComputedSetting('icon'),
            iconStyleModel: pckgComputedSetting('iconStyle'),
            iconBorderModel: pckgComputedScopeModel('fa-border'),
            iconCircleModel: pckgComputedScopeModel('fa-crop-circle'),
            iconWidthModel: pckgComputedScopeModel('fa-fw'),
            iconRotateModel: oneOfListedClassesModel(['fa-rotate-90', 'fa-rotate-180', 'fa-rotate-270']),
            iconFlipModel: oneOfListedClassesModel(['fa-flip-horizontal', 'fa-flip-vertical', 'fa-flip-both']),
        }
    }
</script>
<template>
    <i :class="templateClass + ' ' + iconClass"></i>
</template>

<script>
    export default {
        name: 'pckg-icon',
        mixins: [CommsHub.component],
        data: function () {
            let d = CommsHub.component.data.call(this);
            d.mapper = {
                normal: 'fa',
                solid: 'fas',
                light: 'fal',
                brand: 'fab',
                duotone: 'fad',
            };

            return d;
        },
        computed: {
            icon: function () {
                return this.action.settings.icon;
            },
            iconStyle: function () {
                return this.action.settings.iconStyle;
            },
            iconClass: function () {
                return (this.mapper[this.iconStyle] || 'fa') + ' ' + 'fa-' + this.icon;
            }
        }
    }
</script>

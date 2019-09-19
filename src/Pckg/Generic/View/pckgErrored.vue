<template>
    <span v-if="myErrored" class="pckg-loader text-center" :class="mode">
        <i class="fal fa-exclamation fa-2x"></i>
        <br />
        <p class="clr-error">Error loading content</p>
    </span>
</template>

<script>
    export default {
        name: 'pckg-errored',
        props: {
            errored: {
                default: true
            },
            mode: {
                default: 'display-block'
            }
        },
        watch: {
            errored: function (errored) {
                this.myErrored = errored;
            }
        },
        data: function () {
            return {
                myErrored: this.errored,
            };
        }
    }
</script>
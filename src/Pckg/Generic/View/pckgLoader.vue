<template>
    <span v-if="myLoading" class="pckg-loader text-center" :class="mode">
        <i class="fa fa-spinner-third fa-spin fa-2x"></i>
    </span>
</template>

<script>
    export default {
        name: 'pckg-loader',
        props: {
            loading: {
                default: true
            },
            mode: {
                default: 'display-block'
            }
        },
        watch: {
            loading: function (loading) {
                this.myLoading = loading;
            }
        },
        data: function () {
            return {
                myLoading: this.loading,
            };
        }
    }
</script>
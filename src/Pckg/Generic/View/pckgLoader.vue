<template>
    <div v-if="myLoading" class="pckg-loader display-block text-center">
        <i class="fa fa-spinner-third fa-spin fa-2x"></i>
    </div>
</template>

<script>
    export default {
        name: 'pckg-loader',
        props: {
            loading: {
                default: false
            },
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
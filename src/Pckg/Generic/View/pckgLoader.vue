<template>
    <span v-if="myLoading" class="pckg-loader text-center" :class="mode">
        <i class="fal fa-spinner-third fa-spin fa-fw" :class="'fa-' + size"></i>
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
            },
            size: {
                default: '2x',
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
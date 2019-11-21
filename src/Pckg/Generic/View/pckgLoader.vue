<template>
    <span v-if="myLoading" class="pckg-loader text-center" :class="mode">
        <i class="fal fa-fw" :class="['fa-' + size, icon]"></i>
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
            },
            icon: {
                default: 'fa-spinner-third fa-spin'
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
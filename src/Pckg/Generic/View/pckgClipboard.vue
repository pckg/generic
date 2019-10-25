<template>
    <div class="pckg-clipboard" :class="type == 'simple' ? '' : (type == 'inline' ? '--inline' : 'input-group')">
        <a v-if="type == 'default' && link" class="input-group-addon" :href="text" title="Open in new tab"><i class="fal fa-link"></i></a>
        <input type="text" class="form-control"
               @click.prevent="copyToClipboard"
               data-toggle="tooltip" v-model="text"/>
        <a target="_blank"
           @click.prevent="copyToClipboard"
           :href="text"
           title="Copy link"
           class="__copy-handle"
           :class="type == 'simple' ? '' : 'input-group-addon'"
        ><i class="fal fa-copy"></i></a>
        <slot name="copy"></slot>
    </div>
</template>

<style lang="less" scoped>
    .pckg-clipboard {
        &.--inline {
            display: inline-block;
            .form-control {
                border: none;
                background: transparent;
                box-shadow: none;
                display: inline;
                font-size: inherit;
                line-height: inherit;
                font-weight: bold;
                width: fit-content;
                width: 10rem;
            }
            .__copy-handle {
                display: none;
            }
        }
    }
</style>

<script>
    export default {
        name: 'pckg-clipboard',
        props: {
            type: {
                type: String,
                default: 'default'
            },
            text: {
                type: String,
                default: ''
            },
            link: {
                type: Boolean,
                default: false
            }
        },
        data: function () {
            return {
                error: ''
            };
        },
        methods: {
            copyToClipboard: function () {
                var element = $(this.$el).find('input').get(0);
                var title = 'Please press Ctrl/Cmd+C to copy';

                // is element selectable?
                if (element && element.select) {
                    element.select();

                    try {
                        document.execCommand('copy');
                        //element.blur();
                        title = 'Copied!';
                    } catch (err) {
                        console.log(err);
                    }

                }

                $(element).tooltip({
                    'trigger': 'manual',
                    'title': title
                });

                $(element).tooltip('show').mouseout(function () {
                    $(element).tooltip('hide');
                });
            }
        }
    }
</script>
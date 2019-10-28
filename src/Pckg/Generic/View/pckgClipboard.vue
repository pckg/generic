<template>
    <div class="c-pckg-clipboard"
         v-outer-click="onBodyClick"
         :class="type ? '--' + type : ''">

        <!-- mandatory element -->
        <input type="text"
               class="__copy-element"
               @click.prevent="copyToClipboard"
               data-toggle="tooltip" v-model="text"/>

        <!-- copy handle -->
        <a target="_blank"
           @click.prevent="copyToClipboard"
           :href="text"
           title="Copy to clipboard"
           class="__copy-handle input-group-addon"><i class="fal fa-fw fa-copy"></i></a>

    </div>
</template>

<style lang="less" scoped>
    .c-pckg-clipboard {
        position: relative;
        display: inline-block;

        .__copy-handle {
            padding: 3px;
        }

        .__copy-element {
            position: absolute;
            z-index: -1;
            left: 0;
            right: 0;
            opacity: 0;
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
            onBodyClick: function () {
                var element = $(this.$el).find('input').get(0);
                $(element).tooltip('hide');
                $(this.$el).find('.tooltip').remove();
            },
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
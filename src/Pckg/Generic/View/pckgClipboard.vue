<template>
    <div class="c-pckg-clipboard"
         v-outer-click="onBodyClick"
         data-toggle="tooltip"
         :class="type ? '--' + type : ''">

        <!-- mandatory element -->
        <input type="text"
               class="__copy-element"
               @click.prevent="copyToClipboard"
               v-model="text"/>

        <!-- copy handle -->
        <a target="_blank"
           @click.prevent="copyToClipboard"
           :href="text"
           title="Copy to clipboard"
           class="__copy-handle"><i class="fal fa-fw fa-copy"></i></a>

    </div>
</template>

<style lang="less" scoped>
    .c-pckg-clipboard {
        display: inline-block;
        vertical-align: bottom;
        // min width for a clipboard to work
        width: 3rem;
        text-align: center;

        .__copy-handle {
            max-width: 2rem;
            // this is needed for a clipboard to work
            display: table-cell;
            width: 1%;
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
                let $el = $(this.$el);
                $el.tooltip('hide');
                $el.find('.tooltip').remove();
            },
            copyToClipboard: function () {
                let input = $(this.$el).find('input').get(0);
                let title = 'Please copy manually';

                // is element selectable?
                let $el = $(this.$el);
                if (input && input.select) {
                    input.select();

                    try {
                        document.execCommand('copy');
                        //element.blur();
                        title = 'Copied!';
                    } catch (err) {
                        console.log(err);
                    }

                }

                $el.tooltip({
                    'trigger': 'manual',
                    'title': title
                });

                $el.tooltip('show').mouseout(function () {
                    $el.tooltip('hide');
                });
            }
        }
    }
</script>
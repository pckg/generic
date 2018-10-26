<template>
    <div class="pckg-clipboard" :class="type == 'simple' ? '' : 'input-group'">
        <a v-if="type == 'default' && link" class="input-group-addon" :href="text" title="Open in new tab"><i class="fa fa-link"></i></a>
        <input type="text" class="form-control"
               @click.prevent="copyToClipboard"
               data-toggle="tooltip" v-model="text"/>
        <a target="_blank" @click.prevent="copyToClipboard" :href="text" title="Copy link"
           :class="type == 'simple' ? '' : 'input-group-addon'"
        ><i class="fa fa-copy"></i></a>
    </div>
</template>

<script>
    export default {
        name: 'pckg-clipboard',
        mixins: [pckgDelimiters],
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
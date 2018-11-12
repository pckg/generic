<template>
    <div class="btn-group btn-group-sm pull-right">

        <button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                class="btn btn-default dropdown-toggle"><span class="caret"></span> <span class="sr-only">Toggle Dropdown</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-right">
            <li v-for="action in actions.record"
                v-if="action.recordHref && record[action.recordHref] || action.event">
                <a v-if="action.recordHref && record[action.recordHref]"
                   :href="record[action.recordHref]">
                    <i class="fa" :class="'fa-' + action.icon"></i>
                    {{ action.title }}
                </a>
                <a v-else-if="action.event" href="#"
                   @click.prevent="recordAction(record, action.event)">
                    <i class="fa" :class="'fa-' + action.icon"></i>
                    {{ action.title }}
                </a>
            </li>
        </ul>

    </div>
</template>

<script>
    export default {
        mixins: [pckgDelimiters],
        name: 'pckg-maestro-actions',
        template: '#pckg-maestro-actions',
        props: {
            actions: {},
            record: {
                type: Object,
                required: true
            },
            recordactionhandler: {
                /*type: Function,
                 required: true*/
            },
            identifier: {
                default: '',
                type: String
            }
        },
        methods: {
            recordAction: function (record, action) {
                $dispatcher.$emit('record:' + action, record, record.id, this.identifier);
            }
        },
        created: function () {
            /**
             * This is annoying
             * .dropdown-backdrop should have display: none
             */
            Vue.nextTick(function () {
                $(this.$el).find('.dropdown-toggle').each(function () {
                    $(this).on('mouseenter', function () {
                        $(this).click();
                        $(this).parent().on('mouseleave', function () {
                            $("body").trigger("click");
                        });
                    });
                });
            }.bind(this));
        }
    };
</script>
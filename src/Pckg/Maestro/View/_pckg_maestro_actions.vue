<template>
    <div class="btn-group btn-group-sm pull-right">

        <a href="#"
           type="button"
           data-toggle="dropdown"
           aria-haspopup="true"
           aria-expanded="false"
           class="__maestro-back-button"
           title="See more options">
            <i class="fal fa-fw fa-chevron-double-down"></i>
        </a>

        <!--<button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                class="__maestro-back-button">
            <i class="fal fa-fw fa-ellipsis-v"></i>
        </button>-->

        <ul class="dropdown-menu dropdown-menu-right">
            <li v-for="action in actions.record"
                v-if="action.recordHref && record[action.recordHref] || action.event">
                <pb-link v-if="action.recordHref && record[action.recordHref]"
                   :to="record[action.recordHref]">
                    <i class="fa fa-fw" :class="'fa-' + action.icon"></i>
                    {{ action.title }}
                </pb-link>
                <a v-else-if="action.event" href="#"
                   @click.prevent="recordAction(record, action.event)">
                    <i class="fa fa-fw" :class="'fa-' + action.icon"></i>
                    {{ action.title }}
                </a>
            </li>
        </ul>

    </div>
</template>

<script>
    export default {
        name: 'pckg-maestro-actions',
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
                this.$parent.localBus.$emit('record:' + action, record, record.id, this.identifier);
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

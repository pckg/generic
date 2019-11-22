<template>
    <div class="c-dispatcher-notifications">

        <!-- support for multiple positions -->
        <div class="pckg-dispatcher-notifications-wrapper"
             v-for="pos in positions" :class="'--position-' + (pos || 'default')">

            <!-- render alert -->
            <div class="alert alert-dismissible animated" role="alert"
                 :class="alertClass(notification)"
                 v-for="notification in position(pos)">

                <!-- show icon if set -->
                <div class="col" v-if="notification.icon">
                    <i class="fal fa-fw" :class="notification.icon"></i>
                </div>

                <!-- notification content -->
                <div class="col" v-html="notification.content"></div>

                <!-- show confirm or close icon-->
                <div class="col">
                    <a v-if="notification.onConfirm" type="button" class="btn btn-primary" data-dismiss="alert"
                       aria-label="Close">
                        <span aria-hidden="true" @click.prevent="confirmNotification(notification)">GOT IT</span>
                    </a>
                    <button v-else type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true" @click="removeNotification(notification)">&times;</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-dispatcher-notifications',
        mixins: [pckgTimeout],
        data: function () {
            return {
                notifications: [],
                positions: [null, 'top-center']
            };
        },
        methods: {
            removeNotification: function (notification) {
                utils.splice(this.notifications, notification);
            },
            confirmNotification: function (notification) {
                if (notification.onConfirm) {
                    notification.onConfirm();
                }
                this.removeNotification(notification);
            },
            alertClass: function (notification) {
                let alertType = 'alert-' + notification.type;
                let animationType = notification.type === 'danger' ? 'shake' : 'fadeInUp';
                return `${alertType} ${animationType}`;
            },
            iconClass: function (notification) {
                return notification.type == 'danger'
                    ? 'fa-exclamation'
                    : (notification.type == 'success'
                            ? 'fa-check'
                            : 'fa-info'
                    );
            },
            createNotification: function (msg, type) {
                if (typeof msg === 'object') {
                    msg.type = msg.type || type;
                    return msg;
                }

                return {
                    content: msg,
                    type: type,
                    icon: this.iconClass({type: type})
                };
            },
            timeoutClose: function (notification) {
                if (notification.onConfirm) {
                    return;
                }

                this.timeout('autoclose', function () {
                    this.removeNotification(notification);
                }.bind(this), notification.timeout || 5000, notification);
            },
            processMsg: function (msg, type) {
                let notification = this.createNotification(msg, type);
                this.notifications.push(notification);
                this.timeoutClose(notification, type);
            },
            position: function (position) {
                return this.notifications.filter(function (notification) {
                    if (!position) {
                        return !notification.position;
                    }

                    return notification.position === position;
                });
            }
        },
        created: function () {
            $dispatcher.$on('notification:success', function (msg) {
                this.processMsg(msg, 'success');
            }.bind(this));

            $dispatcher.$on('notification:error', function (msg) {
                this.processMsg(msg, 'danger');
            }.bind(this));

            $dispatcher.$on('notification:info', function (msg) {
                this.processMsg(msg, 'info');
            }.bind(this));
        }
    }
</script>
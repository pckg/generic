<template>
    <div class="pckg-dispatcher-notifications-wrapper">
        <div 
            class="alert alert-dismissible animated" role="alert"
            :class="alertClass(notification)"
            v-for="notification in notifications">

            <div class="col">
                <i class="fas fa-fw" :class="iconClass(notification)"></i>
            </div>
            <div class="col" v-html="notification.content"></div>
            <div class="col">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true" @click="removeNotification(notification)">&times;</span>
                </button>
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
                notifications: []
            };
        },
        methods: {
            removeNotification: function (notification) {
                utils.splice(this.notifications, notification);
            },
            alertClass: function(notification) {
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
            }
        },
        created: function () {
            $dispatcher.$on('notification:success', function (msg) {
                var notification = {
                    content: msg,
                    type: 'success'
                };
                this.notifications.push(notification);
                this.timeout('autoclose', function () {
                    this.removeNotification(notification);
                }.bind(this), 5000, notification);
            }.bind(this));

            $dispatcher.$on('notification:error', function (msg) {
                var notification = {
                    content: msg,
                    type: 'danger'
                };
                this.notifications.push(notification);
                this.timeout('autoclose', function () {
                    this.removeNotification(notification);
                }.bind(this), 10000, notification);
            }.bind(this));

            $dispatcher.$on('notification:info', function (msg) {
                var notification = {
                    content: msg,
                    type: 'info'
                };
                this.notifications.push(notification);
                this.timeout('autoclose', function () {
                    this.removeNotification(notification);
                }.bind(this), 10000, notification);
            }.bind(this));
        }
    }
</script>
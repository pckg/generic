<template>
    <div class="pckg-dispatcher-notifications-wrapper">
        <div class="alert alert-dismissible alert-fixed" role="alert"
             :class="'alert-' + notification.type"
             v-for="notification in notifications">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true" @click="removeNotification(notification)">&times;</span></button>
            <div v-html="notification.content"></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-dispatcher-notifications',
        mixins: [pckgTimeout, pckgDelimiters],
        data: function () {
            return {
                notifications: []
            };
        },
        methods: {
            removeNotification: function (notification) {
                this.notifications.splice(this.notifications.indexOf(notification), 1);
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
                }.bind(this), 5000, notification);
            }.bind(this));
        }
    }
</script>
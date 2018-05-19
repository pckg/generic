<template>
    <div class="pckg-ping"></div>
</template>

<script>
    export default {
        name: 'pckg-ping',
        mixins: [pckgInterval],
        data: function () {
            return {
                lastActivity: moment().unix(),
                lastPing: moment().unix(),
                diff: 5 * 60 // 5 minutes
            };
        },
        created: function () {
            $('body').on('mousemove', this.updateLastActivity);
            this.interval('someInterval', this.performLoginCheck, this.diff * 1000);
        },
        methods: {
            updateLastActivity: function () {
                this.lastActivity = moment().unix();
                if (this.lastActivity - this.lastPing > this.diff) {
                    this.performLoginCheck();
                }
            },
            performLoginCheck: function () {
                this.lastPing = moment().unix();
                http.get($store.state.router.urls['api.auth.user'], function (data) {
                    if (data.loggedIn) {
                        return;
                    }

                    confirm('Your session expired, please login in another window');
                    this.performLoginCheck();
                }.bind(this));
            }
        }
    }
</script>
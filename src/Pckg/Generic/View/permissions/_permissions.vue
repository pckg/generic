<template>
    <div class="pckg-generic-permissions">
        <table class="table">
            <thead>
            <tr>
                <th>Groups</th>
                <th v-for="group in groups" v-bind:colspan="actions.length">{{ group }}</th>
            </tr>
            <tr>
                <th>Permissions</th>
                <template v-for="group in groups">
                    <th v-for="action in actions">{{ action }}</th>
                </template>
            </tr>
            </thead>
            <tbody>
            <tr v-for="record in records">
                <td>{{ record.name }}</td>
                <template v-for="(group,gk) in groups">
                    <td v-for="(action,ak) in actions">
                        <input type="checkbox" v-model="permissions[ak][gk]" value="1"/>
                    </td>
                </template>
            </tr>
            </tbody>
        </table>

        <button class="btn btn-success btn-block" @click.prevent="savePermissions">Save permissions</button>
    </div>
</template>

<script type="text/javascript">
    export default {
        name: 'pckg-generic-permissions',
        props: {
            type: {
                required: true
            },
            id: {
                required: true
            }
        },
        data: function () {
            return {
                permissionsModel: this.type,
                groups: {},
                permissions: {},
                actions: {},
                records: []
            };
        },
        methods: {
            fetchData: function () {
                http.get('/api/permissions?for=' + this.type + '&id=' + this.id, function (data) {
                    this.groups = data.groups;
                    this.actions = data.actions;
                    this.records = data.records;
                    var permissions = {};
                    $.each(this.actions, function (actionKey, action) {
                        permissions[actionKey] = permissions[actionKey] || {};
                        $.each(this.groups, function (groupKey, group) {
                            permissions[actionKey][groupKey] = !!(data.permissions[actionKey] && data.permissions[actionKey][groupKey]);
                        });
                    }.bind(this));
                    this.permissions = permissions;
                }.bind(this));
            },
            togglePermission: function (ak, gk) {
                this.permissions[ak][gk] = this.permissions[ak][gk] ? false : true;
            },
            savePermissions: function () {
                http.post('/api/permissions?for=' + this.type + '&id=' + this.id, {permissions: this.permissions}, function (data) {

                }.bind(this));
            }
        },
        created: function () {
            this.fetchData();
        }
    }
</script>
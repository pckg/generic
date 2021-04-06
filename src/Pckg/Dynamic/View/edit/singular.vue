<template>
    <pckg-loader v-if="loading"></pckg-loader>
    <div class="pckg-dynamic-record-singular" v-else>

        <!--{{ formalize | raw }}-->
        <pckg-maestro-form :table-id="$route.params.table"
                           :form-model="record"></pckg-maestro-form>

        <!--{% for tabelize in tabelizes.0 %}
            {{ tabelize | raw }}
        {% endfor %}

        {% for functionize in functionizes.0 %}
            {{ functionize | raw }}
        {% endfor %}-->
    </div>
</template>

<script>

const routerLoading = function (props) {
    let computed = {
        loading: function () {
            let loading = false;

            $.each(props, (key, value) => {
                if (!this[key]) {
                    loading = true;
                    return false;
                }
            });

            return loading;
        }
    };

    $.each(props, function (prop, val) {
        val = JSON.parse(JSON.stringify(val));
        computed[prop] = function () {
            return this.$store.state.generic.metadata.router[prop] || val;
        };
    });

    return computed;
};

export default {
    data: function () {
        return {
            formalize: {},
        };
    },
    watch: {
        '$route': {
            deep: true,
            immediate: true,
            handler: function () {
                this.initialFetch();
            }
        }
    },
    methods: {
        initialFetch: function () {
            return;
            http.get('/api/dynamic/table/' + this.$route.params.table, (data) => {
                this.table = data.table;
                this.actions = data.actions;
                this.loading = false;
            });

            if (this.$route.params.record) {
                http.get('/api/dynamic/form/' + this.$route.params.table + '/' + this.$route.params.record, (data) => {

                });
            }
        },
    },
    computed: routerLoading({table: {}, record: {}, actions: {}, mode: 'view'}),
}
</script>

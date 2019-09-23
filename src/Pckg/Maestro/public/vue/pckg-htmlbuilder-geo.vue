<template>
    <div class="pckg-htmlbuilder-geo">
        <button type="button" @click.prevent="openMap()" class="btn btn-info btn-md" title="Open map">
            <i class="fal fa-globe" aria-hidden="true"></i>
        </button>

        <pckg-bootstrap-modal :visible="modal" @close="modal = null">
            <div slot="header">Select location</div>
            <div slot="body">
                <vue-component-gmaps selector="#gmap" id="gmap" theme="base" :search="search" :ref="'gmap'"
                                     center="46.055144;14.512284" :zoom="10"
                                     :locations="locations" v-model="value"></vue-component-gmaps>

                <form-group type="text" label="Search" v-model="search"></form-group>

                <button type="button" class="btn btn-success" data-dismiss="modal" @click.prevent="selectAndClose">
                    Select and close
                </button>
            </div>
        </pckg-bootstrap-modal>
    </div>
</template>

<script>
    export default {
        props: {
            value: {}
        },
        data: function () {
            return {
                search: null,
                modal: null
            };
        },
        computed: {
            locations: function () {
                var locations = [];
                if (this.value) {
                    locations.push({geo: this.geoValue});
                }
                return locations;
            },
            geoValue: function () {
                return this.value.split(';');
            }
        },
        methods: {
            openMap: function () {
                var value = $(this.$el).parent().find('input.geo').val();
                if (value) {
                    this.$refs.gmap.setCenter(value);
                }
                this.$refs.gmap.singletonMap();
                this.modal = true;
            },
            selectAndClose: function () {
                var val = this.$refs.gmap.getLocation().geo.join(';');
                /**
                 * Change form's data/model, unit.geo.
                 */
                this.$emit('change', val);
                this.$emit('input', val);
            }
        },
        created: function () {
            this.$nextTick(function () {
                //$(this.$el).parent().find('input.geo').on('focus', this.openMap);
            });
        }
    }
</script>
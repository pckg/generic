<template>
    <div class="pckg-select" :class="styleClass">
        <select v-if="multiple" class="form-control" multiple v-model="selected">
            <option value v-if="withEmpty"> -- select value(s) --</option>
            <option v-for="(option, key) in finalOptions" :value="key" v-html="option"></option>
            <optgroup v-for="(optgroup, label) in finalOptionGroups" :label="label">
                <option v-for="(option, key) in optgroup" :value="key" v-html="option"></option>
            </optgroup>
        </select>
        <select v-else class="form-control" v-model="selected">
            <option value v-if="withEmpty"> -- select value --</option>
            <option v-for="(option, key) in finalOptions" :value="key" v-html="option"></option>
            <optgroup v-for="(optgroup, label) in finalOptionGroups" :label="label">
                <option v-for="(option, key) in optgroup" :value="key" v-html="option"></option>
            </optgroup>
        </select>
    </div>
</template>

<script>
    export default {
        mixins: [pckgTimeout],
        name: 'pckg-select',
        model: {
            prop: 'selected',
            event: 'input'
        },
        data: function () {
            return {
                options: this.initialOptions
            };
        },
        props: {
            title: {
                default: ''
            },
            id: {
                default: '',
                type: String
            },
            flat: {
                default: false,
                type: Boolean
            },
            withEmpty: {
                default: true
            },
            initialOptions: {
                default: function () {
                    return [];
                }
            },
            selected: {
                default: function () {
                    return [];
                }
            },
            initialMultiple: {
                default: true,
                type: Boolean
            },
            refreshUrl: {
                type: String,
                default: ''
            },
            styleClass: {
                default: '',
                type: String
            }
        },
        computed: {
            finalOptions: function () {
                var options = {};

                $.each(this.options, function (key, item) {
                    if (this.flat) {
                        options[this.getId(item, key)] = this.getTitle(item, key);
                        return;
                    }

                    if (typeof item != 'string') {
                        return;
                    }

                    options[this.getId(item, key)] = this.getTitle(item, key);
                }.bind(this));

                return options;
            },
            finalOptionGroups: function () {
                if (this.flat) {
                    return {};
                }

                var options = {};
                $.each(this.options, function (key, item) {
                    if (typeof item == 'string') {
                        return;
                    }

                    if (Array.isArray(item)) {
                        $.each(item, function (subKey, subItem) {
                            var k = this.getId(subItem, subKey);
                            if (!options[key]) {
                                options[key] = {};
                            }
                            options[key][k] = this.getTitle(subItem, subKey);
                        }.bind(this));
                    } else {
                        options[this.getId(item, key)] = this.getTitle(item, key);
                    }
                }.bind(this));

                return options;
            },
            multiple: function () {
                return this.initialMultiple;
            }
        },
        watch: {
            selected: function (newVal, oldVal) {
                this.refreshPicker(newVal);
            },
            options: function (newVal) {
                Vue.nextTick(function () {
                    $(this.$el).find('select').selectpicker('refresh');
                }.bind(this));
            }
        },
        methods: {
            getTitle: function (option, key) {
                if (typeof option == 'string') {
                    return option;
                }

                if (typeof this.title == 'function') {
                    return this.title(option);
                }

                if (!this.title.length) {
                    return option;
                }

                return option[this.title];
            },
            getId: function (option, id) {
                if (!this.id.length) {
                    return id;
                }

                if (typeof option != 'object') {
                    return option;
                }

                return option[this.id];
            },
            changed: function () {
                this.refreshPicker(this.selected);
            },
            refreshPicker: function (val) {
                this.$emit('input', val); // v-model
                this.$emit('change', val); // change event
                /*Vue.nextTick(function () {
                    $(this.$el).find('select').trigger('vue.change', val);
                    $(this.$el).find('select').selectpicker('refresh');
                }.bind(this));*/
            },
            refreshList: function () {
                this.timeout('refreshList', function () {
                    if (!this.refreshUrl || this.refreshUrl.length < 1) {
                        return;
                    }

                    /**
                     * @T00D00 - we should keep already selected options.
                     */
                    var search = $(this.$el).find('.bs-searchbox input').val();
                    if (search.length < 1) {
                        return;
                    }

                    http.getJSON(this.refreshUrl + '?search=' + search + '&selected=' + (Array.isArray(this.selected) ? this.selected.join(',') : this.selected), function (data) {
                        if (false && Object.keys(data).length == 1) {
                            this.options = data[Object.keys(data)[0]];
                        } else {
                            this.options = data.records;
                        }
                    }.bind(this));
                }.bind(this), 333);
            },
            initPicker: function () {
                var selectpicker = $(this.$el).find('select').selectpicker({
                    liveSearch: true,
                    actionsBox: true,
                    //dropdownAlignRight: 'auto',
                    liveSearchNormalize: true,
                    //mobile: true,
                    //width: 'auto'
                });

                /*selectpicker.on('changed.bs.select', function() {
                    return false;
                });*/

                selectpicker.selectpicker('refresh');

                $(document).ready(function () {
                    $(this.$el).find('select').on('change', function () {
                        this.$nextTick(function () {
                            this.$emit('input', $(this.$el).find('select').val());
                        }.bind(this));
                    }.bind(this));
                }.bind(this));

                $(this.$el).find('.bs-searchbox input').on('keyup', function () {
                    this.refreshList();
                }.bind(this));
            }
        },
        mounted: function () {
            this.$nextTick(this.initPicker);

            /**
             * Initial fetch.
             */
            if ((!this.options || this.options.length == 0) && (!this.refreshUrl || this.refreshUrl.length > 0)) {
                this.refreshList();
            }
        }
    }
</script>
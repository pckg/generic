<template>
    <div class="pckg-select" :class="styleClass">
        <i v-if="loading" class="fa fa-spin fa-spinner position-absolute"></i>
        <select v-if="multiple" class="form-control" multiple v-model="selectedModel" :name="name">
            <option value v-if="withEmpty"> -- select value(s) --</option>
            <option v-for="(option, key) in finalOptions" :value="key" v-html="option"></option>
            <optgroup v-for="(optgroup, label) in finalOptionGroups" :label="label">
                <option v-for="(option, key) in optgroup" :value="key" v-html="option"></option>
            </optgroup>
        </select>
        <select v-else class="form-control" v-model="selectedModel" :name="name">
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
                options: this.initialOptions,
                selectedModel: this.makeModel(this.selected),
                loading: false
            };
        },
        props: {
            title: {
                default: ''
            },
            watchInitial: {
                default: true,
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
            },
            name: {
                type: String,
                default: ''
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
                this.selectedModel = this.makeModel(newVal);
            },
            initialMultiple: function (newVal, oldVal) {
                this.multiple = newVal;
                this.selectedModel = this.makeModel(this.selected);
            },
            selectedModel: function (newVal, oldVal) {
                console.log('model changed');
                this.$emit('input', newVal); // v-model
            },
            initialOptions: function (newVal, oldVal) {
                if (newVal == oldVal || newVal == this.options || !this.watchInitial) {
                    console.log('same options');
                    return;
                }
                console.log('initial options changed', newVal, oldVal);
                this.options = newVal;
                this.refreshPicker();
            }
        },
        methods: {
            makeModel: function (value) {
                return this.multiple
                    ? (Array.isArray(value) ? value : [value])
                    : value;
            },
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
            refreshPicker: function (val) {
                Vue.nextTick(function () {
                    console.log('refreshing');
                    try {
                        $(this.$el).find('select').selectpicker('refresh');
                    } catch (e) {
                        console.log(e);
                    }
                }.bind(this));
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

                    this.loading = true;
                    http.getJSON(this.refreshUrl + '?search=' + search + '&selected=' + (Array.isArray(this.selectedModel) ? this.selectedModel.join(',') : this.selectedModel), function (data) {
                        this.options = data.records;
                        this.refreshPicker();
                        this.loading = false;
                    }.bind(this), function () {
                        this.loading = false;
                    });
                }.bind(this), 333);
            },
            initPicker: function () {
                var selectpicker = $(this.$el).find('select').selectpicker({
                    liveSearch: true,
                    actionsBox: true,
                    dropupAuto: false,
                    //dropdownAlignRight: 'auto',
                    liveSearchNormalize: true,
                    //mobile: true,
                    //width: 'auto',
                    showTick: true
                });

                this.refreshPicker();

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
            if ((!this.options || this.options.length == 0) && this.refreshUrl && this.refreshUrl.length > 0) {
                this.refreshList();
            }
        }
    }
</script>
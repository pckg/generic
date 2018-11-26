<template>
    <div class="pckg-select" :class="styleClass">
        <div v-show="false">
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

        <div class="btn-group">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i v-if="loading" class="fa fa-spin fa-spinner position-absolute"></i>
                <template v-if="initialMultiple && selected && selected.length > 1">({{ selected.length }})</template>
                {{ selectedTitle }} <span class="caret text-right"></span>
            </a>
            <ul class="dropdown-menu" :style="maxHeightStyle">
                <li v-if="(refreshUrl && refreshUrl.length > 0) || (options && Object.keys(options).length > 10)">
                    <input type="text" class="form-control input-sm" v-model="search" placeholder="Search ..."/>
                </li>
                <li v-if="!initialMultiple && withEmpty"><a href="#" @click.prevent="toggleOption($event, null)"> - </a></li>
                <li v-for="(option, key) in finalOptions">
                    <a href="#" @click.prevent="toggleOption($event, key)">
                        <span class="text-left">{{ option}}</span>
                        <i class="fa fa-check pull-right" v-if="isValueSelected(key)"></i>
                    </a>
                </li>
                <template v-for="(optgroup, label) in finalOptionGroups">
                    <li><b>{{ label }}</b></li>
                    <li v-for="(option, key) in optgroup">
                        <a href="#" @click.prevent="toggleOption($event, key)">
                            <span class="text-left">{{ option}}</span>
                            <i class="fa fa-check pull-right" v-if="isValueSelected(key)"></i>
                        </a>
                    </li>
                </template>
            </ul>
        </div>
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
                loading: false,
                search: ''
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
            },
            name: {
                type: String,
                default: ''
            }
        },
        computed: {
            maxHeightStyle: function() {
                return null;
                let myBottom = parseInt($(this.$el).offset().top) - parseInt($(this.$el).outerHeight());
                let bodyBottom = parseInt($('body').outerHeight());
                let h = bodyBottom - myBottom;

                console.log(h);
                return {'max-height': h + 'px'};
            },
            selectedTitle: function () {
                let selected = Array.isArray(this.selectedModel) ? this.selectedModel : [this.selectedModel];
                let titles = [];

                $.each(this.finalOptions, function (i, option) {
                    if (selected.indexOf(i) >= 0) {
                        titles.push(option);
                    }
                });
                $.each(this.finalOptionGroups, function (i, optionGroup) {
                    $.each(optionGroup, function (j, option) {
                        if (selected.indexOf(j) >= 0) {
                            titles.push(option);
                        }
                    });
                });

                if (titles.length == 0) {
                    return ' - - select value - - ';
                }

                let joined = titles.join(', ');

                if (joined.length > 40) {
                    return joined.substring(0, 40) + ' ...';
                }

                return joined;
            },
            finalOptions: function () {
                return this.extractOptions(this.options);
            },
            finalOptionGroups: function () {
                if (this.flat) {
                    return {};
                }

                return this.extractOptionGroups(this.options);
            },
            multiple: function () {
                return this.initialMultiple;
            }
        },
        watch: {
            search: function () {
                this.refreshList();
            },
            selected: function (newVal, oldVal) {
                if (!this.initialMultiple) {
                    this.selectedModel = newVal;
                    return;
                }

                if (!newVal) {
                    this.selectedModel = [];
                    return;
                }

                this.selectedModel = Array.isArray(newVal) ? newVal : [newVal];
            },
            selectedModel: function (newVal, oldVal) {
                console.log('selected to', newVal);
                this.$emit('input', newVal);
            },
            options: function (newVal) {
            },
            initialOptions: function (newVal) {
                console.log('initial options changed', newVal, this.options);
                if (Object.keys(newVal) != Object.keys(this.options)) {
                    this.options = this.mergeOptions(newVal);
                }
            },
            initialMultiple: function (newVal) {
                if (newVal && !Array.isArray(this.selectedModel)) {
                    this.selectedModel = this.selectedModel ? [this.selectedModel] : [];
                } else if (!newVal && Array.isArray(this.selectedModel)) {
                    this.selectedModel = this.selectedModel[0];
                }
            }
        },
        methods: {
            isOptionFiltered: function(item, key){
                if (!this.search || this.search.length == 0) {
                    return false;
                }

                return item.toLowerCase().indexOf(this.search.toLowerCase()) < 0
                    && key.toLowerCase().indexOf(this.search.toLowerCase()) < 0;
            },
            extractOptions: function (o) {
                var options = {};

                $.each(o, function (key, item) {
                    if (this.isOptionFiltered(key, item)) {
                        return;
                    }
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
            extractOptionGroups: function (o) {
                var options = {};
                $.each(this.options, function (key, item) {
                    if (typeof item == 'string') {
                        return;
                    }

                    if (this.isOptionFiltered(key, item)) {
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
                this.selectedModel = this.makeModel(this.selected);
            },
            selectedModel: function (newVal, oldVal) {
                console.log('model changed');
                this.$emit('input', newVal); // v-model
            },
            initialOptions: function (newVal, oldVal) {
                if (newVal == oldVal || newVal == this.options) {
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
            extractFlatOptions: function (o) {
                let options = this.extractOptions(o);
                $.each(this.extractOptionGroups(o), function (i, group) {
                    $.each(group, function (j, option) {
                        options[j] = option;
                    });
                });
                return options;
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
            toggleOption: function ($event, key) {
                if (this.initialMultiple) {
                    if (!this.selectedModel) {
                        this.selectedModel = key ? [key] : [];
                    } else {
                        let i = this.selectedModel.indexOf(key);
                        if (i >= 0) {
                            this.selectedModel.splice(i, 1);
                        } else {
                            if (!this.selectedModel) {
                                this.selectedModel = [key];
                            } else {
                                this.selectedModel.push(key);
                            }
                        }
                    }
                } else {
                    this.selectedModel = this.selectedModel == key ? null : key;
                }

                if (this.initialMultiple) {
                    $event.stopPropagation();
                }
            },
            refreshList: function () {
                this.timeout('refreshList', function () {
                    if (!this.refreshUrl || this.refreshUrl.length < 1) {
                        return;
                    }

                    this.loading = true;
                    http.getJSON(this.refreshUrl + '?search=' + this.search + '&selected=' + (Array.isArray(this.selectedModel) ? this.selectedModel.join(',') : this.selectedModel), function (data) {
                        this.options = data.records;
                        this.refreshPicker();
                        this.loading = false;
                    }.bind(this), function () {
                        this.loading = false;
                    });
                }.bind(this), 333);
            },
            isValueSelected: function (val) {
                return this.initialMultiple ? this.selectedModel && this.selectedModel.indexOf(val) >= 0 : (val == this.selectedModel);
            },
            mergeOptions: function (newOptions) {
                let selected = this.initialMultiple ? this.selectedModel : [this.selectedModel];

                /**
                 * Find options that are selected but does not exist in collection.
                 */
                let allOptions = this.extractFlatOptions(this.options);
                $.each(selected, function (i, val) {
                    if (!newOptions[val] && allOptions[val]) {
                        console.log('non existent', val, selected);
                        newOptions[val] = allOptions[val];
                    }
                });

                return newOptions;
            }
        },
        mounted: function () {
            /**
             * Initial fetch.
             */
            if ((!this.options || this.options.length == 0) && this.refreshUrl && this.refreshUrl.length > 0) {
                // this.refreshList();
            }
            console.log($(this.$el), this);
        }
    }
</script>
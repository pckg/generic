<template>
    <div class="pckg-select" :class="[styleClass, isDisabled ? 'is-disabled' : '']">
        <div class="btn-group">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" @click="focusSearch">
                <i v-if="loading" class="fal fa-spin fa-spinner-third position-absolute"></i>
                <template v-if="hasMultipleSelected">({{ selected.length }})</template>
                {{ selectedTitle }} <i class="__more-icon far fa-chevron-down pull-right"
                                       :class="isDisabled ? 'color-grayish' : ''"></i>
            </a>
            <ul class="dropdown-menu" :style="maxHeightStyle" v-if="!isDisabled">
                <li class="no-hover"
                    v-if="hasSearch && ((refreshUrl && refreshUrl.length > 0) || (options && Object.keys(options).length > 10))">
                    <input type="text" class="form-control input-sm"
                           v-model="search"
                           :placeholder="searchPlaceholder"
                           @keydown.enter="selectFirst"/>
                </li>
                <li v-if="!isRequired" @click.prevent="toggleOption($event, null)">
                    <a href="#" @click.prevent class="color-grayish">
                        {{ withEmpty }}
                    </a>
                </li>
                <li v-for="(option, key) in finalOptions" :key="key" @click.prevent="toggleOption($event, key)">
                    <a href="#" @click.prevent>
                        <span class="text-left">
                            <i v-if="myMultiple && isValueSelected(key)" class="fal fa-fw fa-check-square"></i>
                            <i v-else-if="myMultiple && !isValueSelected(key)" class="fal fa-fw fa-square"></i>
                            {{ option}}
                            <i v-if="!myMultiple && isValueSelected(key)" class="fal fa-fw fa-check"></i>
                        </span>
                    </a>
                </li>
                <template v-for="(optgroup, label) in finalOptionGroups">
                    <li :key="label"><b>{{ label }}</b></li>
                    <li v-for="(option, key) in optgroup" :key="label + key" @click.prevent="toggleOption($event, key)">
                        <a href="#" @click.prevent>
                            <i v-if="myMultiple && isValueSelected(key)" class="fal fa-fw fa-check-square"></i>
                            <i v-else-if="myMultiple && !isValueSelected(key)" class="fal fa-fw fa-square"></i>
                            {{ option}}
                            <i v-if="!myMultiple && isValueSelected(key)" class="fal fa-fw fa-check"></i>
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
        },
        data: function () {
            return {
                myMultiple: this.initialMultiple,
                options: this.initialOptions,
                selectedModel: this.makeModel(this.selected),
                loading: false,
                search: '',
                myParentValue: this.parentValue,
            };
        },
        props: {
            searchPlaceholder: {
                default: 'Search ...',
            },
            hasSearch: {
                default: true
            },
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
                default: ' - - open - -'
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
            initialRefresh: {
                default: false,
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
            },
            isDisabled: {
                type: Boolean,
                default: false
            },
            isRequired: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            maxHeightStyle: function () {
                return null;
                let myBottom = parseInt($(this.$el).offset().top) - parseInt($(this.$el).outerHeight());
                let bodyBottom = parseInt($('body').outerHeight());
                let h = bodyBottom - myBottom;

                return {'max-height': h + 'px'};
            },
            finalOptions: function () {
                return this.extractOptions(this.options);
            },
            finalOptionGroups: function () {
                return this.extractOptionGroups(this.options);
            },
            selectedTitle: function () {
                let selected = Array.isArray(this.selectedModel) ? this.selectedModel : [this.selectedModel];
                let titles = [];

                let options = this.finalOptions;
                $.each(options, function (i, option) {
                    if (selected.indexOf(i) >= 0 || selected.indexOf(parseInt(i)) >= 0 || selected.indexOf(i.toString()) >= 0) {
                        titles.push(option);
                    }
                });

                let groups = this.finalOptionGroups;
                $.each(groups, function (i, optionGroup) {
                    $.each(optionGroup, function (j, option) {
                        if (selected.indexOf(j) >= 0 || selected.indexOf(parseInt(j)) >= 0 || selected.indexOf(j.toString()) >= 0) {
                            titles.push(option);
                        }
                    });
                });

                if (titles.length == 0) {
                    return this.withEmpty || ' - - select value - - ';
                }

                let joined = titles.join(', ');

                if (joined.length > 42) {
                    return joined.substring(0, 42) + ' ...';
                }

                return joined;
            },
            hasMultipleSelected: function () {
                return this.myMultiple && Array.isArray(this.selected) && this.selected.length > 1;
            }
        },
        watch: {
            search: function () {
                this.refreshList();
            },
            selectedModel: function (newVal, oldVal) {
                this.$emit('change', newVal);
                this.$emit('input', newVal);
            },
            initialOptions: function (newVal) {
                if (Object.keys(newVal) != Object.keys(this.options)) {
                    this.options = this.mergeOptions(newVal);
                }
            },
            selected: function (newVal, oldVal) {
                this.myMultiple = Array.isArray(newVal);
                this.selectedModel = this.makeModel(newVal);
            },
            initialMultiple: function (newVal, oldVal) {
                this.myMultiple = newVal;
                this.selectedModel = this.makeModel(this.selected);
            }
        },
        methods: {
            focusSearch: function () {
                this.$nextTick(function () {
                    this.dropdownOpened();
                    $(this.$el).find('ul.dropdown-menu > li:first-child input').first().focus();
                }.bind(this));
            },
            isOptionFiltered: function (key, item) {
                if (!this.search || this.search.length == 0) {
                    return false;
                }

                return (typeof item == 'string' && item.toLowerCase().indexOf(this.search.toLowerCase()) < 0 || item == this.search)
                    && (typeof key == 'string' && key.toLowerCase().indexOf(this.search.toLowerCase()) < 0 || item == this.search);
            },
            extractOptions: function (o) {
                var options = {};

                $.each(o, function (key, item) {
                    if (this.flat) {
                        let title = this.getTitle(item, key);
                        let k = this.getId(item, key);
                        if (this.isOptionFiltered(k, title)) {
                            return;
                        }
                        options[k] = title;
                        return;
                    }

                    if (typeof item != 'string') {
                        return;
                    }

                    let title = this.getTitle(item, key);
                    let k = this.getId(item, key);

                    if (this.isOptionFiltered(k, title)) {
                        return;
                    }
                    options[k] = title;
                }.bind(this));

                return options;
            },
            extractOptionGroups: function (o) {
                if (this.flat) {
                    return {};
                }

                var options = {};
                $.each(this.options, function (key, item) {
                    if (typeof item === 'string' || typeof item === 'number') {
                        return;
                    }

                    if (Array.isArray(item)) {
                        $.each(item, function (subKey, subItem) {
                            var k = this.getId(subItem, subKey);
                            let title = this.getTitle(subItem, subKey)
                            if (this.isOptionFiltered(k, title)) {
                                return;
                            }
                            if (!options[key]) {
                                options[key] = {};
                            }
                            options[key][k] = title;
                        }.bind(this));
                        return
                    }

                    let k = this.getId(item, key);
                    let title = this.getTitle(item, key);
                    if (this.isOptionFiltered(k, title)) {
                        return;
                    }
                    options[k] = title;
                }.bind(this));

                return options;
            },
            makeModel: function (value) {
                return this.myMultiple
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
                    $event.stopPropagation();
                }

                if (this.initialMultiple && Array.isArray(this.selectedModel)) {
                    let i = this.selectedModel.indexOf(key);
                    if (i >= 0) {
                        this.selectedModel.splice(i, 1);
                        return;
                    }

                    this.selectedModel.push(key);
                    return;
                }

                this.selectedModel = this.selectedModel == key ? null : key;
                // close and make normal
                this.dropdownClosed();
            },
            dropdownClosed: function () {
                $(this).closest('--relative-menu-opened').removeClass('--relative-menu-opened');
            },
            dropdownOpened: function () {
                $('.--relative-menu-opened').removeClass('--relative-menu-opened');
                $(this.$el).closest('.s-form-field').addClass('--relative-menu-opened');
            },
            refreshList: function () {
                this.timeout('refreshList', function () {
                    if (!this.refreshUrl || this.refreshUrl.length < 1) {
                        return;
                    }

                    this.loading = true;
                    http.getJSON(this.refreshUrl + '?search=' + this.search + '&selected=' + (Array.isArray(this.selectedModel) ? this.selectedModel.join(',') : this.selectedModel), function (data) {
                        this.options = data.records;
                        this.loading = false;
                    }.bind(this), function () {
                        this.loading = false;
                    });
                }.bind(this), 333);
            },
            isValueSelected: function (val) {
                if (Array.isArray(this.selectedModel)) {
                    return this.selectedModel.indexOf(val) >= 0;
                }

                return this.selectedModel === val;
            },
            mergeOptions: function (newOptions) {
                let selected = Array.isArray(this.selectedModel) ? this.selectedModel : (this.selectedModel ? [this.selectedModel] : []);

                /**
                 * Find options that are selected but does not exist in collection.
                 */
                let allOptions = this.extractFlatOptions(this.options);
                $.each(selected, function (i, val) {
                    if (!newOptions[val] && allOptions[val]) {
                        newOptions[val] = allOptions[val];
                    }
                });

                return newOptions;
            }
        },
        created: function () {
            /**
             * Initial fetch.
             */
            if (this.initialRefresh) {
                this.refreshList();
            }
        },
        mounted: function () {
            if ((!this.options || this.options.length == 0) && this.refreshUrl && this.refreshUrl.length > 0) {
                // this.refreshList();
            }
        }
    }
</script>
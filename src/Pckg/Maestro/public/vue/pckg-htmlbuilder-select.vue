<template>
    <div class="pckg-htmlbuilder-select btn-group">
        <a href="button" :href="realViewUrl" target="_blank" class="btn btn-default btn-sm"
           title="View">
            <i class="fa-fw fal fa-search" aria-hidden="true"></i>
        </a>
        <a href="button" :href="url" target="_blank" class="btn btn-default btn-sm"
           title="To list">
            <i class="fa-fw fal fa-list-ul" aria-hidden="true"></i>
        </a>
        <a @click.prevent="refreshList" href="#" class="btn btn-default btn-sm"
                title="Refresh">
            <i class="fa-fw fal fa-sync-alt" aria-hidden="true"></i>
        </a>
    </div>
</template>

<script>
    export default {
        mixins: [pckgTranslations],
        props: {
            value: null,
            url: null,
            viewUrl: null,
            refreshUrl: null,
            initialOptions: {
                default: function () {
                    return [];
                }
            }
        },
        data: function () {
            return {
                options: this.initialOptions,
                v: null
            };
        },
        methods: {
            refreshList: function () {
                http.getJSON(this.refreshUrl, function (data) {
                    var $select = $(this.$el).next().find('select');
                    var value = $select.val();
                    $select.html('');
                    $.each(data.records, function (key, val) {
                        if (typeof val == 'object' || typeof val == 'array') {
                            var optgroup = '<optgroup label="' + key + '">';
                            $.each(val, function (k, v) {
                                optgroup += '<option value="' + (k === 0 ? '' : k) + '">' + v + '</option>';
                            });
                            optgroup += '</optgroup>';
                            $select.append(optgroup);
                        } else {
                            $select.append('<option value="' + (key === 0 ? '' : key) + '">' + val + '</option>');
                        }
                    });
                    $select.selectpicker('refresh');
                    $select.val(value).change();
                }.bind(this));
            }
        },
        computed: {
            realViewUrl: function () {
                return utils.url(this.viewUrl, {record: this.value});
            }
        }
    }
</script>
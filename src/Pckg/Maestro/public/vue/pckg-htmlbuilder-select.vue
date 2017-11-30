<template>
    <div class="pckg-htmlbuilder-select">
        <a href="button" :href="realViewUrl" target="_blank" class="btn btn-warning btn-md"
           :title="__('pckg.htmlbuilder.btn.viewRecord')">
            <i class="fa fa-search" aria-hidden="true"></i>
        </a>
        <a href="button" :href="url" target="_blank" class="btn btn-info btn-md"
           :title="__('pckg.htmlbuilder.btn.toList')">
            <i class="fa fa-list-ul" aria-hidden="true"></i>
        </a>
        <button type="button" @click.prevent="refreshList" class="btn btn-success btn-md"
                :title="__('pckg.htmlbuilder.btn.refreshList')">
            <i class="fa fa-refresh" aria-hidden="true"></i>
        </button>
    </div>
</template>

<script>
    export default {
        mixins: [pckgTranslations],
        props: {
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
                    this.v = value;
                }.bind(this));
            }
        },
        mounted: function () {
            var $t = this;
            $(this.$el).parent().find('select').on('change', function () {
                $t.v = $(this).val();
            });
            this.v = $(this.$el).parent().find('select').val() || null;
        },
        computed: {
            realViewUrl: function () {
                return utils.url(this.viewUrl, {record: this.v});
            }
        }
    }
</script>
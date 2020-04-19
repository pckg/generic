<template>
    <div class="table-floating-bottom-bar">
        <div class="table-actions" v-if="ids.length > 0">
            <div class="pull-left" style="margin-right: 4rem;">
                {{ ids.length }} items selected
                <!--<template v-if="allChecked && paginator.total != ids.length">
                    <br/>
                    <a href="#"> Select all items from all pages</a>
                </template>-->
            </div>

            <div class="pull-right">
                <slot name="actions" class="__entity-actions"></slot>
            </div>

            <div class="clearfix"></div>
        </div>
        <div class="table-paginator" v-if="paginator && paginator.perPage < paginator.total">
            <pckg-dynamic-paginator :ref="'maestro-paginator'"
                                    :initial-per-page="paginator.perPage"
                                    :initial-page="paginator.page"
                                    :total="paginator.total"
                                    :url="paginator.url"
                                    :resetpaginatorurl="resetPaginatorUrl"
            ></pckg-dynamic-paginator>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'table-statusbar',
        props: {
            paginator: {
                default: function () {
                    return {
                        perPage: 50,
                        page: 1,
                        total: 0,
                        url: null
                    };
                }
            },
            allChecked: {
                default: false
            },
            ids: {
                default: function () {
                    return [];
                }
            }
        }
    }
</script>
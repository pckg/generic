<TEMPLATE-IS-IN-SCRIPT>
    <div :id="'action-' + action.id" :class="actionClass" :style="actionStyle" v-pagebuilder>
        <pckg-action-bg :action="action"></pckg-action-bg>
        <frontpage-action-outline :action="action" v-if="action.active"></frontpage-action-outline>
        <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
    </div>
</TEMPLATE-IS-IN-SCRIPT>

<script>
    export default {
        name: 'pckg-action',
        mixins: [pckgElement, pckgSmartComponent],
        data: function () {
            let d = pckgElement.data ? pckgElement.data.call(this) : {};

            d.templateRender = null;
            d.tpl = null;
            d.myAction = this.action;

            return d;
        },
        render: function (h) {
            if (!this.templateRender) {
                if (this.$options.template) {
                    return this.$options.template;
                }

                return h('div', 'Loading ...');
            }

            try {
                let render = this.templateRender();
                return render;
            } catch (e) {
                console.log('Error rendering template', e, this.action);
                return h('div', 'Error rendering template: ' + e.getMessage())
            }
        },
        watch: {
            tpl: {
                immediate: true,
                handler: function (newVal, oldVal) {
                    let build = this.action.build || '';
                    if (!build) {
                        console.log('No action.build.')
                    }
                    let res;
                    if (true || build.indexOf('slot="') == -1) {
                        build = '<div :id="\'action-\' + action.id" :class="actionClass" :style="actionStyle" v-pagebuilder>'
                            + '<pckg-action-bg :action="action"></pckg-action-bg>'
                            + '<frontpage-action-outline :action="action" v-if="action.active"></frontpage-action-outline>'
                            + build
                            + '</div>';
                    }
                    try {
                        res = Vue.compile(build);
                    } catch (e) {
                        console.log(build);
                        console.log('error building template', build);
                        res = Vue.compile('<p>Error building template</p>');
                    }

                    this.templateRender = res.render;

                    // staticRenderFns belong into $options,
                    // appearantly

                    this.$options.staticRenderFns = [];

                    // clean the cache of static elements
                    // this is a cache of the results from the staticRenderFns
                    this._staticTrees = [];

                    // Fill it with the new staticRenderFns
                    if (res.staticRenderFns) {
                        for (var i in res.staticRenderFns) {
                            //staticRenderFns.push(res.staticRenderFns[i]);
                            this.$options.staticRenderFns.push(res.staticRenderFns[i]);
                        }
                    }
                }
            }
        }
    }
</script>
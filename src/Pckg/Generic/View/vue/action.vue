<TEMPLATE-IS-IN-SCRIPT>
    <div :id="'action-' + action.id" :class="actionClass" :style="actionStyle" v-pagebuilder>
        <pckg-action-bg :action="action"></pckg-action-bg>
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
                return h('div', 'Error rendering template: ' + e.getMessage())
            }
        },
        watch: {
            tpl: {
                immediate: true,
                handler: function (newVal, oldVal) {
                    let build = this.action.build || '';
                    /**
                     * Push all generic attributes to every action.
                     */
                    let res;
                    let actionAttributes = ' :id="\'action-\' + action.id" :class="actionClass" :style="actionStyle" v-pagebuilder ';
                    if (build && this.action.raw) {
                        let split = build.split(' ');
                        build = split[0] + actionAttributes + split.slice(1).join(' ');
                    } else if (true || build.indexOf('slot="') == -1) {
                        build = '<div' + actionAttributes + '>'
                            + '<pckg-action-bg :action="action"></pckg-action-bg>'
                            + build
                            + '</div>';
                    }
                    try {
                        res = Vue.compile(build);
                    } catch (e) {
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
<templates>
    <div :id="'action-' + action.id" :data-action-id="action.id" :class="actionClass" :style="actionStyle">
        <slot name="body">
            <component v-for="a in subactions" :action-id="a.id" :is="a.component" :key="a.id"></component>
        </slot>
    </div>
</templates>

<script>
    export default {
        name: 'pckg-action',
        mixins: [pckgElement],
        data: function () {
            return {
                templateRender: null,
                tpl: null,
                myAction: this.action
            };
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
                    let res;
                    let b = '<div :id="\'action-\' + action.id" :data-action-id="action.id" :class="actionClass" :style="actionStyle">' + (this.action.build || '<p>No build?</p>') + '</div>';
                    try {
                        res = Vue.compile(b);
                    } catch (e) {
                        console.log(this.action.build || '<p>No build?</p>');
                        console.log('error building template', b);
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
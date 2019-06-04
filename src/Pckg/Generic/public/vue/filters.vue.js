Vue.filter('number', function (price) {
    return parseInt(price) == parseFloat(price) ? parseInt(price) : parseFloat(price);
});

Vue.filter('price', function (price, decimals) {
    return locale.price(price, decimals);
});

Vue.filter('roundPrice', function (price, decimals) {
    return locale.roundPrice(price, decimals);
});

Vue.filter('roundNumber', function (price, decimals) {
    return parseInt(price) == parseFloat(price) ? parseInt(price) : parseFloat(price).toFixed(decimals || 2);
});

Vue.filter('date', function (date, format) {
    return locale.date(date, format);
});

Vue.filter('time', function (date, format) {
    return locale.time(date, format);
});

Vue.filter('datetime', function (date, format) {
    return locale.datetime(date, format);
});

Vue.filter('timespan', function (timespan) {
    let format = timespan.split(' ')[1] || null;

    if (!format) {
        return;
    }

    let num = parseInt(timespan.split(' ')[0]);
    let duration = moment.duration(num, format);

    let mapper = {
        minute: 'mm',
        hour: 'hh',
        day: 'dd'
    };

    let f = null;
    $.each(mapper, function (k, v) {
        if (format.indexOf(k) >= 0) {
            f = v;
            return false;
        }
    });

    if (!f) {
        return;
    }

    return moment.localeData().relativeTime(num, true, f, false);
});

Vue.filter('ucfirst', function (string) {
    return utils.ucfirst(string);
});

Vue.filter('nl2br', function (string) {
    if (!string) {
        return '';
    }

    return utils.nl2br(nl2br);
});

Vue.filter('html2text', function (html) {
    if (!html) {
        return '';
    }

    let span = document.createElement('span');
    span.innerHTML = html;
    return span.textContent || span.innerText;
});

Vue.directive('outer-click', {
    bind: function (el, binding, vnode) {
        $dispatcher.$on('body:click', function (e){
            if ($(e.target).closest(el).is($(el))) {
                return;
            }

            binding.value(e);
        });
    },
    unbind: function (el, binding, vnode) {
        $dispatcher.$off('body:click', function (e){
            if ($(e.target).closest(el).is($(el))) {
                return;
            }

            binding.value(e);
        });
    }
});

Vue.directive('popup-image', {
    bind: function (el) {
        $(el).magnificPopup({type: 'image'});
    }
});

Vue.directive('pagebuilder-small', {
    bind: function (el, directive) {

        el.addEventListener('mouseenter', function ($event) {
            let genericMode = $store.state.generic.genericMode;

            if (genericMode != 'edit') {
                return;
            }

            $store.commit('setActionFocus', {actionId: $(el).attr('data-action-id'), focus: true});
        });

        el.addEventListener('mouseleave', function ($event) {
            let genericMode = $store.state.generic.genericMode;

            if (genericMode != 'edit') {
                return;
            }

            $store.commit('setActionFocus', {actionId: $(el).attr('data-action-id'), focus: false});
        });

    }
});

Vue.directive('pagebuilder', {
    bind: function (el, directive) {
        /**
         * @T00D00 - this directive should be enabled only when edit mode is enabled
         */
        let timeoutObject = pckgTimeout;

        el.addEventListener('click', function ($event) {
            let genericMode = $store.state.generic.genericMode;

            if (genericMode != 'edit') {
                return;
            }

            $event.preventDefault();
            $event.stopPropagation();

            if ($(el).find('.mce-content-body').length > 0) {
                return false;
            }

            timeoutObject.methods.timeout('componentClicked', function () {
                $dispatcher.$emit('pckg-editor:actionChanged', el.__vue__.action);
            }, 333, timeoutObject);

            return false;
        });

        el.addEventListener('dblclick', function ($event) {
            let genericMode = $store.state.generic.genericMode;

            if (genericMode != 'edit' && viewMode != 'threesome') {
                return;
            }

            timeoutObject.methods.removeTimeout('componentClicked');
            $event.preventDefault();
            $event.stopPropagation();

            if ($(el).find('.bind-content').length == 0) {
                return;
            }

            if ($(el).find(el.id + '.mce-content-body').length > 0) {
                return;
            }

            let action = el.__vue__.action;
            let content = el.__vue__.content;
            initTinymce(el.id + ' .bind-content', {
                menubar: false,
                inline: true,
                //theme: 'inlite',
                content_css: null,
                toolbar: (function () {
                    let toolbar = tinyMceConfig.toolbar.slice(0);
                    if (toolbar[0].indexOf('save') !== 0) {
                        toolbar[0] = 'save commsCancel close | ' + toolbar[0];
                    }
                    return toolbar;
                })(),
                save_onsavecallback: function (editor) {
                    content.content = editor.getContent();
                    $store.commit('setActionContent', {action: action, content: content});

                    http.post(utils.url('@pckg.generic.pageStructure.content', {content: content.id}), {content: content}, function (data) {
                    });

                    editor.destroy();
                },
                init_instance_callback: function (editor) {
                    editor.execCommand('mceFocus', false);

                    /*editor.on('Change', function () {
                        let content = this.content;
                        let editorContent = editor.getContent();
                        if (editorContent === content.content) {
                            return;
                        }
                        content.content = editor.getContent();
                        $store.commit('setActionContent', {action: this.action, content: content});
                    }.bind(this));*/
                }
            });
            //$dispatcher.$emit('pckg-frontpage:editContent', this.action);

            return false;
        });

        el.addEventListener('mouseenter', function ($event) {
            let genericMode = $store.state.generic.genericMode;

            $store.commit('setActionFocus', {actionId: el.__vue__.action.id, focus: true});
        });

        el.addEventListener('mouseleave', function ($event) {
            let genericMode = $store.state.generic.genericMode;

            $store.commit('setActionFocus', {actionId: el.__vue__.action.id, focus: false});
        });
        /*el.addEventListener("click", event => {
            event.preventDefault();
            window.location.assign(event.target.href);
        });*/
    }
});

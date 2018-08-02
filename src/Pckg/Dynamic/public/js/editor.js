var pckgEditors = {};
var destroyTinymce = function (selector) {
    //if (pckgEditors[selector]) {
    $('#' + selector).parent().find('.manual-dropzone').remove(); // @T00D00?
    //tinymce.remove('#' + selector);
    var ti = tinymce.get(selector);
    if (ti) {
        ti.destroy();
    }
    if (pckgEditors[selector]) {
        delete pckgEditors[selector];
    }
    //}
};

var initTinymce = function (selector, config) {
    console.log('Initializing tinymce and dropzone on ' + selector);
    var selected = $('#' + selector);
    var $dropzone = $('<div class="manual-dropzone"></div>');
    selected.parent().append($dropzone);
    $dropzone.idify();
    var $dropzoneInst = new Dropzone('#' + $dropzone.attr('id'), {
        url: '/dynamic/uploader',
        previewsContainer: null,
        previewTemplate: '<div></div>',
        maxFilesize: 8
    });

    var defaultConfig = {
        content_css: '/app/derive/src/Pckg/Generic/public/tinymce.css',
        selector: '#' + selector,
        height: 500,
        convert_urls: false,
        theme: 'modern',
        link_class_list: [
            {title: 'Link', value: ''},
            {title: 'Button', value: 'button'},
            {title: 'Bordered button', value: 'button button-bordered'},
            {title: 'No shadow button', value: 'button no-shadow'},
            {title: 'Shadow button', value: 'button shadow'},
            {title: 'Primary color', value: 'button color-primary'},
            {title: 'Secondary color', value: 'button color-secondary'},
            {title: 'Dark button', value: 'button color-dark'},
            {title: 'Light button', value: 'button color-light'}
        ],
        style_formats: [
            {
                title: 'Headings',
                items: [
                    {
                        title: 'Heading 1', format: 'h1'
                    },
                    {
                        title: 'Heading 2', format: 'h2'
                    },
                    {
                        title: 'Heading 3', format: 'h3'
                    },
                    {
                        title: 'Heading 4', format: 'h4'
                    },
                    {
                        title: 'Heading 5', format: 'h5'
                    },
                    {
                        title: 'Heading 6', format: 'h6'
                    }
                ]
            },

            {
                title: 'Inline',
                items: [
                    {
                        title: 'Bold', icon: 'bold', format: 'bold'
                    },
                    {
                        title: 'Italic', icon: 'italic', format: 'italic'
                    },
                    {
                        title: 'Underline', icon: 'underline', format: 'underline'
                    },
                    {
                        title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough'
                    },
                    {
                        title: 'Superscript', icon: 'superscript', format: 'superscript'
                    },
                    {
                        title: 'Subscript', icon: 'subscript', format: 'subscript'
                    },
                    {
                        title: 'Code', icon: 'code', format: 'code'
                    }
                ]
            },

            {
                title: 'Blocks',
                items: [
                    {
                        title: 'Paragraph', format: 'p'
                    },
                    {
                        title: 'Blockquote', format: 'blockquote'
                    },
                    {
                        title: 'Div', format: 'div'
                    },
                    {
                        title: 'Pre', format: 'pre'
                    },
                    {
                        title: 'Button', format: 'button'
                    }
                ]
            },

            {
                title: 'Alignment',
                items: [
                    {
                        title: 'Left', icon: 'alignleft', format: 'alignleft'
                    },
                    {
                        title: 'Center', icon: 'aligncenter', format: 'aligncenter'
                    },
                    {
                        title: 'Right', icon: 'alignright', format: 'alignright'
                    },
                    {
                        title: 'Justify', icon: 'alignjustify', format: 'alignjustify'
                    }
                ]
            },
            {
                title: 'Font sizes',
                items: [
                    {
                        title: 'XXS', format: 'font_size_xxs'
                    },
                    {
                        title: 'XS', format: 'font_size_xs'
                    },
                    {
                        title: 'S', format: 'font_size_s'
                    },
                    {
                        title: 'M', format: 'font_size_m'
                    },
                    {
                        title: 'L', format: 'font_size_l'
                    },
                    {
                        title: 'XL', format: 'font_size_xl'
                    },
                    {
                        title: 'XXL', format: 'font_size_xxl'
                    },
                    {
                        title: 'XXXL', format: 'font_size_xxxl'
                    },
                ]
            },
            {
                title: 'Font colors',
                items: [
                    {
                        title: 'Darken', format: 'colordarken'
                    },
                    {
                        title: 'Darken+', format: 'colordarkenplus'
                    },
                    {
                        title: 'Lighten', format: 'colorlighten'
                    },
                    {
                        title: 'Lighten+', format: 'colorlightenplus'
                    },
                    {
                        title: 'Light', format: 'colorlight'
                    },
                    {
                        title: 'Dark', format: 'colordark'
                    },
                    {
                        title: 'Primary', format: 'colorprimary'
                    },
                    {
                        title: 'Secondary', format: 'colorsecondary'
                    }
                ]
            },
        ],
        formats: {
            font_size_xxs: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-xxs'
            },
            font_size_xs: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-xs'
            },
            font_size_s: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-s'
            },
            font_size_m: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-m'
            },
            font_size_l: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-l'
            },
            font_size_xl: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-xl'
            },
            font_size_xxl: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-xxl'
            },
            font_size_xxxl: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'font-size-xxxl'
            },
            alignleft: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', styles: {'text-align': 'left'}
            },
            aligncenter: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', styles: {'text-align': 'center'}
            },
            alignright: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', styles: {'text-align': 'right'}
            },
            alignjustify: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'text-justify'
            },
            colordarken: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-darken'
            },
            colorlighten: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-lighten'
            },
            colordarkenplus: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-darken-plus'
            },
            colorlightenplus: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-lighten-plus'
            },
            colorlight: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-light'
            },
            colordark: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-dark'
            },
            colorprimary: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-primary'
            },
            colorsecondary: {
                selector: 'a,p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table', classes: 'color-secondary'
            },
            button: {
                selector: 'a', classes: 'button'
            }
        },
        extended_valid_elements: 'pc-kg',
        custom_elements: 'pc-kg',
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak autoresize',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'emoticons template paste textcolor colorpicker textpattern imagetools codesample pckg noneditable'
        ],
        toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        toolbar2: 'print preview media | forecolor backcolor emoticons | codesample',
        image_advtab: true,
        allow_html_in_named_anchor: true,
        allow_unsafe_link_target: true,
        forced_root_block: 'p',
        force_br_newlines: false,
        force_p_newlines: true,
        protect: [
            // /{{[^}]+}}/g,  // Protect {{ }}
            // /{%[^}]+%}/g,  // Protect {% %}
        ],
        valid_elements: '*[*]',
        templates: [
            /*{title: 'Test template 1', content: 'Test 1'},
             {title: 'Test template 2', content: 'Test 2'}*/
        ],
        image_class_list: [
            {title: 'None', value: ''},
            {title: 'Default (.editor-img)', value: 'editor-img'},
            {title: 'Responsive (.img-responsive)', value: 'img-responsive'},
            {title: 'Circle (.img-circle)', value: 'img-circle'},
            {title: 'Rounded (.img-rounded)', value: 'img-rounded'}
        ],
        images_upload_url: '/dynamic/uploader',
        automatic_uploads: false,
        autoresize_min_height: '160px',
        allow_script_urls: true,
        file_picker_callback: function (cb, value, meta) {
            $dropzoneInst.on('success', function (file, data) {
                cb(data.url, {title: null, class: 'pckg-img'});
            });

            $dropzone.trigger('click');
        },
        setup: function(editor) {
            editor.addCommand('mceInsertLink', function (ui, value) {
                var anchor;

                if (typeof value == 'string') {
                    value = {href: value};
                }

                anchor = dom.getParent(editor.selection.getNode(), 'a');

                //value.href = value.href.replace(/\s+/g, '%20');

                // Remove existing links if there could be child links or that the href isn't specified
                if (!anchor || !value.href) {
                    formatter.remove('link');
                }

                // Apply new link to selection
                if (value.href) {
                    editor.formatter.apply('link', value, anchor);
                }
            });
        }
    };

    if (config) {
        $.each(config, function (key, val) {
            if (key == 'setup') {
                var tempSetup = defaultConfig.setup;
                defaultConfig.setup = function(editor) {
                    tempSetup(editor);
                    val(editor);
                }
            } else {
                defaultConfig[key] = val;
            }
        });
    }

    pckgEditors[selector] = tinymce.init(defaultConfig);

    return pckgEditors[selector];
};

$(document).ready(function () {

    /**
     * Start pc-kg (editor variable) plugin.
     */
    document.createElement('pc-kg');

    tinymce.PluginManager.add('pckg', function (editor, url) {
        // Add a button that opens a window
        editor.addButton('pckgElementButton', {
            text: 'Add 2 pckg',
            icon: false,
            onclick: function () {
                // Open window
                editor.windowManager.open({
                    title: 'Please input text',
                    body: [
                        {type: 'textbox', name: 'description', label: 'Text'}
                    ],
                    onsubmit: function (e) {
                        // Insert content when the window form is submitted
                        editor.insertContent('<span class="tinymce-pckg mceNonEditable">order:id</span>');
                    }
                });
            }
        });

        var menus = [];
        $.each(Pckg.config.editor.variables || {}, function (parentName, subVariables) {
            var submenus = [];
            $.each(subVariables, function (name, key) {
                submenus.push({
                    text: name,
                    onclick: function () {
                        editor.insertContent('<span class="tinymce-pckg mceNonEditable">' + key + '</span>');
                    }
                });
            });
            if (submenus.length) {
                menus.push({
                    text: parentName,
                    menu: submenus
                });
            }
        });

        // Adds a menu item to the tools menu
        editor.addMenuItem('pckgElementMenuItem', {
            text: 'Variables',
            context: 'tools',
            menu: menus/*[
             {
             text: 'Order',
             menu: [
             {
             text: 'ID',
             onclick: function () {
             editor.insertContent('<span class="tinymce-pckg mceNonEditable">order:id</span>');
             }
             },
             {
             text: 'Hash',
             onclick: function () {
             editor.insertContent('<span class="tinymce-pckg mceNonEditable">order:hash</span>');
             }
             }
             ]
             },
             {
             text: 'Company',
             menu: [
             {
             text: 'Short name',
             onclick: function () {
             editor.insertContent('<span class="tinymce-pckg mceNonEditable">company:short_name</span>');
             }
             },
             {
             text: 'Long name',
             onclick: function () {
             editor.insertContent('<span class="tinymce-pckg mceNonEditable">company:long_name</span>');
             }
             }
             ]
             }
             ]*/
        });
    });

    /**
     * End plugin
     */

    /**
     *
     * @type {string}
     */
    tinymce.baseURL = '/node_modules/tinymce/';

    $('textarea.editor').each(function () {
        var selector = null;
        if ($(this).attr('id')) {
            selector = '#' + $(this).attr('id');
        } else {
            selector = 'html-editor-' + Math.round((Math.random() * 100000));
            $(this).attr('id', selector);
        }
    });

    $('.pckg-editor-enabled').each(function () {
        var id = $(this).attr('id');
        initTinymce(id);
    });

    /**
     * this workaround makes magic happen
     * thanks @harry: http://stackoverflow.com/questions/18111582/tinymce-4-links-plugin-modal-in-not-editable
     */
    $(document).on('focusin', function (e) {
        if ($(e.target).closest(".mce-window").length) {
            e.stopImmediatePropagation();
        }
    });
});
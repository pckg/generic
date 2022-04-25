import Dropzone from "dropzone";
import tinymce from "tinymce";

/* Default icons are required for TinyMCE 5.3 or above */
//import 'tinymce/icons/default';

/* A theme is also required */
//import 'tinymce/themes/inlite';

/* Import the skin */
//import 'tinymce/skins/lightgray/skin.min.css';

/* Import plugins */
/*import 'tinymce/plugins/advlist';
import 'tinymce/plugins/code';
import 'tinymce/plugins/emoticons';
import 'tinymce/plugins/emoticons/js/emojis';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';*/

const pckgEditors = {};

export function destroyTinymce(selector) {
    //if (pckgEditors[selector]) {
    $('#' + selector).parent().find('.manual-dropzone').remove(); // @T00D00?
    //tinymce.remove('#' + selector);
    var ti = tinymce.get(selector);
    if (ti) {
        ti.destroy();
    }
    if (window?.pckgEditors[selector]) {
        delete window.pckgEditors[selector];
    }
    //}
};

const $tinyMceConfig = {
    entity_encoding: 'raw',
    link_class_list: [
        {title: 'Link', value: ''},
        {title: 'Button', value: 'button'},
        {title: 'Bordered button', value: 'button btn-bordered'},
        {title: 'No shadow button', value: 'button no-shadow'},
        {title: 'Shadow button', value: 'button shadow'},
        {title: 'Primary color', value: 'button color-primary'},
        {title: 'Secondary color', value: 'button color-secondary'},
        {title: 'Dark button', value: 'button color-dark'},
        {title: 'Light button', value: 'button color-light'},
        {title: 'Small button', value: 'button size-xs'}
    ],
    link_context_toolbar: true,
    style_formats: [
        {
            title: 'Headings',
            items: [
                {title: 'Heading 1', format: 'h1'},
                {title: 'Heading 2', format: 'h2'},
                {title: 'Heading 3', format: 'h3'},
                {title: 'Heading 4', format: 'h4'},
                {title: 'Heading 5', format: 'h5'},
                {title: 'Heading 6', format: 'h6'}
            ]
        },
        {
            title: 'Inline',
            items: [
                {title: 'Bold', icon: 'bold', format: 'bold'},
                {title: 'Italic', icon: 'italic', format: 'italic'},
                {title: 'Underline', icon: 'underline', format: 'underline'},
                {title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough'},
                {title: 'Superscript', icon: 'superscript', format: 'superscript'},
                {title: 'Subscript', icon: 'subscript', format: 'subscript'},
                {title: 'Code', icon: 'code', format: 'code'}
            ]
        },
        {
            title: 'Blocks',
            items: [
                {title: 'Paragraph', format: 'p'},
                {title: 'Blockquote', format: 'blockquote'},
                {title: 'Div', format: 'div'},
                {title: 'Pre', format: 'pre'},
                {title: 'Button', format: 'button'}
            ]
        },
        {
            title: 'Alignment',
            items: [
                {title: 'Left', icon: 'alignleft', format: 'alignleft'},
                {title: 'Center', icon: 'aligncenter', format: 'aligncenter'},
                {title: 'Right', icon: 'alignright', format: 'alignright'},
                {title: 'Justify', icon: 'alignjustify', format: 'alignjustify'}
            ]
        },
        {
            title: 'Font sizes',
            items: [
                {title: 'XXS', format: 'font_size_xxs'},
                {title: 'XS', format: 'font_size_xs'},
                {title: 'S', format: 'font_size_s'},
                {title: 'M', format: 'font_size_m'},
                {title: 'L', format: 'font_size_l'},
                {title: 'XL', format: 'font_size_xl'},
                {title: 'XXL', format: 'font_size_xxl'},
                {title: 'XXXL', format: 'font_size_xxxl'},
            ]
        },
        {
            title: 'Font colors',
            items: [
                {title: 'Darken', format: 'colordarken'},
                {title: 'Darken+', format: 'colordarkenplus'},
                {title: 'Lighten', format: 'colorlighten'},
                {title: 'Lighten+', format: 'colorlightenplus'},
                {title: 'Light', format: 'colorlight'},
                {title: 'Dark', format: 'colordark'},
                {title: 'Primary', format: 'colorprimary'},
                {title: 'Secondary', format: 'colorsecondary'}
            ]
        },
        {
            title: 'Font families',
            items: [
                {
                    title: 'Primary', format: 'font-family-primary',
                }, {
                    title: 'Secondary', format: 'font-family-secondary',
                }, {
                    title: 'Primary (system)', format: 'font-family-primary-system',
                }, {
                    title: 'Secondary (system)', format: 'font-family-secondary-system',
                }
            ]
        },
        {
            title: 'Buttons',
            items: [
                {title: 'Button', format: 'buttonelement'},
                {title: 'Bordered', format: 'buttonborderedelement'},
            ]
        },
    ],
    formats: {
        buttonelement: {
            selector: 'a', classes: 'button',
        },
        buttonborderedelement: {
            selector: 'a', classes: 'button',
        },
        /*buttonsizexs: {
            selector: 'a', classes: 'button size-xs',
        },
        buttonsizesm: {
            selector: 'a', classes: 'button size-m',
        },*/
        font_size_xxs: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-xxs'
        },
        font_size_xs: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-xs'
        },
        font_size_s: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-s'
        },
        font_size_m: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-m'
        },
        font_size_l: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-l'
        },
        font_size_xl: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-xl'
        },
        font_size_xxl: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-xxl'
        },
        font_size_xxxl: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'font-size-xxxl'
        },
        alignleft: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', styles: {'text-align': 'left'}
        },
        aligncenter: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', styles: {'text-align': 'center'}
        },
        alignright: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', styles: {'text-align': 'right'}
        },
        alignjustify: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'text-justify'
        },
        colordarken: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-darken'
        },
        colorlighten: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-lighten'
        },
        colordarkenplus: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-darken-plus'
        },
        colorlightenplus: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-lighten-plus'
        },
        colorlight: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-light'
        },
        colordark: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-dark'
        },
        colorprimary: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-primary'
        },
        colorsecondary: {
            selector: 'a,p,h1,h2,h3,h4,h5,h6,span,td,th,div,ul,ol,li,table', classes: 'color-secondary'
        },
        button: {
            selector: 'a', classes: 'button'
        }
    },
    image_class_list: [
        {title: 'None', value: ''},
        {title: 'Default (.editor-img)', value: 'editor-img'},
        {title: 'Responsive (.img-responsive)', value: 'img-responsive'},
        {title: 'Circle (.img-circle)', value: 'img-circle'},
        {title: 'Rounded (.img-rounded)', value: 'img-rounded'}
    ],
    plugins: [ // help
        'autosave advlist autolink lists link image charmap print preview hr anchor pagebreak autoresize',
        'searchreplace wordcount visualblocks visualchars code fullscreen',
        'hr insertdatetime media nonbreaking save table contextmenu directionality',
        'emoticons template paste textcolor colorpicker textpattern imagetools codesample noneditable'
    ],
    toolbar: [ // imagetools
        'undo redo | link unlink image media | forecolor backcolor | hr table removeformat code',
        'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent'
    ],
    height: 500,
    convert_urls: false,
    theme: 'modern',
    powerpaste_word_import: 'clean',
    powerpaste_html_import: 'clean',
    extended_valid_elements: 'pc-kg',
    custom_elements: 'pc-kg',
    image_advtab: true,
    allow_html_in_named_anchor: true,
    allow_unsafe_link_target: true,
    forced_root_block: 'p',
    force_br_newlines: false,
    force_p_newlines: true,
    protect: [
        // /{{[^linkchecker}]+}}/g,  // Protect {{ }}
        // /{%[^}]+%}/g,  // Protect {% %}
    ],
    valid_elements: '*[*]',
    templates: [
        /*{title: 'Test template 1', content: 'Test 1'},
         {title: 'Test template 2', content: 'Test 2'}*/
    ],
    image_title: true,
    automatic_uploads: false,
    autoresize_min_height: '160px',
    allow_script_urls: true,
    insert_toolbar: 'quicktable image',
    selection_toolbar: 'bold italic | h2 h3 | blockquote quicklink',
    contextmenu: 'inserttable | cell row column deletetable',
    // inline: false,
    table_default_attributes: {},
    table_default_styles: {},
    table_resize_bars: false,
    table_class_list: [],
    table_cell_class_list: [],
    table_row_class_list: []
};

export const tinyMceConfig = $tinyMceConfig;

export function initTinymce(selector, config) {
    let elements = document.getElementsByName('pckgvdth');
    let vdth = elements.length === 1 ? elements[0].getAttribute('content') : null;

    var selected = $('#' + selector);

    let $dropzone, $dropzoneInst;
    $dropzone = $('<div class="manual-dropzone" id="manual-dropzone-' + Math.round(Math.random() * 1000000) + '"></div>');
    selected.parent().append($dropzone);

    $dropzoneInst = new Dropzone('#' + $dropzone.attr('id'), {
        url: '/dynamic/uploader?dropzone',
        previewsContainer: null,
        previewTemplate: '<div></div>',
        maxFilesize: 8,
        headers: vdth ? {
            'X-Pckg-CSRF': vdth
        } : {}
    });

    let c = JSON.parse(JSON.stringify($tinyMceConfig));
    let contentCss = typeof Dropzone !== 'undefined'
        ? '/app/derive/src/Pckg/Generic/public/tinymce.css'
        : '/storage/tinymce-themes/tinymce.css';

    var defaultConfig = Object.assign(c, {
        content_css: contentCss,
        selector: '#' + selector,
        images_upload_url: '/dynamic/uploader?vdth=' + vdth,
        file_picker_callback: function (cb, value, meta) {
            if ($dropzoneInst) {
                $dropzoneInst.on('success', function (file, data) {
                    cb(data.url, {title: null, class: 'pckg-img'});
                });

                $dropzone.trigger('click');
            }
        },
        setup: function (editor) {
            editor.addCommand('mceInsertLink', function (ui, value) {
                var anchor;

                if (typeof value == 'string') {
                    value = {href: value};
                }

                anchor = tinymce.DOM.getParent(editor.selection.getNode(), 'a');

                //value.href = value.href.replace(/\s+/g, '%20');

                // Remove existing links if there could be child links or that the href isn't specified
                if (!anchor || !value.href) {
                    editor.formatter.remove('link');
                }

                // Apply new link to selection
                if (value.href) {
                    editor.formatter.apply('link', value, anchor);
                }
            });

            editor.on('Paste Change input Undo Redo', function () {
                var content = editor.getContent();
                var updated = content.replace(/<\/?g[^>]*>/g, "");

                if (updated === content) {
                    return;
                }

                editor.setContent(updated);
            });
        }
    });

    if (config) {
        $.each(config, function (key, val) {
            if (key === 'variables') {

                var tempSetup = defaultConfig.setup;
                defaultConfig.setup = function (editor) {
                    tempSetup(editor);

                    let menus = val.map((v) => {
                        return {
                            text: v.name,
                            onclick: function () {
                                editor.insertContent('<span data-var="' + v.key + '" class="tinymce-pckg mceNonEditable">' + v.name + '</span>');
                            }
                        };
                    });

                    editor.addMenuItem('pckgElementMenuItem', {
                        text: 'Variables',
                        context: 'tools',
                        menu: menus
                    });
                };

            } else if (key === 'setup') {
                var tempSetup = defaultConfig.setup;
                defaultConfig.setup = function (editor) {
                    tempSetup(editor);
                    val(editor);
                }
            } else {
                defaultConfig[key] = val;
            }
        });
    }

    if (!window.pckgEditors) {
        window.pckgEditors = {};
        
        /**
         * this workaround makes magic happen
         * thanks @harry: http://stackoverflow.com/questions/18111582/tinymce-4-links-plugin-modal-in-not-editable
         */
        $(document).on('focusin', function (e) {
            if ($(e.target).closest(".mce-window").length) {
                e.stopImmediatePropagation();
            }
        });

        if ($dispatcher) {
            $dispatcher.$emit('tinymce:init:first', tinymce);
        }
    }

    // link themes, plugins and skins
    tinymce.baseURL = '/storage/static/tinymce';

    window.pckgEditors[selector] = tinymce.init(defaultConfig);

    return window.pckgEditors[selector];
};
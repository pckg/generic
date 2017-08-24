var pckgEditors = {}, initTinymce;
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
        $.each(Pckg.config.editor.variables || {}, function(parentName, subVariables){
            var submenus = [];
            $.each(subVariables, function(name, key){
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
    tinymce.baseURL = '/bower_components/tinymce/';

    initTinymce = function (selector) {
        var selected = $('#' + selector);
        selected.append('<div class="manual-dropzone"></div>');
        var manualDropzone = selected.parent().find('.manual-dropzone');
        return tinymce.init({
            content_css: '/app/derive/src/Pckg/Generic/public/tinymce.css',
            selector: '#' + selector,
            height: 500,
            convert_urls: false,
            theme: 'modern',
            extended_valid_elements: 'pc-kg',
            custom_elements: 'pc-kg',
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code fullscreen',
                'insertdatetime media nonbreaking save table contextmenu directionality',
                'emoticons template paste textcolor colorpicker textpattern imagetools codesample pckg noneditable'
            ],
            toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            toolbar2: 'print preview media | forecolor backcolor emoticons | codesample',
            image_advtab: true,
            allow_html_in_named_anchor: true,
            allow_unsafe_link_target: true,
            forced_root_block: false,
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
                {title: 'Default (.editor-img)', value: 'editor-img'}
            ],
            images_upload_url: '/dynamic/uploader',
            automatic_uploads: false,
            file_picker_callback: function (cb, value, meta) {
                console.log(cb, value, meta);

                manualDropzone.dropzone({
                    url: '/dynamic/uploader',
                    previewsContainer: null,
                    previewTemplate: '<div></div>',
                    maxFilesize: 8,
                    success: function (file, data) {
                        data = $.parseJSON(data);
                        console.log(file, data);

                        cb(data.url, {title: null, class: 'pckg-img'});
                    }
                });

                manualDropzone.click();

                // Note: In modern browsers input[type="file"] is functional without
                // even adding it to the DOM, but that might not be the case in some older
                // or quirky browsers like IE, so you might want to add it to the DOM
                // just in case, and visually hide it. And do not forget do remove it
                // once you do not need it anymore.

                /*input.onchange = function() {
                 var file = this.files[0];

                 console.log("changed", file, this);
                 return;

                 // Note: Now we need to register the blob in TinyMCEs image blob
                 // registry. In the next release this part hopefully won't be
                 // necessary, as we are looking to handle it internally.
                 var id = 'blobid' + (new Date()).getTime();
                 var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                 var blobInfo = blobCache.create(id, file);
                 blobCache.add(blobInfo);

                 // call the callback and populate the Title field with the file name
                 cb(blobInfo.blobUri(), { title: file.name });
                 };

                 input.click();*/
                /*
                 console.log(callback, value, meta);*/
            }
        });
    }

    $('textarea.editor').each(function () {
        var selector = null;
        if ($(this).attr('id')) {
            selector = '#' + $(this).attr('id');
        } else {
            selector = 'html-editor-' + Math.round((Math.random() * 100000));
            $(this).attr('id', selector);
        }
    });

    $('.pckg-editor-enabled').each(function(){
        var id = $(this).attr('id');
        pckgEditors[id] = initTinymce(id);
    });

    $('.pckg-editor-toggle').on('click', function () {
        var id = $(this).closest('div').find('textarea.editor').attr('id');
        if (pckgEditors[id]) {
            tinymce.remove('#' + id);
            delete pckgEditors[id];
        } else {
            pckgEditors[id] = initTinymce(id);
        }
    });
});
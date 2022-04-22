$(document).ready(function () {

    let PluginManager = tinymce.util.Tools.resolve('tinymce.PluginManager');

    PluginManager.add('comms', function (editor, url) {
        editor.addButton('close', {
            text: 'Close',
            icon: false,
            onclick: function () {
                if (!confirm('Do you want to lose any unsaved changes?')) {
                    return;
                }
                editor.destroy();
                // Open window
                /*editor.windowManager.open({
                    title: 'Example plugin',
                    body: [
                        {type: 'textbox', name: 'title', label: 'Title'}
                    ],
                    onsubmit: function (e) {
                        // Insert content when the window form is submitted
                        editor.insertContent('Title: ' + e.data.title);
                    }
                });*/
            }
        });
        editor.addButton('commsCancel', {
            text: 'Cancel',
            icon: false,
            onclick: function () {
                if (!confirm('Do you want to cancel any unsaved changes?')) {
                    return;
                }
                editor.buttons.cancel.onclick(editor, url);
            }
        });

        // Adds a menu item to the tools menu
        /*editor.addMenuItem('example', {
            text: 'Example plugin',
            context: 'tools',
            onclick: function() {
                // Open window with a specific url
                editor.windowManager.open({
                    title: 'TinyMCE site',
                    url: 'https://www.tinymce.com',
                    width: 800,
                    height: 600,
                    buttons: [{
                        text: 'Close',
                        onclick: 'close'
                    }]
                });
            }
        });*/

        return {
            getMetadata: function () {
                return {
                    name: "Comms TinyMCE plugin",
                    url: "https://comms.dev/"
                };
            }
        };
    });

    document.createElement('pc-kg');
    PluginManager.add('pckg', function (editor, url) {
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
        if (Pckg && Pckg.config && Pckg.config.editor && Pckg.config.editor.variables) {
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
        } else {
            menus.push({
                text: 'Test variable',
                onclick: function () {
                    editor.insertContent('<span class="tinymce-pckg mceNonEditable">Test variable</span>');
                }
            });
        }

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
    /*tinymce.baseURL = Pckg && Pckg.data && Pckg.data.dimensions ? '/node_modules/tinymce' : '/storage/tinymce-themes';

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
    });*/

    /**
     * this workaround makes magic happen
     * thanks @harry: http://stackoverflow.com/questions/18111582/tinymce-4-links-plugin-modal-in-not-editable
     */
});
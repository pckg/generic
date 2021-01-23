tinymce.PluginManager.add('comms', function (editor, url) {
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
var initUninitialiedSelectpicker = function () {
    $('.pckg-selectpicker:not(.initialized)').each(function () {
        $(this).addClass('initialized');
        var $select = $(this);
        $select.selectpicker({liveSearch: true});
        var dataRefreshUrl = $select.attr('data-refresh-url');
        if ($select.hasClass('ajax') || (dataRefreshUrl && dataRefreshUrl.length > 0)) {
            var searchTimeout;
            $select.parent().find('.bs-searchbox input').on('keydown keyup change', function () {
                var $input = $(this);
                clearTimeout(searchTimeout);
                var val = $input.val();
                searchTimeout = setTimeout(function () {
                    console.log("searching ...");
                    http.getJSON(dataRefreshUrl + '?search=' + val, function (data) {
                        var val = $select.val();
                        $select.find('option').remove();
                        $.each(data.records, function (key, val) {
                            if (typeof val == 'object' || typeof val == 'array') {
                                var optgroup = '<optgroup label="' + key + '">';
                                $.each(val, function (k, v) {
                                    optgroup += '<option value="' + (k === 0 ? '' : k) + '">' + v + '</option>';
                                });
                                optgroup += '</optgroup>';
                                $select.append(optgroup);
                            } else {
                                $select.append('<option value="' + (key === 0 ? '' : key) + '">' + val + '</option>');
                            }
                        });
                        $select.val(val);
                        $select.selectpicker('refresh');
                    });
                }, 500);
            });
        }
    });
};

$(document).ready(function () {

    $('[data-toggle="popover"]').popover({
        html: true,
        container: 'body'
    });

    $('a.btn.delete').on('click', function () {
        var $a = $(this);
        if (confirm('Do you really want to delete it?')) {
            $.get($a.attr('href'), function (data) {
                $a.closest('tr').detach();
            });
        }
    });

    $('form div:not(.checkbox) > label').on('click', function (e) {
        e.preventDefault();

        return false;
    });

    $('input.toggle-vertically[type=checkbox]').on('click', function () {
        $(this).closest('table').find('tr td:nth-child(' + ($(this).closest('th').index() + 1) + ') input[type=checkbox]').prop('checked', $(this).is(':checked'));
    });

    $('input.toggle-horizontally[type=checkbox]').on('click', function () {
        $(this).closest('tr').find('td input[type=checkbox]').prop('checked', $(this).is(':checked'));
    });

    $('input.datetime').datetimepicker({
        format: 'YYYY-MM-DD HH:mm'
    }).on('dp.change', function (ev) {
        //$(this).datetimepicker('hide');
    });

    $('input.date').datetimepicker({
        format: 'YYYY-MM-DD'
    }).on('dp.change', function (ev) {
        //$(this).datetimepicker('hide');
    });

    $('input.time').datetimepicker({
        format: 'HH:mm'
    }).on('dp.change', function (ev) {
        //$(this).datetimepicker('hide');
    });

    $('.form-group .input-group .fa.fa-calendar').on('click', function () {
        $(this).closest('.input-group').find('input.datetime').focus();
    });

    /* --- SIDEBAR AND CONTENT CONTAINER ---- */

    /* ELEMENTS */
    /* sidebar container */
    $sidebar = $(".maestro-sidebar");
    /* sidebar background */
    $sidebarBg = $(".maestro-sidebar-background");
    /* content container */
    $content = $(".maestro-content");
    /* link for expanding and collapsing */
    $sidebarCollapse = $(".maestro-sidebar .collapse-sidebar a");

    /* SETTINGS */

    /* FUNCTIONS */

    /* Collapse or expand */
    function sidebarCollapseExpand() {
        //sidebar is collapsed and needs to be expanded
        if (isSidebarCollapsed()) {
            $sidebar.removeClass('collapsed');
            $sidebarBg.removeClass('collapsed');
            $content.removeClass('expanded');
            setCookie('maestro-sidebar-collapsed', false);
        }
        //sidebar is expanded and needs to be collapsed
        else {
            $sidebar.addClass('collapsed');
            $sidebarBg.addClass('collapsed');
            $content.addClass('expanded');
            $sidebar.find('.collapse.in').removeClass('in');
            setCookie('maestro-sidebar-collapsed', true);
        }
    }

    function isSidebarCollapsed() {
        return ($sidebar.hasClass('collapsed') ? true : false);
    }

    function collapsedHoverOn() {
        $('.maestro-sidebar #main-admin-nav, .maestro-sidebar #main-admin-user-nav').on('mouseenter.collapse.data-api', '[data-toggle=collapse]', function (e) {
            collapsed = $(".maestro-sidebar").hasClass('collapsed');

            var $this = $(this),
                href, target = $this.attr('data-target') || e.preventDefault() || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, ''); //strip for ie7

            if (collapsed) {
                //show submenu
                $(target).addClass('in');
                //add class to parent
                $(target).parent().addClass('hoveractive');
                //show submenu background
                //$('.maestro-sidebar-submenu-background').show();
            }
            //when user leaves the submenu with mouse
            $(target).parent().on('mouseleave', function () {
                if (collapsed) {
                    //hide submenu
                    $(target).removeClass('in');
                    //remove class from parent
                    $(target).parent().removeClass('hoveractive');
                    //hide  submenu background
                    //$('.maestro-sidebar-submenu-background').hide();
                }
            })
            //prevent click action on menu
                .on('click.collapse.data-api', '[data-toggle=collapse]', function (e) {
                    if (collapsed) {
                        e.stopPropagation();
                        return false;
                    }
                })
        })
    }

    /* EVENTS */

    /* when user wants to collapse or expande the menu */
    $sidebarCollapse.click(function () {
        sidebarCollapseExpand();
    });

    /* fix search, group actions and table header on lists */

    function affixFromTop() {
        if ($('.affix-dynamic-offset').length) {
            offsetFromTop = $('.affix-dynamic-offset').offset().top;

            /*  */
            $('.affix-thead-fixed, .affix-dynamic-offset').each(function () {
                $(this).affix({
                    offset: {
                        top: offsetFromTop
                    }
                })
            });

            $('.affix-thead-container').each(function () {
                cwidth = $(this).children('.affix-thead-static').width();
                cheight = $(this).children('.affix-thead-static').height();
                $(this).children('.affix-thead-fixed').width(cwidth);
                $('.affix-dynamic-offset').css('padding-bottom', 40 + cheight);
            })

            //set width when fixed
            $('.affix-dynamic-offset').on('affix.bs.affix', function () {
                $(this).width($(document).width() - $(".maestro-sidebar").width()).css('left', $(".maestro-sidebar").width());
            })
            //default width when not fixed
            $('.affix-dynamic-offset').on('affixed-top.bs.affix', function () {
                $(this).width('auto');
            })
            //on ready set width if fixed
            if ($('.affix-dynamic-offset').hasClass('affix')) {
                $(this).width($(document).width() - $(".maestro-sidebar").width()).css('left', $(".maestro-sidebar").width());
            }
        }
    }

    //affixFromTop();
    collapsedHoverOn();

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

        // Adds a menu item to the tools menu
        editor.addMenuItem('pckgElementMenuItem', {
            text: 'Variables',
            context: 'tools',
            menu: [
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
            ]
        });
    });

    tinymce.baseURL = '/bower_components/tinymce/';

    function initTinymce(selector) {
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

    var editors = {};
    $('.pckg-editor-toggle').on('click', function () {
        var id = $(this).closest('div').find('textarea.editor').attr('id');
        if (editors[id]) {
            tinymce.remove('#' + id);
            delete editors[id];
        } else {
            editors[id] = initTinymce(id);
        }
    });

    initUninitialiedSelectpicker();
});
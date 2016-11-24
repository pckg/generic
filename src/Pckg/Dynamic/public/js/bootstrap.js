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
    });
    $('input.date').datetimepicker({
        format: 'YYYY-MM-DD'
    });
    $('input.time').datetimepicker({
        format: 'HH:mm'
    });
    $('.form-group .input-group .fa.fa-calendar').on('click', function () {
        $(this).closest('.input-group').find('input.datetime').focus();
    });

    $('textarea.editor').each(function () {
        var selector = null;
        if ($(this).attr('id')) {
            selector = '#' + $(this).attr('id');
        } else {
            selector = 'html-editor-' + Math.round((Math.random() * 100000));
            $(this).attr('id', selector);

        }

        function initTinymce(selector) {
            tinymce.init({
                selector: '#' + selector, height: 500,
                theme: 'modern',
                plugins: [
                    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars code fullscreen',
                    'insertdatetime media nonbreaking save table contextmenu directionality',
                    'emoticons template paste textcolor colorpicker textpattern imagetools codesample'
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
                ]/*,
                content_css: [
                    '/css/bootstrap.css',
                    '/css/default.css',
                ]*/
            });
        }

        var val = $(this).val();
        if (val.indexOf('{% ') < 0 && val.indexOf('{{ ') < 0 && val.indexOf('<') == 0 && val.split("\n").length > 1) {
            initTinymce(selector);
        } else {
            var changeEvent = $(this).on('focus', function () {
                if (confirm('Do you want to enable editor?')) {
                    initTinymce(selector);
                }
                changeEvent.off();
            })
        }
    });
});
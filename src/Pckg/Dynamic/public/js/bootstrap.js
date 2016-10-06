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
        $(this).summernote({
            resetCss: true,
            minHeight: '240px'
        });
    });
});
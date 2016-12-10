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
        $(this).datetimepicker('hide');
    });

    $('input.date').datetimepicker({
        format: 'YYYY-MM-DD'
    }).on('dp.change', function (ev) {
        $(this).datetimepicker('hide');
    });

    $('input.time').datetimepicker({
        format: 'HH:mm'
    }).on('dp.change', function (ev) {
        $(this).datetimepicker('hide');
    });

    $('.form-group .input-group .fa.fa-calendar').on('click', function () {
        $(this).closest('.input-group').find('input.datetime').focus();
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
                $(this).addClass('col-sm-10');
            })
            //default width when not fixed
            $('.affix-dynamic-offset').on('affixed-top.bs.affix', function () {
                $(this).removeClass('col-sm-10');
            })
            //on ready set width if fixed
            if ($('.affix-dynamic-offset').hasClass('affix')) {
                $('.affix-dynamic-offset').addClass('col-sm-10');
            }
        }
    }

    affixFromTop();

    function initTinymce(selector) {
        return tinymce.init({
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

    $('textarea.editor').each(function () {
        var selector = null;
        if ($(this).attr('id')) {
            selector = '#' + $(this).attr('id');
        } else {
            selector = 'html-editor-' + Math.round((Math.random() * 100000));
            $(this).attr('id', selector);

        }

        /*var val = $(this).val();
         if (val.indexOf('{% ') < 0 && val.indexOf('{{ ') < 0 && val.indexOf('<') == 0 && val.split("\n").length > 1) {
         initTinymce(selector);
         } else {
         var changeEvent = $(this).on('focus', function () {
         if (confirm('Do you want to enable editor?')) {
         initTinymce(selector);
         }
         changeEvent.off();
         })
         }*/
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

    $('.pckg-selectpicker').each(function () {
        var $select = $(this);
        $select.selectpicker();
        if ($select.hasClass('ajax')) {
            var searchTimeout;
            $select.parent().find('.bs-searchbox input').on('keydown', function () {
                var $input = $(this);
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    var val = $input.val();
                    http.getJSON($select.attr('data-refresh-url') + '?search=' + val, function (data) {
                        var val = $select.val();
                        $select.find('option').remove();
                        $.each(data.records, function (key, val) {
                            $select.append('<option value="' + key + '">' + val + '</option>');
                        });
                        $select.val(val);
                        $select.selectpicker('refresh');
                    });
                }, 500);
            });
        }
    });
});
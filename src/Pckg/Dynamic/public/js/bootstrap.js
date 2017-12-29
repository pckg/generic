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
                        $select.selectpicker('refresh');
                        $select.val(val).change();
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

    $('input.datetime:not(.vue-takeover)').datetimepicker({
        format: 'YYYY-MM-DD HH:mm'
    })/*.on('dp.change', function (ev) {
     //$(this).datetimepicker('hide');
     })*/;

    $('input.date:not(.vue-takeover)').datetimepicker({
        format: 'YYYY-MM-DD'
    })/*.on('dp.change', function (ev) {
     //$(this).datetimepicker('hide');
     })*/;

    $('input.time:not(.vue-takeover)').datetimepicker({
        format: 'HH:mm'
    })/*.on('dp.change', function (ev) {
     //$(this).datetimepicker('hide');
     })*/;

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

    initUninitialiedSelectpicker();
});
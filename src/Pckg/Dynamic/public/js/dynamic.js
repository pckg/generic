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
                        $select.val(val);
                    });
                }, 500);
            });
        }
    });
};

let checkSidebarPosition = function () {
    let sidebarInside = parseFloat($('.maestro-sidebar-inside').outerHeight());
    let bottomBar = parseFloat($('.maestro-sidebar-bottom').outerHeight());
    let viewportHeight = parseFloat($(window).height());
    let offset = 50;

    if (viewportHeight >= sidebarInside + bottomBar + offset) {
        $('body').removeClass('sidebar-static');
        return;
    } else if ($('body').hasClass('sidebar-static')) {
        return;
    }

    $('body').addClass('sidebar-static');
};

$(document).ready(function () {
    checkSidebarPosition();
    $(window).on('resize', checkSidebarPosition);
    var $body = $('body');

    /**
     * Start fix multiple modals for scroll
     */
    $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal:visible').length && $(document.body).addClass('modal-open');
    });
    /**
     * End fix multiple modals for scroll
     */

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
            $body.removeClass('collapsed');
            setCookie('maestro-sidebar-collapsed', false);
        }
        //sidebar is expanded and needs to be collapsed
        else {
            $body.addClass('collapsed');
            $sidebar.find('.collapse.in').removeClass('in');
            setCookie('maestro-sidebar-collapsed', true);
        }
    }

    function isSidebarCollapsed() {
        return ($body.hasClass('collapsed') ? true : false);
    }

    function collapsedHoverOn() {
        $('.maestro-sidebar #main-admin-nav').on('mouseenter.collapse.data-api', '[data-toggle=collapse]', function (e) {
            collapsed = $body.hasClass('collapsed');

            var $this = $(this),
                href,
                target = $this.attr('data-target') || e.preventDefault() || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, ''); //strip for ie7

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

    //affixFromTop();
    collapsedHoverOn();

    initUninitialiedSelectpicker();
});
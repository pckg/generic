(function ($) {

    var forms = [
        {
            css: 'sort',
            id: 'Sort'
        },
        {
            css: 'filter',
            id: 'Filter'
        },
        {
            css: 'group',
            id: 'Group'
        }
    ];

    $(document).ready(function () {

        $(forms).each(function (i, val) {

            $('#form' + val.id + ' input[name=add]').on('click', function () {
                var $actions = $(this).closest('.actions');
                var $field = $actions.prev();

                $('.' + val.css + '-applied .panel-body').append($field.detach());
                $('.' + val.css + '-applied .panel-body').append($actions.detach());
            });

            $('#form' + val.id + ' input[name=remove]').on('click', function () {
                var $actions = $(this).closest('.actions');
                var $field = $actions.prev();

                $('.' + val.css + '-skipped .panel-body fieldset.submit').before($field.detach());
                $('.' + val.css + '-skipped .panel-body fieldset.submit').before($actions.detach());
            });

        });

    });

})(jQuery);
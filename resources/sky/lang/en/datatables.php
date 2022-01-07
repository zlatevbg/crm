<?php

return [
    'language' => [
        'decimal' => '',
        'emptyTable' => 'No data available in table',
        'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
        'infoEmpty' => 'Showing 0 to 0 of 0 entries',
        'infoFiltered' => '(filtered from _MAX_ total entries)',
        'infoPostFix' => '',
        'thousands' => ',',
        'lengthMenu' => 'Show _MENU_ entries',
        'loadingRecords' => 'Loading...',
        'processing' => '<span class="fa-left"><i class="fas fa-spinner fa-pulse mt-1"></i>Processing...</span>',
        'search' => '',
        'searchPlaceholder' => 'Search',
        'zeroRecords' => 'No matching records found',

        'paginate' => [
            'first' => 'First',
            'last' => 'Last',
            'next' => 'Next',
            'previous' => 'Previous',
        ],

        'aria' => [
            'first' => ': activate to sort column ascending',
            'last' => ': activate to sort column descending',
        ],
    ],

    'lengthOptions' => [
        's' => [[10, 25, 50, -1], [10, 25, 50, "All"]],
        'm' => [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "All"]],
        'l' => [[10, 25, 50, 100, 250, 500, 1000], [10, 25, 50, 100, 250, 500, 1000]],
    ],
];

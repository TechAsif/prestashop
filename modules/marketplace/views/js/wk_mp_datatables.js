/**
* 2010-2021 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through LICENSE.txt file inside our module
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright 2010-2021 Webkul IN
* @license LICENSE.txt
*/

$(document).ready(function() {
    if ($("#wk_datatable_list").length) {
        $('#wk_datatable_list').DataTable({
            "bStateSave": true,
            "order": [],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "language": {
                "lengthMenu": display_name + " _MENU_ " + records_name,
                "zeroRecords": no_product,
                "info": show_page + " _PAGE_ " + show_of + " _PAGES_ ",
                "infoEmpty": no_record,
                "infoFiltered": "(" + filter_from + " _MAX_ " + t_record + ")",
                "sSearch": search_item,
                "oPaginate": {
                    "sPrevious": p_page,
                    "sNext": n_page
                }
            }
        });

        $('select[name="wk_datatable_list_length"]').addClass('form-control-select');
    }
});
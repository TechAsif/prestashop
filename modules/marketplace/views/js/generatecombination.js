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
    $('.price_impact').each(function() { calcPrice($(this), false); });
});

function attr_selectall() {
    var elem = getE('product_att_list');
    if (elem) {
        var i;
        for (i = 0; i < elem.length; i++)
            elem.options[i].selected = true;
    }
}

function create_attribute_row(id, id_group, name, price, price_tax_incl, weight) {
    var html = '';
    html += '<tr id="result_' + id + '">';
    html += '<td><input type="hidden" value="' + id + '" name="options[' + id_group + '][' + id + ']" />' + name + '</td>';
    html += '<td>' + i18n_tax_exc + '<input id="related_to_price_impact_ti_' + id + '" class="text-center price_impact form-control" style="width:70px" type="text" value="' + price + '" name="price_impact_' + id + '" onkeyup="calcPrice($(this), false)" pattern="^-?\\d+(\\.\\d+)?"></td>';
    html += '<td><input style="width:50px;margin-top:15px;" type="text" class="text-center form-control" value="' + weight + '" name="weight_impact_' + id + '" pattern="^-?\\d+(\\.\\d+)?"></td>';
    html += '</tr>';

    return html;
}

function add_attr_multiple() {
    var attr = getE('attribute_group');
    if (!attr)
        return;
    var length = attr.length;
    var target;
    var new_elem;

    for (var i = 0; i < length; ++i) {
        elem = attr.options[i];
        if (elem.selected) {
            name = elem.parentNode.getAttribute('name');
            target = $('#table_' + name);
            if (target && !getE('result_' + elem.getAttribute('name'))) {
                //target.css('display', 'block');
                new_elem = create_attribute_row(elem.getAttribute('name'), elem.parentNode.getAttribute('name'), elem.value, '0.00', '0.00', '0');
                target.append(new_elem);
                toggle(target.parent()[0], true);
            }
        }
    }
}

function del_attr_multiple() {
    var attr = getE('attribute_group');

    if (!attr)
        return;
    var length = attr.length;
    var target;

    for (var i = 0; i < length; ++i) {
        elem = attr.options[i];
        if (elem.selected) {
            target = getE('table_' + elem.parentNode.getAttribute('name'));
            if (target && getE('result_' + elem.getAttribute('name'))) {
                target.removeChild(getE('result_' + elem.getAttribute('name')));
                // if (!target.lastChild || !target.lastChild.id) {
                //     toggle(target.parentNode, false);
                // }
            }
        }
    }
}

function getE(element) {
    return document.getElementById(element)
}

function calcPrice(element, element_has_tax) {
    var element_price = parseFloat(element.val().replace(/,/g, '.'));
    var other_element_price = 0;
    if (!isNaN(element_price)) {
        if (element_has_tax) {
            other_element_price = ps_round(parseFloat(parseFloat(element_price) / ((parseFloat(product_tax) / 100) + 1)), 2).toFixed(2);
        } else {
            other_element_price = ps_round(parseFloat(parseFloat(element_price) * ((parseFloat(product_tax) / 100) + 1)), 2).toFixed(2);
        }
    }
    $('#related_to_' + element.attr('name')).val(other_element_price);
}
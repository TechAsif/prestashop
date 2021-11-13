/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

 var Tools = {

  /**
   * Constructs a float value from an arbitrarily-formatted string.
   * In order to prevent unexpected behavior, make sure that your value has a decimal part.
   * @param {String} value Value to convert to float
   * @param {Boolean} [coerce=false] If true, this function will return 0 instad of NaN if the value cannot be parsed to float
   *
   * @return {Number}
   */
  parseFloatFromString: function(value, coerce) {
    value = String(value).trim();

    if ('' === value) {
      return 0;
    }

    // check if the string can be converted to float as-is
    var parsed = parseFloat(value);
    if (String(parsed) === value) {
      return parsed;
    }

    // replace arabic numbers by latin
		value = value
			// arabic
			.replace(/[\u0660-\u0669]/g, function(d) {
				return d.charCodeAt(0) - 1632;
			})
			// persian
			.replace(/[\u06F0-\u06F9]/g, function(d) {
        return d.charCodeAt(0) - 1776;
      })
		;

    // remove all non-digit characters
    var split = value.split(/[^\dE-]+/);

    if (1 === split.length) {
      // there's no decimal part
      return parseFloat(value);
    }

    for (var i = 0; i < split.length; i++) {
      if ('' === split[i]) {
        return coerce ? 0 : NaN;
      }
    }

    // use the last part as decimal
    var decimal = split.pop();

    // reconstruct the number using dot as decimal separator
    return parseFloat(split.join('') +  '.' + decimal);
  }
};

function truncateDecimals(value, decimals) {
  var numPower = Math.pow(10, decimals);
  var tempNumber = value * numPower;
  var roundedTempNumber = Math.floor(tempNumber);
  return roundedTempNumber / numPower;
}

$(document).ready(function () {
  //remove collection item
  $(document).on('click', '#form_step2_specific_price_sp_id_customer-data .delete', function (e) {
    e.preventDefault();
    var _this = $(this);

    modalConfirmation.create(translate_javascripts['Are you sure to delete this?'], null, {
      onContinue: function () {
        _this.parent().parent().hide();
        _this.parent().remove();
      }
    }).show();
  });

  //define source
  this['form_step2_specific_price_sp_id_customer_source'] = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    identify: function (obj) {
      return obj.id_customer;
    },
    remote: {
      url: 'http://localhost/ps174/admin084oazcuj/index.php?controller=AdminCustomers&token=1fdd86b8965b8eb9b2d872bcaac2b1ce&sf2=1&ajax=1&tab=AdminCustomers&action=searchCustomers&customer_search=%QUERY',
      cache: false,
      wildcard: '%QUERY',
      transform: function (response) {
        if (!response) {
          return [];
        }
        return response;
      }
    }
  });

  //define typeahead
  $('#form_step2_specific_price_sp_id_customer').typeahead({
    limit: 200,
    minLength: 2,
    highlight: true,
    cache: false,
    hint: false,
  }, {
    display: 'fullname_and_email',
    source: this['form_step2_specific_price_sp_id_customer_source'],
    limit: 30,
    templates: {
      suggestion: function (item) {
        return '<div>' + item.fullname_and_email + '</div>'
      }
    }
  }).bind('typeahead:select', function (ev, suggestion) {

    //if collection length is up to limit, return
    if (1 != 0 && $('#form_step2_specific_price_sp_id_customer-data li').length >= 1) {
      return;
    }

    var value = suggestion.id_customer;
    if (suggestion.id_product_attribute) {
      value = value + ',' + suggestion.id_product_attribute;
    }

    var html = '<li class="media">';
    html += sprintf('<div class="media-body"><div class="label">%s</div><i class="material-icons delete">clear</i></div>', suggestion.fullname_and_email);
    html += '<input type="hidden" name="form[step2][specific_price][sp_id_customer][data][]" value="' + value + '" />';
    html += '</li>';
    $('#form_step2_specific_price_sp_id_customer-data').show();
    $('#form_step2_specific_price_sp_id_customer-data').append(html);

  }).bind('typeahead:close', function (ev) {
    $(ev.target).val('');
  });
});

$(document).ready(function () {
  specificPrices.init();
  priceCalculation.init();


  // /** Attach date picker */
  // $('.datepicker').datetimepicker({
  //   locale: full_language_code,
  //   format: 'YYYY-MM-DD'
  // });

  /** tooltips should be hidden when we move to another tab */
  $('#form-nav').on('click', '.nav-item', function clearTooltipsAndPopovers() {
    $('[data-toggle="tooltip"]').tooltip('hide');
    $('[data-toggle="popover"]').popover('hide');
  });
});


/**
 * Specific prices management
 */
var specificPrices = (function () {
  var id_product = $('#form_id_product').val();
  var elem = $('#js-specific-price-list');
  var leaveInitialPrice = $('#form_step2_specific_price_leave_bprice');
  var productPriceField = $('#form_step2_specific_price_sp_price');
  var discountTypeField = $('#form_step2_specific_price_sp_reduction_type');
  var discountTaxField = $('#form_step2_specific_price_sp_reduction_tax');
  var initSpecificPriceForm = new Object();

  /** Get all specific prices */
  function getInitSpecificPriceForm() {
    $('#specific_price_form').find('select,input').each(function () {
      initSpecificPriceForm[$(this).attr('id')] = $(this).val();
    });
    $('#specific_price_form').find('input:checkbox').each(function () {
      initSpecificPriceForm[$(this).attr('id')] = $(this).prop('checked');
    });
  }

  /** Get all specific prices */
  function getAll() {
    var url = elem.attr('data').replace(/list\/\d+/, 'list/' + id_product);

    $.ajax({
      type: 'GET',
      url: url,
      success: function (specific_prices) {
        var tbody = elem.find('tbody');
        tbody.find('tr').remove();

        if (specific_prices.length > 0) {
          elem.removeClass('hide');
        } else {
          elem.addClass('hide');
        }

        $.each(specific_prices, function (key, specific_price) {
          var row = '<tr>' +
            '<td>' + specific_price.rule_name + '</td>' +
            '<td>' + specific_price.attributes_name + '</td>' +
            '<td>' + specific_price.currency + '</td>' +
            '<td>' + specific_price.country + '</td>' +
            '<td>' + specific_price.group + '</td>' +
            '<td>' + specific_price.customer + '</td>' +
            '<td>' + specific_price.fixed_price + '</td>' +
            '<td>' + specific_price.impact + '</td>' +
            '<td>' + specific_price.period + '</td>' +
            '<td>' + specific_price.from_quantity + '</td>' +
            '<td>' + (specific_price.can_delete ? '<a href="' + $('#js-specific-price-list').attr('data-action-delete').replace(/delete\/\d+/, 'delete/' + specific_price.id_specific_price) + '" class="js-delete delete btn tooltip-link delete pl-0 pr-0"><i class="material-icons">delete</i></a>' : '') + '</td>' +
            '</tr>';

          tbody.append(row);
        });
      }
    });
  }

  /**
   * Add a specific price
   * @param {object} elem - The clicked link
   */
  function add(elem) {
    $.ajax({
      type: 'POST',
      url: $('#specific_price_form').attr('data-action'),
      data: $('#specific_price_form input, #specific_price_form select, #form_id_product').serialize(),
      beforeSend: function () {
        elem.attr('disabled', 'disabled');
      },
      success: function () {
        showSuccessMessage(translate_javascripts['Form update success']);
        $('#specific_price_form .js-cancel').click();
        getAll();
      },
      complete: function () {
        elem.removeAttr('disabled');
      },
      error: function (errors) {
        showErrorMessage(errors.responseJSON);
      }
    });
  }

  /**
   * Remove a specific price
   * @param {object} elem - The clicked link
   */
  function remove(elem) {
    modalConfirmation.create(translate_javascripts['This will delete the specific price. Do you wish to proceed?'], null, {
      onContinue: function () {
        $.ajax({
          type: 'GET',
          url: elem.attr('href'),
          beforeSend: function () {
            elem.attr('disabled', 'disabled');
          },
          success: function (response) {
            getAll();
            resetForm();
            showSuccessMessage(response);
          },
          error: function (response) {
            showErrorMessage(response.responseJSON);
          },
          complete: function () {
            elem.removeAttr('disabled');
          }
        });
      }
    }).show();
  }

  /** refresh combinations list selector for specific price form */
  function refreshCombinationsList() {
    var elem = $('#form_step2_specific_price_sp_id_product_attribute');
    var url = elem.attr('data-action').replace(/product-combinations\/\d+/, 'product-combinations/' + id_product);

    $.ajax({
      type: 'GET',
      url: url,
      success: function (combinations) {
        /** remove all options except first one */
        elem.find('option:gt(0)').remove();

        $.each(combinations, function (key, combination) {
          elem.append('<option value="' + combination.id + '">' + combination.name + '</option>');
        });
      }
    });
  }

  /**
   * Because all "forms" are encapsulated in a global form, we just can't use reset button
   * Reset all subform inputs values
   */
  function resetForm() {
    $('#specific_price_form').find('input').each(function () {
      $(this).val(initSpecificPriceForm[$(this).attr('id')]);
    });
    $('#specific_price_form').find('select').each(function () {
      $(this).val(initSpecificPriceForm[$(this).attr('id')]).change();
    });
    $('#specific_price_form').find('input:checkbox').each(function () {
      $(this).prop("checked", true);
    });
  }

  return {
    'init': function () {
      this.getAll();

      $('#specific-price .add').click(function () {
        $(this).hide();
      });

      $('#specific_price_form .js-cancel').click(function () {
        resetForm();
        $('#specific-price > a').click();
        $('#specific-price .add').click().show();
        productPriceField.prop('disabled', true);
      });

      $('#specific_price_form .js-save').click(function () {
        add($(this));
      });

      $(document).on('click', '#js-specific-price-list .js-delete', function (e) {
        e.preventDefault();
        remove($(this));
      });

      $('#form_step2_specific_price_sp_reduction_type').change(function () {
        if ($(this).val() === 'percentage') {
          $('#form_step2_specific_price_sp_reduction_tax').hide();
        } else {
          $('#form_step2_specific_price_sp_reduction_tax').show();
        }
      });

      this.refreshCombinationsList();

      /* enable price field only when needed */
      leaveInitialPrice.on('click', function togglePriceField() {
        productPriceField.prop('disabled', $(this).is(':checked'))
          .val('')
          ;
      });

      /* enable tax type field only when reduction by amount is selected */
      discountTypeField.on('change', function toggleDiscountTaxField() {
        var uglySelect2Selector = $('#select2-form_step2_specific_price_sp_reduction_tax-container').parent().parent();
        if ($(this).val() === 'amount') {
          uglySelect2Selector.show();
        } else {
          uglySelect2Selector.hide();
        }
      });

      this.getInitSpecificPriceForm();

    },
    'getAll': function () {
      getAll();
    },
    'refreshCombinationsList': function () {
      refreshCombinationsList();
    },
    'getInitSpecificPriceForm': function () {
      getInitSpecificPriceForm();
    }
  };
})();



/**
 * Price calculation
 */
var priceCalculation = (function () {
  var priceHTElem = $('#form_step2_price');
  var priceHTShortcutElem = $('#form_step1_price_shortcut');
  var priceTTCElem = $('#form_step2_price_ttc');
  var priceTTCShorcutElem = $('#form_step1_price_ttc_shortcut');
  var ecoTaxElem = $('#form_step2_ecotax');
  var taxElem = $('#form_step2_id_tax_rules_group');
  var reTaxElem = $('#step2_id_tax_rules_group_rendered');
  var displayPricePrecision = priceHTElem.attr('data-display-price-precision');
  var ecoTaxRate = ecoTaxElem.attr('data-eco-tax-rate');

  /**
   * Add taxes to a price
   * @param {Number} price - Price without tax
   * @param {Number[]} rates - Rates to apply
   * @param {Number} computationMethod The computation calculate method
   */
  function addTaxes(price, rates, computationMethod) {
    var price_with_taxes = price;

    var i = 0;
    if (computationMethod === '0') {
      for (i in rates) {
        price_with_taxes *= (1.00 + parseFloat(rates[i]) / 100.00);
        break;
      }
    } else if (computationMethod === '1') {
      var rate = 0;
      for (i in rates) {
        rate += rates[i];
      }
      price_with_taxes *= (1.00 + parseFloat(rate) / 100.00);
    } else if (computationMethod === '2') {
      for (i in rates) {
        price_with_taxes *= (1.00 + parseFloat(rates[i]) / 100.00);
      }
    }

    return price_with_taxes;
  }

  /**
   * Remove taxes from a price
   * @param {Number} price - Price with tax
   * @param {Number[]} rates - Rates to apply
   * @param {Number} computationMethod - The computation method
   */
  function removeTaxes(price, rates, computationMethod) {
    var i = 0;
    if (computationMethod === '0') {
      for (i in rates) {
        price /= (1 + rates[i] / 100);
        break;
      }
    } else if (computationMethod === '1') {
      var rate = 0;
      for (i in rates) {
        rate += rates[i];
      }
      price /= (1 + rate / 100);
    } else if (computationMethod === '2') {
      for (i in rates) {
        price /= (1 + rates[i] / 100);
      }
    }

    return price;
  }

  /**
   *
   * @return {Number}
   */
  function getEcotaxTaxIncluded() {
    var displayPrecision = 6;
    var ecoTax = Tools.parseFloatFromString(ecoTaxElem.val());

    if (isNaN(ecoTax)) {
      ecoTax = 0;
    }

    if (ecoTax === 0) {
      return ecoTax;
    }
    var ecotaxTaxExcl = ecoTax / (1 + ecoTaxRate);

    return ps_round(ecotaxTaxExcl * (1 + ecoTaxRate), displayPrecision);
  }

  function getEcotaxTaxExcluded() {
    return Tools.parseFloatFromString(ecoTaxElem.val()) / (1 + ecoTaxRate);
  }

  return {

    init: function () {
      /** on update tax recalculate tax include price */
      taxElem.change(function () {
        if (reTaxElem.val() !== taxElem.val()) {
          reTaxElem.val(taxElem.val()).trigger('change');
        }

        priceCalculation.taxInclude();
        priceTTCElem.change();
      });

      reTaxElem.change(function () {
        taxElem.val(reTaxElem.val()).trigger('change');
      });

      /** update without tax price and shortcut price field on change */
      $('#form_step1_price_shortcut, #form_step2_price').keyup(function () {
        var price = priceCalculation.normalizePrice($(this).val());

        if ($(this).attr('id') === 'form_step1_price_shortcut') {
          $('#form_step2_price').val(price).change();
        } else {
          $('#form_step1_price_shortcut').val(price).change();
        }

        priceCalculation.taxInclude();
      });

      /** update HT price and shortcut price field on change */
      $('#form_step1_price_ttc_shortcut, #form_step2_price_ttc').keyup(function () {
        var price = priceCalculation.normalizePrice($(this).val());

        if ($(this).attr('id') === 'form_step1_price_ttc_shortcut') {
          $('#form_step2_price_ttc').val(price).change();
        } else {
          $('#form_step1_price_ttc_shortcut').val(price).change();
        }

        priceCalculation.taxExclude();
      });

      /** on price change, update final retails prices */
      $('#form_step2_price, #form_step2_price_ttc').change(function () {
        var taxExcludedPrice = priceCalculation.normalizePrice($('#form_step2_price').val());
        var taxIncludedPrice = priceCalculation.normalizePrice($('#form_step2_price_ttc').val());

        formatCurrencyCldr(taxExcludedPrice, function (result) {
          $('#final_retail_price_te').text(result);
        });
        formatCurrencyCldr(taxIncludedPrice, function (result) {
          $('#final_retail_price_ti').text(result);
        });
      });

      /** update HT price and shortcut price field on change */
      $('#form_step2_ecotax').keyup(function () {
        priceCalculation.taxExclude();
      });

      /** combinations : update TTC price field on change */
      $(document).on('keyup', '.combination-form .attribute_priceTE', function () {
        priceCalculation.impactTaxInclude($(this));
        priceCalculation.impactFinalPrice($(this));
      });
      /** combinations : update HT price field on change */
      $(document).on('keyup', '.combination-form .attribute_priceTI', function () {
        priceCalculation.impactTaxExclude($(this));
      });
      /** combinations : update wholesale price, unity and price TE field on blur */
      $(document).on('blur', '.combination-form .attribute_wholesale_price,.combination-form .attribute_unity,.combination-form .attribute_priceTE', function () {
        $(this).val(priceCalculation.normalizePrice($(this).val()));
      });

      // priceCalculation.taxInclude();

      $('#form_step2_price, #form_step2_price_ttc').change();
    },

    /**
     * Converts a price string into a number
     * @param {String} price
     * @return {Number}
     */
    normalizePrice: function (price) {
      return Tools.parseFloatFromString(price, true);
    },

    /**
     * Adds taxes to a price
     * @param {Number} price Price without taxes
     * @return {Number} Price with added taxes
     */
    addCurrentTax: function (price) {
      var rates = this.getRates();
      var computation_method = taxElem.find('option:selected').attr('data-computation-method');
      var priceWithTaxes = Number(ps_round(addTaxes(price, rates, computation_method), displayPricePrecision));
      var ecotaxIncluded = Number(getEcotaxTaxIncluded());

      return priceWithTaxes + ecotaxIncluded;
    },

    /**
     * Calculates the price with taxes and updates the elements containing it
     */
    taxInclude: function () {
      var newPrice = truncateDecimals(this.addCurrentTax(this.normalizePrice(priceHTElem.val())), 6);

      priceTTCElem.val(newPrice).change();
      priceTTCShorcutElem.val(newPrice).change();
    },

    /**
     * Removes taxes from a price
     * @param {Number} price Price with taxes
     * @return {Number} Price without taxes
     */
    removeCurrentTax: function (price) {
      var rates = this.getRates();
      var computation_method = taxElem.find('option:selected').attr('data-computation-method');

      return ps_round(removeTaxes(ps_round(price - getEcotaxTaxIncluded(), displayPricePrecision), rates, computation_method), displayPricePrecision);
    },

    /**
     * Calculates the price without taxes and updates the elements containing it
     */
    taxExclude: function () {
      var newPrice = truncateDecimals(this.removeCurrentTax(this.normalizePrice(priceTTCElem.val())), 6);

      priceHTElem.val(newPrice).change();
      priceHTShortcutElem.val(newPrice).change();
    },

    /**
     * Calculates and displays the impact on price (including tax) for a combination
     * @param {jQuery} obj
     */
    impactTaxInclude: function (obj) {
      var price = Tools.parseFloatFromString(obj.val());
      var targetInput = obj.closest('div[id^="combination_form_"]').find('input.attribute_priceTI');
      var newPrice = 0;

      if (!isNaN(price)) {
        var rates = this.getRates();
        var computation_method = taxElem.find('option:selected').attr('data-computation-method');
        newPrice = ps_round(addTaxes(price, rates, computation_method), 6);
        newPrice = truncateDecimals(newPrice, 6);
      }

      targetInput
        .val(newPrice)
        .trigger('change')
        ;
    },

    /**
     * Calculates and displays the final price for a combination
     * @param {jQuery} obj
     */
    impactFinalPrice: function (obj) {
      var price = this.normalizePrice(obj.val());
      var finalPrice = obj.closest('div[id^="combination_form_"]').find('.final-price');
      var defaultFinalPrice = finalPrice.attr('data-price');
      var priceToBeChanged = Number(price) + Number(defaultFinalPrice);
      priceToBeChanged = truncateDecimals(priceToBeChanged, 6);

      finalPrice.html(priceToBeChanged);
    },

    /**
     * Calculates and displays the impact on price (excluding tax) for a combination
     * @param {jQuery} obj
     */
    impactTaxExclude: function (obj) {
      var price = Tools.parseFloatFromString(obj.val());
      var targetInput = obj.closest('div[id^="combination_form_"]').find('input.attribute_priceTE');
      var newPrice = 0;

      if (!isNaN(price)) {
        var rates = this.getRates();
        var computation_method = taxElem.find('option:selected').attr('data-computation-method');
        newPrice = removeTaxes(ps_round(price, displayPricePrecision), rates, computation_method);
        newPrice = truncateDecimals(newPrice, 6);
      }

      targetInput
        .val(newPrice)
        .trigger('change')
        ;
    },

    /**
     * Returns the tax rates that apply
     * @return {Number[]}
     */
    getRates: function () {
      return taxElem
        .find('option:selected')
        .attr('data-rates')
        .split(',')
        .map(function (rate) {
          return Tools.parseFloatFromString(rate, true);
        });
    }
  };
})();

/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(customThemeActions, {
	documentReady: function() {
		af.productItemSelector = '.js-product-miniature-wrapper';
		customThemeActions.updateListViewParam($('.view-switcher').find('.current').data('view'));
		customThemeActions.bindAdditionalEvents();
	},
	bindAdditionalEvents: function() {
		$('#products').on('click', '.js-search-link', function(e) {
			e.preventDefault();
			e.stopImmediatePropagation();
			if ($(this).hasClass('select-list')) {
				var txt = $(this).text(),
					$title = $(this).closest('.dropdown').find('.select-title');
				if ($(this).closest('.products-nb-per-page').length) { // number of products per page
					let nb_items = parseInt($(this).attr('href').split('resultsPerPage=')[1]);
					$('#af_nb_items').val(nb_items).change();
					$title.contents().eq(0).replaceWith(txt);
				} else if ($(this).closest('.products-sort-order').length) { // sorting
					let value = $(this).attr('href').split('order=')[1].split('&')[0].split('.');
					af.applySorting(value[1], value[2]);
					$title.find('.select-title-name').html(txt);
				}
				$title.parent().click();
			} else if ($(this).closest('.view-switcher').length) {  // grid/list
				customThemeActions.updateListViewParam($(this).data('view'));
				$('#af_orderWay').change();
			}
		});
		if ($('#search_center_filter_toggler').length) {
			customThemeActions.centerPanelActions();
		}
	},
	updateListViewParam: function(view) {
		if (!af.$listViewInput) {
			af.$listViewInput = $('<input type="hidden" name="listView">').appendTo('.hidden_inputs');
		}
		af.$listViewInput.val(view || 'grid');
	},
	updateContentAfter: function (jsonData) {
		prestashop.emit('afterUpdateProductList');
		if (load_more && jsonData.product_count_text) {
			$('#js-product-list-top').find('.showing').html(af.utf8_decode(jsonData.product_count_text));
		}
	},
	centerPanelActions: function() {
		af.center_panel_open = $('#facets_search_center').is(':visible');
		$('.compact-toggle').addClass('hidden');
		$('.af_subtitle').on('click', function(e) {
			if (!af.isCompact) {
				e.stopImmediatePropagation();
			}
		});
		if ($('#af_reload_action').val() == 2) {
			af.$viewBtn.data('active-center', 1).on('click', function() {
				if (!af.isCompact) {
					$(this).data('clicked-center', 1);
				}
			});
			prestashop.on('afterUpdateProductList', function() {
				setTimeout(function() {
					if (!af.isCompact && af.center_panel_open && af.$viewBtn.data('clicked-center')) {
						$('#search_center_filter_toggler').click();
						af.$viewBtn.data('clicked-center', 0);
					}
				}, 200);
			});
		}
		$('#products').on('click', '#search_center_filter_toggler', function(e) {
			if (af.isCompact) {
				e.stopImmediatePropagation();
				$('.compact-toggle').click();
			} else if (af.$viewBtn.data('active-center')) {
				af.$viewBtn.data('active', af.center_panel_open);
			}
		});
	},
});
/* since 3.1.6 */

/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$(document).ready(function() {
    af.productItemSelector = '.ajax_block_product';
});
customThemeActions.updateContentAfter = function(r) {
    if (typeof $.LeoCustomAjax == 'function') {
        var leoCustomAjax = new $.LeoCustomAjax();
        leoCustomAjax.processAjax();
    }
    if (typeof callLeoFeature != 'undefined') {
        callLeoFeature();
    }
};
/* since 3.1.5 */

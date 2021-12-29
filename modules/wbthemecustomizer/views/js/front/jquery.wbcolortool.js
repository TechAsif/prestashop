/*
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2020 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function () {
  function add_backgroundcolor(bgcolor) {
    $(
      '<style type="text/css">.product-additional-info .add_to_compare, .product-additional-info .prowish,.btn-primary,.block-social .social li:hover,.owl-theme .owl-dots .owl-dot.active span, .owl-theme .owl-dots .owl-dot span:hover, .owl-theme .owl-dots .owl-dot:hover span,.foot-sp,.owl-theme .owl-dots .owl-dot span,.blogd,.button-search,.view_more:hover i,#scroll,.ico-menu .bar::after, .pagination .page-list li a { background:#' +
        bgcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.wb-menu-vertical ul li.level-1:hover > a, .view_more a:hover span,.des_namepost,.foot-copy ._blank:hover,.footer-container li a:hover, #footer .lnk_wishlist:hover, .foot-payment i:hover,a:hover, a:focus,.read_more:hover, .read_more:focus,.cart-name:hover, .thumbnail-container .product-title:hover, .thumbnail-container .product-title a:hover,.wbpc-main .count,.next-prevb .owl-theme .owl-nav [class*="owl-"]:hover,.product-flag,.first-word span,.statmenu a:hover,#_desktop_language_selector button:hover, #_desktop_currency_selector button:hover, .hcom:hover a, .wishl:hover a, .usvg:hover, .hcart:hover, .my-info:hover { color:#' +
        bgcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.categorybr:hover,.deliveryinfo .ses:hover ul li.ser-svg,#search_widget{ border-color:#' +
        bgcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.hcom:hover svg, .wishl:hover svg, .hcart:hover svg, .my-info:hover svg { stroke:#' +
        bgcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.read_more:hover svg,#footer_contact .icon svg,#_desktop_language_selector button:hover svg, #_desktop_currency_selector button:hover svg, .user-info:hover svg { fill:#' +
        bgcolor +
        "}</style>"
    ).appendTo("head");

    if ($(window).width() >= 992) {
      $(
        '<style type="text/css">{ background-color:#' + bgcolor + "}</style>"
      ).appendTo("head");
      $(
        '<style type="text/css">{ border-color:#' + bgcolor + "}</style>"
      ).appendTo("head");
      $('<style type="text/css">{ color:#' + bgcolor + "}</style>").appendTo(
        "head"
      );
    }
    if ($(window).width() <= 991) {
      $(
        '<style type="text/css">{ background-color:#' + bgcolor + "}</style>"
      ).appendTo("head");
      $('<style type="text/css">{ fill:#' + bgcolor + "}</style>").appendTo(
        "head"
      );
    }
    if (LANG_RTL == "1") {
      $(
        '<style type="text/css">{ border-right-color:#' +
          bgcolor +
          " !important}</style>"
      ).appendTo("head");
    }
  }
  function add_hovercolor(hcolor) {
    $(
      '<style type="text/css">.button-search:focus,.product-additional-info .add_to_compare:hover, .product-additional-info .prowish:hover,.btn-primary.focus, .btn-primary:focus, .btn-primary:hover, .btn-primary.active, .btn-primary:active, .open > .btn-primary.dropdown-toggle, .btn-primary.active.focus, .btn-primary.active:focus, .btn-primary.active:hover, .btn-primary:active.focus, .btn-primary:active:focus, .btn-primary:active:hover, .open > .btn-primary.dropdown-toggle.focus, .open > .btn-primary.dropdown-toggle:focus, .open > .btn-primary.dropdown-toggle:hover, .btn-primary.disabled.focus, .btn-primary.disabled:focus, .btn-primary.disabled:hover, .btn-primary:disabled.focus, .btn-primary:disabled:focus, .btn-primary:disabled:hover,#scroll:hover,.block_newsletter .btn,.thumbnail-container .button-group button:hover, .thumbnail-container .button-group a:hover,.carti,.owl-cat .cartb:hover,.container_wb_megamenu .title-menu,.catel,.cat-shop,#owl-image-slider .btn-primary:hover,.content-cate h3:hover,.reducep,.slide-progress,#product_comparison .btn-product.add-to-cart,#product_comparison .btn-product.add-to-cart:hover, #product_comparison .btn-product.add-to-cart:focus,.custom-radio input[type="radio"]:checked + span,.products-sort-order .select-title,.product-tab .nav-tabs .nav-item a.active::before,.blog_mask .icon:hover,.slideshow-panel .owl-theme .owl-nav [class*="owl-"]:hover,.wb-menu-vertical li.level-1 > a span::after,.custom-checkbox input[type="checkbox"] + span .checkbox-checked,#search_block_top .btn.button-search,.quickview .arrows .arrow-up:hover, .quickview .arrows .arrow-down:hover,.group-span-filestyle .btn-default,.input-group .input-group-btn > .btn[data-action="show-password"],.pagination .page-list li.current a,.pagination .page-list li a:hover, .pagination .page-list li a:focus{ background:#' +
        hcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.cartiva, .view_more:hover i,.cartb,.block_newsletter h1,.star::after,.star.star_on::after,.cattab.tabs .nav-link.active, .cattab.tabs .nav-link:hover, .view_cat_menu a:hover,.wb-menu-vertical li.level-1 > a:hover::before, .wb-menu-vertical li.level-1 > a:focus::before,.cattab.pro-tab.tabs .nav-tabs .nav-link.active, .cattab.pro-tab.tabs .nav-tabs .nav-link:hover, .view_cat_menu a:hover,.wb-menu-vertical ul li.level-1:hover > a, .view_menu a:hover,.content_test h3,.wb-menu-vertical ul li.level-1:hover > a, .view_more a:hover,#header .wb-menu-vertical ul li.level-1 ul li a:hover,.shopnow:hover,.sub-cat ul a:hover,.shopcate:hover,.currency-selector li.current a, .language-selector li.current a,.whishlist-am a,.noty_text_body a,.pro-tab.tabs .nav-tabs .nav-link.active, .pro-tab.tabs .nav-tabs .nav-link:hover,.frst-ds a,.cart-products-count.cart-c,.cartn,.cateall:hover .callname,.banner-des h3,#products .wbpc-main .seconds .count,.offerword h4,.cartc,.block-categories .collapse-icons .add:hover, .block-categories .collapse-icons .remove:hover,.block-categories .collapse-icons .add:hover,.block-categories .collapse-icons .remove:hover,#blog .post_title,.post_title a:hover,.right-nav .dropdown-menu a:hover,.next-prevb #testi.owl-theme .owl-nav .owl-prev:hover, .next-prevb #testi.owl-theme .owl-nav .owl-next:hover,.cate-img span:hover,#wbsearch_data .items-list li .content_price .price,#cta-terms-and-conditions-0,.page-my-account #content .links a:hover,.page-my-account #content .links a:hover i,.facet-title,.product-tab .nav-item a:hover, .product-tab .nav-item a.active,.social-sharing li:hover a,#header .wb-cart-item-info a.wb-bt-product-quantity:hover i,.view_more a:hover{ color:#' +
        hcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.thumbnail-container .button-group button:hover, .thumbnail-container .button-group a:hover,.cat-img:hover,.pro-tab.tabs .nav-tabs .nav-link.active, .pro-tab.tabs .nav-tabs .nav-link:hover,.product-miniature:hover,.product-flag,.test1, .test2, .test3,.language-selector li.current .lang-flag,.timg,.product-images > li.thumb-container > .thumb.selected, .product-images > li.thumb-container > .thumb:hover,.form-control:focus,.owl-theme .owl-dots .owl-dot.active span,.blog_mask .icon:hover{ border-color:#' +
        hcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">{ border-left-color:#' + hcolor + "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">{ border-right-color:#' + hcolor + "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">{ border-bottom-color:#' + hcolor + "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.header-nav .dropdown-menu, .user-down, .language-selector .dropdown-menu, .currency-selector .dropdown-menu,.head-cart-drop,.language-selector .dropdown-menu, .currency-selector .dropdown-menu,.wb-menu-vertical .menu-dropdown,.view_menu .more-menu{ border-top-color:#' +
        hcolor +
        "}</style>"
    ).appendTo("head");
    $(
      '<style type="text/css">.test-img svg,.headsvg svg{ fill:#' +
        hcolor +
        "}</style>"
    ).appendTo("head");
  }

  $(".wbopen-closeclr").click(function () {
    if ($(this).hasClass("wbclrdisable")) {
      $(this).removeClass("wbclrdisable");
      $(this).addClass("wbclrenable");

      $(".wbcolor_box").animate({ right: "30px" }, 450);

      $(".wbcolor_box").css({
        "box-shadow": "0 10px 35px 10px rgba(0,0,0,.06)",
        background: "#fff",
        "border-radius": "4px 0 4px 4px",
      });
      $(".wbcolor_option,.wbcolor_title").animate({ opacity: "1" }, 450);
    } else {
      $(this).removeClass("wbclrenable");
      $(this).addClass("wbclrdisable");

      $(".wbcolor_box").animate({ right: "-250px" }, 450);

      $(".wbcolor_box").css({
        "box-shadow": "none",
        background: "transparent",
      });
      $(".wbcolor_option,.wbcolor_title").animate({ opacity: "0" }, 450);
    }
  });
  $("#backgroundColor, #hoverColor").each(function () {
    var $el = $(this);
    var date = new Date();
    date.setTime(date.getTime() + 1440 * 60 * 1000);
    $el.ColorPicker({
      color: "#444444",
      onChange: function (hsb, hex, rgb) {
        $el.find("div").css("backgroundColor", "#" + hex);
        switch ($el.attr("id")) {
          case "backgroundColor":
            add_backgroundcolor(hex);
            $.cookie("wbcolor_backg", hex, { expires: date });
            break;
          case "hoverColor":
            add_hovercolor(hex);
            $.cookie("wbcolor_hoverg", hex, { expires: date });
            break;
        }
      },
    });
  });
  var date = new Date();
  date.setTime(date.getTime() + 1440 * 60 * 1000);
  if ($.cookie("wbcolor_backg") && $.cookie("wbcolor_hoverg")) {
    add_backgroundcolor($.cookie("wbcolor_backg"));
    add_hovercolor($.cookie("wbcolor_hoverg"));
    var backgr = "#" + $.cookie("wbcolor_backg");
    var activegr = "#" + $.cookie("wbcolor_hoverg");
    $("#backgroundColor div").css({ "background-color": backgr });
    $("#hoverColor div").css({ "background-color": activegr });
  }

  /*Theme mode layout*/
  if (!$.cookie("wbcolor_input") && WB_mainLayout == "boxed") {
    $("input[name=wbcolor_input][value=box]").attr("checked", true);
  } else if (!$.cookie("wbcolor_input") && WB_mainLayout == "fullwidth") {
    $("input[name=wbcolor_input][value=wide]").attr("checked", true);
  } else if ($.cookie("wbcolor_input") == "boxed") {
    $("body").removeClass("fullwidth");
    $("body").removeClass("boxed");
    $("body").addClass("boxed");
    $.cookie("wbcolor_input", "boxed");
    $.cookie("wbcolor_input_input", "box");
    $("input[name=wbcolor_input][value=box]").attr("checked", true);
  } else if ($.cookie("wbcolor_input") == "fullwidth") {
    $("body").removeClass("fullwidth");
    $("body").removeClass("boxed");
    $("body").addClass("fullwidth");
    $.cookie("wbcolor_input", "fullwidth");
    $.cookie("wbcolor_input_input", "wide");
    $("input[name=wbcolor_input][value=wide]").attr("checked", true);
  }
  $("input[name=wbcolor_input][value=box]").click(function () {
    $("body").removeClass("fullwidth");
    $("body").removeClass("boxed");
    $("body").addClass("boxed");
    $.cookie("wbcolor_input", "boxed");
    fullwidth_click();
  });
  $("input[name=wbcolor_input][value=wide]").click(function () {
    $("body").removeClass("fullwidth");
    $("body").removeClass("boxed");
    $("body").addClass("fullwidth");
    $.cookie("wbcolor_input", "fullwidth");
    fullwidth_click();
  });

  $(".wbcolorpart a").click(function () {
    var id_color = this.id;
    $.cookie("wbcolor_backg", id_color.substring(0, 6));
    $.cookie("wbcolor_hoverg", id_color.substring(7, 13));
    add_backgroundcolor($.cookie("wbcolor_backg"));
    add_hovercolor($.cookie("wbcolor_hoverg"));
    var backgr = "#" + $.cookie("wbcolor_backg");
    var activegr = "#" + $.cookie("wbcolor_hoverg");
    $("#backgroundColor div").css({ "background-color": backgr });
    $("#hoverColor div").css({ "background-color": activegr });
  });

  $(".wbcolor_reset").click(function () {
    $.cookie("wbcolor_backg", "");
    $.cookie("wbcolor_hoverg", "");
    $.cookie("wbcolor_input", "");
    location.reload();
  });

  // Webibazaar Dark Light Mode
  if (!$.cookie("wbdlmodecolor_input") && WB_darklightLayout == "darkmode") {
    $("input[name=wbdlmodecolor_input][value=wbdarkmode]").attr(
      "checked",
      true
    );
  } else if (
    !$.cookie("wbdlmodecolor_input") &&
    WB_darklightLayout == "lightmode"
  ) {
    $("input[name=wbdlmodecolor_input][value=wblightmode]").attr(
      "checked",
      true
    );
  } else if ($.cookie("wbdlmodecolor_input") == "darkmode") {
    $("body").removeClass("lightmode");
    $("body").removeClass("darkmode");
    $("body").addClass("darkmode");
    $.cookie("wbdlmodecolor_input", "darkmode");
    $.cookie("wbdlmodecolor_input_input", "wbdarkmode");
    $("input[name=wbdlmodecolor_input][value=wbdarkmode]").attr(
      "checked",
      true
    );
  } else if ($.cookie("wbdlmodecolor_input") == "lightmode") {
    $("body").removeClass("lightmode");
    $("body").removeClass("darkmode");
    $("body").addClass("lightmode");
    $.cookie("wbdlmodecolor_input", "lightmode");
    $.cookie("wbdlmodecolor_input_input", "wblightmode");
    $("input[name=wbdlmodecolor_input][value=wblightmode]").attr(
      "checked",
      true
    );
  }
  $("input[name=wbdlmodecolor_input][value=wbdarkmode]").click(function () {
    $("body").removeClass("lightmode");
    $("body").removeClass("darkmode");
    $("body").addClass("darkmode");
    $.cookie("wbdlmodecolor_input", "darkmode");
    lightmode_click();
  });
  $("input[name=wbdlmodecolor_input][value=wblightmode]").click(function () {
    $("body").removeClass("lightmode");
    $("body").removeClass("darkmode");
    $("body").addClass("lightmode");
    $.cookie("wbdlmodecolor_input", "lightmode");
    lightmode_click();
  });

  function fullwidth_click() {
    $(".wbFullWidth").each(function () {
      var t = $(this);
      var fullwidth = $("main").width(),
        margin_full = fullwidth / 2;
      if (LANG_RTL != 1) {
        t.css({
          left: "50%",
          position: "relative",
          width: fullwidth,
          "margin-left": -margin_full,
        });
      } else {
        t.css({
          right: "50%",
          position: "relative",
          width: fullwidth,
          "margin-right": -margin_full,
        });
      }
    });
  }
  function fullwidth_click() {
    $(".wbLightMode").each(function () {
      var t = $(this);
      var lightmode = $("main").width(),
        margin_full = lightmode / 2;
      if (LANG_RTL != 1) {
        t.css({
          left: "50%",
          position: "relative",
          width: lightmode,
          "margin-left": -margin_full,
        });
      } else {
        t.css({
          right: "50%",
          position: "relative",
          width: lightmode,
          "margin-right": -margin_full,
        });
      }
    });
  }
  // Webibazaar Active Class Js
  $(".uscolorac").click(function () {
    $(".uscolorac").removeClass("active");
    $(this).addClass("active");
  });
});

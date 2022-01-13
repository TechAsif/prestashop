{**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{block name='header_nav'}
  <nav class="header-nav v-center">
    <div class="head-left">
      <span class="mobile-logo hidden-lg-up">
        <a href="{$urls.base_url}">
          <svg width="47" height="41">
            <use xlink:href="#lgLogo">
          </svg>
        </a>
      </span>
      {hook h='displayNav1'}
    </div>
    <div class="text-sm-center hidden-md-down">
      <span class="usp-banner">
        {hook h='displayUSP'}
      </span>
    </div>
    <div class="right-nav text-xs-right ">
      {hook h='displayNav2'}
      {include file="../../modules/wbthemecustomizer/views/templates/front/colortool.tpl"}
    </div>

  </nav>

{/block}

{block name='header_top'}

  <div class="allhead">
    <div class="header-top">
      <div class="v-center">
        <div class="hidden-md-down head-logo">
          <a href="{$urls.base_url}">

            <svg width="47" height="41">
              <use xlink:href="#lgLogo">
            </svg>
            {* <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}"> *}
          </a>
        </div>
        <div class="hidden-md-down top-menus">
          <nav class="navbar navbar-expand-md">
            <ul class="navbar-nav ml-auto">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarBrands" role="button" data-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  Brands
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarBrands">
                  <div class="">
                    {widget name="lg_custom_brands"}
                  </div>
                </div>
              </li>

              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarPopulerProducts" role="button"
                  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Popular Products
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarPopulerProducts">
                  <div class="">
                    {widget name="lg_popular_products"}
                  </div>
                </div>
              </li>
            </ul>
          </nav>
        </div>
        <div class="hidden-lg-up text-sm-center mobile">
          <div id="menu-icon">
            <div class="navbar-header">
              <button type="button" class="btn-navbar navbar-toggle" data-toggle="collapse" onclick="openNav()">
                <i class="fa fa-bars"></i></button>
            </div>
          </div>
          <div id="mySidenav" class="sidenav">
            <div class="close-nav">
              <a class="closebtn float-xs-right">
                <svg width="12" height="41" viewBox="0 0 12 41" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M11 1.47168L1.74927 20.5003L11 39.529" stroke="#FF0000" stroke-opacity="0.38" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </a>
            </div>
            <div id="mobile_top_menu_wrapper">
              <div class="mobile_menu_header">
                <a class="mobile_menu_logo" href="{$urls.base_url}">
                  <svg width="47" height="41">
                    <use xlink:href="#lgLogo">
                  </svg>
                </a>
              </div>
              <div class="mobile_menu_contents">
                <ul class="nav nav-pills mb-1" id="pills-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="mobile_category_pill-tab" data-toggle="pill"
                      href="#mobile_category_pill" role="tab" aria-controls="mobile_category_pill"
                      aria-selected="true">Category</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="mobile_brands_pill-tab" data-toggle="pill" href="#mobile_brands_pill"
                      role="tab" aria-controls="mobile_brands_pill" aria-selected="false">Brands</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="mobile_populer_products_pill-tab" data-toggle="pill"
                      href="#mobile_populer_products_pill" role="tab" aria-controls="mobile_populer_products_pill"
                      aria-selected="false">Popular Products</a>
                  </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                  <div class="tab-pane fade in show active" id="mobile_category_pill" role="tabpanel"
                    aria-labelledby="mobile_category_pill-tab"></div>
                  <div class="tab-pane fade" id="mobile_brands_pill" role="tabpanel"
                    aria-labelledby="mobile_brands_pill-tab">
                    <div class="">
                      {widget name="lg_custom_brands"}
                    </div>
                  </div>
                  <div class="tab-pane fade" id="mobile_populer_products_pill" role="tabpanel"
                    aria-labelledby="mobile_populer_products_pill-tab">
                    <div class="">
                      {widget name="lg_popular_products"}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div style="flex: 1;text-align: center;">
          {hook h='displayTop'}
        </div>
        <div class="header-right-navs">
          {hook h='displayNav'}
          <a href="{$urls.pages.addresses}" class="d-inline-block hidden-md-down my-info text-xs-center">
            <svg width="21" height="24" viewBox="0 0 21 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M19.3125 10C19.3125 17 10.3125 23 10.3125 23C10.3125 23 1.3125 17 1.3125 10C1.3125 7.61305 2.26071 5.32387 3.94854 3.63604C5.63637 1.94821 7.92555 1 10.3125 1C12.6994 1 14.9886 1.94821 16.6765 3.63604C18.3643 5.32387 19.3125 7.61305 19.3125 10Z"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path
                d="M10.3121 13.6334C12.3188 13.6334 13.9456 12.0067 13.9456 10C13.9456 7.99332 12.3188 6.36658 10.3121 6.36658C8.30545 6.36658 6.67871 7.99332 6.67871 10C6.67871 12.0067 8.30545 13.6334 10.3121 13.6334Z"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>My Info</span>
          </a>

        </div>

      </div>
    </div>
    <div class="topmenu container-fluid">
      {hook h='displayNavFullWidth'}
    </div>
  </div>
  <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="lgLogo" viewBox="0 0 47 41">
      <path
        d="M29.5949 10.9785C29.808 12.8214 31.0674 13.7559 33.338 13.7559C33.8285 13.7621 34.3165 13.6854 34.7814 13.5289C35.1851 13.4008 35.5597 13.1948 35.8841 12.9224C36.1885 12.6685 36.4321 12.3494 36.5968 11.9887C36.7684 11.6199 36.8564 11.2178 36.8546 10.811C36.8618 10.4685 36.7932 10.1287 36.6537 9.81576C36.513 9.52263 36.3028 9.26827 36.0415 9.07478C35.744 8.84335 35.4098 8.66331 35.0529 8.54208C34.6119 8.37574 34.1613 8.23584 33.7036 8.12315C33.193 8.00396 32.69 7.85408 32.1975 7.67432C31.9145 7.56803 31.6457 7.42725 31.3972 7.25517C31.275 7.17122 31.1778 7.0556 31.1163 6.9207C31.0785 6.81109 31.0586 6.69613 31.0574 6.58021C31.0532 6.40114 31.103 6.22496 31.2001 6.07448C31.3012 5.9151 31.4403 5.78334 31.605 5.69106C31.8152 5.57139 32.0432 5.4862 32.2803 5.43873C32.5718 5.37846 32.8689 5.34923 33.1665 5.35153C33.8451 5.35153 34.355 5.45032 34.686 5.64741C34.8374 5.7301 34.9662 5.84863 35.0612 5.99262C35.1561 6.13661 35.2143 6.30167 35.2307 6.47337C35.2373 6.52047 35.2606 6.56361 35.2964 6.59489C35.3323 6.62617 35.3781 6.64348 35.4257 6.64364H36.3371C36.3643 6.64361 36.3912 6.63795 36.4162 6.62701C36.4411 6.61607 36.4635 6.60009 36.4819 6.58007L36.54 6.51327L36.5331 6.43069C36.4658 5.61421 36.1291 5.00691 35.535 4.62733C34.9649 4.24989 34.1257 4.05849 33.0408 4.05849C32.5951 4.05402 32.1509 4.11022 31.7205 4.22554C31.349 4.32358 30.9987 4.48889 30.6868 4.71327C30.4014 4.92388 30.1674 5.19643 30.0024 5.51046C29.8412 5.83158 29.7596 6.18671 29.7643 6.54597C29.7536 6.92395 29.8265 7.29963 29.9776 7.64625C30.1244 7.94472 30.3404 8.20383 30.6075 8.40205C30.9134 8.61974 31.2502 8.79051 31.6067 8.90868C31.9838 9.03949 32.4198 9.16796 32.9022 9.29049C33.458 9.43318 33.9177 9.56985 34.273 9.69837C34.5593 9.78585 34.8333 9.90966 35.0882 10.0668C35.2449 10.1566 35.3724 10.2895 35.4557 10.4497C35.5308 10.613 35.567 10.7914 35.5616 10.9711C35.5628 11.1721 35.5183 11.3708 35.4316 11.5522C35.3389 11.738 35.202 11.8983 35.033 12.0189C34.8218 12.1652 34.5875 12.275 34.3399 12.3437C34.0089 12.428 33.6682 12.468 33.3266 12.4627C32.539 12.4627 31.9406 12.3241 31.5512 12.053C31.3685 11.9237 31.2157 11.7569 31.1028 11.5637C30.9899 11.3705 30.9197 11.1553 30.8968 10.9328C30.8912 10.8849 30.8682 10.8408 30.8322 10.8087C30.7962 10.7767 30.7497 10.759 30.7015 10.759H29.7904C29.7627 10.759 29.7353 10.7649 29.71 10.7762C29.6847 10.7876 29.6621 10.8041 29.6436 10.8248L29.5852 10.8941L29.5949 10.9785Z" />
      <path fill-rule="evenodd" clip-rule="evenodd"
        d="M16.0856 13.4331C16.6432 13.6536 17.2385 13.7633 17.8381 13.7559C18.2848 13.7584 18.7297 13.6982 19.1597 13.5769C19.5612 13.4637 19.9407 13.2838 20.2825 13.0447C20.6103 12.8053 20.8932 12.5098 21.1182 12.172C21.3487 11.8122 21.5087 11.4119 21.5897 10.9924C21.5951 10.9639 21.5942 10.9346 21.5869 10.9066C21.5797 10.8785 21.5664 10.8525 21.5478 10.8302L21.4853 10.759H20.509C20.4608 10.759 20.4142 10.7767 20.3782 10.8088C20.3421 10.841 20.3192 10.8852 20.3137 10.9332C20.2895 11.1633 20.2002 11.3818 20.0563 11.5631C19.8974 11.7625 19.7016 11.9294 19.4795 12.0547C19.2398 12.1945 18.981 12.2988 18.7113 12.3644C18.4247 12.4292 18.1319 12.4622 17.8381 12.4627C17.4479 12.466 17.0605 12.3969 16.6956 12.2587C16.3546 12.1231 16.0464 11.9165 15.7913 11.6527C15.5106 11.3476 15.2908 10.9916 15.1439 10.604C14.9851 10.1837 14.884 9.74381 14.8435 9.29636H21.6039C21.656 9.29629 21.706 9.27554 21.7429 9.23866C21.7798 9.20178 21.8006 9.15177 21.8006 9.09961V8.90134C21.8097 8.22384 21.7142 7.54899 21.5174 6.90064C21.3496 6.33994 21.0687 5.81953 20.6922 5.37145C20.3347 4.94921 19.8849 4.61491 19.3775 4.39442C18.8366 4.1652 18.254 4.05079 17.6665 4.05843C17.0899 4.05147 16.5183 4.16612 15.9889 4.3949C15.4853 4.61876 15.0376 4.95128 14.6777 5.36859C14.2933 5.81513 14.0042 6.33551 13.8281 6.89778C13.4299 8.21276 13.4341 9.61675 13.84 10.9293C14.0262 11.4964 14.3235 12.0207 14.7146 12.4716C15.0977 12.8863 15.5651 13.2142 16.0856 13.4331ZM20.4616 8.00334H14.8598C14.9238 7.63576 15.0328 7.27747 15.1844 6.93653C15.3378 6.60454 15.5485 6.30216 15.8068 6.04327C16.0476 5.81433 16.3329 5.63729 16.645 5.52314C16.9729 5.40673 17.3186 5.34866 17.6665 5.35154C18.4084 5.35154 19.0131 5.58156 19.5149 6.05469L19.5159 6.05562C19.9837 6.49051 20.3014 7.14507 20.4616 8.00334Z" />
      <path
        d="M25.0688 13.477C25.3356 13.5938 25.6843 13.653 26.1052 13.653C26.2372 13.653 26.3717 13.649 26.505 13.6412C26.635 13.6336 26.7672 13.622 26.8982 13.6067C26.9461 13.6011 26.9902 13.5781 27.0222 13.542C27.0543 13.506 27.0719 13.4595 27.0719 13.4113V12.546C27.0719 12.5183 27.0661 12.491 27.0549 12.4658C27.0436 12.4406 27.0272 12.418 27.0066 12.3995L27.0023 12.3958C26.9617 12.3615 26.909 12.345 26.8561 12.3501C26.7901 12.3565 26.7298 12.3598 26.677 12.3598H26.4597C26.2933 12.3612 26.1271 12.3474 25.9632 12.3185C25.8531 12.2914 25.7533 12.2328 25.6759 12.1499C25.5776 12.0264 25.5096 11.8816 25.4774 11.7271C25.421 11.4521 25.3959 11.1716 25.4026 10.891V5.58022H26.658C26.7101 5.58016 26.7602 5.55941 26.797 5.52253C26.8339 5.48565 26.8547 5.43564 26.8548 5.38347V4.48389C26.8547 4.43172 26.8339 4.38171 26.7971 4.34482C26.7602 4.30794 26.7101 4.28719 26.658 4.28714H25.4026V2.16269C25.4025 2.11053 25.3818 2.06052 25.3449 2.02363C25.308 1.98674 25.258 1.966 25.2058 1.96594H24.3061C24.2539 1.966 24.2039 1.98674 24.167 2.02363C24.1301 2.06052 24.1094 2.11053 24.1093 2.16269V4.28711H23.1741C23.1219 4.28716 23.0719 4.30791 23.035 4.3448C22.9981 4.38168 22.9774 4.43169 22.9773 4.48386V5.38347C22.9774 5.43563 22.9982 5.48564 23.035 5.52252C23.0719 5.5594 23.1219 5.58015 23.1741 5.58022H24.1093V10.811C24.1067 11.2079 24.1263 11.6045 24.1678 11.9992C24.1971 12.3126 24.2871 12.6172 24.4326 12.8963C24.581 13.1498 24.8029 13.3523 25.0688 13.477Z" />
      <path
        d="M10.4018 13.5272H11.3014C11.3536 13.5271 11.4036 13.5064 11.4405 13.4695C11.4774 13.4326 11.4981 13.3826 11.4982 13.3305V0.824862C11.4981 0.772698 11.4774 0.722685 11.4405 0.685799C11.4036 0.648913 11.3536 0.628167 11.3014 0.628113H10.4018C10.3497 0.628167 10.2997 0.648913 10.2628 0.685799C10.2259 0.722685 10.2051 0.772698 10.2051 0.824862V13.3305C10.2051 13.3826 10.2259 13.4326 10.2628 13.4695C10.2997 13.5064 10.3497 13.5271 10.4018 13.5272Z" />
      <path
        d="M27.9342 4.33185H28.3684C28.4038 4.33199 28.4386 4.32252 28.469 4.30445C28.4994 4.28638 28.5243 4.26038 28.5411 4.22924L29.1917 3.03651L29.2178 2.99413V2.18312C29.2178 2.13096 29.197 2.08095 29.1601 2.04407C29.1232 2.00718 29.0732 1.98644 29.021 1.98638H28.2088C28.1566 1.98644 28.1066 2.00718 28.0697 2.04407C28.0328 2.08095 28.0121 2.13096 28.012 2.18312V3.17182L27.8298 4.09844C27.8243 4.12689 27.8253 4.15618 27.8325 4.18423C27.8397 4.21227 27.8531 4.23837 27.8716 4.26065L27.9342 4.33185Z" />
      <path
        d="M19.4127 37.324C20.3958 36.3143 21.1731 35.1252 21.7444 33.7567C21.7798 33.6709 21.8142 33.5846 21.8475 33.4976H15.5265C15.0349 34.0025 14.4569 34.3945 13.7926 34.6735C13.1416 34.9525 12.4441 35.092 11.7001 35.092C10.9561 35.092 10.2519 34.9458 9.58759 34.6535C8.93657 34.3479 8.36527 33.9361 7.87368 33.4179C7.39538 32.8998 7.01673 32.2886 6.73772 31.5844C6.45871 30.8803 6.31921 30.1163 6.31921 29.2926C6.31921 28.4157 6.45871 27.6252 6.73772 26.921C7.01673 26.2036 7.39538 25.5924 7.87368 25.0875C8.36527 24.5694 8.93657 24.1774 9.58759 23.9117C10.2519 23.6327 10.9561 23.4932 11.7001 23.4932C12.4441 23.4932 13.1416 23.6327 13.7926 23.9117C14.4569 24.1774 15.0349 24.5694 15.5265 25.0875C15.5739 25.1363 15.6204 25.186 15.6659 25.2367H21.4138C21.681 25.2367 21.8682 24.9732 21.7676 24.7256C21.7599 24.7067 21.7522 24.6879 21.7444 24.669C21.1731 23.274 20.3958 22.0849 19.4127 21.1017C18.4295 20.1052 17.2736 19.3413 15.945 18.8098C14.6297 18.2651 13.2147 17.9927 11.7001 17.9927C10.1987 17.9927 8.78378 18.2784 7.45517 18.8497C6.13984 19.421 4.98395 20.2115 3.98749 21.2213C3.00432 22.2177 2.22708 23.4068 1.65578 24.7886C1.08448 26.1703 0.798828 27.6717 0.798828 29.2926C0.798828 30.8869 1.08448 32.375 1.65578 33.7567C2.22708 35.1252 3.00432 36.3143 3.98749 37.324C4.98395 38.3205 6.13984 39.111 7.45517 39.6956C8.78378 40.2669 10.1987 40.5526 11.7001 40.5526C13.2147 40.5526 14.6297 40.2669 15.945 39.6956C17.2736 39.111 18.4295 38.3205 19.4127 37.324Z" />
      <path
        d="M12.5538 26.6823C11.104 26.6823 9.94444 27.8561 9.94444 29.2983C9.94444 30.7431 11.1148 31.9029 12.5605 31.9029H16.5802H22.3244H24.7357H30.5327C31.8618 31.9029 32.8929 30.6392 32.8929 29.276C32.8929 27.9049 31.8601 26.6499 30.5106 26.6499C30.7761 26.0483 31.1173 25.5276 31.5342 25.0875C32.0258 24.5694 32.5971 24.1774 33.2481 23.9117C33.9124 23.6327 34.6166 23.4932 35.3606 23.4932C36.1046 23.4932 36.8021 23.6327 37.4531 23.9117C38.1174 24.1774 38.6954 24.5694 39.187 25.0875C39.6786 25.5924 40.0639 26.2036 40.3429 26.921C40.6352 27.6252 40.7813 28.4157 40.7813 29.2926C40.7813 30.1562 40.6352 30.9467 40.3429 31.6642C40.0639 32.3816 39.6786 32.9928 39.187 33.4976C38.6954 34.0025 38.1174 34.3945 37.4531 34.6735C36.8021 34.9525 36.1046 35.092 35.3606 35.092C34.6166 35.092 33.9124 34.9458 33.2481 34.6535C32.6147 34.3562 32.0567 33.9583 31.5742 33.4597H25.1984C25.2363 33.5592 25.2756 33.6582 25.3163 33.7567C25.8876 35.1252 26.6648 36.3143 27.648 37.324C28.6445 38.3205 29.8003 39.111 31.1157 39.6956C32.4443 40.2669 33.8592 40.5526 35.3606 40.5526C36.8752 40.5526 38.2902 40.2669 39.6055 39.6956C40.9341 39.111 42.09 38.3205 43.0732 37.324C44.0563 36.3143 44.8336 35.1252 45.4049 33.7567C45.9762 32.375 46.2618 30.8869 46.2618 29.2926C46.2618 27.592 45.9762 26.0508 45.4049 24.669C44.8336 23.274 44.0563 22.0849 43.0732 21.1017C42.09 20.1052 40.9341 19.3413 39.6055 18.8098C38.2902 18.2651 36.8752 17.9927 35.3606 17.9927C34.1062 17.9927 32.9121 18.1922 31.7782 18.591C31.555 18.6695 31.3342 18.7557 31.1157 18.8497C29.8003 19.421 28.6445 20.2115 27.648 21.2213C26.6648 22.2177 25.8876 23.4068 25.3163 24.7886C25.0688 25.3871 24.8749 26.0081 24.7346 26.6516L12.5538 26.6823Z" />
      <path
        d="M22.3244 31.9029H16.5802C16.4996 32.0863 16.4114 32.2633 16.3158 32.4332C16.214 32.614 16.1038 32.7868 15.9851 32.9508C15.8441 33.1458 15.6911 33.3285 15.5265 33.4976H21.8475L22.0398 32.9508C22.0955 32.7798 22.1471 32.6073 22.1945 32.4332C22.2419 32.2585 22.2854 32.0797 22.3244 31.9029Z" />
      <path
        d="M30.5327 31.9029H24.7357C24.7749 32.0802 24.8179 32.259 24.8652 32.4332C24.9124 32.6073 24.9637 32.7798 25.0192 32.9508C25.0743 33.1206 25.135 33.293 25.1984 33.4597H31.5742C31.5608 33.4458 31.5475 33.4319 31.5342 33.4179C31.3972 33.2696 31.2688 33.114 31.1482 32.9508C31.0264 32.786 30.9132 32.6136 30.8081 32.4332C30.7091 32.2635 30.6169 32.0868 30.5327 31.9029Z" />
      <path
        d="M22.3253 31.903H16.581C16.5005 32.0864 16.4123 32.2634 16.3166 32.4334C16.2149 32.6141 16.1046 32.7869 15.986 32.951C15.8449 33.146 15.692 33.3287 15.5273 33.4978H21.8484L22.0407 32.951C22.0964 32.78 22.1479 32.6074 22.1953 32.4334C22.2428 32.2587 22.2862 32.0798 22.3253 31.903Z"
        fill="black" />
      <path
        d="M30.5336 31.903H24.7365C24.7757 32.0803 24.8188 32.2591 24.8661 32.4334C24.9133 32.6074 24.9646 32.78 25.0201 32.951C25.0752 33.1207 25.1359 33.2931 25.1993 33.4598H31.5751C31.5617 33.446 31.5483 33.4321 31.535 33.4181C31.3981 33.2697 31.2696 33.1141 31.149 32.951C31.0272 32.7862 30.9141 32.6137 30.8089 32.4334C30.71 32.2636 30.6178 32.0869 30.5336 31.903Z"
        fill="black" />
    </symbol>
  </svg>
{/block}
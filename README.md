# prestashop
 
https://stackoverflow.com/questions/41887460/select-list-is-not-in-group-by-clause-and-contains-nonaggregated-column-inc/41887524

SELECT  *  FROM `ps_specific_price`  WHERE id_group = 4 GROUP by id_product ;


SELECT  *  FROM `ps_specific_price` sp inner JOIN ps_product_lang pl on sp.id_product = pl.id_product WHERE sp.id_group = 4 GROUP by sp.id_product ;

SELECT * FROM `ps_specific_price` WHERE id_group = 4 order by id_product,reduction;



SELECT * FROM `ps_specific_price` sp inner JOIN ps_product pp on sp.id_product = pp.id_product WHERE sp.id_group = 4 order by sp.id_product,sp.reduction;

https://www.youtube.com/watch?v=rgu8fZlLsIM
https://www.youtube.com/watch?v=ELqYWI12q-c&t=1060s
https://www.youtube.com/watch?v=2zEFCCPTsrM
https://www.youtube.com/watch?v=RBseAM7COzg
https://www.youtube.com/watch?v=z8ieS5ZPLxw
https://www.youtube.com/watch?v=-PkIVa_xBDE


https://www.youtube.com/watch?v=hDKhxBXf-KY

https://www.prestashop.com/forums/topic/938656-how-to-modify-the-default-add-quantity-from-1-to-anything/
Become a WordPress Developer: Unlocking Power With Code
18.189.132.157

openlight speed:
admin username: admin_dp
admin password: 12345678



api work:
ps_wbslider_slides
`ps_wbslider_slides_lang`
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

SELECT image, url from ps_wbslider_slides ws INNER JOIN ps_wbslider_slides_lang wsl on ws.id_wbslider_slides = wsl.id_wbslider_slides WHERE ws.active = 1 GROUP by ws.id_wbslider_slides;


private function getCategoryProductPriceInfo($catId)
    {

       /*  $product_query = Db::getInstance()->getRow(
            'SELECT max(price)max_price,min(price)min_price
            FROM ' . _DB_PREFIX_ . 'product 
            where `id_category_default` = ' . $catId);

        $resultResponse = array(
            'max_price' => $product_query['max_price'],
            'min_price' => $product_query['min_price']
        ); */

        $product_query = Db::getInstance()->executeS('SELECT image, url from ps_wbslider_slides ws INNER JOIN ps_wbslider_slides_lang wsl on ws.id_wbslider_slides = wsl.id_wbslider_slides WHERE ws.active = 1 GROUP by ws.id_wbslider_slides');

        $resultResponse = array(
            'max_price' => $product_query['max_price'],
            'min_price' => $product_query['min_price']
        );

        return $product_query;
    }


    !fVL2MdXC_=P3h=k
9463#D!v%AeX!5x!

CheerUp
https://demo.tagdiv.com/newspaper_classic_blog/
https://preview.themeforest.net/item/gridlove-creative-grid-style-news-magazine-wordpress-theme/full_screen_preview/17990371?_ga=2.234794781.2024642310.1636139865-1546467426.1636121110

https://avada.theme-fusion.com/food/


avada most beautiful feature:
1.WordPress Multisite (WPMU) Tested and Approved
2.Full control over site width; content area and sidebars

hook,group,history,module shop

https://letsgobd.com/api/product?display=full&limit=0,30&id_category=300,%20307,%20315,%20317,%20322,%20333,%20345,%20353,%20357,%20379,%20385,%20396,%20316,%20585,%20775,%20318,%20319,%20320,%20321,%20323,%20325,%20328,%20329,%20335,%20337,%20342,%20344,%20346,%20347,%20348,%20349,%20350,%20351,%20352,%20354,%20355,%20356,%20397,%20398,%20399,%20758&lang=en&ws_key=7PIAP3JUHID7VK459Q5HKN5AJLX3AV4T&output_format=JSON


sudo certbot certonly --webroot -w /usr/local/lsws/digitalpartake/html/wordpress -d digitalpartake.com,www.digitalpartake.com
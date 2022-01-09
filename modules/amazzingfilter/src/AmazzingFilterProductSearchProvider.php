<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

use PrestaShop\PrestaShop\Core\Product\Search\Pagination;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class AmazzingFilterProductSearchProvider implements ProductSearchProviderInterface
{
    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    private function getAvailableSortOrders()
    {
        $sorted_options = array();
        $current_option = 'product.'.$this->context->filtered_result['sorting'];
        $options = $this->module->getSortingOptions($current_option);
        foreach ($options as $opt) {
            $sorted_options[] = (new SortOrder($opt['entity'], $opt['field'], $opt['direction']))
            ->setLabel($opt['label']);
        }
        return $sorted_options;
    }

    public function runQuery(
        ProductSearchContext $context,
        ProductSearchQuery $query
    ) {
        $products = $this->context->filtered_result['products'];
        $total = $this->context->filtered_result['total'];
        $sorting_options = $this->getAvailableSortOrders();
        $result = new ProductSearchResult();
        $result->setProducts($products)->setTotalProductsCount($total)->setAvailableSortOrders($sorting_options);
        if (!empty($this->context->forced_sorting)) {
            $so = new SortOrder('product', $this->context->forced_sorting['by'], $this->context->forced_sorting['way']);
            $query->setSortOrder($so);
        }
        if (!empty($this->context->forced_nb_items)) {
            $query->setResultsPerPage($this->context->forced_nb_items);
        }
        // $query
        //     ->setQueryType('products')
        //     ->setSortOrder(new SortOrder('product', 'date_add', 'desc'))
        // ;
        return $result;
    }

    public function getPaginationVariables($page, $products_num, $products_per_page, $current_url)
    {
        $pagination = new MyCustomPagination();
        $pages_nb = $this->module->getNumberOfPages($products_num, $products_per_page);
        $pagination->setPage($page)->setPagesCount($pages_nb);
        $from = ($products_per_page * ($page - 1)) + 1;
        $to = $products_per_page * $page;
        $pages = $pagination->buildLinks();
        $page_txt = $this->module->page_link_rewrite_text;
        foreach ($pages as &$p) {
            $p['url'] = $this->module->updateQueryString($current_url, array($page_txt => $p['page']));
        }
        return array(
            'total_items' => $products_num,
            'items_shown_from' => $from,
            'items_shown_to' => ($to <= $products_num) ? $to : $products_num,
            'pages' => $pages,
            // Compare to 3 because there are the next and previous links
            'should_be_displayed' => (count($pages) > 3),
        );
    }
}



class MyCustomPagination
{
    /**
     * The total number of pages for this query.
     *
     * @var int
     */
    private $pagesCount;

    /**
     * The index of the returned page.
     *
     * @var int
     */
    private $page;

    public function setPagesCount($pagesCount)
    {
        $this->pagesCount = (int) $pagesCount;

        return $this;
    }

    public function getPagesCount()
    {
        return $this->pagesCount;
    }

    public function setPage($page)
    {
        $this->page = (int) $page;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    private function buildPageLink($page, $type = 'page')
    {
        $current = $page === $this->getPage();

        return [
            'type' => $type,
            'page' => $page,
            'clickable' => !$current,
            'current' => $type === 'page' ? $current : false,
        ];
    }

    private function buildSpacer()
    {
        return [
            'type' => 'spacer',
            'page' => null,
            'clickable' => false,
            'current' => false,
        ];
    }

    public function buildLinks()
    {
        $links = [];

        $addPageLink = function ($page) use (&$links) {
            static $lastPage = null;

            if ($page < 1 || $page > $this->getPagesCount()) {
                return;
            }

            if (null !== $lastPage && $page > $lastPage + 1) {
                $links[] = $this->buildSpacer();
            }

            if ($page !== $lastPage) {
                $links[] = $this->buildPageLink($page);
            }

            $lastPage = $page;
        };

        $boundaryContextLength = 1;
        $pageContextLength = 5;

        $links[] = $this->buildPageLink(max(1, $this->getPage() - 1), 'previous');

        for ($i = 0; $i < $boundaryContextLength; ++$i) {
            $addPageLink(1 + $i);
        }

        $start = max(1, $this->getPage() - (int) floor(($pageContextLength - 1) / 2));
        if ($start + $pageContextLength > $this->getPagesCount()) {
            $start = $this->getPagesCount() - $pageContextLength + 1;
        }

        for ($i = 0; $i < $pageContextLength; ++$i) {
            $addPageLink($start + $i);
        }

        for ($i = 0; $i < $boundaryContextLength; ++$i) {
            $addPageLink($this->getPagesCount() - $boundaryContextLength + 1 + $i);
        }

        $links[] = $this->buildPageLink(min($this->getPagesCount(), $this->getPage() + 1), 'next');

        return $links;
    }
}
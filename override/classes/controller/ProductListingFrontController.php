<?php
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


// use PrestaShop\PrestaShop\Core\Filter\CollectionFilter;
// use PrestaShop\PrestaShop\Core\Filter\HashMapWhitelistFilter;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
// use PrestaShop\PrestaShop\Core\Product\Search\Pagination;
// use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchResult;
// use PrestaShop\PrestaShop\Core\Product\Search\Facet;
// use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
// use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchProviderInterface;
// use PrestaShop\PrestaShop\Core\Product\Search\FacetsRendererInterface;

/**
 * This class is the base class for all front-end "product listing" controllers,
 * like "CategoryController", that is, controllers whose primary job is
 * to display a list of products and filters to make navigation easier.
 */
abstract class ProductListingFrontController extends ProductListingFrontControllerCore
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Pagination is HARD. We let the core do the heavy lifting from
     * a simple representation of the pagination.
     *
     * Generated URLs will include the page number, obviously,
     * but also the sort order and the "q" (facets) parameters.
     *
     * @param ProductSearchQuery  $query
     * @param ProductSearchResult $result
     *
     * @return an array that makes rendering the pagination very easy
     */
    protected function getTemplateVarPagination(
        ProductSearchQuery $query,
        ProductSearchResult $result
    ) {
        $pagination = new CustomPagination();
        $pagination
            ->setPage($query->getPage())
            ->setPagesCount(
                ceil($result->getTotalProductsCount() / $query->getResultsPerPage())
            )
        ;

        $totalItems = $result->getTotalProductsCount();
        $itemsShownFrom = ($query->getResultsPerPage() * ($query->getPage() - 1)) + 1;
        $itemsShownTo = $query->getResultsPerPage() * $query->getPage();

        return array(
            'total_items' => $totalItems,
            'items_shown_from' => $itemsShownFrom,
            'items_shown_to' => ($itemsShownTo <= $totalItems) ? $itemsShownTo : $totalItems,
            'pages' => array_map(function ($link) {
                $link['url'] = $this->updateQueryString(array(
                    'page' => $link['page'],
                ));

                return $link;
            }, $pagination->buildLinks()),
            // Compare to 3 because there are the next and previous links
            'should_be_displayed' => (count($pagination->buildLinks()) > 3)
        );
    }

}


class CustomPagination
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

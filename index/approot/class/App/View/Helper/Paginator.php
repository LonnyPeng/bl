<?php
namespace App\View\Helper;

use Framework\ServiceLocator\ServiceLocator;
use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\Utils\Http;
use App\Utils\Paginator as FrameworkPaginator;

class Paginator extends FrameworkPaginator implements ServiceLocatorAwareInterface
{
    public function __invoke($itemCountTotal, $itemCountPerPage = 20)
    {
        $helpers = $this->locator->get(HELPER_MANAGER);
        if ($helpers->param("pagesize")) {
            $itemCountPerPage = $helpers->param("pagesize");
        }

        $this->setTotalItemCount($itemCountTotal)
             ->setItemCountPerPage($itemCountPerPage)
             ->setCurrentPageNumber($helpers->param("page"));

        if ($helpers->param("page") > $this->getPageCount()) {
            Http::redirect($helpers->url->removeParams("page"));
        }
        return $this;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $locator
     * @return Url
     */
    public function setServiceLocator(ServiceLocator $locator)
    {
        $this->locator = $locator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->locator;
    }
}

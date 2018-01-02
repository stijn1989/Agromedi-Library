<?php
namespace ITC;


/**
 * Controls pagination.
 *
 * @package ITC
 */
class Paginator
{


    private $currentPage;

    private $itemsPerPage;

    private $totalItems;

    private $pages;

    private $baseUri;


    public function __construct($baseUri, $itemsPerPage, $totalItems)
    {
        $this->setBaseUri($baseUri);
        $this->setItemsPerPage($itemsPerPage);
        $this->setTotalItems($totalItems);
    }


    public function setBaseUri($baseUri)
    {
        if(substr($baseUri, -1) != '/') $baseUri .= '/';
        $this->baseUri = $baseUri;
    }


    public function getBaseUri()
    {
        return $this->baseUri;
    }


    public function setItemsPerPage($itemsPerPage)
    {
        if($itemsPerPage <= 0) throw new \Exception('$itemsPerPage cannot be lower than zero!');
        $this->itemsPerPage = $itemsPerPage;
    }


    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }


    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
        $this->pages = ceil($totalItems/$this->itemsPerPage);
    }


    public function getTotalItems()
    {
        return $this->totalItems;
    }


    public function setCurrentPage($currentPage)
    {
        if($currentPage < 0 || $currentPage > $this->pages) $currentPage = 0;
        $this->currentPage = $currentPage;
    }


    public function getCurrentPage()
    {
        return $this->currentPage;
    }


    public function render()
    {
        $pages = $this->pages;
        $current = $this->getCurrentPage();
        $uri = $this->getBaseUri();
        $render = '';

        if($pages > 1) {
            $render = $this->renderAboveTen($pages, $current, $uri);
        }

        return $render;
    }


    private function renderBelowTen($pages, $current, $uri)
    {
        $render = '<nav><ul class="pagination" style="margin:0">';
        if($current > 1) {
            $render .= '<li>
                    <a href="' . $uri . ($current - 1) . '" aria-label="Vorige">
                        <span aria-hidden="true">«</span>
                    </a>
                </li>';
        }

        for($i = 1 ; $i <= $pages ; $i++) {
            if($i == $current) {
                $render .= '<li class="active"><a href="' . $uri . $i . '"><strong>' . $i . '</strong></a></li>';
            } else {
                $render .= '<li><a href="' . $uri . $i . '">' . $i . '</a></li>';
            }
        }

        if($current < $pages) {
            $render .= '<li>
                    <a href="' . $uri . ($current + 1) . '" aria-label="Volgende">
                        <span aria-hidden="true">»</span>
                    </a>
                </li>';
        }
        $render .= '</ul></nav>';

        return $render;
    }


    private function renderAboveTen($pages, $current, $uri)
    {
        $render = '<div class="btn-group">';
        if($current > 1) {
            $render .= '<a href="' . $uri . ($current - 1) . '" class="btn btn-default-bright" aria-label="Vorige">
                            <span aria-hidden="true">«</span>
                        </a>';
        }

        $render .= '<a href="' . $uri . $current . '" class="btn btn-default-bright">Pagina ' . $current .'</a>';

        $render .= '<button type="button" class="btn btn-default-bright dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                    </button>';
        $render .= '<ul class="dropdown-menu">';
        for($i = 1 ; $i <= $pages ; $i++) {
            $render .= '<li><a href="'. $uri . $i . '">Pagina ' . $i . '</a></li>';
        }
        $render .= '</ul>';

        if($current < $pages) {
            $render .= '<a href="' . $uri . ($current + 1) . '" class="btn btn-default-bright" aria-label="Vorige">
                            <span aria-hidden="true">»</span>
                        </a>';
        }
        $render .= '</div>';

        return $render;
    }


}
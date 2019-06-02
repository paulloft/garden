<?php

/**
 * Class PagerModule
 * For page output switch module
 */

namespace Addons\Dashboard\Modules;

use Addons\Dashboard\Interfaces\Module;
use Garden\Exception\NotFound;
use Garden\Renderers\View;
use Garden\Request;
use Garden\Response;
use Garden\Traits\Instance;

class Pager implements Module {
    protected $current;
    protected $pageCount;

    protected $perPage = 20;
    protected $count = 0;
    protected $options = [
        'parameter' => 'p',
        'showAlways' => true,
        'arrows' => true,
        'range' => 3
    ];

    protected $request;

    use Instance;

    /**
     * PagerModule constructor.
     * @param int $count count items
     * @param int $perPage items per page
     * @param array $options optiont from PagerModule::$options
     */
    public function __construct($count, $perPage = false, array $options = [])
    {
        if ($perPage) {
            $this->perPage = $perPage;
        }
        $this->count = $count;
        $this->options = array_merge($this->options, $options);

        $this->request = Request::current();
    }

    /**
     * Returned offset from sql query
     * @return int
     */
    public function offset(): int
    {
        return ($this->curentPage() - 1) * $this->perPage;
    }

    /**
     * Return count of pages
     * @return int
     */
    public function countPage(): int
    {
        if (!$this->pageCount) {
            $this->pageCount = (int)ceil($this->count / $this->perPage) ?: 1;
        }

        return $this->pageCount;
    }

    /**
     * Return number of current page
     * @param int $num
     * @return int
     */
    public function curentPage($num = null): int
    {
        if ($num) {
            $this->current = (int)$num;
        } elseif (!$this->current) {
            $param = $this->options['parameter'];
            $pageCount = $this->countPage();
            $current = (int)$this->request->getQuery($param, 0);
            $this->current = ($current > 0 && $current <= $pageCount) ? $current : 1;
        }

        return $this->current;
    }

    /**
     * Rendering function
     * @return string
     * @throws NotFound
     */
    public function render(array $params = []): string
    {
        $pageCount = $this->countPage();

        if ($pageCount <= 1 && !$this->options['showAlways']) {
            return false;
        }

        $current = $this->curentPage();

        $defaultStart = 1;
        $defaultEnd = $pageCount;

        $pages = $this->getPages($current, $defaultStart, $defaultEnd);

        $view = new View('pager', 'modules', 'dashboard');

        $view->setData('pages', $pages);
        $view->setData('current', $current);
        $view->setData('start', $defaultStart);
        $view->setData('end', $defaultEnd);
        $view->setData('uri', $this->getUri());
        $view->setData('showArrows', $this->options['arrows']);

        return $view->fetch(Response::current());
    }

    /**
     * @return string
     */
    protected function getUri(): string
    {
        $path = $this->request->getPath();
        $query = $this->request->getQuery();
        unset($query['p']);

        $query = http_build_query($query);

        return $path . '?p=<page>' . ($query ? '&' . $query : null);
    }

    /**
     * @param $current
     * @param $defaultStart
     * @param $defaultEnd
     * @return array
     */
    protected function getPages(int $current, int $defaultStart, int $defaultEnd): array
    {

        $range = (int)$this->options['range'];
        $displayPages = ($range * 2) + 1;

        if ($defaultEnd <= $displayPages) {
            //(ex. 1 2 3 4 5 6 7)
            $start = $defaultStart;
            $end = $defaultEnd;
        } elseif ($current + $range <= $displayPages + 1) {
            // (ex: 1 2 3 4 5 6 7 ... 81)
            $start = $defaultStart;
            $end = $displayPages;
        } elseif ($current + $range >= $defaultEnd - 1) {
            // (ex: 1 ... 75 76 77 78 79 80 81)
            $start = $current - $range;
            $end = $defaultEnd;
        } else {
            // (ex: 1 ... 4 5 6 7 8 9 10 ... 81)
            $start = $current - $range;
            $end = $current + $range;
        }

        $pages = [];
        if ($start !== $defaultStart) {
            $pages[] = $defaultStart;
            $pages[] = null;
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end !== $defaultEnd) {
            $pages[] = null;
            $pages[] = $defaultEnd;
        }

        return $pages;
    }
}
<?php

/**
 * Class PagerModule
 * For page output switch module
 */

class PagerModule
{
    protected $current;
    protected $pageCount;

    protected $perPage = 20;
    protected $count = 0;
    protected $options = [
        'parameter'  => 'p',
        'showAlways' => true,
        'arrows'     => true,
        'range'      => 3
    ];

    protected $request;

    use \Garden\Traits\Instance;

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

        $this->request = \Garden\Gdn::request();
    }

    /**
     * Returned offset from sql query
     * @return int|null
     */
    public function offset()
    {
        return ($this->curentPage() - 1) * $this->perPage;
    }

    /**
     * Return count of pages
     * @return int
     */
    public function countPage()
    {
        if(!$this->pageCount) {
            $this->pageCount = (int)ceil($this->count / $this->perPage) ?: 1;
        }
        
        return $this->pageCount;
    }

    /**
     * Return number of current page
     * @param null $num
     * @return int|null
     */
    public function curentPage($num = null)
    {
        if ($num) {
            $this->current = $num;
        } elseif (!$this->current) {
            $param = val('parameter', $this->options);
            $pageCount = $this->countPage();
            $current = (int)$this->request->getQuery($param, 0);
            $this->current = ($current > 0 && $current <= $pageCount) ? $current : 1;
        }

        return $this->current;
    }

    /**
     * Rendering function
     * @return bool|string
     */
    public function render()
    {
        $pageCount = $this->countPage();

        if ($pageCount <= 1 && !val('showAlways', $this->options)) {
            return false;
        }

        $current = $this->curentPage();

        $defaultStart = 1;
        $defaultEnd   = $pageCount;

        $pages = $this->getPages($current, $defaultStart, $defaultEnd);

        $controller = new \Garden\Controller;

        $controller->setData('pages', $pages);
        $controller->setData('current', $current);
        $controller->setData('start', $defaultStart);
        $controller->setData('end', $defaultEnd);
        $controller->setData('uri', $this->getUri());
        $controller->setData('showArrows', val('arrows', $this->options));

        return $controller->fetchView('pager', 'modules', 'dashboard');
    }

    protected function getUri()
    {
        $path = $this->request->getPath();
        $query = $this->request->getQuery();
        unset($query['p']);

        $query = http_build_query($query);

        return $path.'?p=<page>'.($query ? '&'.$query : null);
    }

    protected function getPages($current, $defaultStart, $defaultEnd)
    {
        
        $range = val('range', $this->options);
        $displayPages = ($range * 2) + 1;

        if ($defaultEnd <= $displayPages) {
            //(ex. 1 2 3 4 5 6 7)
            $start = $defaultStart; 
            $end   = $defaultEnd;
        } elseif ($current + $range  <= $displayPages + 1) {
            // (ex: 1 2 3 4 5 6 7 ... 81)
            $start = $defaultStart; 
            $end   = $displayPages;
        } elseif ($current + $range >= $defaultEnd - 1) {
            // (ex: 1 ... 75 76 77 78 79 80 81)
            $start = $current - $range;
            $end   = $defaultEnd;
        } else {
            // (ex: 1 ... 4 5 6 7 8 9 10 ... 81)
            $start = $current - $range;
            $end   = $current + $range;
        }

        $pages = [];
        if($start !== $defaultStart) {
            $pages[] = $defaultStart;
            $pages[] = null;
        }

        for ($i=$start; $i <= $end; $i++) { 
            $pages[] = $i;
        }

        if($end !== $defaultEnd) {
            $pages[] = null;
            $pages[] = $defaultEnd;
        }

        return $pages;
    }
}
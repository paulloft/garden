<?php

class PagerModule extends \Garden\Controller
{
    protected $current;
    protected $pageCount;

    protected $perPage = 20;
    protected $count = 0;
    protected $options = array(
        'parameter'  => 'p',
        // 'direction'  => 'asc',
        'showAlways' => true,
        'arrows'     => true,
        'range'      => 3,
    );

    protected $request;

    public function __construct($count, $perPage = false, $options = [])
    {
        if ($perPage) $this->perPage = $perPage;
        $this->count = $count;
        $this->options = array_merge($this->options, $options);

        $this->request = \Garden\Gdn::request();

        parent::__construct();
    }

    public function offset()
    {
        return ($this->curentPage() - 1) * $this->perPage;
    }

    public function countPage()
    {
        if(!$this->pageCount) {
            $this->pageCount = (int)ceil($this->count / $this->perPage) ?: 1;
        }
        
        return $this->pageCount;
    }

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

    protected function getUri()
    {
        $path = $this->request->getPath();
        $query = $this->request->getQuery();
        unset($query['p']);

        $query = http_build_query($query);

        return $path.'?p=<page>'.($query ? '&'.$query : null);
    }

    public function toString()
    {
        $pageCount = $this->countPage();

        if ($pageCount <= 1 && !val('showAlways', $this->options))
            return false;

        $current = $this->curentPage();

        $defaultStart = 1;
        $defaultEnd   = $pageCount;

        $pages = $this->getPages($current, $defaultStart, $defaultEnd);

        $this->setData('pages', $pages);
        $this->setData('current', $current);
        $this->setData('start', $defaultStart);
        $this->setData('end', $defaultEnd);
        $this->setData('uri', $this->getUri());
        $this->setData('showArrows', val('arrows', $this->options));

        return $this->fetchView('pager', 'modules', 'dashboard');
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

        $pages = array();
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
<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class PaginationParser implements Parser
{
    const NAME = __CLASS__;

    const KEY_PAGE = 'page';
    const KEY_NBBYPAGE = 'number';

    const DEFAULT_PAGE  = 1;
    const DEFAULT_MAX_RESULT_PER_PAGE  = 100;
    const DEFAULT_NB_RESULT_PER_PAGE = 20;

    protected $maxResultPerPage;
    protected $defaultNbResultPerPage;

    public function __construct($maxResultPerPage = self::DEFAULT_MAX_RESULT_PER_PAGE, $defaultNbResultPerPage = self::DEFAULT_NB_RESULT_PER_PAGE)
    {
        $this->maxResultPerPage = $maxResultPerPage;
        $this->defaultNbResultPerPage = $defaultNbResultPerPage;
    }

    public function parse(Request $request)
    {
        $page = $this->parsePage($request);
        $nbResultPerPage = $this->parseNbPerPage($request);

        return array(
            self::KEY_PAGE => $page,
            self::KEY_NBBYPAGE => $nbResultPerPage
        );
    }

    protected function parsePage(Request $request)
    {
        $page = $request->query->get(self::KEY_PAGE);
        $page = $page && is_numeric($page) && $page > 0 ? floor($page) : 1;

        return $page;
    }

    protected function parseNbPerPage(Request $request)
    {
        $number = $request->query->get(self::KEY_NBBYPAGE);
        $number = $number && is_numeric($number) && $number > 0 ? floor($number) : $this->defaultNbResultPerPage;
        $number = $number <= $this->maxResultPerPage ? $number : $this->maxResultPerPage;

        return $number;
    }
}

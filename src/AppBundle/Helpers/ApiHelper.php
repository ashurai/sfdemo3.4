<?php
namespace AppBundle\Helpers;
class ApiHelper
{
    /**
     * Custom HTTP codes
     */
    const USER_DISABLED = 222;
    /**
     * @param $page
     * @param $elements_per_page
     *
     * @return int
     */
    public static function getPaginatorOffset($page, $elements_per_page = 10)
    {
        if (!$elements_per_page) {
            return 0;
        } elseif ($elements_per_page == 1) {
            return 1;
        } elseif ($page <= 1 || !$page) {
            return 0;
        }
        return (($page - 1) * $elements_per_page);
    }
    /**
     * @param $elements
     * @param $elements_per_page
     * @return int
     */
    public static function getTotalPaginationCounter($elements, $elements_per_page = 10)
    {
        if ($elements <= $elements_per_page) {
            return 1;
        } elseif (($elements % $elements_per_page) > 0) {
            return intval($elements / $elements_per_page)+1;
        }
        return intval($elements / $elements_per_page);
    }
}

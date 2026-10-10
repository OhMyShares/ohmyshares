<?php

namespace App\QueryFilters;

use Cerbero\QueryFilters\QueryFilters;

class BaseFilters extends QueryFilters
{
    /**
     * Sort param
     * @param $field
     */
    public function sort($field): void
    {
        // Parsing "-" (disabled)
        // $direction = 'asc';
        // if (substr($field, 0, 1) == '-') {
        //     $direction = 'desc';
        //     $field = substr($field, 1);
        // }

        $direction = 'asc';
        $d = $this->request->get('direction');
        if (in_array($d, ['asc', 'desc'])) {
            $direction = $d;
        }

        $this->query->orderBy($field, $direction);
    }
}

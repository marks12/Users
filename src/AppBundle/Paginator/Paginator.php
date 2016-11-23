<?php
/**
 * Created by PhpStorm.
 * User: tsv
 * Date: 27.10.16
 * Time: 14:46
 */

namespace AppBundle\Paginator;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator
{
    private $data = [];
    private $pagination = [];

    public function __construct($query, $fetchJoinCollection)
    {
        $offset = $query->getFirstResult();
        $limit = $query->getMaxResults();

        $paginator = new DoctrinePaginator($query, $fetchJoinCollection = false);
        $count = count($paginator);

        $data = [];

        foreach ($paginator as $author) {
            $data[] = $author;
        }

        $this->data = $data;
        $this->pagination = [
            'count' => $count,
            'page' => (int)$this->getPage($offset, $limit),
            'per_page' => (int)$limit
        ];
    }

    private function getPage($offset, $limit) {

        if($offset) {
            return ceil($offset / $limit) + 1;
        } else {
            return 1;
        }
    }

    public function getResult() {
        return [
            'data' => $this->data,
            'paginatoin' => $this->pagination
        ];
    }
}
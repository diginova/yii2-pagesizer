<?php

namespace diginova\pagesizer\data;

class ArrayDataProvider extends \portalium\data\ArrayDataProvider
{
    protected function prepareModels()
    {
        if ($this->pagination != false && $this->pagination->getPageSize() && isset($_GET['per-page'])) {
            $this->pagination->pageSize = isset($_GET['per-page']) ? $_GET['per-page'] : $this->pagination->pageSize;
        }
        return parent::prepareModels();
    }
}

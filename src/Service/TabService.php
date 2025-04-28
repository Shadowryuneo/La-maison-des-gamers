<?php

namespace App\Service;

use App\Repository\CategoriesRepository;

class TabService
{
    public function __construct(private CategoriesRepository $categoriesRepository)
    {
        
    }

    public function getCategories(): array
    {
        return $this->categoriesRepository->findAll();
    }
}
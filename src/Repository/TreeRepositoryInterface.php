<?php namespace App\Repository;

interface TreeRepositoryInterface
{
    public function hasSubCategory(int $id): bool;
}

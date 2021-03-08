<?php namespace App\Api\Item\Admin\Finder;

interface IdFinder extends Finder
{
    /** @return FinderEntityView[] */
    public function findById(int $id): array;
}

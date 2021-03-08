<?php namespace App\Form\DataTransformer;

use App\Storage\FileLoader;
use Ewll\DBBundle\Repository\RepositoryProvider;
use Symfony\Component\Form\DataTransformerInterface;

class StorageFileToBase64ViewTransformerFactory
{
    private $repositoryProvider;
    private $fileLoader;

    public function __construct(RepositoryProvider $repositoryProvider, FileLoader $fileLoader)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->fileLoader = $fileLoader;
    }

    public function create(int $width, int $height, int $size): DataTransformerInterface
    {
        return new StorageFileToBase64ViewTransformer(
            $this->repositoryProvider,
            $this->fileLoader,
            $width,
            $height,
            $size
        );
    }
}

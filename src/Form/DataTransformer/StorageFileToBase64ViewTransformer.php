<?php namespace App\Form\DataTransformer;

use App\Entity\StorageFile;
use App\Storage\Exception\DownloadException;
use App\Storage\Exception\UploadException;
use App\Storage\FileLoader;
use Ewll\DBBundle\Repository\RepositoryProvider;
use RuntimeException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StorageFileToBase64ViewTransformer implements DataTransformerInterface
{
    const BASE64_IMAGE_PREFIX = 'data:image/jpeg;base64,';

    private $repositoryProvider;
    private $fileLoader;
    private $width;
    private $height;
    private $size;

    public function __construct(
        RepositoryProvider $repositoryProvider,
        FileLoader $fileLoader,
        int $width,
        int $height,
        int $size
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->fileLoader = $fileLoader;
        $this->width = $width;
        $this->height = $height;
        $this->size = $size;
    }

    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        $storageFile = $this->repositoryProvider->get(StorageFile::class)->findById($value);
        if (null === $storageFile) {
            throw new RuntimeException("Storage file #$value not found in DB");
        }

        try {
            $raw = $this->fileLoader->download($storageFile);
        } catch (DownloadException $e) {
            throw new RuntimeException("Cannot download file #$value from storage");
        }

        $transformedValue = self::BASE64_IMAGE_PREFIX . base64_encode($raw);

        return $transformedValue;
    }

    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $size = mb_strlen($value, 'UTF-8');
        if ($size > $this->size) {
            $failure = new TransformationFailedException('Oversized image.');
            $failure->setInvalidMessage('image.size');

            throw $failure;
        }
        $prefixPosition = mb_strpos($value, self::BASE64_IMAGE_PREFIX, 0, 'UTF-8');
        if (0 !== $prefixPosition) {
            $failure = new TransformationFailedException('Cannot decode image (prefix)');
            $failure->setInvalidMessage('image.base64-prefix');

            throw $failure;
        }
        $value = mb_substr($value, mb_strlen(self::BASE64_IMAGE_PREFIX, 'UTF-8'), null, 'UTF-8');

        $imageData = getimagesize('data://application/octet-stream;base64,' . $value);
        if (false === $imageData) {
            $failure = new TransformationFailedException('Cannot decode image (getimagesizefromstring)');
            $failure->setInvalidMessage('image.data');

            throw $failure;
        }
        $width = $imageData[0];
        $height = $imageData[1];
        if ($width !== $this->width) {
            $failure = new TransformationFailedException("Incorrect image width: '$width'");
            $failure->setInvalidMessage('image.width');

            throw $failure;
        }
        if ($height !== $this->height) {
            $failure = new TransformationFailedException("Incorrect image height: '$height'");
            $failure->setInvalidMessage('image.height');

            throw $failure;
        }

        $raw = base64_decode($value);
        if (false === $raw) {
            $failure = new TransformationFailedException('Cannot decode image (base64_decode)');
            $failure->setInvalidMessage('image.decode');

            throw $failure;
        }

        $name = md5(time() . uniqid());//@TODO request_id
        $storageFileRepository = $this->repositoryProvider->get(StorageFile::class);
        $existsStorageFile = $storageFileRepository->findOneBy(['name' => $name]);
        if (null !== $existsStorageFile) {
            throw new RuntimeException("Storage file with name '$name' exists");//@TODO
        }
        $extension = 'jpg';
        $directory = "sf/{$name[0]}/{$name[1]}/{$name[2]}/";
        $storageFile = StorageFile::create(StorageFile::TYPE_ID_PRODUCT_IMAGE, $name, $extension, $directory);
        try {
            $this->fileLoader->upload($storageFile, $raw);
        } catch (UploadException $e) {
            $failure = new TransformationFailedException('Cannot upload file to storage');
            $failure->setInvalidMessage('image.upload');

            throw $failure;
        }
        $storageFileRepository->create($storageFile);

        return $storageFile->id;
    }
}

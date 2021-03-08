<?php namespace App\Storage;

use App\Entity\StorageFile;
use App\Storage\Exception\DownloadException;
use App\Storage\Exception\UploadException;
use Aws\S3\S3Client;
use Exception;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Symfony\Bridge\Monolog\Logger;

class FileLoader
{
    private $logger;
    private $awsKey;
    private $awsSecret;
    private $bucketRegion;
    private $bucketName;

    public function __construct(
        Logger $logger,
        string $awsKey,
        string $awsSecret,
        string $bucketRegion,
        string $bucketName
    ) {
        $this->logger = $logger;
        $this->awsKey = $awsKey;
        $this->awsSecret = $awsSecret;
        $this->bucketRegion = $bucketRegion;
        $this->bucketName = $bucketName;
    }

    /** @throws DownloadException */
    public function download(StorageFile $storageFile): string
    {
        try {
            $filesystem = $this->getFileSystem();
            $raw = $filesystem->read($storageFile->compilePath());
            if (false === $raw) {
                throw new Exception('no info');
            }

            return $raw;
        } catch (Exception $e) {
            $error = 'Cannot download file';
            $this->logger->err($error, ['message' => $e->getMessage()]);

            throw new DownloadException($error, 0, $e);
        }
    }

    /** @throws UploadException */
    public function upload(StorageFile $storageFile, string $raw)
    {
        try {
            $filesystem = $this->getFileSystem();
            $result = $filesystem->write($storageFile->compilePath(), $raw, ['ACL' => 'public-read']);
            if (false === $result) {
                throw new Exception('no info');
            }
        } catch (Exception $e) {
            $error = 'Cannot upload file';
            $this->logger->err($error, ['message' => $e->getMessage()]);

            throw new UploadException($error, 0, $e);
        }
    }

    private function getFileSystem(): Filesystem
    {
        $client = new S3Client([
            'credentials' => [
                'key' => $this->awsKey,
                'secret' => $this->awsSecret,
            ],
            'region' => $this->bucketRegion,
            'version' => '2006-03-01',
        ]);

        $adapter = new AwsS3Adapter($client, $this->bucketName);
        $filesystem = new Filesystem($adapter);

        return $filesystem;
    }
}

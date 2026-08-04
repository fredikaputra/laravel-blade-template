<?php

declare(strict_types=1);

namespace App\Actions\Storage;

use Aws\S3\S3Client;
use Illuminate\Config\Repository;

final readonly class AbortMultipartUpload
{
    public function __construct(private S3Client $s3, private Repository $repository) {}

    public function handle(string $key, string $uploadId): void
    {
        $this->s3->abortMultipartUpload([
            'Bucket' => $this->repository->string('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);
    }
}

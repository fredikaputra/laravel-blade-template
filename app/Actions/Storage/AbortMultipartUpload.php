<?php

declare(strict_types=1);

namespace App\Actions\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

final readonly class AbortMultipartUpload
{
    public function __construct(private S3Client $s3) {}

    public function handle(string $key, string $uploadId): void
    {
        $this->s3->abortMultipartUpload([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);
    }
}

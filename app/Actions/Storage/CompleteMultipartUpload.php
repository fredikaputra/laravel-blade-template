<?php

declare(strict_types=1);

namespace App\Actions\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

final readonly class CompleteMultipartUpload
{
    public function __construct(private S3Client $s3) {}

    /**
     * @param  array<int, array{PartNumber: int, ETag: string}>  $parts
     * @return array{key: string, location: string}
     */
    public function handle(string $key, string $uploadId, array $parts): array
    {
        $result = $this->s3->completeMultipartUpload([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ]);

        return [
            'key' => is_string($result['Key']) ? $result['Key'] : '',
            'location' => is_string($result['Location']) ? $result['Location'] : '',
        ];
    }
}

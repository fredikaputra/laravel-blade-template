<?php

declare(strict_types=1);

namespace App\Actions\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

final readonly class CreateMultipartUpload
{
    public function __construct(private S3Client $s3) {}

    /**
     * @return array{upload_id: string, key: string}
     */
    public function handle(string $path, ?string $contentType = null): array
    {
        $params = [
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => $path,
        ];

        if ($contentType !== null && $contentType !== '') {
            $params['ContentType'] = $contentType;
        }

        $result = $this->s3->createMultipartUpload($params);

        return [
            'upload_id' => is_string($result['UploadId']) ? $result['UploadId'] : '',
            'key' => is_string($result['Key']) ? $result['Key'] : '',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

final readonly class ListMultipartUploadParts
{
    public function __construct(private S3Client $s3) {}

    /**
     * @return array<int, array{part_number: int, etag: string, size: int}>
     */
    public function handle(string $key, string $uploadId): array
    {
        $result = $this->s3->listParts([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);

        $rawParts = is_array($result['Parts']) ? $result['Parts'] : [];

        $parts = [];

        foreach ($rawParts as $part) {
            if (! is_array($part)) {
                continue;
            }

            $parts[] = [
                'part_number' => is_numeric($part['PartNumber']) ? (int) $part['PartNumber'] : 0,
                'etag' => is_string($part['ETag']) ? $part['ETag'] : '',
                'size' => is_numeric($part['Size']) ? (int) $part['Size'] : 0,
            ];
        }

        return $parts;
    }
}

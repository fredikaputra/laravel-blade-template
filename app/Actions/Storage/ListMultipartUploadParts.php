<?php

declare(strict_types=1);

namespace App\Actions\Storage;

use Aws\S3\S3Client;
use Illuminate\Config\Repository;

final readonly class ListMultipartUploadParts
{
    public function __construct(private S3Client $s3, private Repository $repository) {}

    /**
     * @return list<array{part_number: int, etag: string, size: int}>
     */
    public function handle(string $key, string $uploadId): array
    {
        $result = $this->s3->listParts([
            'Bucket' => $this->repository->string('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);

        $rawParts = \is_array($result['Parts']) ? $result['Parts'] : [];

        $parts = [];

        foreach ($rawParts as $part) {
            if (! \is_array($part)) {
                continue;
            }

            $partNumber = $part['PartNumber'] ?? null;
            $etag = $part['ETag'] ?? null;
            $size = $part['Size'] ?? null;

            $parts[] = [
                'part_number' => \is_numeric($partNumber) ? (int) $partNumber : 0,
                'etag' => \is_string($etag) ? $etag : '',
                'size' => \is_numeric($size) ? (int) $size : 0,
            ];
        }

        return $parts;
    }
}

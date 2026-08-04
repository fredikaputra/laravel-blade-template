<?php

declare(strict_types=1);

namespace App\Actions\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

final readonly class SignMultipartUploadPart
{
    public function __construct(private S3Client $s3) {}

    /**
     * @return array{url: string, part_number: int}
     */
    public function handle(
        string $key,
        string $uploadId,
        int $partNumber,
        int $expiresInMinutes = 20
    ): array {
        $command = $this->s3->getCommand('UploadPart', [
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => $key,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);

        $signedRequest = $this->s3->createPresignedRequest(
            $command,
            Date::now()->addMinutes($expiresInMinutes)
        );

        return [
            'url' => (string) $signedRequest->getUri(),
            'part_number' => $partNumber,
        ];
    }
}

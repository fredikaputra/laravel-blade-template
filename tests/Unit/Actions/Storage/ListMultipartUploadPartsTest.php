<?php

declare(strict_types=1);

use App\Actions\Storage\ListMultipartUploadParts;
use Aws\Result;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

covers(ListMultipartUploadParts::class);

it('lists uploaded parts for a multipart session', function (): void {
    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('listParts')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
        ])
        ->andReturn(new Result([
            'Parts' => [
                [
                    'PartNumber' => 1,
                    'ETag' => '"etag-1"',
                    'Size' => 10485760,
                ],
                [
                    'PartNumber' => '2',
                    'ETag' => '"etag-2"',
                    'Size' => '5242880',
                ],
            ],
        ]));

    $action = new ListMultipartUploadParts($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123');

    expect($result)->toBe([
        [
            'part_number' => 1,
            'etag' => '"etag-1"',
            'size' => 10485760,
        ],
        [
            'part_number' => 2,
            'etag' => '"etag-2"',
            'size' => 5242880,
        ],
    ]);
});

it('returns empty array when no parts exist in result', function (): void {
    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('listParts')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
        ])
        ->andReturn(new Result([]));

    $action = new ListMultipartUploadParts($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123');

    expect($result)->toBeEmpty();
});

it('handles malformed parts and missing fields gracefully', function (): void {
    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('listParts')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
        ])
        ->andReturn(new Result([
            'Parts' => [
                'not-an-array',
                [
                    'PartNumber' => null,
                    'ETag' => null,
                    'Size' => null,
                ],
            ],
        ]));

    $action = new ListMultipartUploadParts($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123');

    expect($result)->toBe([
        [
            'part_number' => 0,
            'etag' => '',
            'size' => 0,
        ],
    ]);
});

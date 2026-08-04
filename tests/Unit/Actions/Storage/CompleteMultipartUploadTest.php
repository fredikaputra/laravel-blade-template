<?php

declare(strict_types=1);

use App\Actions\Storage\CompleteMultipartUpload;
use Aws\Result;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

covers(CompleteMultipartUpload::class);

it('completes multipart upload with location in result', function (): void {
    $parts = [
        ['PartNumber' => 1, 'ETag' => '"etag1"'],
        ['PartNumber' => 2, 'ETag' => '"etag2"'],
    ];

    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('completeMultipartUpload')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ])
        ->andReturn(new Result([
            'Key' => 'videos/sample.mp4',
            'Location' => 'https://s3.example.com/videos/sample.mp4',
        ]));

    $action = new CompleteMultipartUpload($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123', $parts);

    expect($result)->toBe([
        'key' => 'videos/sample.mp4',
        'location' => 'https://s3.example.com/videos/sample.mp4',
    ]);
});

it('completes multipart upload without location in result', function (): void {
    $parts = [
        ['PartNumber' => 1, 'ETag' => '"etag1"'],
    ];

    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('completeMultipartUpload')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ])
        ->andReturn(new Result([
            'Key' => 'videos/sample.mp4',
        ]));

    $action = new CompleteMultipartUpload($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123', $parts);

    expect($result)->toBe([
        'key' => 'videos/sample.mp4',
        'location' => '',
    ]);
});

it('completes multipart upload without key and location in result', function (): void {
    $parts = [
        ['PartNumber' => 1, 'ETag' => '"etag1"'],
    ];

    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('completeMultipartUpload')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ])
        ->andReturn(new Result([]));

    $action = new CompleteMultipartUpload($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123', $parts);

    expect($result)->toBe([
        'key' => '',
        'location' => '',
    ]);
});

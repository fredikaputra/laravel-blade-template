<?php

declare(strict_types=1);

use App\Actions\Storage\CreateMultipartUpload;
use Aws\Result;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

covers(CreateMultipartUpload::class);

it('creates multipart upload with content type', function (): void {
    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('createMultipartUpload')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'ContentType' => 'video/mp4',
        ])
        ->andReturn(new Result([
            'UploadId' => 'test-upload-id-123',
            'Key' => 'videos/sample.mp4',
        ]));

    $action = new CreateMultipartUpload($s3);

    $result = $action->handle('videos/sample.mp4', 'video/mp4');

    expect($result)->toBe([
        'upload_id' => 'test-upload-id-123',
        'key' => 'videos/sample.mp4',
    ]);
});

it('creates multipart upload without content type', function (?string $contentType): void {
    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('createMultipartUpload')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'files/sample.bin',
        ])
        ->andReturn(new Result([
            'UploadId' => 'test-upload-id-456',
            'Key' => 'files/sample.bin',
        ]));

    $action = new CreateMultipartUpload($s3);

    $result = $action->handle('files/sample.bin', $contentType);

    expect($result)->toBe([
        'upload_id' => 'test-upload-id-456',
        'key' => 'files/sample.bin',
    ]);
})->with([
    'null content type' => null,
    'empty string content type' => '',
]);

it('handles missing or non-string upload id and key in result', function (): void {
    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('createMultipartUpload')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'files/sample.bin',
        ])
        ->andReturn(new Result([]));

    $action = new CreateMultipartUpload($s3);

    $result = $action->handle('files/sample.bin');

    expect($result)->toBe([
        'upload_id' => '',
        'key' => '',
    ]);
});

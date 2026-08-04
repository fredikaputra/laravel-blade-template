<?php

declare(strict_types=1);

use App\Actions\Storage\AbortMultipartUpload;
use Aws\Result;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;

covers(AbortMultipartUpload::class);

it('aborts multipart upload session', function (): void {
    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('abortMultipartUpload')
        ->once()
        ->with([
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
        ])
        ->andReturn(new Result([]));

    $action = new AbortMultipartUpload($s3);

    $action->handle('videos/sample.mp4', 'test-upload-123');
});

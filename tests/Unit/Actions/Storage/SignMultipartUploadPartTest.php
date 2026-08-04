<?php

declare(strict_types=1);

use App\Actions\Storage\SignMultipartUploadPart;
use Aws\CommandInterface;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Config;

covers(SignMultipartUploadPart::class);

it('generates presigned url for a multipart part with custom expiration', function (): void {
    $now = now();
    $this->travelTo($now);

    $command = Mockery::mock(CommandInterface::class);
    $signedRequest = new Request('PUT', 'https://s3.example.com/videos/sample.mp4?uploadId=test-upload-123&partNumber=1');

    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('getCommand')
        ->once()
        ->with('UploadPart', [
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
            'PartNumber' => 1,
        ])
        ->andReturn($command);

    $s3->shouldReceive('createPresignedRequest')
        ->once()
        ->with(
            $command,
            Mockery::on(fn (DateTimeInterface $exp): bool => $exp->getTimestamp() === $now->copy()->addMinutes(15)->getTimestamp())
        )
        ->andReturn($signedRequest);

    $action = new SignMultipartUploadPart($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123', 1, 15);

    expect($result)->toBe([
        'url' => 'https://s3.example.com/videos/sample.mp4?uploadId=test-upload-123&partNumber=1',
        'part_number' => 1,
    ]);
});

it('generates presigned url for a multipart part with default expiration', function (): void {
    $now = now();
    $this->travelTo($now);

    $command = Mockery::mock(CommandInterface::class);
    $signedRequest = new Request('PUT', 'https://s3.example.com/videos/sample.mp4?uploadId=test-upload-123&partNumber=2');

    $s3 = $this->mock(S3Client::class);
    $s3->shouldReceive('getCommand')
        ->once()
        ->with('UploadPart', [
            'Bucket' => Config::string('filesystems.disks.s3.bucket'),
            'Key' => 'videos/sample.mp4',
            'UploadId' => 'test-upload-123',
            'PartNumber' => 2,
        ])
        ->andReturn($command);

    $s3->shouldReceive('createPresignedRequest')
        ->once()
        ->with(
            $command,
            Mockery::on(fn (DateTimeInterface $exp): bool => $exp->getTimestamp() === $now->copy()->addMinutes(20)->getTimestamp())
        )
        ->andReturn($signedRequest);

    $action = new SignMultipartUploadPart($s3);

    $result = $action->handle('videos/sample.mp4', 'test-upload-123', 2);

    expect($result)->toBe([
        'url' => 'https://s3.example.com/videos/sample.mp4?uploadId=test-upload-123&partNumber=2',
        'part_number' => 2,
    ]);
});

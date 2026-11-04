<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageFileCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('products/1/medium/photo.webp', 'fake-image-bytes');
    }

    public function test_product_images_can_be_cached_by_phones(): void
    {
        $response = $this->get('/storage/products/1/medium/photo.webp');

        $response->assertOk();
        $this->assertSame('fake-image-bytes', $response->baseResponse->getFile()->getContent());

        $cache = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cache);
        $this->assertStringContainsString('max-age=604800', $cache);
        $this->assertStringNotContainsString('no-store', $cache);
        $this->assertNotNull($response->headers->get('ETag'));
        $this->assertNotNull($response->headers->get('Last-Modified'));
    }

    public function test_serving_an_image_does_not_open_a_session(): void
    {
        $response = $this->get('/storage/products/1/medium/photo.webp');

        $this->assertEmpty($response->headers->getCookies());
    }

    public function test_an_image_already_on_the_phone_is_not_sent_again(): void
    {
        $etag = $this->get('/storage/products/1/medium/photo.webp')->headers->get('ETag');

        $this->get('/storage/products/1/medium/photo.webp', ['If-None-Match' => $etag])
            ->assertStatus(304);
    }

    public function test_a_missing_file_returns_404(): void
    {
        $this->get('/storage/products/1/medium/absent.webp')->assertNotFound();
    }

    public function test_files_outside_the_public_folder_cannot_be_read(): void
    {
        $this->get('/storage/../.env')->assertNotFound();
        $this->get('/storage/..%2F..%2F.env')->assertNotFound();
    }
}

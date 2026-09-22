<?php

test('cloudcampus configuration is loaded with required keys', function () {
    expect(config('cloudcampus.default_quota_bytes'))->toBe(5368709120)
        ->and(config('cloudcampus.max_upload_size_kb'))->toBe(102400)
        ->and(config('cloudcampus.share_link_default_expiry_days'))->toBe(7)
        ->and(config('cloudcampus.trash_retention_days'))->toBe(30)
        ->and(config('cloudcampus.user_storage_path'))->toBe('users')
        ->and(config('cloudcampus.allowed_mime_types'))->toBeArray()->not->toBeEmpty();
});

test('application responds successfully on homepage', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

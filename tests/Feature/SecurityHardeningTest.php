<?php

use App\Models\QuizQuestion;
use App\Models\TrainingMaterial;
use App\Support\TrustedMediaUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('hanya URL video dari Google Drive dan YouTube yang dapat di-embed', function () {
    expect(TrustedMediaUrl::embedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'video_youtube'))
        ->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ')
        ->and(TrustedMediaUrl::embedUrl('https://drive.google.com/file/d/abc_DEF-123/view', 'video_gdrive'))
        ->toBe('https://drive.google.com/file/d/abc_DEF-123/preview')
        ->and(TrustedMediaUrl::embedUrl('https://contoh-tidak-tepercaya.test/embed/abc_DEF-123', 'video_youtube'))
        ->toBeNull()
        ->and(TrustedMediaUrl::embedUrl('http://www.youtube.com/watch?v=dQw4w9WgXcQ', 'video_youtube'))
        ->toBeNull();
});

test('model materi dan soal tidak memakai URL fallback yang tidak tepercaya', function () {
    $material = new TrainingMaterial(['tipe_media' => 'video_youtube', 'media_url' => 'https://contoh-tidak-tepercaya.test/video']);
    $question = new QuizQuestion(['tipe_media' => 'video_gdrive', 'media_url' => 'https://contoh-tidak-tepercaya.test/video']);

    expect($material->embed_url)->toBeNull()
        ->and($question->embed_url)->toBeNull();
});

test('login dan pemindaian dibatasi setelah lima percobaan per menit', function () {
    $identity = 'NIK-RATE-LIMIT-' . fake()->uuid();

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['user_id' => $identity])->assertRedirect();
    }

    $this->post('/login', ['user_id' => $identity])->assertTooManyRequests();
});

test('halaman login tidak lagi mengambil seluruh data manpower', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->get('/login')->assertOk();

    expect(collect(DB::getQueryLog())->pluck('query')->implode(' '))->not->toContain('m_manpower');
});

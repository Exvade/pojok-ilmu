<?php

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Lengkapi training dengan satu materi dan post-test bersoal agar dianggap siap dikerjakan operator.
 */
function makeTrainingReady(Training $training): Training
{
    TrainingMaterial::create(['training_id' => $training->id, 'urutan' => 1, 'judul' => 'Materi ' . $training->judul, 'tipe_media' => 'video_youtube', 'media_url' => 'https://example.com/video']);
    $postTest = Quiz::create([
        'training_id' => $training->id, 'tipe' => 'post_test', 'judul' => 'Post-test ' . $training->judul,
        'durasi_menit' => 15, 'passing_grade' => 80, 'max_retries' => 3, 'cooldown_hours' => 24,
    ]);
    QuizQuestion::create([
        'quiz_id' => $postTest->id, 'pertanyaan' => 'Pilih jawaban benar',
        'opsi_a' => 'Benar', 'opsi_b' => 'Salah', 'opsi_c' => 'Tidak tahu', 'opsi_d' => 'Lewati',
        'kunci_jawaban' => 'a',
    ]);

    return $training;
}

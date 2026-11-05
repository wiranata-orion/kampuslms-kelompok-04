<?php

namespace Tests\Unit;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Setelah `php artisan config:cache`, env() di luar config/ mengembalikan null.
 * Test ini gagal jika ada pemanggilan env() baru di luar config/.
 */
class NoEnvOutsideConfigTest extends TestCase
{
    public function test_env_hanya_dipakai_di_folder_config(): void
    {
        $root = dirname(__DIR__, 2);
        $violations = [];

        foreach (['app', 'bootstrap', 'routes', 'database', 'resources'] as $dir) {
            $path = $root.'/'.$dir;

            if (! is_dir($path)) {
                continue;
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                $name = str_replace('\\', '/', $file->getPathname());

                // bootstrap/cache berisi file hasil generate.
                if (! $file->isFile() || $file->getExtension() !== 'php' || str_contains($name, '/bootstrap/cache/')) {
                    continue;
                }

                foreach ($this->envCallLines((string) file_get_contents($name)) as $line) {
                    $violations[] = substr($name, strlen($root) + 1).":{$line}";
                }
            }
        }

        $this->assertSame([], $violations, "env() ditemukan di luar config/:\n".implode("\n", $violations));
    }

    /** @return array<int, int> nomor baris pemanggilan env() (komentar dan string diabaikan) */
    private function envCallLines(string $code): array
    {
        $tokens = array_values(array_filter(
            token_get_all($code),
            fn ($t) => ! (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))
        ));

        $lines = [];

        foreach ($tokens as $i => $token) {
            if (! is_array($token) || $token[0] !== T_STRING || strtolower($token[1]) !== 'env') {
                continue;
            }

            $next = $tokens[$i + 1] ?? null;
            $prev = $tokens[$i - 1] ?? null;
            $isCall = $next === '(';
            $isMethodOrDefinition = is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true);

            if ($isCall && ! $isMethodOrDefinition) {
                $lines[] = $token[2];
            }
        }

        return $lines;
    }
}

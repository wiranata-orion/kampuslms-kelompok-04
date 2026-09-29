<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Cegah error "Specified key was too long" di MySQL/MariaDB versi
        // lama saat migrasi membuat unique index di atas kolom string
        // (courses.code, users.email, users.nim_nip, dsb).
        Schema::defaultStringLength(191);

        // Deteksi dini: relasi yang belum di-eager-load (N+1), pengisian
        // atribut yang tidak ada di $fillable, dan akses atribut yang
        // tidak ada di model. HANYA aktif di luar production supaya tidak
        // mematikan aplikasi di depan pengguna asli kalau ada yang lolos.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Direktif Blade `@role('admin') ... @endrole` (bisa lebih dari
        // satu: `@role('admin', 'dosen') ... @endrole`) untuk
        // menyembunyikan/menampilkan elemen UI per peran tanpa perlu
        // Policy. PENTING: ini HANYA kerapian tampilan, BUKAN pengganti
        // otorisasi — menyembunyikan tombol tidak mencegah orang
        // mengetik URL-nya langsung. Pengecekan akses sesungguhnya tetap
        // wajib lewat abort_unless() di controller.
        Blade::if('role', function (string ...$roles) {
            $user = auth()->user();

            return $user && in_array($user->role, $roles, true);
        });
    }
}
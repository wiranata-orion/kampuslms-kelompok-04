<x-layout title="Akses Ditolak · Kampuskin">
	<section class="panel" style="max-width:680px;margin:8vh auto;text-align:center;padding:clamp(28px,7vw,64px)">
		<span class="eyebrow">Akses terbatas</span><h1 style="font-size:clamp(4rem,12vw,7rem);color:var(--violet)">403</h1><h2>Kamu belum bisa masuk ke halaman ini</h2><p>{{ $exception->getMessage() ?: 'Halaman ini hanya tersedia untuk akun dengan izin yang sesuai.' }}</p>
		<div class="actions" style="justify-content:center">@auth<a class="btn" href="{{ route('dashboard') }}">Kembali ke beranda</a>@else<a class="btn" href="{{ route('login') }}">Masuk</a>@endauth</div>
	</section>
</x-layout>

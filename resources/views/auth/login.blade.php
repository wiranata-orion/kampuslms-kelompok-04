<x-layout title="Masuk · Kampuskin">
	<section class="login-card">
		<div class="login-art">
			<a class="brand" href="{{ route('login') }}"><span class="brand-mark" aria-hidden="true"></span><span class="brand-name">Kampuskin</span></a>
			<div class="orbit-mark" aria-hidden="true"><span></span></div>
			<div><span class="eyebrow">Belajar, terhubung, bertumbuh</span><h1>Tempat ide baik jadi karya nyata.</h1><p>Masuk ke ruang kuliah digitalmu.</p></div>
		</div>
		<div class="login-form-side">
			<span class="eyebrow">Selamat datang kembali</span>
			<h2>Masuk akun</h2>
			<p>Gunakan email kampus untuk melanjutkan.</p>
			@if ($errors->any()) <div class="alert alert-error" role="alert">{{ $errors->first() }}</div> @endif
			<form action="{{ route('login.store') }}" method="POST">
				@csrf
				<div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>@error('email')<p class="field-error">{{ $message }}</p>@enderror</div>
				<div class="field"><label for="password">Kata sandi</label><input id="password" type="password" name="password" autocomplete="current-password" required>@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
				<div class="field"><label style="display:flex;align-items:center;gap:9px;font-weight:500"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya</label></div>
				<div class="actions"><button class="btn btn-pink" type="submit">Masuk ke Kampuskin</button></div>
			</form>
		</div>
	</section>
</x-layout>

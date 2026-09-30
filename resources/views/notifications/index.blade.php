<x-layout title="Notifikasi · Kampuskin">
    <section class="page-heading"><div><span class="eyebrow">Pusat aktivitas</span><h1>Notifikasi</h1><p class="subtitle">Kabar terbaru tentang kelas dan aktivitas belajarmu.</p></div>@if(isset($notifications) && $notifications->isNotEmpty())<form action="{{ route('notifications.readAll') }}" method="POST">@csrf @method('PATCH')<button class="btn btn-quiet" type="submit">Tandai semua dibaca</button></form>@endif</section>
    <section class="panel">
        @forelse ($notifications ?? [] as $notification)
            <article class="notice-row {{ $notification->read_at ? '' : 'unread' }}"><div><span class="eyebrow">{{ $notification->created_at?->diffForHumans() }}</span><h2>{{ data_get($notification->data, 'title', 'Pembaruan kampus') }}</h2><p>{{ data_get($notification->data, 'message', data_get($notification->data, 'body', 'Ada pembaruan baru untuk akunmu.')) }}</p>@if(data_get($notification->data, 'url'))<a href="{{ data_get($notification->data, 'url') }}">Lihat detail</a>@endif</div>@unless($notification->read_at)<form action="{{ route('notifications.read', $notification) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-quiet btn-small" type="submit">Tandai dibaca</button></form>@else<span class="pill pill-muted">Dibaca</span>@endunless</article>
        @empty
            <div class="empty-state"><span class="empty-mark">N</span><h2>Semua tenang</h2><p>Notifikasi baru akan muncul di sini.</p></div>
        @endforelse
        @if(isset($notifications) && method_exists($notifications, 'links'))<x-pagination :paginator="$notifications" />@endif
    </section>
</x-layout>
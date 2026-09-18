@props(['paginator'])

@if ($paginator->hasPages())
    <nav style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; margin-top: 1rem;">
        @if ($paginator->onFirstPage())
            <span style="color: #94a3b8;">&laquo; Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="color: #2563eb; text-decoration: none;">&laquo; Sebelumnya</a>
        @endif

        <span style="color: #475569;">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="color: #ff8aca; text-decoration: none;">Selanjutnya &raquo;</a>
        @else
            <span style="color: #94a3b8;">Selanjutnya &raquo;</span>
        @endif

        <span style="color: #94a3b8; margin-left: auto;">{{ $paginator->total() }} data</span>
    </nav>
@endif

@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="pagination-row" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage()) <span class="btn btn-quiet btn-small" aria-disabled="true">Sebelumnya</span> @else <a class="btn btn-quiet btn-small" href="{{ $paginator->previousPageUrl() }}">Sebelumnya</a> @endif
        <span>Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages()) <a class="btn btn-quiet btn-small" href="{{ $paginator->nextPageUrl() }}">Selanjutnya</a> @else <span class="btn btn-quiet btn-small" aria-disabled="true">Selanjutnya</span> @endif
        <span class="total">{{ $paginator->total() }} data</span>
    </nav>
@endif

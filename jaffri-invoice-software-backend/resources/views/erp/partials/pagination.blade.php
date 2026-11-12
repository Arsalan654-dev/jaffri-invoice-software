@if(isset($paginator) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div class="pagination-wrapper">
        {{ $paginator->links() }}
    </div>
@endif

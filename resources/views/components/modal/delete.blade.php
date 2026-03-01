@props([
    'id',
    'route',
    'title' => 'Are you sure?',
    'message',
    'cancelText' => 'Cancel',
    'deleteText' => 'Delete',
    'boldName' => false,
])

<div class="modal modal-blur fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body py-4">
                <div class="modal-title mb-2">{{ $title }}</div>
                <div class="text-secondary">
                    {!! $boldName
                        ? str_replace($userName = $message['name'] ?? '', '<strong>' . $userName . '</strong>', $message)
                        : $message !!}
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                    {{ $cancelText }}
                </button>

                <form action="{{ $route }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        {{ $deleteText }}
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

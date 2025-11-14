@props([
    'id',
    'title' => __('invoice.delete_invoice'),
    'action' => '#',
    'item' => '',
    'text' => __('invoice.delete_invoice_confirmation_with_id'),
])

@php
    // Replace :id first (plain)
    $tempText = str_replace(':id', $id, $text);

    // Replace :item with a highlighted span
    $finalText = str_replace(':item', "<span class='fw-bold text-danger'>{$item}</span>", $tempText);
@endphp

<div class="modal modal-blur fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">

                <div class="modal-body">
                    <div class="modal-title">{{ $title }}</div>

                    {{-- Allow HTML so the highlight works --}}
                    <div>{!! $finalText !!}</div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                        {{ __('modal.cancel') }}
                    </button>

                    <button type="submit" class="btn btn-danger">
                        {{ __('modal.confirm') }}
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

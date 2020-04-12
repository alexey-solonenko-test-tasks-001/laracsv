<div class="{{ $wrapCls ?? 'col-12 col-md-3 my-2 my-md-0' }} " >
    <button 
        class='{{ $btnCls ?? '' }} {{ $btnBaseCls ??  'btn-block btn btn-primary' }}'
        @if (isset($ajaxCall))
        data-btn-self-init-ajax="{{ $ajaxCall }}"
        @endif
        >{{ $label ?? 'Label' }}</button>
</div>
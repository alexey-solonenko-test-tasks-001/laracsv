<option
    value={{ $option['val'] ?? '' }}
    @if(isset($val))
        @if ($option['val'] = $val) 
            selected
        @endif
    @endif
    @if(isset($option['disabled']))
        @if ($option['disabled'])
            disabled
        @endif
    @endif
    >{{ $option['text'] ?? '' }}</option>
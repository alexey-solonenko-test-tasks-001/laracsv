<div class="{{ $wrapCls ?? 'col-12 col-md-3' }} ">
    <label for="{{ $form.'_'.$name }}">{{ $label }}</label>
    <input id='{{ $form."_".$name }}' value='{{ $val ?? '' }}' type='text' name='{{ $name }}' class='form-control ' />
</div>
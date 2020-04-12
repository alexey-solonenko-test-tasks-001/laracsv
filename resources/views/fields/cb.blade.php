<div class="{{ $wrapCls ?? 'col-12 col-md-3' }} ">
    <div class='form-check'>
        <input id='{{ $form."_".$name }}' value='{{ $val ?? '1' }}' type='checkbox' name='{{ $name }}' class=' form-check-input' />
        <label for="{{ $form.'_'.$name }}" class="form-check-label">{{ $label }}</label>
    </div> 
</div>
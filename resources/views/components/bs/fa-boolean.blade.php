@props(['data', 'trueClass' => 'text-success', 'falseClass' => 'text-danger'])

@if($data)
    <span class="{{ $trueClass }}"><i class="fas fa-check"></i></span>
@else
    <span class="{{ $falseClass }}"><i class="fas fa-times"></i></span>
@endif

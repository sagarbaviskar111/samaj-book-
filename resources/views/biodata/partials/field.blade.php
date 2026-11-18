{{-- Generic form field. Params: name, label, type (text|number|date|time|select|textarea|tel), options?, hint?, placeholder?, list?, required?, span2? --}}
@php
    $type = $type ?? 'text';
    $raw = $biodata->{$name} ?? null;
    if ($raw instanceof \Carbon\CarbonInterface) { $raw = $raw->format('Y-m-d'); }
    if ($name === 'birth_time' && $raw) { $raw = substr((string) $raw, 0, 5); }
    if ($name === 'manglik' && ! is_null($raw)) { $raw = $raw ? '1' : '0'; }
    $value = old($name, $raw);
    $options = $options ?? null;
@endphp
<div class="{{ ($span2 ?? false) ? 'sm:col-span-2' : '' }}">
    <label class="field-label" for="f-{{ $name }}">{{ $label }}@if($required ?? false) <span class="text-rose-700" aria-label="ज़रूरी">*</span>@endif</label>
    @if($type === 'select')
        <select id="f-{{ $name }}" name="{{ $name }}" class="input !min-h-12 !text-base" @required($required ?? false) @if($seg ?? false) data-seg @endif @isset($hint) aria-describedby="h-{{ $name }}" @endisset>
            <option value="">{{ $placeholder ?? '— चुनिए —' }}</option>
            @foreach($options as $k => $l)
                <option value="{{ $k }}" @selected((string) $value === (string) $k)>{{ $l }}</option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea id="f-{{ $name }}" name="{{ $name }}" rows="4" maxlength="2000" class="input !text-base" placeholder="{{ $placeholder ?? '' }}" @isset($hint) aria-describedby="h-{{ $name }}" @endisset>{{ $value }}</textarea>
    @else
        <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" class="input !min-h-12 !text-base"
               placeholder="{{ $placeholder ?? '' }}" @if($list ?? false) list="dl-{{ $name }}" @endif
               @if($type === 'number') min="{{ $min ?? 0 }}" max="{{ $max ?? 99 }}" inputmode="numeric" @endif
               @if($type === 'tel') inputmode="tel" maxlength="14" @endif
               @isset($hint) aria-describedby="h-{{ $name }}" @endisset
               @required($required ?? false)>
        @if($list ?? false)
            <datalist id="dl-{{ $name }}">@foreach($list as $o)<option value="{{ $o }}">@endforeach</datalist>
        @endif
    @endif
    @isset($hint)<p id="h-{{ $name }}" class="mt-1 text-sm text-ink-700">{{ $hint }}</p>@endisset
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>

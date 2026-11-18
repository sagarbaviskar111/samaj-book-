{{-- Text size control: अ- (smaller), अ (normal), अ+ (bigger); 3 steps in all. Logic lives in the inline script in layouts/base.blade.php. --}}
@props(['dark' => false])
<div role="group" aria-label="{{ __('अक्षर का आकार') }}" title="{{ __('अक्षर का आकार') }}"
     {{ $attributes->class(['mb-fs inline-flex shrink-0 items-center overflow-hidden rounded-full ring-1', $dark ? 'bg-white/10 ring-white/30' : 'bg-white ring-ink-200']) }}>
    @foreach([['window.sbFsStep&&window.sbFsStep(-1)', 'अ-', 'अक्षर छोटा करें', 'text-sm'], ['window.sbFs&&window.sbFs(1)', 'अ', 'अक्षर सामान्य करें', 'text-base'], ['window.sbFsStep&&window.sbFsStep(1)', 'अ+', 'अक्षर बड़ा करें', 'text-lg']] as [$js, $txt, $label, $sz])
        <button type="button" onclick="{{ $js }}" aria-label="{{ __($label) }}" title="{{ __($label) }}"
                class="mb-fs-btn grid h-10 min-w-10 place-items-center px-1.5 font-bold leading-none {{ $sz }} {{ $dark ? 'text-white' : 'text-ink-800' }}">{{ $txt }}</button>
    @endforeach
</div>

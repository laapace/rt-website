@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-neutral-300 focus:border-primary-600 focus:ring-primary-600 rounded-lg shadow-sm']) }}>

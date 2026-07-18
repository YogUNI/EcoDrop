@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-200 focus:border-blue-500 focus:ring-blue-500 focus:ring-1 rounded-xl bg-gray-50 text-sm font-semibold transition py-2.5 px-4 text-gray-700']) !!}>
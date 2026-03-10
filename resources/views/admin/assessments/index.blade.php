<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Screening Tests') }}</h2>
            <a href="{{ route('admin.assessments.create') }}"
               class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition">
                + {{ __('New assessment') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Screening Test') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('Questions') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('credits') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($assessments as $assessment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800">{{ $assessment->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $assessment->slug }}</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700">
                                    {{ $assessment->questions_count }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700">
                                    {{ $assessment->credits_cost }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full
                                        {{ $assessment->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $assessment->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.assessments.show', $assessment) }}"
                                       class="text-primary-600 text-sm hover:underline">{{ __('Manage') }} →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

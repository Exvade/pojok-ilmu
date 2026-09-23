@props(['headers' => []])

<div class="relative overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
            <tr>
                @if(isset($thead))
                    {{ $thead }}
                @else
                    @foreach($headers as $header)
                        <th scope="col" class="px-6 py-4 font-semibold whitespace-nowrap">
                            {{ $header }}
                        </th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 [&_tr:hover]:bg-gray-50 dark:[&_tr:hover]:bg-gray-800/50 transition-colors duration-200">
            {{ $slot }}
        </tbody>
    </table>
</div>

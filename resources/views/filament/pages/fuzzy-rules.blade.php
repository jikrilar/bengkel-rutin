<x-filament-panels::page>
    <div class="space-y-5">
        <div class="max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">
            Rule ini merupakan basis keputusan canonical. Admin dapat meninjau antecedent dan consequent, tetapi tidak dapat menambah, mengubah, atau menghapus rule.
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Progress KM</th>
                        <th class="px-4 py-3">Progress waktu</th>
                        <th class="px-4 py-3">Penggunaan</th>
                        <th class="px-4 py-3">Consequent</th>
                        <th class="px-4 py-3">Penjelasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($rules as $rule)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-950 dark:text-white">{{ $rule->code }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ str($rule->km_state)->headline() }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ str($rule->time_state)->headline() }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ str($rule->usage_state)->headline() }}</td>
                            <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">{{ $rule->consequent === 'urgent' ? 'Mendesak' : 'Tidak Mendesak' }}</td>
                            <td class="min-w-72 px-4 py-3 text-gray-600 dark:text-gray-400">{{ $rule->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>

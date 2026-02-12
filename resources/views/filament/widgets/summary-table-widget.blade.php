<x-filament-widgets::widget>
    <x-filament::section heading="Summary Penanganan Kasus">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-white">
                        <th class="bg-gray-700 px-3 py-2">Unit Kerja</th>
                        <th class="bg-rose-600 px-3 py-2 text-right">Jumlah</th>
                        <th class="bg-sky-600 px-3 py-2 text-right">Lidik</th>
                        <th class="bg-sky-600 px-3 py-2 text-right">Sidik</th>
                        <th class="bg-emerald-600 px-3 py-2 text-right">Henti Lidik</th>
                        <th class="bg-emerald-600 px-3 py-2 text-right">P21</th>
                        <th class="bg-emerald-600 px-3 py-2 text-right">SP3</th>
                        <th class="bg-emerald-600 px-3 py-2 text-right">Diversi</th>
                        <th class="bg-emerald-600 px-3 py-2 text-right">RJ</th>
                        <th class="bg-emerald-600 px-3 py-2 text-right">Limpah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summaryRows as $row)
                        <tr class="{{ $row['unit_kerja'] === 'TOTAL' ? 'bg-gray-100 text-gray-900 font-semibold dark:bg-gray-800 dark:text-gray-100' : 'border-b border-gray-200 text-gray-800 dark:border-gray-700 dark:text-gray-200' }}">
                            <td class="px-3 py-2">{{ $row['unit_kerja'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['jumlah'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['lidik'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['sidik'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['henti_lidik'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['p21'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['sp3'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['diversi'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['rj'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['limpah'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

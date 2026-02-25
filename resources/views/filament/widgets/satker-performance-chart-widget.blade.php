<x-filament-widgets::widget>
    <x-filament::section>
        <div
            x-data="{
                chart: null,
                chartData: @js($chartData),
                periode: 'bulan_ini',
                loading: false,
                pendingRequest: null,
                isDark: document.documentElement.classList.contains('dark'),

                async init() {
                    await this.loadChartJs()
                    // Detect dark mode
                    this.isDark = document.documentElement.classList.contains('dark')
                    this.renderChart()
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy()
                        this.chart = null
                    }
                },

                loadChartJs() {
                    return new Promise((resolve) => {
                        if (window.Chart) {
                            resolve()
                            return
                        }
                        const script = document.createElement('script')
                        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js'
                        script.onload = () => resolve()
                        document.head.appendChild(script)
                    })
                },

                async changePeriode(newPeriode) {
                    this.periode = newPeriode
                    this.loading = true

                    const requestId = Date.now()
                    this.pendingRequest = requestId

                    try {
                        const data = await $wire.call('fetchChartData', newPeriode)
                        if (this.pendingRequest === requestId) {
                            this.chartData = data
                            this.renderChart()
                        }
                    } catch (e) {
                        console.warn('Chart update failed:', e)
                    } finally {
                        if (this.pendingRequest === requestId) {
                            this.loading = false
                        }
                    }
                },

                getBarColors() {
                    return this.chartData.data.map((val) => {
                        if (val === 0) return this.isDark ? '#374151' : '#e5e7eb'
                        return '#6366f1'
                    })
                },

                renderChart() {
                    if (this.chart) {
                        this.chart.destroy()
                        this.chart = null
                    }

                    const canvas = this.$refs.canvas
                    if (!canvas) return
                    const ctx = canvas.getContext('2d')

                    const dataValues = this.chartData.data
                    const colors = this.getBarColors()

                    // Adaptive text colors for light/dark mode
                    const tickColor = this.isDark ? '#e5e7eb' : '#374151'
                    const gridColor = this.isDark ? 'rgba(107, 114, 128, 0.3)' : 'rgba(156, 163, 175, 0.4)'
                    const borderColor = this.isDark ? '#6b7280' : '#d1d5db'

                    const displayLabels = this.chartData.labels.map((label, i) => {
                        return dataValues[i] === 0 ? label + '\n(Nihil)' : label
                    })

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: displayLabels,
                            datasets: [{
                                label: 'Jumlah Kasus',
                                data: dataValues,
                                backgroundColor: colors,
                                borderColor: colors,
                                borderWidth: 1,
                                borderRadius: 6,
                                maxBarThickness: 44,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: { bottom: 10, top: 10 }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0,
                                        color: tickColor,
                                        font: { size: 12, weight: 'bold' },
                                    },
                                    grid: {
                                        color: gridColor,
                                    },
                                    border: {
                                        color: borderColor,
                                    },
                                },
                                x: {
                                    ticks: {
                                        color: tickColor,
                                        font: { size: 11, weight: '600' },
                                        maxRotation: 45,
                                        minRotation: 25,
                                    },
                                    grid: {
                                        display: false,
                                    },
                                    border: {
                                        color: borderColor,
                                    },
                                },
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                    titleColor: '#f9fafb',
                                    bodyColor: '#f3f4f6',
                                    borderColor: '#6366f1',
                                    borderWidth: 1,
                                    cornerRadius: 8,
                                    padding: 12,
                                    titleFont: { size: 13, weight: 'bold' },
                                    bodyFont: { size: 12 },
                                    callbacks: {
                                        label: function(context) {
                                            const val = context.parsed.y
                                            return val === 0 ? ' Nihil (0 Kasus)' : ' ' + val + ' Kasus'
                                        }
                                    }
                                },
                            },
                        },
                    })
                },
            }"
            x-init="init()"
        >
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                    Satker Berdasarkan Jumlah Kasus
                </h3>
                <div class="flex items-center gap-2">
                    <div
                        x-show="loading"
                        x-transition
                        class="h-4 w-4 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"
                    ></div>
                    <select
                        x-model="periode"
                        x-on:change="changePeriode($event.target.value)"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm transition focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                    >
                        <option value="bulan_ini">Bulan Ini</option>
                        <option value="bulan_lalu">Bulan Lalu</option>
                        <option value="1_tahun">1 Tahun Penuh</option>
                    </select>
                </div>
            </div>

            {{-- Chart --}}
            <div style="position: relative; width: 100%; height: 370px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

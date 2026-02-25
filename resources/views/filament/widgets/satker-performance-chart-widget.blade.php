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
                isMobile: window.innerWidth < 768,

                async init() {
                    await this.loadChartJs()
                    this.isDark = document.documentElement.classList.contains('dark')
                    this.isMobile = window.innerWidth < 768
                    this.renderChart()

                    // Re-render on resize for responsive switching
                    this._resizeHandler = this.debounce(() => {
                        const wasMobile = this.isMobile
                        this.isMobile = window.innerWidth < 768
                        if (wasMobile !== this.isMobile) {
                            this.renderChart()
                        }
                    }, 250)
                    window.addEventListener('resize', this._resizeHandler)
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy()
                        this.chart = null
                    }
                    if (this._resizeHandler) {
                        window.removeEventListener('resize', this._resizeHandler)
                    }
                },

                debounce(fn, ms) {
                    let timer
                    return (...args) => {
                        clearTimeout(timer)
                        timer = setTimeout(() => fn.apply(this, args), ms)
                    }
                },

                loadChartJs() {
                    return new Promise((resolve) => {
                        if (window.Chart && window.ChartDataLabels) {
                            resolve()
                            return
                        }
                        const loadScript = (src) => new Promise((res) => {
                            const s = document.createElement('script')
                            s.src = src
                            s.onload = () => res()
                            document.head.appendChild(s)
                        })
                        loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js')
                            .then(() => loadScript('https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js'))
                            .then(() => {
                                Chart.register(ChartDataLabels)
                                resolve()
                            })
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

                getStepSize(data) {
                    const max = Math.max(...data, 1)
                    if (max <= 10) return 1
                    if (max <= 30) return 5
                    if (max <= 100) return 10
                    if (max <= 500) return 50
                    return 100
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
                    const tickColor = this.isDark ? '#e5e7eb' : '#374151'
                    const gridColor = this.isDark ? 'rgba(107, 114, 128, 0.3)' : 'rgba(156, 163, 175, 0.4)'
                    const borderColor = this.isDark ? '#6b7280' : '#d1d5db'

                    const displayLabels = this.chartData.labels.map((label, i) => {
                        return dataValues[i] === 0 ? label + ' (Nihil)' : label
                    })

                    // Adjust container height for mobile horizontal layout
                    const container = canvas.parentElement
                    if (this.isMobile) {
                        const barCount = displayLabels.length
                        const rowHeight = 36
                        const minHeight = 320
                        container.style.height = Math.max(minHeight, barCount * rowHeight + 80) + 'px'
                    } else {
                        container.style.height = '370px'
                    }

                    const chartConfig = this.isMobile
                        ? this.getMobileConfig(displayLabels, dataValues, colors, tickColor, gridColor, borderColor)
                        : this.getDesktopConfig(displayLabels, dataValues, colors, tickColor, gridColor, borderColor)

                    this.chart = new Chart(ctx, chartConfig)
                },

                // Mobile: horizontal bars — labels on left, easy to read
                getMobileConfig(labels, data, colors, tickColor, gridColor, borderColor) {
                    return {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Jumlah Kasus',
                                data: data,
                                backgroundColor: colors,
                                borderColor: colors,
                                borderWidth: 1,
                                borderRadius: 4,
                                barThickness: 18,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {
                                padding: { right: 8, left: 0, top: 4, bottom: 4 }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: this.getStepSize(data),
                                        precision: 0,
                                        color: tickColor,
                                        font: { size: 11, weight: 'bold' },
                                    },
                                    grid: { color: gridColor },
                                    border: { color: borderColor },
                                },
                                y: {
                                    ticks: {
                                        color: tickColor,
                                        font: { size: 11, weight: '500' },
                                        crossAlign: 'far',
                                    },
                                    grid: { display: false },
                                    border: { color: borderColor },
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
                                    padding: 10,
                                    titleFont: { size: 12, weight: 'bold' },
                                    bodyFont: { size: 11 },
                                    callbacks: {
                                        label: function(context) {
                                            const val = context.parsed.x
                                            return val === 0 ? ' Nihil (0 Kasus)' : ' ' + val + ' Kasus'
                                        }
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    offset: 4,
                                    color: tickColor,
                                    font: { size: 11, weight: 'bold' },
                                    formatter: (val) => val === 0 ? '' : val,
                                },
                            },
                        },
                    }
                },

                // Desktop: vertical bars — labels at bottom
                getDesktopConfig(labels, data, colors, tickColor, gridColor, borderColor) {
                    return {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Jumlah Kasus',
                                data: data,
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
                                        stepSize: this.getStepSize(data),
                                        precision: 0,
                                        color: tickColor,
                                        font: { size: 12, weight: 'bold' },
                                    },
                                    grid: { color: gridColor },
                                    border: { color: borderColor },
                                },
                                x: {
                                    ticks: {
                                        color: tickColor,
                                        font: { size: 11, weight: '600' },
                                        maxRotation: 45,
                                        minRotation: 25,
                                    },
                                    grid: { display: false },
                                    border: { color: borderColor },
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
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    offset: 2,
                                    color: tickColor,
                                    font: { size: 12, weight: 'bold' },
                                    formatter: (val) => val === 0 ? '' : val,
                                },
                            },
                        },
                    }
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

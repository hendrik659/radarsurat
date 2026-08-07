import Chart from 'chart.js/auto';

document.querySelectorAll('[data-dashboard-trend-chart]').forEach((canvas) => {
    const encodedChart = canvas.dataset.chart;

    if (!encodedChart) {
        return;
    }

    let chartData;

    try {
        chartData = JSON.parse(encodedChart);
    } catch {
        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Surat Masuk',
                    data: chartData.incoming,
                    borderColor: '#3182ce',
                    backgroundColor: 'rgba(49, 130, 206, 0.10)',
                    pointBackgroundColor: '#3182ce',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: 'Surat Keluar',
                    data: chartData.outgoing,
                    borderColor: '#dd6b20',
                    backgroundColor: 'rgba(221, 107, 32, 0.05)',
                    pointBackgroundColor: '#dd6b20',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                    },
                },
                tooltip: {
                    displayColors: true,
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.18)',
                    },
                },
            },
        },
    });
});

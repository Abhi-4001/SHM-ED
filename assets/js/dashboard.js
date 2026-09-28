(() => {
    const canvas = document.getElementById('assessmentChart');
    const dataElement = document.getElementById('assessment-chart-data');
    if (!canvas || !dataElement || typeof Chart === 'undefined') {
        return;
    }

    const data = JSON.parse(dataElement.textContent);
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Educational rule points',
                data: data.scores,
                borderColor: '#087f8c',
                backgroundColor: 'rgba(8, 127, 140, 0.12)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Rule points (not a medical score)' } },
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (item) => `${item.parsed.y} educational points` } },
            },
        },
    });
})();

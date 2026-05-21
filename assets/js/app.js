document.querySelectorAll('.js-confirm').forEach((item) => {
  item.addEventListener('click', (event) => {
    if (!confirm('Подтвердить удаление?')) {
      event.preventDefault();
    }
  });
});

document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
  const rows = JSON.parse(canvas.dataset.chart || '[]');
  const labels = rows.map((row) => row.label);
  const values = rows.map((row) => Number(row.total));
  new Chart(canvas, {
    type: canvas.dataset.type || 'bar',
    data: {
      labels,
      datasets: [{
        data: values,
        label: 'Показатель',
        backgroundColor: ['#19c37d', '#ff9f1c', '#2d9cdb', '#ffffff', '#7f8ea3'],
        borderColor: '#111827'
      }]
    },
    options: {
      plugins: { legend: { labels: { color: '#f8fafc' } } },
      scales: {
        x: { ticks: { color: '#cbd5e1' }, grid: { color: '#1f2937' } },
        y: { ticks: { color: '#cbd5e1' }, grid: { color: '#1f2937' } }
      }
    }
  });
});


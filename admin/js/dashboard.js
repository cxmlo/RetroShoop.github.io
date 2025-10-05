// ==================== DASHBOARD CHARTS ====================

document.addEventListener('DOMContentLoaded', function() {
    cargarGraficaVentas();
    cargarGraficaProductos();
});

async function cargarGraficaVentas() {
    try {
        const response = await fetch('api_dashboard.php?action=ventas_mensuales');
        const data = await response.json();
        
        if(data.success) {
            const ctx = document.getElementById('ventasChart');
            if(!ctx) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.meses,
                    datasets: [{
                        label: 'Ventas ($)',
                        data: data.ventas,
                        borderColor: '#000',
                        backgroundColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                font: {
                                    family: "'Courier New', monospace",
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    family: "'Courier New', monospace"
                                },
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: "'Courier New', monospace"
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch(error) {
        console.error('Error cargando gráfica de ventas:', error);
    }
}

async function cargarGraficaProductos() {
    try {
        const response = await fetch('api_dashboard.php?action=productos_mas_vendidos');
        const data = await response.json();
        
        if(data.success) {
            const ctx = document.getElementById('productosChart');
            if(!ctx) return;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.productos,
                    datasets: [{
                        label: 'Cantidad Vendida',
                        data: data.cantidades,
                        backgroundColor: [
                            '#667eea',
                            '#764ba2',
                            '#f093fb',
                            '#4facfe',
                            '#00f2fe'
                        ],
                        borderColor: '#000',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                font: {
                                    family: "'Courier New', monospace",
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    family: "'Courier New', monospace"
                                },
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: "'Courier New', monospace"
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch(error) {
        console.error('Error cargando gráfica de productos:', error);
    }
}
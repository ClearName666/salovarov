
// Основные функции админ-панели

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initAdminPanel();
    initModals();
    initDeleteConfirmations();
    initFormValidations();
    initFilters();
    initCharts();
});

// Инициализация админ-панели
function initAdminPanel() {
    // Активный пункт меню
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.admin-nav a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
        }
    });
    
    // Переключение видимости пароля
    const passwordToggles = document.querySelectorAll('.toggle-password');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });
}

// Модальные окна
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.modal-close, .btn-cancel');
    
    // Открытие модального окна
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const target = this.dataset.modalTarget;
            const modal = document.getElementById(target);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Закрытие модального окна
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Закрытие по клику вне модального окна
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            });
        }
    });
}

// Подтверждение удаления
function initDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-action="delete"]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const message = this.dataset.confirm || 'Вы уверены, что хотите удалить этот элемент?';
            const url = this.href || this.dataset.url;
            const method = this.dataset.method || 'POST';
            
            if (confirm(message)) {
                if (method === 'POST') {
                    // Отправка POST запроса
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    if (csrfToken) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_token';
                        input.value = csrfToken;
                        form.appendChild(input);
                    }
                    
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    // Обычный переход по ссылке
                    window.location.href = url;
                }
            }
        });
    });
}

// Валидация форм
function initFormValidations() {
    const forms = document.querySelectorAll('.admin-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Создаем сообщение об ошибке, если его нет
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.textContent = 'Это поле обязательно для заполнения';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Пожалуйста, заполните все обязательные поля', 'error');
            }
        });
    });
    
    // Удаление сообщений об ошибках при вводе
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.remove();
            }
        });
    });
}

// Фильтры и поиск
function initFilters() {
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.admin-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }, 300));
    }
}

// Графики (упрощенная реализация)
function initCharts() {
    const chartContainers = document.querySelectorAll('.chart-container');
    
    chartContainers.forEach(container => {
        const canvas = container.querySelector('canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            const type = container.dataset.chartType || 'bar';
            const data = JSON.parse(container.dataset.chartData || '{}');
            
            // Простая реализация графика
            if (type === 'bar') {
                drawSimpleBarChart(ctx, data);
            } else if (type === 'line') {
                drawSimpleLineChart(ctx, data);
            }
        }
    });
}

function drawSimpleBarChart(ctx, data) {
    const labels = data.labels || ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн'];
    const values = data.values || [65, 59, 80, 81, 56, 55];
    const colors = data.colors || ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c'];
    
    const maxValue = Math.max(...values);
    const barWidth = 40;
    const spacing = 30;
    const startX = 50;
    const startY = 200;
    
    // Оси
    ctx.beginPath();
    ctx.moveTo(30, 20);
    ctx.lineTo(30, startY);
    ctx.lineTo(400, startY);
    ctx.strokeStyle = '#333';
    ctx.stroke();
    
    // Столбцы
    labels.forEach((label, index) => {
        const x = startX + (barWidth + spacing) * index;
        const height = (values[index] / maxValue) * 150;
        const y = startY - height;
        
        ctx.fillStyle = colors[index % colors.length];
        ctx.fillRect(x, y, barWidth, height);
        
        // Подписи
        ctx.fillStyle = '#333';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(label, x + barWidth / 2, startY + 20);
        ctx.fillText(values[index], x + barWidth / 2, y - 10);
    });
}

function drawSimpleLineChart(ctx, data) {
    // Реализация линейного графика
    // ...
}

// Вспомогательные функции
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button class="notification-close"><i class="fas fa-times"></i></button>
    `;
    
    document.body.appendChild(notification);
    
    // Анимация появления
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Автоматическое закрытие
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
    
    // Закрытие по клику
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });
}

// Экспорт данных
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('th, td');
        
        cells.forEach(cell => {
            // Игнорируем кнопки действий
            if (!cell.closest('.actions')) {
                rowData.push(`"${cell.textContent.trim()}"`);
            }
        });
        
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (navigator.msSaveBlob) {
        navigator.msSaveBlob(blob, filename);
    } else {
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Импорт данных
function importFromCSV(inputId, callback) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const contents = e.target.result;
            if (typeof callback === 'function') {
                callback(contents);
            }
        };
        reader.readAsText(file);
    });
}

// AJAX запросы
function sendAjaxRequest(url, method, data, successCallback, errorCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            const response = JSON.parse(xhr.responseText);
            if (successCallback) successCallback(response);
        } else {
            if (errorCallback) errorCallback(xhr.statusText);
        }
    };
    
    xhr.onerror = function() {
        if (errorCallback) errorCallback('Network error');
    };
    
    const params = new URLSearchParams(data).toString();
    xhr.send(params);
}
// assets/js/booking.js

document.addEventListener('DOMContentLoaded', function() {
    // Маска для телефона
    const phoneInput = document.getElementById('passenger_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value[0] === '8') {
                    value = '7' + value.substring(1);
                }
                if (value[0] !== '7') {
                    value = '7' + value;
                }
                let formatted = '+7 ';
                if (value.length > 1) formatted += '(' + value.substring(1, 4);
                if (value.length > 4) formatted += ') ' + value.substring(4, 7);
                if (value.length > 7) formatted += '-' + value.substring(7, 9);
                if (value.length > 9) formatted += '-' + value.substring(9, 11);
                e.target.value = formatted;
            }
        });
    }
    
    // Валидация формы бронирования
    const bookingForm = document.querySelector('form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('passenger_name');
            const emailInput = document.getElementById('passenger_email');
            const phoneInput = document.getElementById('passenger_phone');
            
            let isValid = true;
            let errorMessage = '';
            
            // Проверка имени
            if (!nameInput.value.trim()) {
                isValid = false;
                errorMessage += 'Введите полное имя.\n';
                nameInput.classList.add('error');
            } else {
                nameInput.classList.remove('error');
            }
            
            // Проверка email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                isValid = false;
                errorMessage += 'Введите корректный email.\n';
                emailInput.classList.add('error');
            } else {
                emailInput.classList.remove('error');
            }
            
            // Проверка телефона
            const phoneRegex = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;
            if (!phoneRegex.test(phoneInput.value)) {
                isValid = false;
                errorMessage += 'Введите телефон в формате +7 (999) 123-45-67.\n';
                phoneInput.classList.add('error');
            } else {
                phoneInput.classList.remove('error');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Пожалуйста, исправьте ошибки:\n\n' + errorMessage);
            }
        });
    }
    
    // Подсветка полей с ошибками
    const inputs = document.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });
    });
});

// Функция подтверждения бронирования
function confirmBooking(flightId, passengers) {
    if (confirm(`Вы действительно хотите забронировать ${passengers} билет(ов)?`)) {
        window.location.href = `booking.php?flight_id=${flightId}&passengers=${passengers}`;
    }
}

// Функция отмены бронирования
function cancelBooking(bookingId) {
    if (confirm('Вы уверены, что хотите отменить это бронирование?')) {
        fetch(`cancel_booking.php?id=${bookingId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Бронирование отменено');
                location.reload();
            } else {
                alert('Ошибка: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при отмене бронирования');
        });
    }
}
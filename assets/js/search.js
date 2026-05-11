function bookFlight(flightId) {
    if (confirm('Вы действительно хотите забронировать этот рейс?')) {
        const passengers = document.querySelector('input[name="passengers"]') ? 
                          document.querySelector('input[name="passengers"]').value : 1;
        window.location.href = 'booking.php?flight_id=' + flightId + '&passengers=' + passengers;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const flightCards = document.querySelectorAll('.flight-card');
    flightCards.forEach((card, index) => {
        card.style.animationDelay = (index * 0.1) + 's';
    });
});
// Обновленный search.js
function bookFlight(flightId) {
    const passengers = document.querySelector('input[name="passengers"]') ? 
                      document.querySelector('input[name="passengers"]').value : 1;
    
    // Получаем количество пассажиров из формы поиска
    const searchPassengers = document.getElementById('passengers') ? 
                            document.getElementById('passengers').value : 1;
    
    const actualPassengers = passengers || searchPassengers || 1;
    
    window.location.href = 'booking.php?flight_id=' + flightId + '&passengers=' + actualPassengers;
}
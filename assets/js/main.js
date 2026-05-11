// Устанавливаем минимальную дату на завтра
const today = new Date();
const tomorrow = new Date(today);
tomorrow.setDate(tomorrow.getDate() + 1);

const departureDate = document.getElementById('departure');
const returnDate = document.getElementById('return');

const formatDate = (date) => {
    return date.toISOString().split('T')[0];
};

if (departureDate) {
    departureDate.min = formatDate(tomorrow);
    returnDate.min = formatDate(tomorrow);
    departureDate.value = formatDate(tomorrow);
    
    departureDate.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const nextDay = new Date(selectedDate);
        nextDay.setDate(nextDay.getDate() + 1);
        returnDate.min = formatDate(nextDay);
        
        if (returnDate.value && new Date(returnDate.value) < nextDay) {
            returnDate.value = formatDate(nextDay);
        }
    });
}
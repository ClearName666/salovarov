<?php include "includes/header.php"; ?>
<link rel="stylesheet" href="assets/css/style.css">
<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="alert alert-success">Ваше сообщение успешно отправлено!</div>
<?php endif; ?>
<div class="contact-section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="contact-info-card">
                    <h3>Наши контакты</h3>
                    <p class="mb-4">Мы всегда готовы помочь вам с выбором билетов и бронированием.</p>
                    
                    <div class="info-item">
                        <span class="icon">📍</span>
                        <div>
                            <h5>Адрес</h5>
                            <p>ул. Авиаторов, д. 12, офис 304</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <span class="icon">📞</span>
                        <div>
                            <h5>Телефон</h5>
                            <p>+7 (999) 123-45-67</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <span class="icon">✉️</span>
                        <div>
                            <h5>Email</h5>
                            <p>support@salovarov-fly.ru</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="feedback-form-card">
                    <h2>Напишите нам</h2>
                    <form action="functions/send_contact.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Имя</label>
                                <input type="text" name="name" class="form-control custom-input" placeholder="Иван Иванов" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control custom-input" placeholder="example@mail.ru" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Тема сообщения</label>
                            <select name="subject" class="form-select custom-input">
                                <option value="booking">Проблемы с бронированием</option>
                                <option value="payment">Вопросы по оплате</option>
                                <option value="refund">Возврат билетов</option>
                                <option value="other">Другое</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Ваше сообщение</label>
                            <textarea name="message" class="form-control custom-input" rows="6" placeholder="Опишите вашу проблему..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-submit">Отправить сообщение</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
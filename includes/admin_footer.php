
<footer class="admin-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Админ панель</h4>
                <p>Система управления авиабилетами</p>
            </div>
            <div class="footer-section">
                <h4>Быстрые ссылки</h4>
                <ul>
                    <li><a href="admin.php?page=dashboard">Дашборд</a></li>
                    <li><a href="admin.php?page=flights">Рейсы</a></li>
                    <li><a href="admin.php?page=bookings">Брони</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Поддержка</h4>
                <ul>
                    <li><a href="#">Документация</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Контакты</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Авиабилеты. Все права защищены.</p>
        </div>
    </div>
</footer>

<!-- Модальные окна -->
<div id="modalAdd" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Добавить новый элемент</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Контент будет загружен динамически -->
        </div>
        <div class="modal-footer">
            <button class="btn-admin btn-cancel">Отмена</button>
            <button class="btn-admin btn-save">Сохранить</button>
        </div>
    </div>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Редактировать элемент</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Контент будет загружен динамически -->
        </div>
        <div class="modal-footer">
            <button class="btn-admin btn-cancel">Отмена</button>
            <button class="btn-admin btn-save">Обновить</button>
        </div>
    </div>
</div>

<div id="modalDelete" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Подтверждение удаления</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Вы уверены, что хотите удалить этот элемент? Это действие нельзя отменить.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-admin btn-cancel">Отмена</button>
            <button class="btn-admin btn-danger btn-confirm-delete">Удалить</button>
        </div>
    </div>
</div>

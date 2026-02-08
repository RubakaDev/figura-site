# Утилиты

Эта директория содержит различные вспомогательные скрипты для проекта Figura Site.

## Генератор хешей паролей

1. Скрипт `generate_password_hash.py` позволяет генерировать bcrypt хеши, совместимые с PHP password_hash().

#### Установка зависимостей:
```bash
pip install bcrypt
```

#### Запустить напрямую с новым пользователем:
```bash
python utils/generate_password_hash.py <user> <pass>
```

#### Запустить без аргументов (использует 'admin123' по умолчанию):
```bash
python utils/generate_password_hash.py
```

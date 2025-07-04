
# 🎥 StreamLoop

**StreamLoop** is a Laravel-based livestream management tool that integrates with TikTok's Live Studio. It enables you to create live channels and stream continuously looping videos via Google Drive. The system also supports sharing live streams with product suggestions for TikTok's shopping cart feature.

---

## 🧰 Technologies Used

- **Laravel 10** – PHP Web Framework
- **Backpack for Laravel** – Admin dashboard & CRUD interface
- **RabbitMQ** – Asynchronous job queue
- **Laravel Queue (RabbitMQ Driver)** – Queue integration
- **Laravel Scheduler** – Scheduled background tasks
- **MySQL** – Relational database
- **TikTok API** – Livestream and stream key integration
- **Webhooks** – Receive and handle TikTok events
- **RTMP + FFmpeg** – Stream video to TikTok using RTMP with looped playback and custom encoding
- **Custom Artisan Commands** – CLI tools for automation

---

## 📂 Core Features

- 🔧 Create livestream channels and link videos from Google Drive
- 🔁 Auto-generate TikTok stream keys using webhooks
- 📡 Loop videos and stream them to TikTok Live via FFmpeg + RTMP
- 🛒 Share livestreams with shopping cart suggestions
- 🖥️ Admin interface to manage channels, stream status, and logs

---

## 🛠️ Setup Instructions

### 1. Clone the repository

```bash
git clone https://github.com/tannew2k/streamloop.git
cd streamloop
```

### 2. Install dependencies

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Configure environment

Update the `.env` file with your database and RabbitMQ settings:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

QUEUE_CONNECTION=rabbitmq
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_QUEUE=default
```

### 4. Run database migrations

```bash
php artisan migrate
```

### 5. Start the Laravel server

```bash
php artisan serve
```

---

## ⚙️ Running Commands

| Task                        | Command                                  |
|----------------------------|------------------------------------------|
| Start queue worker         | `php artisan queue:work`                 |
| Start scheduler            | `php artisan schedule:work`              |
| Sync channels manually     | `php artisan sync:channel`               |
| Create stream key          | `php artisan create:stream-key {id}`     |
| Test webhook endpoint      | `GET /api/webhook/test`                  |

---

## 🐇 RabbitMQ (Optional via Docker)

```bash
docker run -d --hostname rabbit --name rabbitmq \
  -p 5672:5672 -p 15672:15672 \
  rabbitmq:3-management
```

Access RabbitMQ Management UI:  
👉 [http://localhost:15672](http://localhost:15672)  
Username: `guest` | Password: `guest`

---

## 🎛 Admin Panel (Backpack)

Access the admin dashboard at:  
🌐 `http://localhost:8000/admin`

Login using seeded or manually created admin credentials.

---

## 📁 Project Structure

- `app/Http/Controllers/` – API & webhook logic
- `app/Console/Commands/` – Artisan commands (sync, stream key creation)
- `app/Jobs/` – Background job definitions
- `app/Models/` – Eloquent models
- `app/Helpers/` – Utility classes & webhook helpers
- `app/Services/` – TikTok API integration logic
- `routes/web.php` – Backpack admin routes
- `routes/api.php` – Public API and webhook routes

---

## 🤝 Contributing

Feel free to fork the project, open issues, or submit PRs.  
Maintained by [@tannew2k](https://github.com/tannew2k).

---

## 📜 License

This project is licensed under the **MIT License**.

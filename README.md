# 🖥️ Opportunity & Outreach Dashboard

The Dashboard is a modern, high-performance web interface built with **Laravel 11, Livewire 3, and Tailwind CSS**. It provides real-time control, live streaming logs, and visual management for your automated lead discovery and sales outreach pipeline.

---

## 🌟 Dashboard Features

- **Live Engine Action Streamer**: Trigger and watch Python engine operations (`run_discovery`, `run_google_maps_crawler`, `run_intelligence`, `run_scoring`, `run_enrichment`, `clean_duplicates`) with real-time server-sent events (SSE).
- **Quick Source & Priority Filtering**:
  - `📍 Google Maps Leads` — Filter leads discovered directly from Google Local searches.
  - `🔥 No Website (Top Priority)` — Instantly view high-converting prospects needing turnkey web creation ($2.5k–$5k deal size).
  - `🛠️ Yelp / Clutch / GoodFirms` — Explore agency, tech debt, and performance optimization leads.
- **Outreach Queue & Email Staging**: Review, edit, and approve personalized AI cold email pitches before dispatch.
- **Daily Automated Dispatch**: Integrated console command with rate limiting, email delivery scheduling, and duplicate exclusion.

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ & NPM
- MySQL / MariaDB (shared with `signal-engine`)

### 1. Install Dependencies
```bash
cd signal-dashboard
composer install
npm install
npm run build
```

### 2. Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` database and Python engine settings:
```env
APP_NAME="Nexidant Signal"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexidant_signal
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Path to Python Engine
ENGINE_PATH="/absolute/path/to/signal-engine"
PYTHON_BINARY="/absolute/path/to/signal-engine/venv/bin/python"
```

### 3. Run Database Migrations
```bash
php artisan migrate
```

### 4. Serve Locally
```bash
php artisan serve
```
Visit `http://127.0.0.1:8000` in your browser.

---

## 📬 Automated Daily Outreach Dispatch

To dispatch staged cold emails on a daily schedule, configure a cron job in aaPanel / Linux:

```bash
# Add to Crontab (Runs daily at 09:00 AM)
0 9 * * 1-5 cd /www/wwwroot/nexidant-signal/signal-dashboard && php artisan outreach:dispatch-daily --limit=50 >> /dev/null 2>&1
```

Or run manually from the terminal:
```bash
php artisan outreach:dispatch-daily --limit=25
```

---

## 🔒 Security & Best Practices
- Keep `.env` and `storage/*.key` files private and excluded from Git.
- Ensure `storage/` and `bootstrap/cache/` directories have writable permissions (`chmod -R 775 storage bootstrap/cache`).

# CargoTrace — Global Shipment Visibility & Tracking Platform

> **Web Programming 2 • Final Project (UAS)**  
> *A software engineering portfolio project built with Laravel 12, MySQL, REST API, Tailwind CSS, Leaflet.js, and Service Layer architecture.*

---

## 📌 Project Overview
**CargoTrace** is an enterprise-grade shipment visibility and tracking platform designed for logistics operators and international freight customers. It solves the critical challenge of scattered shipment updates, opaque transit histories, and unmanaged port/customs delays by providing:

- **End-to-End Shipment Lifecycle Management**: Controlled state-machine transitions (`CREATED` → `BOOKED` → `PICKED_UP` → `IN_TRANSIT` → `DELIVERED`).
- **Interactive Geospatial Tracking**: Worldwide port-to-port route mapping with Leaflet.js, OpenStreetMap, waypoints, and live vessel coordinates.
- **Deterministic ETA & Delay Engine**: Algorithmic delay detection based on configurable SLA thresholds and mathematical rules.
- **Exception & Disruption Tracking**: Real-time handling of `PORT_CONGESTION`, `CUSTOMS_HOLD`, `WEATHER_DELAY`, and damaged cargo workflows.
- **Role-Based Access Control (RBAC)**: Secure multi-role platform with dedicated permissions for *Administrator*, *Logistics Operator*, and *Customer*.
- **Comprehensive REST API**: Full JSON API adhering to standard status codes and authenticated via Laravel Sanctum.

---

## 🛠️ Tech Stack & Architecture

### Backend & Core
- **Framework**: PHP 8.2+ / Laravel 12.x
- **Database**: MySQL 8.x (with SQLite support for rapid local testing & CI)
- **API Authentication**: Laravel Sanctum (Token-based API security)
- **HTTP Client**: Laravel HTTP Client (for external Weather & Geocoding integrations)
- **Architecture Pattern**: Controller → Service Layer (`ShipmentService`, `TrackingService`, `EtaService`, `ExceptionService`) → Eloquent Models

### Frontend & UI
- **Templating**: Laravel Blade Components
- **Styling**: Tailwind CSS v4
- **Interactivity**: Vanilla JavaScript (Fetch API)
- **Mapping**: Leaflet.js + OpenStreetMap tiles
- **Analytics & Charts**: Chart.js

### Engineering Quality
- **Version Control**: Git & GitHub with structured commit history
- **Testing**: PHPUnit / Pest automated feature & unit tests
- **Security**: Server-side authorization gates/policies, form request validation, mass-assignment protection, CSRF & XSS prevention

---

## 🗄️ Database Design (Core Entities)
The database strictly implements the 9 relational entities required by the course specification:
1. `users` — Authentication credentials & RBAC role (`admin`, `operator`, `customer`).
2. `carriers` — Shipping lines, airlines, and multimodal logistics partners.
3. `locations` — Origin and destination seaports / airports with UN/LOCODE and GPS coordinates.
4. `shipments` — Core cargo shipments, expected arrival dates, and lifecycle states.
5. `shipment_items` — Manifest line items, weight (kg), and volume (CBM).
6. `tracking_events` — Immutable historical milestones, timestamps, and geolocation updates.
7. `shipment_exceptions` — Disruptions, delay impacts (hours), and resolution statuses.
8. `notifications` — In-app alerts for delay updates and milestone transitions.
9. `audit_logs` — Security and operational audit trail tracking user actions.

---

## 🚀 Getting Started (Installation & Setup)

### Prerequisites
- PHP >= 8.2 with `pdo_mysql`, `pdo_sqlite`, `curl`, `mbstring`, `openssl`, and `zip` extensions
- Composer >= 2.x
- Node.js >= 18.x and npm
- MySQL Server 8.x

### 1. Clone & Install Dependencies
```bash
git clone <your-repository-url> cargotrace
cd cargotrace

# Install PHP dependencies
composer install

# Install Node modules & build frontend assets
npm install
npm run build
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database connection:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cargotrace
DB_USERNAME=root
DB_PASSWORD=
```
*(Alternatively, for local development using SQLite: `DB_CONNECTION=sqlite`)*

### 3. Run Migrations & Database Seeders
```bash
php artisan migrate --seed
```

### 4. Start Local Development Server
```bash
php artisan serve
```
Access the application in your browser at `http://127.0.0.1:8000`.

---

## 🧪 Running Automated Tests
Run the automated test suite with PHPUnit:
```bash
php artisan test
```

---

## 👥 Roles & Demo Credentials (Seeders)
| Role | Email | Password | Permissions |
|---|---|---|---|
| **Administrator** | `admin@cargotrace.com` | `password` | Full system access, audit logs, carriers & location master data |
| **Logistics Operator** | `operator@cargotrace.com` | `password` | Manage shipments, dispatch tracking events, handle exceptions |
| **Customer** | `customer@cargotrace.com` | `password` | Track personal consignments, view timeline, inspect route map |

---

## 📄 License & Academic Attribution
Developed as the Final Term Project (UAS) for **Web Programming 2**.  
Course guidelines and design requirements strictly adhere to the UAS Project Brief.

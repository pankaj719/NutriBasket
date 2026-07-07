# 🖥️ NutriBasket Admin Backend & APIs

This is the central Laravel-based administrative backend and API engine for the **NutriBasket** B2B & B2C grocery & food delivery ecosystem.

This service houses the relational database schema, administrative controls, business reporting dashboards, merchant panels, and APIs used by the customer, vendor, packager, and delivery mobile applications.

---

## 🚀 Key Modules & Custom Extensions

In addition to standard multi-vendor systems, this backend has been enhanced with:

1. **📦 B2B Client & Contract Manager**:
   * Setup enterprise clients (such as *Ozora, CAARA, Box8, Yellow Straw*, and *RDB*).
   * Assign default packagers, default deliverymen, and address points.
   * Manage custom contract templates mapped to B2B price indices.
2. **📈 Intelligent Inventory System**:
   * Real-time calculation of items ordered vs. current stock to output **Extra Needed** metrics.
   * Bulk-updating views mapping newest unit costs, notes, and supplier tracking.
   * Automated purchase logging whenever stock quantities are replenished.
3. **⚙️ Custom Packager Assignment Flow**:
   * Database-level assignments using the `packager_id` field in the `orders` table.
   * Dedicated APIs (`/api/v1/auth/packager/*`) to serve logins, profile management, and state transitions (e.g. *picked_up*, *packaging*).
4. **⏱️ Delayed Stock Deduction Logic**:
   * Stock levels remain unaffected through cart placement and order preparation to prevent inventory deadlocks.
   * Deducts quantity directly from stock at the precise moment of order status shifting to `accepted`.

---

## 🛠️ Tech Stack & Requirements

* **PHP Version**: `^8.1`
* **Framework**: Laravel 10/11
* **Database**: MySQL, Redis (caching and queues)
* **Web Server**: Nginx / Apache (`.htaccess` config included)
* **Real-time Notifications**: Firebase Cloud Messaging (FCM)

---

## 📋 Installation and Setup

1. **Install PHP dependencies**:
   ```bash
   composer install
   ```

2. **Configure Environment Variables**:
   Copy `.env.example` and set up database credentials, mail server, Firebase keys, and Module settings:
   ```bash
   cp .env.example .env
   ```

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Symlink Storage**:
   ```bash
   php artisan storage:link
   ```

6. **Serve Locally**:
   ```bash
   php artisan serve
   ```

---

## 🎨 Asset Changes & Reference Logs

### Custom Styles applied to Admin layouts
* `theme.minc619.css` (Removed `.page-header` bottom-border spacing)
* `vendor.min.css` (Adjusted Select2 default container height to `.3125rem` border-radius alignment)

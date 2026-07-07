# 🏪 NutriBasket Vendor Application

This is the vendor/merchant partner mobile application for **NutriBasket**, a multi-vendor B2B and B2C grocery & food delivery ecosystem.

Store owners, managers, and merchants use this application to manage their products, handle incoming orders, print receipts, track store performance, and schedule promotions.

---

## 🚀 Key Features

* **Walk-In Store POS**: Interactive Point of Sale interface for logging local, over-the-counter purchases that instantly update inventory stocks.
* **Thermal Bluetooth Printing**: Instant order receipts printouts using thermal ESC/POS commands (`print_bluetooth_thermal`, `flutter_esc_pos_utils`).
* **Campaign & Promotions Control**: Manage product discount tags, item add-ons/modifiers, and participate in platform-wide marketing campaigns.
* **Business Analytics & Expenses**: Integrated dashboard trackers showing revenue progress, vendor payout logs, subscription levels, and operating expenses.

---

## 🛠️ Getting Started

### Prerequisites

* **Flutter SDK**: `3.27.4`
* **Dart SDK**: Compatible with Flutter SDK `3.27.4` (target environment: `sdk: '>=3.2.0 <4.0.0'`)

### Setup and Running

1. **Get dependencies**:
   ```bash
   flutter pub get
   ```

2. **Run the application**:
   ```bash
   # Run in debug mode
   flutter run

   # Build Android release APK
   flutter build apk --release
   ```

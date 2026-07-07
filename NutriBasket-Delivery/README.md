# 🛵 NutriBasket Delivery Application

This is the delivery agent/rider companion mobile application for **NutriBasket**, a multi-vendor B2B and B2C grocery & food delivery ecosystem.

Delivery personnel use this application to receive dispatch orders, navigate optimal routes to target destinations, manage Cash on Delivery (COD) transactions, and update order statuses in real-time.

---

## 🚀 Key Features

* **Route Navigation**: Maps out location nodes using `google_maps_flutter` and geocoding services to assist riders.
* **COD & Earnings Log**: Digital wallets tracking driver payments, pending collections, and payout requests.
* **Instant Chat**: Integrated messaging lines allowing riders to coordinate immediately with store vendors or customers.
* **Real-Time Dispatching**: Instant push notifications via Firebase Cloud Messaging (FCM) when a new order is ready for pickup.

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

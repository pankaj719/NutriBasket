# 🛒 NutriBasket Customer Application

This is the customer storefront mobile application for **NutriBasket**, a multi-vendor B2B and B2C grocery & food delivery ecosystem. 

Customers and B2B corporate ordering centers use this application to browse items, place orders, make secure payments, and track their active deliveries in real-time.

---

## 🚀 Key Features

* **B2B & B2C Ordering**: Integrates both retail grocery/food purchases and corporate B2B client contracts.
* **Order Modifications & NA Item Alerts**: Displays clear warnings if packaging staff marks items as **"NA" (Not Available)** or modifies quantities, showing reason logs and subtotal calculations.
* **GetX State Management**: High-speed, responsive screens with a reactive state.
* **Secure Checkout**: Multi-payment gateway integrations (Stripe, Razorpay, Paytm, etc.) along with customer wallets and loyalty point conversions.
* **Live Route Tracking**: Pinpoint delivery addresses and track active riders on an interactive map.

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

2. **Generate model adapter classes** (if applicable):
   ```bash
   flutter pub run build_runner build --delete-conflicting-outputs
   ```

3. **Run the application**:
   ```bash
   # Run in debug mode
   flutter run

   # Build Android release APK
   flutter build apk --release
   ```

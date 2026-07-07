# 📦 NutriBasket Packaging Application

This is the packaging staff companion mobile application for **NutriBasket**, a multi-vendor B2B and B2C grocery & food delivery ecosystem.

Fulfillment center workers and packagers use this application to view order assignments, pick and verify items, mark unavailable items (as "NA"), and compile B2B invoices.

---

## 🚀 Key Features

* **Order Queue Dashboard**: Track items categorized by status: *Assigned*, *In Packaging*, and *Picked Up*.
* **Availability Adjuster**: Edit quantities or mark items **"NA" (Not Available)** if inventory levels are out of stock, automatically recalculating order invoices and updating customer notifications.
* **PDF Invoice Drawer**: Directly fetches and downloads custom professional B2B client invoices (`Order_[ID]_Invoice.pdf`) for corporate partners.
* **Transition Workflows**: Handover ready packages to delivery agents, updating the central Laravel backend database status instantly.

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

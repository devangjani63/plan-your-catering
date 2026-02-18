# Plan Your Catering – WordPress Estimate Engine

A modular, logic-driven WordPress plugin designed for catering businesses to generate dynamic event estimates based on guest count, menu selection, weight scale, and configurable add-ons.

This plugin operates as a fully independent estimation system — not theme dependent and not an e-commerce solution.

---

## 🚀 Core Capabilities

### 🔢 Dynamic Estimate Engine
- Guest-based pricing
- Item weight scale (Simple / Medium / Heavy)
- Add-ons (Per Guest / Flat pricing)
- Real-time total calculation
- Structured estimate breakdown

### 📂 Menu Architecture
- Custom Menu Sections (Veg, Jain, Beverages, etc.)
- Menu Items linked to sections
- Weight-based pricing logic
- Scalable for 800+ items

### ➕ Add-on Management
- Custom Add-ons CPT
- Pricing type:
  - Per Guest
  - Flat Rate
- Enable / Disable toggle
- Fully admin-controlled (no hardcoding)

### 📩 Enquiry System
- Stores enquiries as CPT
- Status tracking
- Admin email notifications
- Structured submission records

---

## 🧠 Architecture Overview

Menu Section  
→ Menu Items (with weight)  
→ Add-ons (price type)  
→ Estimate Calculation  
→ Enquiry Storage  

---

## 🧾 Shortcode Usage

[plan_your_catering]


Renders:
- Multi-step estimation form
- Menu selection
- Add-on selection
- Estimate calculation
- Enquiry submission

---

## ⚙️ Technical Highlights

- Modular class-based architecture
- Shortcode-driven frontend rendering
- Custom Post Types
- Meta fields management
- Business logic separated from theme
- CSV-ready data structure
- Scalable structure

---

## 📦 Folder Structure

plan-your-catering/
│
├── plan-your-catering.php
├── includes/
├── assets/
│   ├── css/
│   └── js/

---

## 🚫 Not an E-Commerce Plugin

This plugin:
- Does NOT process payments
- Does NOT include cart functionality
- Does NOT expose pricing publicly

It is an estimate and enquiry generation engine only.

---

## 🛠 Requirements

- WordPress 6.0+
- PHP 7.4+
- No external dependencies

---

## 📌 Roadmap

- Drag & drop admin ordering
- CSV import UI
- AJAX estimate preview
- PDF estimate export
- CRM integration

---

## 👨‍💻 Author

Devang Jani  
WordPress Developer | Performance Marketing Specialist

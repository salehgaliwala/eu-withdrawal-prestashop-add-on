# EU Withdrawal Button

**Version:** 1.0.0  
**Author:** Jules  
**License:** Commercial License (Proprietary)  
**Compatibility:** PrestaShop 8.0.0 – 9.9.9  

---

## Description

The **EU Withdrawal Button** module provides compliance with **EU Directive 2023/2673** by enabling customers to submit withdrawal (cancellation/return) requests for their orders directly from your PrestaShop store.

This module adds a withdrawal request form accessible from the customer account page, order detail page, and via a footer link for guest tracking — ensuring your store meets European consumer right-to-withdraw requirements.

---

## Features

- **EU Directive 2023/2673 Compliance** — Provides the required withdrawal mechanism for online purchases.
- **Full Order or Partial Withdrawal** — Customers can withdraw an entire order or select specific line items.
- **Custom Order Status** — Automatically creates and assigns a *"Withdrawal Requested"* order status.
- **PDF Receipt** — Auto-generates a withdrawal receipt PDF upon form submission.
- **Email Notifications** — Sends acknowledgment to the customer and notification to store administrators, both with the PDF receipt attached.
- **Back-Office Management** — Dedicated admin panel under *Orders → Withdrawal Requests* for viewing, managing, and exporting withdrawal data.
- **Guest Tracking Support** — A footer link allows guests to submit withdrawal requests without logging in.
- **IP & User-Agent Logging** — Records the submitter's IP address and user agent for audit purposes.
- **CSV Export** — Supports CSV export of withdrawal requests from the back office.

---

## Requirements

- PrestaShop **8.0.0** or higher
- PHP 7.4+
- MySQL / MariaDB

---

## Installation

### Via PrestaShop Back Office

1. Download the module ZIP archive.
2. Go to **Modules → Module Manager** in your PrestaShop back office.
3. Click **Upload a module** and select the ZIP file.
4. Find **EU Withdrawal Button** in the module list and click **Install**.
5. The module will automatically:
   - Create the `euwithdrawal_requests` database table.
   - Create the *"Withdrawal Requested"* order status.
   - Register the admin tab under *Orders*.
   - Register the front-office hooks.

### Manual Installation

1. Extract the ZIP archive into your PrestaShop `/modules/` directory.
2. The module folder should be named `euwithdrawalbutton`.
3. Go to **Modules → Module Manager**, find **EU Withdrawal Button**, and click **Install**.

---

## Usage

### Customer Experience (Front Office)

The module adds the withdrawal option in three locations:

1. **Customer Account Page** — A *"Withdrawal Request"* link is added to the customer account dashboard for logged-in users.
2. **Order Detail Page** — A withdrawal button appears on individual order detail pages, automatically passing the order reference.
3. **Footer (Guest Access)** — A withdrawal link in the footer allows non-logged-in users (guests) to submit a request.

#### Withdrawal Form

The form requires:

- **Customer Name** — Full name of the person submitting the request.
- **Order Reference / Number** — The order identifier (auto-filled when accessed from an order page).
- **Email** — For receipt confirmation (auto-filled for logged-in customers).
- **Withdrawal Type** — Choose between:
  - *Withdraw the entire order*
  - *Withdraw specific line items* (provides item name, number, and quantity fields)

Upon successful submission, the customer receives a confirmation message and an email with the withdrawal receipt PDF attachment.

### Back Office (Administration)

The module adds a **Withdrawal Requests** menu item under the **Orders** menu.

#### Managing Requests

- **View List** — See all withdrawal requests with columns for ID, Order Reference, Customer, Email, Type, and Date.
- **View Details** — Click the *View* action to see full request details including submitted line items.
- **Export PDF** — Export an individual request as a PDF receipt.
- **Export CSV** — Use the built-in export button to download all requests as a CSV file.

---

## Hooks

The module registers the following hooks:

| Hook | Location | Purpose |
|------|----------|---------|
| `displayCustomerAccount` | Customer account page | Adds "Withdrawal Request" link |
| `displayOrderDetail` | Order detail page | Adds withdrawal button with pre-filled order reference |
| `displayFooter` | Site footer | Provides guest withdrawal access link |

---

## Email Notifications

Two email notifications are sent when a withdrawal request is submitted:

1. **Customer Confirmation** — Sent to the customer's email address with the subject *"Withdrawal Request Acknowledgment"* including a PDF receipt.
2. **Admin Notification** — Sent to the shop's email address (`PS_SHOP_EMAIL`) with the subject *"New Withdrawal Request Received"* including a PDF receipt.

Email templates are located in `/mails/en/`.

---

## PDF Receipt

A **Withdrawal Receipt** PDF is automatically generated upon request submission, containing:

- Customer name and email
- Order reference
- Request type (full or partial)
- Submitted items and quantities
- Date of request
- Shop logo and details

The PDF filename follows the pattern: `withdrawal_receipt_[ORDER_REFERENCE].pdf`

---

## Database

The module creates a single table:

### `ps_euwithdrawal_requests` (prefix may vary)

| Column | Type | Description |
|--------|------|-------------|
| `id_euwithdrawal_request` | INT (11) AUTO_INCREMENT | Primary key |
| `id_order` | INT (11) | PrestaShop order ID |
| `id_customer` | INT (11) | PrestaShop customer ID |
| `customer_name` | VARCHAR(255) | Submitter's full name |
| `order_reference` | VARCHAR(64) | Order reference number |
| `email` | VARCHAR(255) | Submitter's email |
| `request_type` | VARCHAR(32) | `entire_order` or `line_items` |
| `items_data` | TEXT | JSON-encoded item data |
| `ip_address` | VARCHAR(255) | Submitter's IP address |
| `user_agent` | VARCHAR(1024) | Submitter's browser user agent |
| `date_add` | DATETIME | Submission timestamp |

---

## Changelog

### v1.0.0 (2024)

- Initial release
- Front-office withdrawal request form with full/partial order support
- Custom "Withdrawal Requested" order status
- PDF receipt generation
- Email notifications (customer + admin)
- Back-office management interface with export capabilities
- IP and user-agent logging

---

## License

This module is **commercial software**. Use of this module is permitted only to customers who have purchased a valid license.

Unauthorized copying, distribution, or use of this module without a valid license is strictly prohibited.

For licensing inquiries, please contact the module author.

---

## Support

For issues, feature requests, or licensing inquiries, please contact the module author.

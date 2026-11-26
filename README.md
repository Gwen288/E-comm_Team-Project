# CrownCare — Natural Hair Care E-Commerce Platform

CrownCare is an e-commerce web application focused on natural hair-care products for African and textured hair. Customers can browse products, manage carts, place orders, get rule-based product recommendations, and book hair consultations. Admins manage the shop, while consultants manage consultation services, availability, and appointments.

## Features

### Customer Features
- **User Authentication:** Register, log in, and manage customer accounts.
- **Product Catalogue:** Browse hair-care products by category and brand.
- **Shopping Cart:** Add products, update quantities, and manage cart items.
- **Checkout & Payments:** Confirm delivery details and pay securely through Paystack (test mode).
- **Order Management:** Place orders and view order-related information.
- **Hair Quiz:** Answer questions about hair type, concerns, goals, scalp, preferences, and budget.
- **Hair Profiles:** Save and update hair characteristics and preferences.
- **Product Recommendations:** Get an explainable routine based on rule-based matching of quiz answers to products, current stock, and budget.

### Admin Features
- Manage products, categories, product sizes, prices, and inventory.
- Review customers and orders, update order statuses, and view sales and catalogue analytics.
- See low-stock products on the admin dashboard.

### Consultant Features
- Manage consultation services, prices, weekly hours, and date-specific availability.
- Review and filter upcoming and past appointments, including client details and appointment history.
- Add private consultant notes and update appointment statuses 
- View consultation and revenue analytics, and send reminder emails for next-day appointments.


### Recommendation System
CrownCare uses a structured, rule-based recommendation approach; it is not a machine-learning model. The quiz answers are scored against coded product tags and routine steps. The system uses live catalogue prices and stock, respects the customer's budget when including optional routine steps, and provides a short explanation for each recommendation. Saved hair-profile attributes support customer profiles and admin insights; the recommendation run uses the answers from the current quiz session.

## Technology Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Payments:** Paystack 
- **Email:** PHPMailer over SMTP for order and consultation messages
- **Browser libraries:** Chart.js for analytics charts and SweetAlert2 for dialogs
- **Local Development Environment:** XAMPP (Apache and MySQL)
- **Database Management:** phpMyAdmin
- **Version Control:** Git and GitHub

## Database Design

The database builds on a relational e-commerce structure and includes tables for customers, products, brands, categories, carts, orders, and payments.

Additional tables support the recommendation feature:

| Table | Purpose |
|---|---|
| `hair_attributes` | Stores hair textures, porosities, concerns, goals, and preferences. |
| `hair_profiles` | Stores each customer's saved hair profile. |
| `hair_profile_attributes` | Connects saved profiles to their selected attributes. |
| `product_hair_attributes` | Connects products to relevant hair attributes; the admin analytics page reports mapping coverage. |

The database uses primary keys, foreign keys, and mapping tables to maintain relationships and reduce unnecessary data duplication.

## Getting Started

### Prerequisites

Install the following:
- XAMPP
- A web browser
- A code editor, such as Visual Studio Code

### Installation

1. **Clone or download the repository.**

   ```bash
   git clone <repository-url>
   ```

2. **Move the project into the XAMPP web directory.**

   Place the repository folder inside `htdocs`, for example:

   ```text
   C:\xampp\htdocs\E-comm_Team-Project
   ```

   The application itself is in the `crowncare` subfolder.

3. **Start Apache and MySQL.**

   Open the XAMPP Control Panel and start both services.

4. **Create the database.**

   Open phpMyAdmin at:

   `http://localhost/phpmyadmin`

   Create the `crowncare` database, or import the project's SQL schema that creates it.

   The `crowncare/database/` folder is excluded from Git. Get the current schema from the copy shared by the team, or export it from an existing local installation.

5. **Import the database schema.**

   Import the project's SQL schema through phpMyAdmin. If the script contains `CREATE DATABASE` and `USE` statements, execute the complete script as intended.

   **Warning:** The clean-slate schema drops existing tables before recreating them. Back up any data you need before importing it.

6. **Configure the database connection.**

   Copy `crowncare/core/db_cred_example.php` to `crowncare/core/db_cred.php`, then update it with your local MySQL host, database name, username, and password. The `db_cred.php` file is ignored by Git, so each team member can keep their own credentials.

   Typical local settings are:

   ```text
   Host: localhost
   Database: crowncare
   Username: root
   Password: (your local MySQL password)
   ```

   These are example local settings; use the credentials configured on your machine.

7. **Configure Paystack test mode.**

   Copy `crowncare/core/paystack_config_example.php` to `crowncare/core/paystack_config.php`.

   In the Paystack dashboard, switch to **Test Mode** and copy the test keys from **Settings → API Keys & Webhooks**:

   - `PAYSTACK_PUBLIC_KEY` — `pk_test_...`
   - `PAYSTACK_SECRET_KEY` — `sk_test_...`

   The `paystack_config.php` file is ignored by Git, so keys are not committed. The checkout page shows a warning until valid test keys are added.

   The callback URL is built from `BASE_URL` and points to `actions/verify_payment_action.php`, so make sure `BASE_URL` matches your local project URL.

   Test card: `4084 0840 8408 4081`, any future expiry date, CVV `408`.

8. **Configure email (PHPMailer).**

   Copy `crowncare/core/mail_config_example.php` to `crowncare/core/mail_config.php`. Set the SMTP host, port, account, and password for the mailbox CrownCare will use. For Gmail, use an app password rather than the account's normal password. The `mail_config.php` file is ignored by Git.

   PHPMailer connects to the configured SMTP server and sends email on the application's behalf. If email is not configured, orders and bookings can still be saved, but messages will not be delivered; email errors are written to the PHP error log. For local testing, set `MAIL_DEV_LOG` in `mail_config.php` to write email contents to a local file instead of sending them.

9. **Open the application.**

   Visit the appropriate project URL in your browser, for example:

   `http://localhost/E-comm_Team-Project/crowncare/`

   Adjust the URL if the application's entry point or folder structure differs. The `BASE_URL` constant in `crowncare/core/core.php` must match the folder path used by your web server.

## Initial Setup

After importing the database, populate it with the data required to use the application:

1. Create an administrator account using the application's registration process.
2. Promote that account to administrator using the project's configured role system.
3. Create or promote a consultant account using the consultant role (`user_role = 3`).
4. Add product categories and products.
5. Configure consultation services, prices, and availability in the consultant portal.
6. Populate `hair_attributes` with the available hair textures, concerns, goals, and preferences.
7. Assign product-to-hair-attribute mappings if they are needed for catalogue insights.

The rule-based recommendation engine uses its coded product tags and requires matching products to exist in the catalogue and be in stock.

## Project Structure

The application follows the existing PHP project structure. Depending on the files included in the repository, the main directories may include:

- `actions/` — Processes application actions and form submissions.
- `classes/` — Contains reusable PHP classes.
- `controllers/` — Handles application logic and request processing.
- `core/` — Contains shared configuration and core functionality.
- `css/` — Stylesheets.
- `js/` — JavaScript files.
- `lib/PHPMailer/` — PHPMailer library files used for SMTP email delivery.
- `views/` — User-facing pages and interfaces.

Refer to the repository's actual directory structure for the definitive file organization.

## Security Considerations

- Passwords should be stored using secure password hashing.
- Database queries involving user input should use prepared statements.
- Protected pages and administrative actions should enforce authentication and authorization.
- Cart operations must be scoped to the authenticated customer's ID.
- Database credentials and Paystack secret keys should not be committed to a public repository.
- Paystack payments must be verified server-side, and the verified amount and currency must match the order total.

## Future Improvements

Potential enhancements include expanding the product catalogue, refining recommendation rules, improving the shopping experience, and hardening the payment flow for production with Paystack webhooks and live keys.

## Academic Project

CrownCare is an e-commerce development project exploring online retail functionality and personalized product discovery for natural hair care.

---

**CrownCare — Find products that fit your hair-care needs.**
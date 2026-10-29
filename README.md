# CrownCare — Natural Hair Care E-Commerce Platform

CrownCare is an e-commerce web application focused on natural hair-care products for African and textured hair. The platform enables customers to browse products, manage their shopping carts, place orders, and discover products suited to their hair characteristics and needs through an automated recommendation feature.

## Features

### Customer Features
- **User Authentication:** Register, log in, and manage customer accounts.
- **Product Catalogue:** Browse hair-care products by category and brand.
- **Shopping Cart:** Add products, update quantities, and manage cart items.
- **Order Management:** Place orders and view order-related information.
- **Hair Profiles:** Save and update hair characteristics and preferences.
- **Personalized Recommendations:** Receive product suggestions based on saved hair-profile attributes and the current shopping goal.

### Admin Features
- Manage product brands and categories.
- Add and manage products in the catalogue.
- Manage administrative access through user roles.

### Recommendation System
CrownCare uses a structured, rule-based recommendation approach. Customers can maintain a saved hair profile while selecting a different shopping goal for an individual visit. The system uses this information alongside product attributes to identify relevant products.

## Technology Stack

- **Frontend:** HTML, CSS, JavaScript, Bootstrap (where used)
- **Backend:** PHP
- **Database:** MySQL
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
| `product_hair_attributes` | Connects products to relevant hair attributes. |

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

   Place the project folder inside `htdocs`, for example:

   ```text
   C:\xampp\htdocs\afrocare
   ```

3. **Start Apache and MySQL.**

   Open the XAMPP Control Panel and start both services.

4. **Create the database.**

   Open phpMyAdmin at:

   `http://localhost/phpmyadmin`

   Create the `crown_care_shoppn` database, or import the provided SQL script that creates it.

5. **Import the database schema.**

   Import the project's SQL schema through phpMyAdmin. If the script contains `CREATE DATABASE` and `USE` statements, execute the complete script as intended.

   **Warning:** The clean-slate schema drops existing tables before recreating them. Back up any data you need before importing it.

6. **Configure the database connection.**

   Update the project's existing database configuration file with your local MySQL host, database name, username, and password.

   Typical local settings are:

   ```text
   Host: localhost
   Database: crowncare_shoppn
   Username: root
   Password: (your local MySQL password)
   ```

   These are example local settings; use the credentials configured on your machine.

7. **Open the application.**

   Visit the appropriate project URL in your browser, for example:

   `http://localhost/crowncare/`

   Adjust the URL if the application's entry point or folder structure differs.

## Initial Setup

After importing the database, populate it with the data required to use the application:

1. Create an administrator account using the application's registration process.
2. Promote that account to administrator using the project's configured role system.
3. Add product brands and categories.
4. Add products to the catalogue.
5. Populate `hair_attributes` with the available hair textures, concerns, goals, and preferences.
6. Assign appropriate hair attributes to products using `product_hair_attributes`.

Product recommendations will require products to have appropriate attribute mappings.

## Project Structure

The application follows the existing PHP project structure. Depending on the files included in the repository, the main directories may include:

- `actions/` — Processes application actions and form submissions.
- `classes/` — Contains reusable PHP classes.
- `controllers/` — Handles application logic and request processing.
- `core/` — Contains shared configuration and core functionality.
- `css/` — Stylesheets.
- `js/` — JavaScript files.
- `views/` — User-facing pages and interfaces.

Refer to the repository's actual directory structure for the definitive file organization.

## Security Considerations

- Passwords should be stored using secure password hashing.
- Database queries involving user input should use prepared statements.
- Protected pages and administrative actions should enforce authentication and authorization.
- Cart operations must be scoped to the authenticated customer's ID.
- Database credentials should not be committed to a public repository.

## Future Improvements

Potential enhancements include expanding the product catalogue, refining recommendation rules, improving the shopping experience, and integrating a payment gateway.

## Academic Project

CrownCare is an e-commerce development project exploring online retail functionality and personalized product discovery for natural hair care.

---

**CrownCare — Find products that fit your hair-care needs.**
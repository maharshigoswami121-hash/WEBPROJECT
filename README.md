#Channel Merchant Web Store

Simple PHP/MySQL storefront for browsing products, managing a cart, and checking out with order persistence.

## Project Description
- Product catalog with categories, ratings, and detail pages.
- Shopping cart stored client-side (localStorage) with server-side order capture.
- Auth flows for users and admins; admin dashboard to add/edit products and view orders.
- Responsive UI built with Bootstrap and custom CSS.

## Setup Instructions
**Environment tooling (already applied):**
- Ran `npm init` and installed `bootstrap` + `sass` (`npm install bootstrap sass`).
- Added `.gitignore` to exclude `node_modules`.
- Created base folders: `js`, `css`, `scss`, `db`, `includes`.
- Added SASS sources: `scss/bootstrap.scss` (`@import "bootstrap/scss/bootstrap";`) and `scss/style.scss` (empty starter).
- Added `index.php` at project root and `js/bootstrap.bundle.min.js`.
- `package.json` script `sass-builder`: `sass --no-source-map scss:css` (compiles `bootstrap.scss` and `style.scss` to `css/`).

**Run locally:**
1. **Requirements:** PHP 8+, MySQL/MariaDB, and a web server stack such as XAMPP.
2. **Database:**
   - Create a database named `webproject_db` (or update `Database/db.php` with your credentials).
   - Ensure required tables exist: `users`, `products`, `orders`, `order_items`, `cart`, `reviews`.
3. **Environment:**
   - Place the project in your web root (e.g., `htdocs/Webproject` for XAMPP).
   - Start Apache and MySQL from your stack controller.
4. **Run:**
   - Visit `http://localhost/index.php` in your browser.
   - Use the UI to register/login and browse products; admins can log in to manage catalog and orders.

## Author
- Name: Maharshi Mukeshpuri Goswami 
- Student ID: 259698870

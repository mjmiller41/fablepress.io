# FablePress.io: A Simplified, Content-First CMS

FablePress.io is a simplified, elegant, and distraction-free Content Management System (CMS) designed specifically to eliminate the bloat, complexity, and security headaches of WordPress. It is tailored for independent writers, creators, and small brands who care about typography, clean presentation, and seamless content workflows.

The system is built on a **PHP 8.x + SQLite/MySQL (Option A)** architecture, optimized for immediate, high-performance deployment on a **Hostinger Business Shared Hosting** plan.

---

## 🚀 Getting Started (Local Development)

FablePress.io requires **zero initial database configuration** when using SQLite mode. The database is initialized and seeded automatically on your first visit.

### 1. Launch the PHP Server
Navigate to the FablePress directory in your terminal and start PHP's built-in web server:
```bash
cd /home/michael/Code/Projects/FablePress
php -S localhost:8000 router.php
```

### 2. Access the Application
*   **Public Landing Page**: [http://localhost:8000](http://localhost:8000)
*   **Admin Dashboard**: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## 🔑 Default Credentials & Role Matrix

To test the Role-Based Access Control (RBAC) system (Screen C), log in using one of the pre-seeded users:

| Username | Password | Access Level | Description |
| :--- | :--- | :--- | :--- |
| **`developer`** | `developer123` | **High** (Developer) | Full system access. Can modify themes, toggle permissions, manage users, and deploy updates. |
| **`manager`** | `manager123` | **Medium** (Content Manager) | Full content access. Can create, edit, delete, and publish any page/story, and manage the navigation menu. |
| **`writer`** | `writer123` | **Limited** (Contributor) | Restricted access. Can draft articles and upload media, but cannot publish directly (must save as draft). |

---

## 🛠️ Project Structure

The project has been organized according to standard modular practices:

```
FablePress/
├── admin/                 # Admin Dashboard Pages
│   ├── admin.css          # Admin Premium CSS Stylesheet
│   ├── footer.php         # Admin Shared Footer layout
│   ├── header.php         # Admin Shared Sidebar Layout & RBAC enforcement
│   ├── index.php          # Admin Main Dashboard Stats
│   ├── login.php          # Secure Admin Session Authentication
│   ├── logout.php         # Session Destroyer
│   ├── media.php          # Drag-and-drop Media Uploader & Library
│   ├── navigation.php     # Interactive Navigation Menu Hierarchy Editor
│   ├── roles.php          # RBAC Roles Configuration Dashboard
│   ├── stories.php        # Stories List & Metadata View
│   └── story-edit.php     # Centered, Distraction-Free Page/Story Editor
├── uploads/               # Directory where uploaded images/files are stored
├── config.php             # Core Database connection, schema migrations, and helpers
├── index.php              # Public site router and dynamic templates renderer
├── style.css              # Premium Public stylesheet (Serif typography & Cream tone)
└── README.md              # Project documentation
```

---

## 🏗️ Architecture & Operations

FablePress.io employs a decoupling strategy to deliver high performance, minimal hosting overhead, and robust security.

### 1. Jekyll-Hybrid Static Generator (`StaticGenerator.php`)
FablePress.io implements a **Dynamic Admin, Static Public** hybrid architecture. 
- **The Concept**: While content creation, role management, and media uploads are handled dynamically in the password-protected Admin Panel via a PHP database connection, the public-facing site is entirely static.
- **Compilation Trigger**: Whenever a page or story is published, edited, or deleted, or when the navigation menu is updated, the admin panel calls `StaticGenerator.php`.
- **Generated Outputs**:
  - Public Home: compiles to `/index.html`.
  - Stories Directory: compiles to `/stories/index.html`.
  - Pages/Stories: compile to their respective clean directories as `/{slug}/index.html`.
- **Zero Database Overhead**: Because the public site serves pre-compiled, static HTML files directly, public-facing queries are reduced to zero. This configuration guarantees **sub-50ms page load times** and reduces server resource usage to a fraction of traditional dynamic CMS platforms.

### 2. Clean URLs & Slim Routing
To support seamless URL structures, FablePress.io routes requests through a unified front-controller pattern when assets do not exist physically.
- **Local Development (`router.php`)**: When using PHP's built-in web server, `router.php` intercepts incoming requests, serves static files directly if they exist (such as CSS or uploads), and routes dynamic paths (like `/admin/*` or preview links) to the Slim application inside `index.php`.
- **Production Server (`.htaccess`)**: On Hostinger Shared Hosting, the `.htaccess` configuration handles Apache URL rewriting. It ensures that requests to static compiled files (e.g. `/welcome-to-fablepress/index.html`) are served instantly by the web server, while dynamic requests (like the Admin panel) are seamlessly rewritten to `index.php`.

### 3. Production Deployment via `.env`
FablePress uses a dynamic environment loading mechanism to allow the same codebase to run locally (usually on SQLite) and in production (usually on MySQL).
- **Environment Loading**: `config.php` automatically parses the `.env` file in the root directory and loads environment variables.
- **Path A Deployment (No Composer Hooks)**: Hostinger Shared Hosting lacks post-deployment Composer hooks. To solve this, **commit the `vendor/` directory directly to GitHub** and push it to production. This ensures all dependencies (including Slim framework and PDO drivers) are immediately available upon git checkout or deployment.
- **Production `.env` Example**:
  Create a `.env` file in the root of your Hostinger installation:
  ```env
  # FablePress Environment Configuration
  DB_MODE=mysql

  # MySQL Database Configuration (Hostinger hPanel)
  DB_HOST=127.0.0.1
  DB_NAME=u123456789_fablepress
  DB_USER=u123456789_admin
  DB_PASS=YourSecureProductionPasswordHere
  ```

### 4. Database Migration Script (`migrate.php`)
Moving from local development (SQLite) to production (MySQL) is streamlined using a built-in migration utility.
- **Prerequisites**:
  1. Set up a MySQL database on Hostinger hPanel.
  2. Create the `.env` file on the server with `DB_MODE=mysql` and your MySQL credentials.
  3. Upload your local SQLite database file `fablepress.db` to the root directory of the Hostinger server.
- **Execution Steps**:
  1. Access the migration utility by navigating to `https://yourdomain.com/migrate.php` in your browser.
  2. The page will verify that `fablepress.db` is present and that the current active mode is `mysql`.
  3. Review the database target details and click **Start MySQL Migration**.
  4. The script truncates target MySQL tables (`users`, `posts`, `media`, `navigation`, `role_permissions`) and inserts the rows from the SQLite file.
- **⚠️ Critical Security Requirement**:
  - Once the migration succeeds, the script **automatically deletes** the source `fablepress.db` file to prevent exposing raw database records.
  - **You MUST manually delete `migrate.php`** from the Hostinger server via hPanel File Manager or FTP immediately. Keeping this file on a live server presents a high security risk of unauthorized database wipes.

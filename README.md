# HR-Database-Manager


A professional-grade backend solution for managing organizational data, focusing on relational database integrity and server-side logic. This system allows for the creation, management, and tracking of employees and their corresponding positions within a company.

## 🚀 Key Features
* **Relational Schema Design**: Implements a structured database with foreign key relationships between `employees` and `positions` tables.
* **SQL Command History**: A custom-built utility to execute and log SQL queries, featuring a session-based history log for rapid command re-execution.
* **Dynamic Data Entry**: Server-side forms for adding new company positions and employees with real-time dropdown population based on existing database records.
* **Complex Data Joins**: Demonstrates advanced SQL querying by joining multiple tables to provide a unified view of employee salaries and roles.

## 🛠 Technical Implementation
* **Backend (PHP)**: Utilizes **PDO (PHP Data Objects)** for secure database interactions and prepared statements to prevent SQL injection.
* **Database (SQLite)**: Optimized relational schema designed to maintain data normalization.
* **UI/UX Logic**: JavaScript-enhanced command execution that allows users to select and re-run historical queries with a single click.

## 📂 Project Structure
* `connect.php`: The database initializer—sets up the schema and establishes the connection.
* `insert.php`: The primary management interface for adding personnel and organizational data.
* `queries1.php`: An advanced SQL developer tool featuring execution logs and command history.

## 💻 Setup & Installation
1. Clone the repository to your local PHP environment (e.g., XAMPP, WAMP, or MAMP).
2. Update the `$databasePath` variable in the `.php` files to match your local directory.
3. Run `connect.php` to automatically generate the database and table structure.
4. Access `insert.php` to begin populating your HR system.



<?php
$databasePath = '/Users/arpine/Documents/my_database.db'; // Update with your path

try {
    $pdo = new PDO("sqlite:$databasePath");
    echo "Connected successfully to the SQLite database!<br>";

    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS positions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        position_name TEXT NOT NULL,
        salary REAL NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        position_id INTEGER,
        FOREIGN KEY (position_id) REFERENCES positions(id)
    )");

    // Insert sample data if needed
    // Uncomment the following lines if you want to add data every time
    /*
    $pdo->exec("INSERT INTO positions (position_name, salary) VALUES 
                ('Director', 120000), 
                ('Web Developer', 75000), 
                ('Engineer', 85000)");

    $pdo->exec("INSERT INTO employees (first_name, last_name, position_id) VALUES 
                ('Emma', 'Sadyan', 2),  
                ('John', 'Doe', 1),   
                ('Alice', 'Smith', 3)");
    
*/
    // Query to fetch employees with their positions and salaries
    $result = $pdo->query("
        SELECT employees.first_name, employees.last_name, positions.position_name, positions.salary
        FROM employees
        JOIN positions ON employees.position_id = positions.id
    ");
    
    // Display results
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['first_name'] . ' ' . $row['last_name'] . ' - Position: ' . $row['position_name'] . ' - Salary: $' . number_format($row['salary'], 2) . "<br>";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

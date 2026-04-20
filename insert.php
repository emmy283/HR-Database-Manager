<?php
$databasePath = '/Users/arpine/Documents/my_database.db'; // Update with your path

try {
    // Create (or open) the SQLite database connection
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

    // Handle new position submission
    if (isset($_POST['add_position'])) {
        $position_name = $_POST['position_name'];
        $salary = $_POST['salary'];

        // Insert new position into the database
        $stmt = $pdo->prepare("INSERT INTO positions (position_name, salary) VALUES (:position_name, :salary)");
        $stmt->execute([':position_name' => $position_name, ':salary' => $salary]);

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit; // Important to stop further execution
    }

    // Handle new employee submission
    if (isset($_POST['add_employee'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $position_id = $_POST['position_id'];

        // Check if the position_id exists in the positions table
        $stmt = $pdo->prepare("SELECT id FROM positions WHERE id = :position_id");
        $stmt->execute([':position_id' => $position_id]);
        $position = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($position) {
            // If the position exists, insert the new employee
            $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, position_id) VALUES (:first_name, :last_name, :position_id)");
            $stmt->execute([':first_name' => $first_name, ':last_name' => $last_name, ':position_id' => $position_id]);

            // Redirect to avoid form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit; // Important to stop further execution
        } else {
            // If the position_id does not exist, show an error
            echo "Error: Position ID $position_id does not exist. Please provide a valid Position ID.<br>";
        }
    }

    // Query to fetch employees with their positions and salaries
    $result = $pdo->query("
        SELECT employees.first_name, employees.last_name, positions.position_name, positions.salary
        FROM employees
        JOIN positions ON employees.position_id = positions.id
    ");
    
    // Prepare output for the text box
    $employeeData = "";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $employeeData .= $row['first_name'] . ' ' . $row['last_name'] . ' - Position: ' . $row['position_name'] . ' - Salary: $' . number_format($row['salary'], 2) . "\n";
    }

    // Fetch all positions for the dropdown in the form
    $positions = $pdo->query("SELECT id, position_name FROM positions")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!-- HTML Form to Add a New Position -->
<h2>Add New Position</h2>
<form method="POST">
    <label for="position_name">Position Name:</label>
    <input type="text" id="position_name" name="position_name" required><br><br>
    <label for="salary">Salary:</label>
    <input type="number" id="salary" name="salary" step="0.01" required><br><br>
    <input type="submit" name="add_position" value="Add Position">
</form>

<!-- HTML Form to Add a New Employee -->
<h2>Add New Employee</h2>
<form method="POST">
    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" required><br><br>
    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" required><br><br>
    <label for="position_id">Position:</label>
    <select id="position_id" name="position_id" required>
        <option value="">Select a position</option>
        <?php
        // Dynamically generate the dropdown options for positions
        foreach ($positions as $position) {
            echo "<option value='{$position['id']}'>{$position['position_name']} (ID: {$position['id']})</option>";
        }
        ?>
    </select><br><br>
    <input type="submit" name="add_employee" value="Add Employee">
</form>

<!-- Display the employee data in a fixed-size text box -->
<h3>Employee List</h3>
<textarea readonly style="width: 400px; height: 200px;">
<?php echo $employeeData; ?>
</textarea>

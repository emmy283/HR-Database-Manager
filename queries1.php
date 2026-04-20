<?php
session_start(); // Start a session to store the history of commands

$databasePath = '/Users/arpine/Documents/my_database.db'; // Update with your actual path

try {
    // Create (or open) the SQLite database connection
    $pdo = new PDO("sqlite:$databasePath");

    // Create the log table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        command TEXT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Handle DDL/DML Command submission
    $ddlCommand = '';
    $errorMessage = ''; // Initialize error message variable
    if (isset($_POST['execute_ddl'])) {
        $ddlCommand = trim($_POST['ddl_command']);  // Get the command from the textarea

        try {
            // Check if the command is a SELECT query
            if (stripos($ddlCommand, 'SELECT') === 0) {
                // It's a SELECT query, we use query() to execute and fetch results
                $result = $pdo->query($ddlCommand);
                if ($result) {
                    // Fetch and display results
                    $resultTable = "<h2>Query executed successfully:</h2>";
                    $resultTable .= "<div class='result-box'><table border='1' cellpadding='5'><tr>";

                    // Fetch the column names from the first row
                    $columns = array_keys($result->fetch(PDO::FETCH_ASSOC));
                    foreach ($columns as $column) {
                        $resultTable .= "<th>$column</th>";
                    }
                    $resultTable .= "</tr>";

                    // Reset the cursor to fetch rows again
                    $result->execute(); // Re-execute to get rows again

                    // Fetch each row and display it
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        $resultTable .= "<tr>";
                        foreach ($row as $value) {
                            $resultTable .= "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        $resultTable .= "</tr>";
                    }
                    $resultTable .= "</table></div>";
                } else {
                    $resultTable = "No results found.<br>";
                }
            } else {
                // It's not a SELECT query, use exec() for other DDL/DML commands
                $pdo->exec($ddlCommand);
                $resultTable = "<h2>Command executed successfully:</h2>";
            }

            // Insert the command into the log table
            $stmt = $pdo->prepare("INSERT INTO log (command) VALUES (:command)");
            $stmt->execute([':command' => $ddlCommand]);

        } catch (PDOException $e) {
            // Catch any errors and set the error message
            $errorMessage = "Error executing command: " . $e->getMessage();
            $resultTable = ''; // Clear result table if there is an error
        }
    }

    // Retrieve the command history from the log table
    $stmt = $pdo->query("SELECT * FROM log ORDER BY id DESC");
    $commandHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Command Page</title>

    <style>
        /* General page styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            box-sizing: border-box;
        }

        .container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-grow: 1;
        }

        /* Command form aligned horizontally */
        .command-form {
            display: flex;
            align-items: flex-start; /* Align textarea and button at the top */
            gap: 10px; /* Spacing between textarea and button */
        }

        /* Left part for the textarea */
        .command-area {
            flex: 1;  /* Takes up 50% of the width */
        }

        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            resize: vertical;
            flex: 1; /* Ensures textarea takes available space */
        }

        /* Styling for Execute Command button */
        input[type="submit"] {
            padding: 10px 20px; /* Add padding for a better look */
            cursor: pointer;
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            margin:110px 30px 0px 30px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Styling for result area */
        .result-box {
            max-width: 100%;
            height: 250px;
            overflow: auto;
            padding: 10px;
            box-sizing: border-box;
            resize: vertical;
            display: flex;
            flex-direction: column;
        }

        /* Command History Box */
        .history-box {
            max-height: 200px;
            overflow-y: auto;
        }

        .history-box h2 {
            position: sticky;
            top: 0;
            background-color: #fff;
            padding: 10px;
            z-index: 1;
            border-bottom: 2px solid #ddd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>

    <!-- Main container for the page -->
    <div class="container">
        <!-- Command Area -->
        <div class="command-area">
            <h2>Write your SQL command:</h2>
            <form method="POST" class="command-form">
                <textarea id="ddl_command" name="ddl_command" rows="5" cols="50" placeholder="Enter your SQL command here..." required>
                    <?php echo htmlspecialchars($ddlCommand); ?>
                </textarea>
                <input type="submit" name="execute_ddl" value="Execute Command">
            </form>
        </div>
    </div>

    <!-- Result Area -->
    <div class="result-box">
        <div class="result-message">
            <?php
                if (!empty($resultTable)) {
                    echo $resultTable;
                }
                if (!empty($errorMessage)) {
                    echo "<div style='color: red;'>$errorMessage</div>";
                }
            ?>
        </div>
    </div>

    <!-- Command History -->
    <h2>SQL Command History</h2>
    <div class="history-box">
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Command</th>
                    <th>Timestamp</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($commandHistory as $entry) {
                        echo "<tr>
                                <td>" . htmlspecialchars($entry['command']) . "</td>
                                <td>" . $entry['timestamp'] . "</td>
                                <td><button onclick=\"populateCommand('" . addslashes(htmlspecialchars($entry['command'])) . "')\">Execute</button></td>
                              </tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function populateCommand(command) {
            document.getElementById('ddl_command').value = command;
        }
    </script>

</body>
</html>

<?php
session_start();
include(__DIR__ . '/../db_connection.php');

$keywords = '';
$results = [];
$resultsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $resultsPerPage;
$totalResults = 0;

$user_name = strtoupper($_SESSION['user_name']); // Retrieve from session after login

if ($_SERVER['REQUEST_METHOD'] == 'POST' || (isset($_GET['keywords']) && !empty($_GET['keywords']))) {
    $keywords = $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST['keywords'] : $_GET['keywords'];
    // Split keywords by comma and trim whitespace
    $keywordsArray = array_map('trim', explode(',', $keywords));
    // Remove empty elements
    $keywordsArray = array_filter($keywordsArray);

    if (count($keywordsArray) > 5) {
        $error = "Please enter up to 5 keywords only.";
    } else {
        // Build the query using LIKE
        $conditions = [];
        $params = [];
        $types = '';

        foreach ($keywordsArray as $keyword) {
            $conditions[] = "english_translation LIKE ?";
            $params[] = "%$keyword%";
            $types .= 's';
        }

        // Count total results for pagination
        $countQuery = "SELECT COUNT(*) as total FROM quran WHERE " . implode(" OR ", $conditions);
        try {
            $countStmt = $conn->prepare($countQuery);
            if ($params) {
                $countStmt->bind_param($types, ...$params);
            }
            $countStmt->execute();
            $totalResults = $countStmt->get_result()->fetch_assoc()['total'];
            $countStmt->close();

            // Main query with pagination
            $query = "SELECT surah, ayat, text, english_translation 
                     FROM quran 
                     WHERE " . implode(" OR ", $conditions) . "
                     LIMIT ? OFFSET ?";
            
            $stmt = $conn->prepare($query);
            
            // Add pagination parameters
            $types .= 'ii';
            $params[] = $resultsPerPage;
            $params[] = $offset;
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                // Highlight keywords in English translation
                $highlightedTranslation = $row['english_translation'];
                foreach ($keywordsArray as $keyword) {
                    $highlightedTranslation = preg_replace(
                        '/(' . preg_quote($keyword, '/') . ')/i',
                        '<span class="highlight">$1</span>',
                        $highlightedTranslation
                    );
                }
                $row['highlighted_translation'] = $highlightedTranslation;
                $results[] = $row;
            }

            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $error = "An error occurred while searching. Error: " . $e->getMessage();
        }
    }
}

$totalPages = ceil($totalResults / $resultsPerPage);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Islamic Explorer</title>
    <link rel="stylesheet" href="../islamicsearch/islamicsearchstyles.css">

</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">
            <button class="open-btn" onclick="toggleSidebar()">☰</button> <!-- Sidebar toggle button -->
            QalamiQuest
        </div>
        <div class="navbar-right">
            <i class="fas fa-bell bell-icon"></i> <!-- Bell icon -->
            <span><?php echo $user_name; ?></span> <!-- Display logged in user's name -->
            <i class="fas fa-user"></i> <!-- Profile icon -->
        </div>
    </div>

    <div class="sidebar" id="sidebar">
        <a href="/../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#"><i class="fas fa-users"></i> Lecturer/Supervisor</a>
        <a href="#"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a> <!-- Updated Logout link -->
    </div>

        <!-- JavaScript to toggle sidebar -->
        <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");

            // Check if the sidebar is currently open or closed
            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-300px"; // Close the sidebar
                mainContent.style.marginLeft = "0"; // Reset the main content margin
            } else {
                sidebar.style.left = "0"; // Open the sidebar
                mainContent.style.marginLeft = "240px"; // Shift the main content
            }
        }
        </script>

    <div class="container">
        <h1>ISLAMIC EXPLORER</h1>
        <p>Search for keywords in the Quran<br>(Maximum 5 keywords, separate with commas)</p>
        
        <form action="" method="POST" onsubmit="return validateKeywords()">
            <div class="search-container">
                <input type="text" 
                       id="keywords"
                       name="keywords" 
                       class="search-input"
                       placeholder="Example: peace, mercy, blessing" 
                       value="<?php echo htmlspecialchars($keywords); ?>" 
                       oninput="updateKeywordCount()"
                       required>
                <div class="keyword-tags" id="keywordTags"></div>
                <div class="keyword-count" id="keywordCount">0 keywords (maximum 5)</div>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <button type="submit">Search</button>
            </div>
        </form>

        <div class="results">
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $result): ?>
                    <div class="ayat">
                        <strong>Surah <?php echo $result['surah']; ?>, Ayat <?php echo $result['ayat']; ?></strong>
                        <div class="arabic-text"><?php echo $result['text']; ?></div>
                        <div class="translation"><?php echo $result['highlighted_translation']; ?></div>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?keywords=<?php echo urlencode($keywords); ?>&page=<?php echo ($currentPage - 1); ?>">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?keywords=<?php echo urlencode($keywords); ?>&page=<?php echo $i; ?>"
                               class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?keywords=<?php echo urlencode($keywords); ?>&page=<?php echo ($currentPage + 1); ?>">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php elseif ($keywords): ?>
                <p>No results found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateKeywordCount() {
            const input = document.getElementById('keywords');
            const keywordTags = document.getElementById('keywordTags');
            const keywordCount = document.getElementById('keywordCount');
            
            // Split by comma and filter out empty strings
            const keywords = input.value
                .split(',')
                .map(k => k.trim())
                .filter(k => k.length > 0);
            
            // Update keyword count
            keywordCount.textContent = `${keywords.length} keywords (maximum 5)`;
            
            // Update keyword tags
            keywordTags.innerHTML = keywords
                .map(keyword => `<div class="keyword-tag">${keyword}</div>`)
                .join('');
            
            // Update count color based on limit
            if (keywords.length > 5) {
                keywordCount.style.color = '#dc2626';
            } else {
                keywordCount.style.color = '#666';
            }
        }

        function validateKeywords() {
            const input = document.getElementById('keywords');
            const keywords = input.value
                .split(',')
                .map(k => k.trim())
                .filter(k => k.length > 0);
            
            if (keywords.length > 5) {
                alert('Please enter no more than 5 keywords.');
                return false;
            }
            return true;
        }

        // Initial update on page load
        updateKeywordCount();
    </script>
</body>
</html>

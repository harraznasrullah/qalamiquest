<?php
session_start();
include(__DIR__ . '/../db_connection.php');

$results = [];
$resultsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $resultsPerPage;
$totalResults = 0;
$searchType = isset($_POST['search_type']) ? $_POST['search_type'] : (isset($_SESSION['search_type']) ? $_SESSION['search_type'] : 'quran');

// Store the selected type in the session
$_SESSION['search_type'] = $searchType;

$user_name = strtoupper($_SESSION['user_name']);
$keywordsArray = []; // Initialize keywords array

// Keyword Handling Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['keywords'])) {
        $keywords = $_POST['keywords'];
        $keywordsArray = array_map('trim', explode(',', $keywords));
        $keywordsArray = array_filter($keywordsArray);
        unset($_SESSION['file_keywords_array']); // Clear file keywords
        $_SESSION['keywords_array'] = $keywordsArray;
    }

    if (isset($_FILES['keywords_file']) && $_FILES['keywords_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['keywords_file']['tmp_name'];
        $fileContent = file_get_contents($fileTmpPath);
        $fileKeywords = extractKeywordsFromFile($fileContent);
        $keywordsArray = array_map('trim', explode(',', $fileKeywords));
        $keywordsArray = array_filter($keywordsArray);
        $_SESSION['file_keywords_array'] = $keywordsArray;
        unset($_SESSION['keywords_array']); // Clear regular keywords
    }
} elseif (isset($_SESSION['file_keywords_array'])) {
    $keywordsArray = $_SESSION['file_keywords_array'];
} elseif (isset($_SESSION['keywords_array'])) {
    $keywordsArray = $_SESSION['keywords_array'];
}

// Database Query
if (count($keywordsArray) > 5) {
    $error = "Please enter up to 5 keywords only.";
} else {
    if (!empty($keywordsArray)) {
        $conditions = [];
        $params = [];
        $types = '';

        foreach ($keywordsArray as $keyword) {
            $conditions[] = "english_translation LIKE ?";
            $params[] = "%$keyword%";
            $types .= 's';
        }

        $tableName = ($searchType === 'hadith') ? 'hadith' : 'quran'; // Determine the table to query

        $whereClause = "WHERE " . implode(" OR ", $conditions);

        $countQuery = "SELECT COUNT(*) as total FROM $tableName " . $whereClause;
        try {
            $countStmt = $conn->prepare($countQuery);
            $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $totalResults = $countStmt->get_result()->fetch_assoc()['total'];
            $countStmt->close();

            $query = "SELECT * FROM $tableName " . $whereClause . " LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($query);
            $types .= 'ii';
            $params[] = $resultsPerPage;
            $params[] = $offset;
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $highlightedTranslation = $row['english_translation'];
                foreach ($keywordsArray as $keyword) {
                    $highlightedTranslation = preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<span class="highlight">$1</span>', $highlightedTranslation);
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

function extractKeywordsFromFile($content) {
    // Define stopwords to remove
    $stopwords = [
        'the', 'and', 'of', 'to', 'a', 'in', 'that', 'with', 'for', 'on', 'as', 'was', 'at', 'by', 
        'is', 'an', 'be', 'this', 'which', 'are', 'it', 'from', 'or', 'has', 'have', 'had', 'not', 
        'but', 'can', 'do', 'does', 'did', 'will', 'would', 'shall', 'should', 'may', 'might', 
        'must', 'could', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'them', 'me', 'him', 'her', 'us',
        'our'
    ];

    // Remove punctuation, convert to lowercase, and split into words
    $words = preg_split('/\s+/', strtolower(preg_replace('/[^\w\s]/', '', $content)));

    // Remove stopwords
    $filteredWords = array_filter($words, function($word) use ($stopwords) {
        return !in_array($word, $stopwords);
    });

    // Count word frequencies
    $wordCounts = array_count_values($filteredWords);

    // Sort by frequency (descending)
    arsort($wordCounts);

    // Extract top 5 frequent words
    $topWords = array_slice(array_keys($wordCounts), 0, 5);

    return implode(',', $topWords);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Islamic Explorer</title>
    <link rel="stylesheet" href="islamicsearchstyles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
    .bookmark-btn {
        margin-top: 15px;
    }
</style>
<body>
<!-- Navbar -->
<div class="navbar">
    <div class="navbar-left">
        <button class="open-btn" onclick="toggleSidebar()">☰</button> <!-- Sidebar toggle button -->
        QalamiQuest
    </div>
    <div class="navbar-right">
        <i class="fas fa-bell bell-icon"></i> <!-- Bell icon -->
        <span><?php echo strtoupper($_SESSION['user_name']); ?></span> <!-- Display logged in user's name -->
        <i class="fas fa-user"></i> <!-- Profile icon -->
    </div>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="../islamicsearch/bookmark/view_bookmarks.php"><i class="fas fa-bookmark"></i> Bookmark</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- JavaScript to toggle sidebar -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");

    if (sidebar.style.left === "0px") {
        sidebar.style.left = "-300px";
        mainContent.style.marginLeft = "0";
    } else {
        sidebar.style.left = "0";
        mainContent.style.marginLeft = "240px";
    }
}
</script>

<div id="main-content">
    <div class="container">
        <h1>ISLAMIC EXPLORER</h1>
        <p>Search for keywords in the Quran<br>(Maximum 5 keywords, separate with commas)</p>
        
        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateKeywords()">
            <div class="search-container">
                <input type="text" id="keywords" name="keywords" class="search-input" placeholder="Example: peace, mercy, blessing" value="<?php echo isset($_SESSION['keywords_array']) ? htmlspecialchars(implode(",",$_SESSION['keywords_array'])) : ''; ?>" oninput="updateKeywordCount()">
                <input type="file" name="keywords_file" accept=".txt">
                <div class="keyword-tags" id="keywordTags"></div>
                <div class="keyword-count" id="keywordCount">0 keywords (maximum 5)</div>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                 <!-- Radio Buttons for Search Type -->
                <form method="POST" action="">
                    <label>
                        <input type="radio" name="search_type" value="quran" <?php echo $searchType === 'quran' ? 'checked' : ''; ?>>
                        Quran
                    </label>
                    <label>
                        <input type="radio" name="search_type" value="hadith" <?php echo $searchType === 'hadith' ? 'checked' : ''; ?>>
                        Hadith
                    </label>
                </form>
                <button class="search" type="submit">Search</button>
            </div>
        </form>
        
        <div class="results">
    <?php
    // Function to highlight keywords in the text
    function highlight_keywords($text, $keywords) {
        foreach ($keywords as $keyword) {
            // Use a regular expression to find whole words (case-insensitive)
            $text = preg_replace('/\b(' . preg_quote($keyword, '/') . ')\b/i', '<span class="highlight">$1</span>', $text);
        }
        return $text;
    }

    // Get the search keywords (from GET or POST request)
    $keywords = isset($_GET['keywords']) ? explode(',', $_GET['keywords']) : []; // Adjust based on your input method
    ?>

    <?php if (!empty($results)): ?>
        <p>Found a total of <?php echo $totalResults; ?> results.</p>
        <?php foreach ($results as $result): ?>
            <div>
                <?php if ($searchType === 'quran'): ?>
                    <strong>Surah <?php echo $result['surah'] ?? 'N/A'; ?>, Ayat <?php echo $result['ayat'] ?? 'N/A'; ?></strong><br>
                    <?php 
                    // Highlight keywords in the Quran text
                    $highlightedText = highlight_keywords($result['text'] ?? '', $keywords);
                    echo $highlightedText; 
                    ?><br>
                    <p><strong>English Translation:</strong> <?php echo highlight_keywords($result['english_translation'] ?? 'N/A', $keywords); ?></p>
                <?php elseif ($searchType === 'hadith'): ?>
                    <strong>Hadith Reference: <?php echo $result['reference'] ?? 'N/A'; ?></strong><br>
                    <p><strong>Arabic Text:</strong> <?php echo highlight_keywords($result['arabic_text'] ?? 'N/A', $keywords); ?></p>
                    <p><strong>English Translation:</strong> <?php echo highlight_keywords($result['english_translation'] ?? 'N/A', $keywords); ?></p>
                <?php endif; ?>
                <button class="bookmark-btn" 
                        data-surah="<?php echo $result['surah'] ?? ''; ?>" 
                        data-ayat="<?php echo $result['ayat'] ?? ''; ?>" 
                        data-text="<?php echo htmlspecialchars($result['text'] ?? '', ENT_QUOTES); ?>" 
                        data-translation="<?php echo htmlspecialchars($result['english_translation'] ?? '', ENT_QUOTES); ?>">
                    Bookmark
                </button>
            </div>
            <hr>
        <?php endforeach; ?>

        <?php
        $paginationKeywords = implode(",", $keywordsArray); // Crucial for file uploads
        if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);

                if ($currentPage > 1): ?>
                    <a href="?keywords=<?php echo urlencode($paginationKeywords); ?>&page=<?php echo ($currentPage - 1); ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?keywords=<?php echo urlencode($paginationKeywords); ?>&page=<?php echo $i; ?>" class="<?php echo ($i === $currentPage) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?keywords=<?php echo urlencode($paginationKeywords); ?>&page=<?php echo ($currentPage + 1); ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>No results found for the given keywords.</p>
    <?php endif; ?>
</div>

<script>
function updateKeywordCount() {
    const keywordsInput = document.getElementById('keywords');
    const keywordTags = document.getElementById('keywordTags');
    const keywordCount = document.getElementById('keywordCount');
    
    const keywords = keywordsInput.value.split(',').map(kw => kw.trim()).filter(kw => kw);
    keywordTags.innerHTML = keywords.map(kw => `<span class="keyword-tag">${kw}</span>`).join('');
    keywordCount.textContent = `${keywords.length} keyword(s) (maximum 5)`;
}
function validateKeywords() {
    const keywordsInput = document.getElementById('keywords');
    const keywords = keywordsInput.value.split(',').map(kw => kw.trim()).filter(kw => kw);
    if (keywords.length > 5) {
        alert('Please enter up to 5 keywords only.');
        return false;
    }
    return true;
}
// Update this part in your JavaScript code
document.addEventListener('DOMContentLoaded', function() {
            const bookmarkButtons = document.querySelectorAll('.bookmark-btn');
            bookmarkButtons.forEach(button => {
                button.addEventListener('click', async function() {
                    const surah = this.dataset.surah;
                    const ayat = this.dataset.ayat;
                    const text = this.dataset.text;
                    const translation = this.dataset.translation;
                    
                    try {
                        const formData = new FormData();
                        formData.append('surah', surah);
                        formData.append('ayat', ayat);
                        formData.append('text', text);
                        formData.append('translation', translation);
                        
                        const response = await fetch('./bookmark/bookmarks.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);
                        
                        const responseText = await response.text(); // First, get raw text
                        console.log('Raw response:', responseText);
                        
                        try {
                            const data = JSON.parse(responseText); // Then try to parse
                            if (data.success) {
                                alert('Bookmark saved successfully!');
                            } else {
                                alert(data.message || 'Failed to save bookmark');
                            }
                        } catch (parseError) {
                            console.error('JSON Parse Error:', parseError);
                            console.error('Unparseable response:', responseText);
                            alert('Server returned an invalid response');
                        }
                    } catch (error) {
                        console.error('Fetch Error:', error);
                        alert('An error occurred while saving the bookmark');
                    }
                });
            });
        });

</script>

</body>
</html>

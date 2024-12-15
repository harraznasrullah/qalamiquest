<?php
session_start();
include(__DIR__ . '/../db_connection.php');

$keywords = '';
$fileKeywords = '';
$results = [];
$resultsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $resultsPerPage;
$totalResults = 0;

$user_name = strtoupper($_SESSION['user_name']); // Retrieve from session after login

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle keywords from input
    if (!empty($_POST['keywords'])) {
        $keywords = $_POST['keywords'];
    }

    // Handle file upload
    if (isset($_FILES['keywords_file']) && $_FILES['keywords_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['keywords_file']['tmp_name'];
        $fileContent = file_get_contents($fileTmpPath);

        // Extract keywords from file
        $fileKeywords = extractKeywordsFromFile($fileContent); // Use helper function
    }

    // Combine keywords from input and file
    $allKeywords = $keywords . ',' . $fileKeywords;
    $keywordsArray = array_map('trim', explode(',', $allKeywords));
    $keywordsArray = array_filter($keywordsArray); // Remove empty elements

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

    // Extract only nouns and adjectives (basic approach using regex for demonstration purposes)
    $nounsAndAdjectives = array_filter($topWords, function($word) {
        return preg_match('/\b\w{3,}\b/', $word); // Simple rule: words with at least 3 characters
    });

    return implode(',', $nounsAndAdjectives);
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
                <!-- Text Input for Keywords -->
                <input type="text" 
                       id="keywords"
                       name="keywords" 
                       class="search-input"
                       placeholder="Example: peace, mercy, blessing" 
                       value="<?php echo htmlspecialchars($keywords); ?>" 
                       oninput="updateKeywordCount()">
                <!-- File Upload Input -->
                <input type="file" name="keywords_file" accept=".txt">
                <div class="keyword-tags" id="keywordTags"></div>
                <div class="keyword-count" id="keywordCount">0 keywords (maximum 5)</div>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <button class="search" type="submit">Search</button>
            </div>
        </form>
        
        <div class="results">
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $result): ?>
                    <div class="ayat">
                        <strong>Surah <?php echo $result['surah']; ?>, Ayat <?php echo $result['ayat']; ?></strong>
                        <div class="arabic-text"><?php echo $result['text']; ?></div>
                        <div class="translation"><?php echo $result['highlighted_translation']; ?></div>
                        <button class="bookmark-btn" data-surah="<?php echo $result['surah']; ?>" data-ayat="<?php echo $result['ayat']; ?>" data-text="<?php echo htmlspecialchars($result['text']); ?>" data-translation="<?php echo htmlspecialchars($result['english_translation']); ?>">Bookmark</button>
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
</div>

<script>
// Real-time keyword count update
function updateKeywordCount() {
    const input = document.getElementById('keywords');
    const keywordTags = document.getElementById('keywordTags');
    const keywordCount = document.getElementById('keywordCount');
    const keywords = input.value.split(',').map(k => k.trim()).filter(k => k.length > 0);
    keywordCount.textContent = `${keywords.length} keywords (maximum 5)`;
    keywordTags.innerHTML = keywords.map(keyword => `<div class="keyword-tag">${keyword}</div>`).join('');
    keywordCount.style.color = keywords.length > 5 ? '#dc2626' : '#666';
}

function validateKeywords() {
    const input = document.getElementById('keywords');
    const keywords = input.value.split(',').map(k => k.trim()).filter(k => k.length > 0);
    if (keywords.length > 5) {
        alert('Please enter no more than 5 keywords.');
        return false;
    }
    return true;
}

updateKeywordCount();

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

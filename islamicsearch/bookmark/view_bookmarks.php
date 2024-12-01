<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookmarks</title>
    <link rel="stylesheet" href="../islamicsearchstyles.css">
    <style>
        .bookmark-date {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .delete-btn {
            background-color: #dc2626;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: #b91c1c;
        }
        .ayat {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-left">
            <button class="open-btn" onclick="toggleSidebar()">☰</button>
            QalamiQuest
        </div>
        <div class="navbar-right">
            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="/../student_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="#"><i class="fas fa-users"></i> Lecturer/Supervisor</a>
        <a href="bookmarks.php" class="active"><i class="fas fa-bookmark"></i> Bookmark</a>
        <a href="edit_profile.php"><i class="fas fa-user"></i> Edit Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="container">
        <h1>My Bookmarks</h1>
        <div id="bookmarks-container"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', async function() {
        try {
            const response = await fetch('./bookmarks.php');
            const data = await response.json();
            
            if (data.success) {
                const container = document.getElementById('bookmarks-container');
                
                if (data.data.length === 0) {
                    container.innerHTML = '<p>No bookmarks found.</p>';
                    return;
                }
                
                data.data.forEach(bookmark => {
                    const date = new Date(bookmark.created_at);
                    const formattedDate = date.toLocaleString();
                    
                    const bookmarkElement = document.createElement('div');
                    bookmarkElement.className = 'ayat';
                    bookmarkElement.innerHTML = `
                        <strong>Surah ${bookmark.surah}, Ayat ${bookmark.ayat}</strong>
                        <div class="arabic-text">${bookmark.text}</div>
                        <div class="translation">${bookmark.english_translation}</div>
                        <div class="bookmark-date">Saved on: ${formattedDate}</div>
                        <button class="delete-btn" data-id="${bookmark.id}">Delete Bookmark</button>
                    `;
                    container.appendChild(bookmarkElement);
                });
                
                // Add delete functionality
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', async function() {
                        const bookmarkId = this.dataset.id;
                        if (confirm('Are you sure you want to delete this bookmark?')) {
                            try {
                                const response = await fetch('bookmarks.php', {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({ id: bookmarkId })
                                });
                                const result = await response.json();
                                if (result.success) {
                                    this.closest('.ayat').remove();
                                    if (document.querySelectorAll('.ayat').length === 0) {
                                        document.getElementById('bookmarks-container').innerHTML = '<p>No bookmarks found.</p>';
                                    }
                                } else {
                                    alert('Failed to delete bookmark');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('An error occurred while deleting the bookmark');
                            }
                        }
                    });
                });
            } else {
                alert('Failed to load bookmarks: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while loading bookmarks');
        }
    });

    // Sidebar toggle function
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.querySelector(".container");
        
        if (sidebar.style.left === "0px") {
            sidebar.style.left = "-300px";
            mainContent.style.marginLeft = "0";
        } else {
            sidebar.style.left = "0";
            mainContent.style.marginLeft = "240px";
        }
    }
    async function loadBookmarks() {
    try {
        const container = document.getElementById('bookmarks-container');
        const response = await fetch('./bookmarks.php');
        
        // Add this debug code
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        // Try parsing it manually to see where it fails
        try {
            const data = JSON.parse(responseText);
            
            if (data.success) {
                container.innerHTML = ''; // Clear loading message
                
                if (data.data.length === 0) {
                    container.innerHTML = '<p>No bookmarks found.</p>';
                    return;
                }
                // ... rest of your code
            }
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            showError('Invalid response from server');
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        showError('Failed to load bookmarks');
    }
}
    </script>
</body>
</html>
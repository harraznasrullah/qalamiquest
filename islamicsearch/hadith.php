<?php
// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'qalamiquest';

// Connect to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Array of Al-Nawawi's Forty Hadith
$hadiths = [
    [
        'arabic_text' => 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى.',
        'english_translation' => 'Actions are but by intentions, and every man shall have only that which he intended.',
        'reference' => 'Al-Bukhari and Muslim'
    ],
    [
        'arabic_text' => 'الدِّينُ النَّصِيحَةُ.',
        'english_translation' => 'Religion is sincere advice.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'مِنْ حُسْنِ إِسْلَامِ الْمَرْءِ تَرْكُهُ مَا لَا يَعْنِيهِ.',
        'english_translation' => 'Part of the perfection of one’s Islam is his leaving that which does not concern him.',
        'reference' => 'At-Tirmidhi'
    ],
    [
        'arabic_text' => 'لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ.',
        'english_translation' => 'None of you [truly] believes until he loves for his brother that which he loves for himself.',
        'reference' => 'Al-Bukhari and Muslim'
    ],
    [
        'arabic_text' => 'مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ.',
        'english_translation' => 'Whoever believes in Allah and the Last Day, let him speak good or remain silent.',
        'reference' => 'Al-Bukhari and Muslim'
    ],
    [
        'arabic_text' => 'لَا ضَرَرَ وَلَا ضِرَارَ.',
        'english_translation' => 'There should be neither harming nor reciprocating harm.',
        'reference' => 'Ibn Majah and others'
    ],
    [
        'arabic_text' => 'إِنَّ اللَّهَ كَتَبَ الإِحْسَانَ عَلَى كُلِّ شَيْءٍ.',
        'english_translation' => 'Verily, Allah has prescribed excellence in all things.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'إِنَّ اللَّهَ لَا يَنْظُرُ إِلَى صُوَرِكُمْ وَأَمْوَالِكُمْ، وَلَكِنْ يَنْظُرُ إِلَى قُلُوبِكُمْ وَأَعْمَالِكُمْ.',
        'english_translation' => 'Allah does not look at your appearance or wealth, but rather He looks at your hearts and deeds.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'الرَّاحِمُونَ يَرْحَمُهُمُ الرَّحْمَنُ.',
        'english_translation' => 'Those who are merciful will be shown mercy by the Most Merciful.',
        'reference' => 'At-Tirmidhi'
    ],
    [
        'arabic_text' => 'مَثَلُ الْمُؤْمِنِينَ فِي تَوَادِّهِمْ وَتَرَاحُمِهِمْ كَمَثَلِ الْجَسَدِ.',
        'english_translation' => 'The believers are like a single body in their mutual love and compassion.',
        'reference' => 'Al-Bukhari and Muslim'
    ]
    // Add the remaining 30 hadiths following the same structure
];

// Insert hadiths into the `hadith` table
foreach ($hadiths as $hadith) {
    $arabic_text = $conn->real_escape_string($hadith['arabic_text']);
    $english_translation = $conn->real_escape_string($hadith['english_translation']);
    $reference = $conn->real_escape_string($hadith['reference']);

    $sql = "INSERT INTO hadith (arabic_text, english_translation, reference)
            VALUES ('$arabic_text', '$english_translation', '$reference')";

    if ($conn->query($sql) === TRUE) {
        echo "Hadith inserted successfully.<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>
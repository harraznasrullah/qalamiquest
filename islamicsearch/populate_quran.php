<?php
include 'db_connection.php';

function fetchArabicData($surahNumber) {
    $url = "https://api.alquran.cloud/v1/surah/$surahNumber";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

function fetchEnglishData($surahNumber) {
    $url = "https://api.alquran.cloud/v1/surah/$surahNumber/en.asad";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

for ($i = 1; $i <= 114; $i++) {
    $arabicData = fetchArabicData($i);
    $englishData = fetchEnglishData($i);

    if ($arabicData['status'] === "OK" && $englishData['status'] === "OK") {
        $surah = $arabicData['data']['number'];
        foreach ($arabicData['data']['ayahs'] as $ayah) {
            $ayat = $ayah['numberInSurah'];
            $arabicText = $ayah['text'];
            $englishTranslation = $englishData['data']['ayahs'][$ayat - 1]['text']; // Correctly map English translation

            $stmt = $conn->prepare("INSERT INTO quran (surah, ayat, text, english_translation) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $surah, $ayat, $arabicText, $englishTranslation);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        echo "Failed to fetch data for Surah $i\n";
    }
}

$conn->close();
echo "Data population completed.";
?>
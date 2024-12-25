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
        'arabic_text' => 'عَنْ أَمِيرِ الْمُؤْمِنِينَ أَبِي حَفْصٍ عُمَرَ بْنِ الْخَطَّابِ رَضِيَ اللهُ عَنْهُ قَالَ:
    سَمِعْتُ رَسُولَ اللَّهِ صلى الله عليه وسلم يَقُولُ: " إنَّمَا الْأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى، فَمَنْ كَانَتْ هِجْرَتُهُ إلَى اللَّهِ وَرَسُولِهِ فَهِجْرَتُهُ إلَى اللَّهِ وَرَسُولِهِ، وَمَنْ كَانَتْ هِجْرَتُهُ لِدُنْيَا يُصِيبُهَا أَوْ امْرَأَةٍ يَنْكِحُهَا فَهِجْرَتُهُ إلَى مَا هَاجَرَ إلَيْهِ',
        'english_translation' => 'Actions are according to intentions, and everyone will get what was intended. Whoever migrates with an intention for Allah and His messenger, the migration will be for the sake of Allah and his Messenger. And whoever migrates for worldly gain or to marry a woman, then his migration will be for the sake of whatever he migrated for.',
        'reference' => 'Bukhari and Muslim'
    ],
    [
        'arabic_text' => 'عَنْ عُمَرَ رَضِيَ اللهُ عَنْهُ أَيْضًا قَالَ:
    بَيْنَمَا نَحْنُ جُلُوسٌ عِنْدَ رَسُولِ اللَّهِ صلى الله عليه و سلم ذَاتَ يَوْمٍ، إذْ طَلَعَ عَلَيْنَا رَجُلٌ شَدِيدُ بَيَاضِ الثِّيَابِ، شَدِيدُ سَوَادِ الشَّعْرِ، لَا يُرَى عَلَيْهِ أَثَرُ السَّفَرِ، وَلَا يَعْرِفُهُ مِنَّا أَحَدٌ. حَتَّى جَلَسَ إلَى النَّبِيِّ صلى الله عليه و سلم . فَأَسْنَدَ رُكْبَتَيْهِ إلَى رُكْبَتَيْهِ، وَوَضَعَ كَفَّيْهِ عَلَى فَخِذَيْهِ،
    وَقَالَ: يَا مُحَمَّدُ أَخْبِرْنِي عَنْ الْإِسْلَامِ.
    فَقَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم الْإِسْلَامُ أَنْ تَشْهَدَ أَنْ لَا إلَهَ إلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَتُقِيمَ الصَّلَاةَ، وَتُؤْتِيَ الزَّكَاةَ، وَتَصُومَ رَمَضَانَ، وَتَحُجَّ الْبَيْتَ إنْ اسْتَطَعْت إلَيْهِ سَبِيلًا.
    قَالَ: صَدَقْت . فَعَجِبْنَا لَهُ يَسْأَلُهُ وَيُصَدِّقُهُ!
    قَالَ: فَأَخْبِرْنِي عَنْ الْإِيمَانِ.
    قَالَ: أَنْ تُؤْمِنَ بِاَللَّهِ وَمَلَائِكَتِهِ وَكُتُبِهِ وَرُسُلِهِ وَالْيَوْمِ الْآخِرِ، وَتُؤْمِنَ بِالْقَدَرِ خَيْرِهِ وَشَرِّهِ.
    قَالَ: صَدَقْت. قَالَ: فَأَخْبِرْنِي عَنْ الْإِحْسَانِ.
    قَالَ: أَنْ تَعْبُدَ اللَّهَ كَأَنَّك تَرَاهُ، فَإِنْ لَمْ تَكُنْ تَرَاهُ فَإِنَّهُ يَرَاك.
    قَالَ: فَأَخْبِرْنِي عَنْ السَّاعَةِ. قَالَ: مَا الْمَسْئُولُ عَنْهَا بِأَعْلَمَ مِنْ السَّائِلِ.
    قَالَ: فَأَخْبِرْنِي عَنْ أَمَارَاتِهَا؟ قَالَ: أَنْ تَلِدَ الْأَمَةُ رَبَّتَهَا، وَأَنْ تَرَى الْحُفَاةَ الْعُرَاةَ الْعَالَةَ رِعَاءَ الشَّاءِ يَتَطَاوَلُونَ فِي الْبُنْيَانِ. ثُمَّ انْطَلَقَ، فَلَبِثْتُ مَلِيًّا،
    ثُمَّ قَالَ: يَا عُمَرُ أَتَدْرِي مَنْ السَّائِلُ؟.
    قُلْتُ: اللَّهُ وَرَسُولُهُ أَعْلَمُ.
    قَالَ: فَإِنَّهُ جِبْرِيلُ أَتَاكُمْ يُعَلِّمُكُمْ دِينَكُمْ',
        'english_translation' => 'It was narrated on the authority of Umar (may Allah be pleased with him), who said:
    While we were one day sitting with the Messenger of Allah (peace be upon him), there appeared before us a man dressed in extremely white clothes and with very black hair. No traces of journeying were visible on him, and none of us knew him. He sat down close by the Prophet (peace be upon him), rested his knee against his thighs, and said, "O Muhammad! Inform me about Islam."
    The Messenger of Allah (peace be upon him) said, "Islam is that you should testify that there is no deity except Allah and that Muhammad is His Messenger, that you should perform salah, pay the Zakah, fast during Ramadan, and perform Hajj to the House, if you are able to do so."
    The man said, "You have spoken truly." We were astonished at his questioning him (the Messenger) and telling him that he was right, but he went on to say, "Inform me about iman."
    He (the Messenger of Allah) answered, "It is that you believe in Allah and His angels and His Books and His Messengers and in the Last Day, and in qadar (fate), both in its good and in its evil aspects." He said, "You have spoken truly."
    Then he (the man) said, "Inform me about Ihsan." He (the Messenger of Allah) answered, "It is that you should serve Allah as though you could see Him, for though you cannot see Him yet (know that) He sees you."
    He said, "Inform me about the Hour." He (the Messenger of Allah) said, "About that, the one questioned knows no more than the questioner." So he said, "Well, inform me about the signs thereof." He said, "They are that the slave-girl will give birth to her mistress, that you will see the barefooted, naked, destitute, the herdsmen of the sheep (competing with each other) in raising lofty buildings." Thereupon the man went of. I waited a while, and then he (the Messenger of Allah) said, "O Umar, do you know who that questioner was?" I replied, "Allah and His Messenger know better." He said, "That was Jibril (the Angel Gabriel). He came to teach you your religion."
',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي عَبْدِ الرَّحْمَنِ عَبْدِ اللَّهِ بْنِ عُمَرَ بْنِ الْخَطَّابِ رَضِيَ اللَّهُ عَنْهُمَا قَالَ: سَمِعْت رَسُولَ اللَّهِ صلى الله عليه و سلم يَقُولُ:
    " بُنِيَ الْإِسْلَامُ عَلَى خَمْسٍ: شَهَادَةِ أَنْ لَا إلَهَ إلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَإِقَامِ الصَّلَاةِ، وَإِيتَاءِ الزَّكَاةِ، وَحَجِّ الْبَيْتِ، وَصَوْمِ رَمَضَانَ',
        'english_translation' => 'On the authority of Abdullah ibn Umar ibn Al-Khattab (may Allah be pleased with him) who said: I heard the Messenger of Allah (peace be upon him) say:
    Islam has been built on five [pillars]: testifying that there is no god but Allah and that Muhammad is the Messenger of Allah, performing the prayers, paying the Zakah, making the pilgrimage to the House, and fasting in Ramadan.',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أُمِّ الْمُؤْمِنِينَ أُمِّ عَبْدِ اللَّهِ عَائِشَةَ رَضِيَ اللَّهُ عَنْهَا، قَالَتْ: قَالَ: رَسُولُ اللَّهِ صلى الله عليه و سلم:
    "مَنْ أَحْدَثَ فِي أَمْرِنَا هَذَا مَا لَيْسَ مِنْهُ فَهُوَ رَدٌّ ',
        'english_translation' => 'On the authority of the mother of the faithful, Aisha (may Allah be pleased with her), who said: The Messenger of Allah (peace be upon him) said:
    He who innovates something in this matter of ours [Islam] that is not of it will have it rejected [by Allah]',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي عَبْدِ اللَّهِ النُّعْمَانِ بْنِ بَشِيرٍ رَضِيَ اللَّهُ عَنْهُمَا، قَالَ: سَمِعْت رَسُولَ اللَّهِ صلى الله عليه و سلم يَقُولُ:
    "إنَّ الْحَلَالَ بَيِّنٌ، وَإِنَّ الْحَرَامَ بَيِّنٌ، وَبَيْنَهُمَا أُمُورٌ مُشْتَبِهَاتٌ لَا يَعْلَمُهُنَّ كَثِيرٌ مِنْ النَّاسِ، فَمَنْ اتَّقَى الشُّبُهَاتِ فَقْد اسْتَبْرَأَ لِدِينِهِ وَعِرْضِهِ، وَمَنْ وَقَعَ فِي الشُّبُهَاتِ وَقَعَ فِي الْحَرَامِ، كَالرَّاعِي يَرْعَى حَوْلَ الْحِمَى يُوشِكُ أَنْ يَرْتَعَ فِيهِ، أَلَا وَإِنَّ لِكُلِّ مَلِكٍ حِمًى، أَلَا وَإِنَّ حِمَى اللَّهِ مَحَارِمُهُ، أَلَا وَإِنَّ فِي الْجَسَدِ مُضْغَةً إذَا صَلَحَتْ صَلَحَ الْجَسَدُ كُلُّهُ، وَإذَا فَسَدَتْ فَسَدَ الْجَسَدُ كُلُّهُ، أَلَا وَهِيَ الْقَلْبُ',
        'english_translation' => 'On the authority of Abu Abdullah al-Numan bin Bashir (ra) who said: I heard the Messenger of Allah(sas) say:
    "The halal is clear and the haram is clear, and between them are matters unclear that are unknown to most people. Whoever is wary of these unclear matters has absolved his religion and honor. And whoever indulges in them has indulged in the haram. It is like a shepherd who herds his sheep too close to preserved sanctuary, and they will eventually graze in it. Every king has a sanctuary, and the sanctuary of Allah is what He has made haram. There lies within the body a piece of flesh. If it is sound, the whole body is sound; and if it is corrupted, the whole body is corrupted. Verily this piece is the heart."',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي رُقَيَّةَ تَمِيمِ بْنِ أَوْسٍ الدَّارِيِّ رَضِيَ اللهُ عَنْهُ أَنَّ النَّبِيَّ صلى الله عليه وسلم قَالَ:
    "الدِّينُ النَّصِيحَةُ." قُلْنَا: لِمَنْ؟ قَالَ: "لِلَّهِ، وَلِكِتَابِهِ، وَلِرَسُولِهِ، وَلِأَئِمَّةِ الْمُسْلِمِينَ وَعَامَّتِهِمْ',
        'english_translation' => 'On the authority of Tamim Al-Dari (may Allah be pleased with him):
    The Prophet (peace be upon him) said, "The religion is naseehah (sincerity)." We said, "To whom?" He (peace be upon him) said, To Allah, His Book, His Messenger, and to the leaders of the Muslims and their common folk',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ ابْنِ عُمَرَ رَضِيَ اللَّهُ عَنْهُمَا، أَنَّ رَسُولَ اللَّهِ صلى الله عليه و سلم قَالَ:
    "أُمِرْتُ أَنْ أُقَاتِلَ النَّاسَ حَتَّى يَشْهَدُوا أَنْ لَا إلَهَ إلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَيُقِيمُوا الصَّلَاةَ، وَيُؤْتُوا الزَّكَاةَ؛ فَإِذَا فَعَلُوا ذَلِكَ عَصَمُوا مِنِّي دِمَاءَهُمْ وَأَمْوَالَهُمْ إلَّا بِحَقِّ الْإِسْلَامِ، وَحِسَابُهُمْ عَلَى اللَّهِ تَعَالَى',
        'english_translation' => 'On the authority of Abdullah ibn Umar (may Allah be pleased with him), the Messenger of Allah (peace be upon him) said:
    I have been ordered to fight against the people until they testify that there is none worthy of worship except Allah and that Muhammad is the Messenger of Allah, and until they establish the Salah and pay the Zakah. And if they do so then they will have gained protection from me for their lives and property, unless [they commit acts that are punishable] in accordance to Islam, and their reckoning will be with Allah the Almighty.',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَةَ عَبْدِ الرَّحْمَنِ بْنِ صَخْرٍ رَضِيَ اللهُ عَنْهُ قَالَ: سَمِعْت رَسُولَ اللَّهِ صلى الله عليه و سلم يَقُولُ:
    "مَا نَهَيْتُكُمْ عَنْهُ فَاجْتَنِبُوهُ، وَمَا أَمَرْتُكُمْ بِهِ فَأْتُوا مِنْهُ مَا اسْتَطَعْتُمْ، فَإِنَّمَا أَهْلَكَ الَّذِينَ مِنْ قَبْلِكُمْ كَثْرَةُ مَسَائِلِهِمْ وَاخْتِلَافُهُمْ عَلَى أَنْبِيَائِهِمْ',
        'english_translation' => 'On the authority of Abu Hurayrah (may Allah be pleased with him) who said: I heard the Messenger of Allah (peace be upon him) say:
    What I have forbidden for you, avoid. What I have ordered you [to do], do as much of it as you can. For verily, it was only their excessive questioning and disagreeing with their Prophets that destroyed [the nations] who were before you',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'نْ أَبِي هُرَيْرَةَ رَضِيَ اللهُ عَنْهُ قَالَ:
    قَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم "إنَّ اللَّهَ طَيِّبٌ لَا يَقْبَلُ إلَّا طَيِّبًا، وَإِنَّ اللَّهَ أَمَرَ الْمُؤْمِنِينَ بِمَا أَمَرَ بِهِ الْمُرْسَلِينَ فَقَالَ تَعَالَى: "يَا أَيُّهَا الرُّسُلُ كُلُوا مِنْ الطَّيِّبَاتِ وَاعْمَلُوا صَالِحًا"، وَقَالَ تَعَالَى: "يَا أَيُّهَا الَّذِينَ آمَنُوا كُلُوا مِنْ طَيِّبَاتِ مَا رَزَقْنَاكُمْ" ثُمَّ ذَكَرَ الرَّجُلَ يُطِيلُ السَّفَرَ أَشْعَثَ أَغْبَرَ يَمُدُّ يَدَيْهِ إلَى السَّمَاءِ: يَا رَبِّ! يَا رَبِّ! وَمَطْعَمُهُ حَرَامٌ، وَمَشْرَبُهُ حَرَامٌ، وَمَلْبَسُهُ حَرَامٌ، وَغُذِّيَ بِالْحَرَامِ، فَأَنَّى يُسْتَجَابُ لَهُ؟',
        'english_translation' => 'On the authority of Abu Hurayrah (may Allah be pleased with him) who said: The Messenger of Allah (peace be upon him) said:
    Allah the Almighty is good and accepts only that which is good. And verily Allah has commanded the believers to do that which He has commanded the Messengers. So the Almighty has said: "O (you) Messengers! Eat of the tayyibat (good things), and perform righteous deeds" [23:51] and the Almighty has said: "O you who believe! Eat of the lawful things that We have provided you" [2:172]. Then he (peace be upon him) mentioned a man who, having journeyed far, is dishevelled and dusty, and who spreads out his hands to the sky saying, "O Lord! O Lord!" while his food is haram, his drink is haram, his clothing is haram, and he has been nourished with haram, so how can [his supplication] be answered?',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي مُحَمَّدٍ الْحَسَنِ بْنِ عَلِيِّ بْنِ أَبِي طَالِبٍ سِبْطِ رَسُولِ اللَّهِ صلى الله عليه و سلم وَرَيْحَانَتِهِ رَضِيَ اللَّهُ عَنْهُمَا، قَالَ:
    حَفِظْت مِنْ رَسُولِ اللَّهِ صلى الله عليه و سلم "دَعْ مَا يُرِيبُك إلَى مَا لَا يُرِيبُك',
        'english_translation' => 'On the authority of Abu Muhammad al-Hasan ibn Ali ibn Abee Talib (may Allah be pleased with him), the grandson of the Messenger of Allah (peace and blessings of Allah be upon him), and the one much loved by him, who said: I memorized from the Messenger of Allah (peace and blessings of Allah be upon him):
    “Leave what makes you doubtful for what does not.”',
        'reference' => 'Tirmidhi & Nasai'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَةَ رَضِيَ اللهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم
    مِنْ حُسْنِ إسْلَامِ الْمَرْءِ تَرْكُهُ مَا لَا يَعْنِيهِ',
        'english_translation' => 'On the authority of Abu Hurayrah (may Allah be pleased with him) who said: The Messenger of Allah (peace be upon him) said:
    "Part of the perfection of ones Islam is his leaving that which does not concern him.',
        'reference' => 'Tirmidhi'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي حَمْزَةَ أَنَسِ بْنِ مَالِكٍ رَضِيَ اللهُ عَنْهُ خَادِمِ رَسُولِ اللَّهِ صلى الله عليه و سلم عَنْ النَّبِيِّ صلى الله عليه و سلم قَالَ:
    لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ',
        'english_translation' => 'On the authority of Abu Hamzah Anas bin Malik (may Allah be pleased with him) - the servant of the Messenger of Allah (peace and blessings of Allah be upon him) - that the Prophet (peace and blessings of Allah be upon him) said :
    None of you will believe until you love for your brother what you love for yourself.',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ ابْنِ مَسْعُودٍ رَضِيَ اللهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم
    لَا يَحِلُّ دَمُ امْرِئٍ مُسْلِمٍ [ يشهد أن لا إله إلا الله، وأني رسول الله] إلَّا بِإِحْدَى ثَلَاثٍ: الثَّيِّبُ الزَّانِي، وَالنَّفْسُ بِالنَّفْسِ، وَالتَّارِكُ لِدِينِهِ الْمُفَارِقُ لِلْجَمَاعَةِ',
        'english_translation' => 'On the authority of Abdullah Ibn Masud (may Allah be pleased with him) who said: The Messenger of Allah (peace be upon him) said:
    It is not permissible to spill the blood of a Muslim except in three [instances]: the married person who commits adultery, a life for a life, and the one who forsakes his religion and separates from the community.',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَةَ رَضِيَ اللهُ عَنْهُ أَنَّ رَسُولَ اللَّهِ صلى الله عليه و سلم قَالَ:
    مَنْ كَانَ يُؤْمِنُ بِاَللَّهِ وَالْيَوْمِ الْآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ، وَمَنْ كَانَ يُؤْمِنُ بِاَللَّهِ وَالْيَوْمِ الْآخِرِ فَلْيُكْرِمْ جَارَهُ، وَمَنْ كَانَ يُؤْمِنُ بِاَللَّهِ وَالْيَوْمِ الْآخِرِ فَلْيُكْرِمْ ضَيْفَهُ',
        'english_translation' => 'On the authority of Abu Hurayrah (may Allah be pleased with him), that the Messenger of Allah (peace be upon him) said:
    "Let him who believes in Allah and the Last Day speak good, or keep silent; and let him who believes in Allah and the Last Day be generous to his neighbour; and let him who believes in Allah and the Last Day be generous to his guest.',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَةَ رَضِيَ اللهُ عَنْهُ أَنَّ رَجُلًا قَالَ لِلنَّبِيِّ صلى الله عليه و سلم أَوْصِنِي. قَالَ:
    "لَا تَغْضَبْ، فَرَدَّدَ مِرَارًا، قَالَ: لَا تَغْضَبْ',
        'english_translation' => 'A man said to the Prophet, Give me advice. The Prophet, peace be upon him, said, Do not get angry. The man asked repeatedly and the Prophet answered each time, Do not get angry',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي يَعْلَى شَدَّادِ بْنِ أَوْسٍ رَضِيَ اللهُ عَنْهُ عَنْ رَسُولِ اللَّهِ صلى الله عليه و سلم قَالَ:
    إنَّ اللَّهَ كَتَبَ الْإِحْسَانَ عَلَى كُلِّ شَيْءٍ، فَإِذَا قَتَلْتُمْ فَأَحْسِنُوا الْقِتْلَةَ، وَإِذَا ذَبَحْتُمْ فَأَحْسِنُوا الذِّبْحَةَ، وَلْيُحِدَّ أَحَدُكُمْ شَفْرَتَهُ، وَلْيُرِحْ ذَبِيحَتَهُ',
        'english_translation' => 'On the authority of Abu Yala Shaddad bin Aws (may Allah be pleased with him), that the Messenger of Allah (peace be upon him) said:
    Verily Allah has prescribed ihsan (perfection) in all things. Thus if you kill, kill well; and if you slaughter, slaughter well. Let each one of you sharpen his blade and let him spare suffering to the animal he slaughters',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي ذَرٍّ جُنْدَبِ بْنِ جُنَادَةَ، وَأَبِي عَبْدِ الرَّحْمَنِ مُعَاذِ بْنِ جَبَلٍ رَضِيَ اللَّهُ عَنْهُمَا، عَنْ رَسُولِ اللَّهِ صلى الله عليه و سلم قَالَ:
    اتَّقِ اللَّهَ حَيْثُمَا كُنْت، وَأَتْبِعْ السَّيِّئَةَ الْحَسَنَةَ تَمْحُهَا، وَخَالِقْ النَّاسَ بِخُلُقٍ حَسَنٍ',
        'english_translation' => 'On the authority of Abu Dharr Jundub ibn Junadah, and Abu Abd-ir-Rahman Muadh bin Jabal (may Allah be pleased with them) that the Messenger of Allah (peace and blessing of Allah be upon him) said:
    Be conscious of Allah wherever you are. Follow the bad deed with a good one to erase it, and engage others with beautiful character.',
        'reference' => 'Tirmidhi'
    ],
    [
        'arabic_text' => 'عَنْ عَبْدِ اللَّهِ بْنِ عَبَّاسٍ رَضِيَ اللَّهُ عَنْهُمَا قَالَ:
    كُنْت خَلْفَ رَسُولِ اللَّهِ صلى الله عليه و سلم يَوْمًا، فَقَالَ: يَا غُلَامِ! إنِّي أُعَلِّمُك كَلِمَاتٍ: احْفَظْ اللَّهَ يَحْفَظْك، احْفَظْ اللَّهَ تَجِدْهُ تُجَاهَك، إذَا سَأَلْت فَاسْأَلْ اللَّهَ، وَإِذَا اسْتَعَنْت فَاسْتَعِنْ بِاَللَّهِ، وَاعْلَمْ أَنَّ الْأُمَّةَ لَوْ اجْتَمَعَتْ عَلَى أَنْ يَنْفَعُوك بِشَيْءٍ لَمْ يَنْفَعُوك إلَّا بِشَيْءٍ قَدْ كَتَبَهُ اللَّهُ لَك، وَإِنْ اجْتَمَعُوا عَلَى أَنْ يَضُرُّوك بِشَيْءٍ لَمْ يَضُرُّوك إلَّا بِشَيْءٍ قَدْ كَتَبَهُ اللَّهُ عَلَيْك؛ رُفِعَتْ الْأَقْلَامُ، وَجَفَّتْ الصُّحُفُ',
        'english_translation' => 'Abu al-Abbas Abdullah bin Abbas(ra) reports:
    “One day I was riding (a horse/camel) behind the Prophet, peace and blessings be upon him, when he said, Young man, I will teach you some words. Be mindful of God, and He will take care of you. Be mindful of Him, and you shall find Him at your side. If you ask, ask of God. If you need help, seek it from God. Know that if the whole world were to gather together in order to help you, they would not be able to help you except if God had written so. And if the whole world were to gather together in order to harm you, they would not harm you except if God had written so. The pens have been lifted, and the pages are dry.',
        'reference' => 'Tirmidhi'
    ],
    [
        'arabic_text' => 'نْ أَبِي مَسْعُودٍ عُقْبَةَ بْنِ عَمْرٍو الْأَنْصَارِيِّ الْبَدْرِيِّ رَضِيَ اللهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم:
    إنَّ مِمَّا أَدْرَكَ النَّاسُ مِنْ كَلَامِ النُّبُوَّةِ الْأُولَى: إذَا لَمْ تَسْتَحِ فَاصْنَعْ مَا شِئْت',
        'english_translation' => 'Abu Masud Uqbah bin Amr al-Ansari al-Badri(ra) reported that the Messenger of Allah(sas) said:
    The Messenger of Allah, peace be upon him, said: Among the early prophetic teachings that have reached people is this: if you do not feel shame, do what you wish',
        'reference' => 'Bukhari'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي عَمْرٍو وَقِيلَ: أَبِي عَمْرَةَ سُفْيَانَ بْنِ عَبْدِ اللَّهِ رَضِيَ اللهُ عَنْهُ قَالَ:
    قُلْت: يَا رَسُولَ اللَّهِ! قُلْ لِي فِي الْإِسْلَامِ قَوْلًا لَا أَسْأَلُ عَنْهُ أَحَدًا غَيْرَك؛ قَالَ: قُلْ: آمَنْت بِاَللَّهِ ثُمَّ اسْتَقِمْ',
        'english_translation' => 'On the authority of Sufyan bin Abdullah (may Allah be pleased with him) who said:
    "I said, O Messenger of Allah, tell me something about Islam which I can ask of no one but you. He (peace be upon him) said, Say I believe in Allah — and then be steadfast',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي عَبْدِ اللَّهِ جَابِرِ بْنِ عَبْدِ اللَّهِ الْأَنْصَارِيِّ رَضِيَ اللَّهُ عَنْهُمَا: "أَنَّ رَجُلًا سَأَلَ رَسُولَ اللَّهِ صلى الله عليه و سلم فَقَالَ:
    أَرَأَيْت إذَا صَلَّيْت الْمَكْتُوبَاتِ، وَصُمْت رَمَضَانَ، وَأَحْلَلْت الْحَلَالَ، وَحَرَّمْت الْحَرَامَ، وَلَمْ أَزِدْ عَلَى ذَلِكَ شَيْئًا؛ أَأَدْخُلُ الْجَنَّةَ؟ قَالَ: نَعَمْ',
        'english_translation' => 'On the authority of Abu Abdullah Jabir bin Abdullah al-Ansari (may Allah be pleased with him):
    A man questioned the Messenger of Allah (peace be upon him) and said: "Do you think that if I perform the obligatory prayers, fast in Ramadan, treat as lawful that which is halal, and treat as forbidden that which is haram, and do not increase upon that [in voluntary good deeds], then I shall enter Paradise? He (peace be upon him) replied, Yes.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي مَالِكٍ الْحَارِثِ بْنِ عَاصِمٍ الْأَشْعَرِيِّ رَضِيَ اللهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم
    الطَّهُورُ شَطْرُ الْإِيمَانِ، وَالْحَمْدُ لِلَّهِ تَمْلَأُ الْمِيزَانَ، وَسُبْحَانَ اللَّهِ وَالْحَمْدُ لِلَّهِ تَمْلَآنِ -أَوْ: تَمْلَأُ- مَا بَيْنَ السَّمَاءِ وَالْأَرْضِ، وَالصَّلَاةُ نُورٌ، وَالصَّدَقَةُ بُرْهَانٌ، وَالصَّبْرُ ضِيَاءٌ، وَالْقُرْآنُ حُجَّةٌ لَك أَوْ عَلَيْك، كُلُّ النَّاسِ يَغْدُو، فَبَائِعٌ نَفْسَهُ فَمُعْتِقُهَا أَوْ مُوبِقُهَا',
        'english_translation' => 'On the authority of Abu Malik al-Harith bin Asim al-Ashari (may Allah be pleased with him) who said: The Messenger of Allah (peace be upon him) said:
    Purity is half of Iman. Alhamdulillah (praise be to Allah) fills the scales, and subhan-Allah (how far from imperfection is Allah) and Alhamdulillah (praise be to Allah) fill that which is between heaven and earth. And the Salah (prayer) is a light, and charity is a proof, and patience is illumination, and the Quran is a proof either for you or against you. Every person starts his day as a vendor of his soul, either freeing it or bringing about its ruin.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي ذَرٍّ الْغِفَارِيِّ رَضِيَ اللهُ عَنْهُ عَنْ النَّبِيِّ صلى الله عليه و سلم فِيمَا يَرْوِيهِ عَنْ رَبِّهِ تَبَارَكَ وَتَعَالَى، أَنَّهُ قَالَ:
    يَا عِبَادِي: إنِّي حَرَّمْت الظُّلْمَ عَلَى نَفْسِي، وَجَعَلْته بَيْنَكُمْ مُحَرَّمًا؛ فَلَا تَظَالَمُوا. يَا عِبَادِي! كُلُّكُمْ ضَالٌّ إلَّا مَنْ هَدَيْته، فَاسْتَهْدُونِي أَهْدِكُمْ. يَا عِبَادِي! كُلُّكُمْ جَائِعٌ إلَّا مَنْ أَطْعَمْته، فَاسْتَطْعِمُونِي أُطْعِمْكُمْ. يَا عِبَادِي! كُلُّكُمْ عَارٍ إلَّا مَنْ كَسَوْته، فَاسْتَكْسُونِي أَكْسُكُمْ. يَا عِبَادِي! إنَّكُمْ تُخْطِئُونَ بِاللَّيْلِ وَالنَّهَارِ، وَأَنَا أَغْفِرُ الذُّنُوبَ جَمِيعًا؛ فَاسْتَغْفِرُونِي أَغْفِرْ لَكُمْ. يَا عِبَادِي! إنَّكُمْ لَنْ تَبْلُغُوا ضُرِّي فَتَضُرُّونِي، وَلَنْ تَبْلُغُوا نَفْعِي فَتَنْفَعُونِي. يَا عِبَادِي! لَوْ أَنَّ أَوَّلَكُمْ وَآخِرَكُمْ وَإِنْسَكُمْ وَجِنَّكُمْ كَانُوا عَلَى أَتْقَى قَلْبِ رَجُلٍ وَاحِدٍ مِنْكُمْ، مَا زَادَ ذَلِكَ فِي مُلْكِي شَيْئًا. يَا عِبَادِي! لَوْ أَنَّ أَوَّلَكُمْ وَآخِرَكُمْ وَإِنْسَكُمْ وَجِنَّكُمْ كَانُوا عَلَى أَفْجَرِ قَلْبِ رَجُلٍ وَاحِدٍ مِنْكُمْ، مَا نَقَصَ ذَلِكَ مِنْ مُلْكِي شَيْئًا. يَا عِبَادِي! لَوْ أَنَّ أَوَّلَكُمْ وَآخِرَكُمْ وَإِنْسَكُمْ وَجِنَّكُمْ قَامُوا فِي صَعِيدٍ وَاحِدٍ، فَسَأَلُونِي، فَأَعْطَيْت كُلَّ وَاحِدٍ مَسْأَلَته، مَا نَقَصَ ذَلِكَ مِمَّا عِنْدِي إلَّا كَمَا يَنْقُصُ الْمِخْيَطُ إذَا أُدْخِلَ الْبَحْرَ. يَا عِبَادِي! إنَّمَا هِيَ أَعْمَالُكُمْ أُحْصِيهَا لَكُمْ، ثُمَّ أُوَفِّيكُمْ إيَّاهَا؛ فَمَنْ وَجَدَ خَيْرًا فَلْيَحْمَدْ اللَّهَ، وَمَنْ وَجَدَ غَيْرَ ذَلِكَ فَلَا يَلُومَن إلَّا نَفْسَهُ',
        'english_translation' => 'On the authority of Abu Dharr Al-Ghafari, of the Prophet (peace be upon him) is that among the sayings he relates from his Lord is that He said:
        "O My servants! I have forbidden oppression for Myself, and I have made it forbidden amongst you, so do not oppress one another. O My servants, all of you are astray except those whom I have guided, so seek guidance from Me and I shall guide you. O My servants, all of you are hungry except those whom I have fed, so seek food from Me and I shall feed you. O My servants, all of you are naked except those whom I have clothed, so seek clothing from Me and I shall clothe you. O My servants, you sommit sins by day and by night, and I forgive all sins, so seek forgiveness from Me and I shall forgive you. O My servants, you will not attain harming Me so as to harm Me, and you will not attain benefitting Me so as to benefit Me. O My servants, if the first of you and the last of you, and the humans of you and the jinn of you, were all as pious as the most pious heart of any individual amongst you, then this would not increase My Kingdom an iota. O My servants, if the first of you and the last of you, and the humans of you and the jinn of you, were all as wicked as the most wicked heart of any individual amongst you, then this would not decrease My Kingdom an iota. O My servants, if the first of you and the last of you, and the humans of you and the jinn of you, were all to stand together in one place and ask of Me, and I were to give everyone what he requested, then that would not decrease what I Possess, except what is decreased of the ocean when a needle is dipped into it. O My servants, it is but your deeds that I account for you, and then recompense you for. So he who finds good, let him praise Allah, and he who finds other than that, let him blame no one but himself.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'نْ أَبِي ذَرٍّ رَضِيَ اللهُ عَنْهُ:
    نَاسًا مِنْ أَصْحَابِ رَسُولِ اللَّهِ صلى الله عليه و سلم قَالُوا لِلنَّبِيِّ صلى الله عليه و سلم يَا رَسُولَ اللَّهِ ذَهَبَ أَهْلُ الدُّثُورِ بِالْأُجُورِ؛ يُصَلُّونَ كَمَا نُصَلِّي، وَيَصُومُونَ كَمَا نَصُومُ، وَيَتَصَدَّقُونَ بِفُضُولِ أَمْوَالِهِمْ. قَالَ: أَوَلَيْسَ قَدْ جَعَلَ اللَّهُ لَكُمْ مَا تَصَّدَّقُونَ؟ إنَّ بِكُلِّ تَسْبِيحَةٍ صَدَقَةً، وَكُلِّ تَكْبِيرَةٍ صَدَقَةً، وَكُلِّ تَحْمِيدَةٍ صَدَقَةً، وَكُلِّ تَهْلِيلَةٍ صَدَقَةً، وَأَمْرٌ بِمَعْرُوفٍ صَدَقَةٌ، وَنَهْيٌ عَنْ مُنْكَرٍ صَدَقَةٌ، وَفِي بُضْعِ أَحَدِكُمْ صَدَقَةٌ. قَالُوا: يَا رَسُولَ اللَّهِ أَيَأْتِي أَحَدُنَا شَهْوَتَهُ وَيَكُونُ لَهُ فِيهَا أَجْرٌ؟ قَالَ: أَرَأَيْتُمْ لَوْ وَضَعَهَا فِي حَرَامٍ أَكَانَ عَلَيْهِ وِزْرٌ؟ فَكَذَلِكَ إذَا وَضَعَهَا فِي الْحَلَالِ، كَانَ لَهُ أَجْرٌ',
        'english_translation' => 'On the authority of Abu Dharr (may Allah be pleased with him):
    Some people from amongst the Companions of the Messenger of Allah (peace be upon him) said to the Prophet (peace be upon him), O Messenger of Allah, the affluent have made off with the rewards they pray as we pray, they fast as we fast, and they give much in charity by virtue of their wealth. He (peace be upon him) said, Has not Allah made things for you to give in charity? Truly every tasbeehah [saying subhan-Allah] is a charity, and every takbeerah [saying Allahu akbar] is a charity, and every tahmeedah [saying alhamdulillah] is a charity, and every tahleelah [saying la ilaha illAllah] is a charity. And commanding the good is a charity, and forbidding an evil is a charity, and in the sexual act of each one of you there is a charity.They said, O Messenger of Allah, when one of us fulfills his sexual desire, will he have some reward for that? He (peace be upon him) said: Do you not see that if he were to act upon it [his desire] in an unlawful manner, then he would be deserving of punishment? Likewise, if he were to act upon it in a lawful manner, then he will be deserving of a reward',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَةَ رَضِيَ اللهُ عَنْهُ قَالَ: قَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم
    كُلُّ سُلَامَى مِنْ النَّاسِ عَلَيْهِ صَدَقَةٌ، كُلَّ يَوْمٍ تَطْلُعُ فِيهِ الشَّمْسُ تَعْدِلُ بَيْنَ اثْنَيْنِ صَدَقَةٌ، وَتُعِينُ الرَّجُلَ فِي دَابَّتِهِ فَتَحْمِلُهُ عَلَيْهَا أَوْ تَرْفَعُ لَهُ عَلَيْهَا مَتَاعَهُ صَدَقَةٌ، وَالْكَلِمَةُ الطَّيِّبَةُ صَدَقَةٌ، وَبِكُلِّ خُطْوَةٍ تَمْشِيهَا إلَى الصَّلَاةِ صَدَقَةٌ، وَتُمِيطُ الْأَذَى عَنْ الطَّرِيقِ صَدَقَةٌ',
        'english_translation' => 'Abu Hurairah (ra) reported that the Messenger of Allah (sas) said,
    Every joint of a person must perform a charity each day that the sun rises: to judge justly between two people is a charity. To help a man with his mount, lifting him onto it or hoisting up his belongings onto it, is a charity. And the good word is a charity. And every step that you take towards the prayer is a charity, and removing a harmful object from the road is a charity.',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ النَّوَّاسِ بْنِ سَمْعَانَ رَضِيَ اللهُ عَنْهُ عَنْ النَّبِيِّ صلى الله عليه و سلم قَالَ:
    الْبِرُّ حُسْنُ الْخُلُقِ، وَالْإِثْمُ مَا حَاكَ فِي صَدْرِك، وَكَرِهْت أَنْ يَطَّلِعَ عَلَيْهِ النَّاسُ" رَوَاهُ مُسْلِمٌ',
        'english_translation' => 'On the authority of an-Nawas bin Saman (may Allah be pleased with him), the Prophet (peace be upon him) said:
    Righteousness is in good character, and wrongdoing is that which wavers in your soul, and which you dislike people finding out about.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي نَجِيحٍ الْعِرْبَاضِ بْنِ سَارِيَةَ رَضِيَ اللهُ عَنْهُ قَالَ:
    وَعَظَنَا رَسُولُ اللَّهِ صلى الله عليه و سلم مَوْعِظَةً وَجِلَتْ مِنْهَا الْقُلُوبُ، وَذَرَفَتْ مِنْهَا الْعُيُونُ، فَقُلْنَا: يَا رَسُولَ اللَّهِ! كَأَنَّهَا مَوْعِظَةُ مُوَدِّعٍ فَأَوْصِنَا، قَالَ: أُوصِيكُمْ بِتَقْوَى اللَّهِ، وَالسَّمْعِ وَالطَّاعَةِ وَإِنْ تَأَمَّرَ عَلَيْكُمْ عَبْدٌ، فَإِنَّهُ مَنْ يَعِشْ مِنْكُمْ فَسَيَرَى اخْتِلَافًا كَثِيرًا، فَعَلَيْكُمْ بِسُنَّتِي وَسُنَّةِ الْخُلَفَاءِ الرَّاشِدِينَ الْمَهْدِيينَ، عَضُّوا عَلَيْهَا بِالنَّوَاجِذِ، وَإِيَّاكُمْ وَمُحْدَثَاتِ الْأُمُورِ؛ فَإِنَّ كُلَّ بِدْعَةٍ ضَلَالَةٌ',
        'english_translation' => 'It was narrated on the authority of Abu Najih al-Irbad bin Sariyah (ra) who said:
    The Messenger of Allah (sas) delivered an admonition that made our hearts fearful and our eyes tearful. We said, "O Messenger of Allah, it is as if this were a farewell sermon, so advise us." He said, "I enjoin you to have Taqwa of Allah and that you listen and obey, even if a slave is made a ruler over you. He among you who lives long enough will see many differences. So for you is to observe my Sunnah and the Sunnah of the rightly-principled and rightly-guided successors, holding on to them with your molar teeth. Beware of newly-introduced matters, for every innovation (bidah) is an error.',
        'reference' => 'Abu Dawud & Al-Tirmidhi'
    ],
    [
        'arabic_text' => 'عَنْ مُعَاذِ بْنِ جَبَلٍ رَضِيَ اللهُ عَنْهُ قَالَ:
    قُلْت يَا رَسُولَ اللَّهِ! أَخْبِرْنِي بِعَمَلٍ يُدْخِلُنِي الْجَنَّةَ وَيُبَاعِدْنِي مِنْ النَّارِ، قَالَ: "لَقَدْ سَأَلْت عَنْ عَظِيمٍ، وَإِنَّهُ لَيَسِيرٌ عَلَى مَنْ يَسَّرَهُ اللَّهُ عَلَيْهِ: تَعْبُدُ اللَّهَ لَا تُشْرِكْ بِهِ شَيْئًا، وَتُقِيمُ الصَّلَاةَ، وَتُؤْتِي الزَّكَاةَ، وَتَصُومُ رَمَضَانَ، وَتَحُجُّ الْبَيْتَ، ثُمَّ قَالَ: أَلَا أَدُلُّك عَلَى أَبْوَابِ الْخَيْرِ؟ الصَّوْمُ جُنَّةٌ، وَالصَّدَقَةُ تُطْفِئُ الْخَطِيئَةَ كَمَا يُطْفِئُ الْمَاءُ النَّارَ، وَصَلَاةُ الرَّجُلِ فِي جَوْفِ اللَّيْلِ، ثُمَّ تَلَا: " تَتَجَافَى جُنُوبُهُمْ عَنِ الْمَضَاجِعِ " حَتَّى بَلَغَ "يَعْمَلُونَ"،[ 32 سورة السجدة / الأيتان : 16 و 17 ] ثُمَّ قَالَ: أَلَا أُخْبِرُك بِرَأْسِ الْأَمْرِ وَعَمُودِهِ وَذُرْوَةِ سَنَامِهِ؟ قُلْت: بَلَى يَا رَسُولَ اللَّهِ. قَالَ: رَأْسُ الْأَمْرِ الْإِسْلَامُ، وَعَمُودُهُ الصَّلَاةُ، وَذُرْوَةُ سَنَامِهِ الْجِهَادُ، ثُمَّ قَالَ: أَلَا أُخْبِرُك بِمَلَاكِ ذَلِكَ كُلِّهِ؟ فقُلْت: بَلَى يَا رَسُولَ اللَّهِ ! فَأَخَذَ بِلِسَانِهِ وَقَالَ: كُفَّ عَلَيْك هَذَا. قُلْت: يَا نَبِيَّ اللَّهِ وَإِنَّا لَمُؤَاخَذُونَ بِمَا نَتَكَلَّمُ بِهِ؟ فَقَالَ: ثَكِلَتْك أُمُّك وَهَلْ يَكُبُّ النَّاسَ عَلَى وُجُوهِهِمْ -أَوْ قَالَ عَلَى مَنَاخِرِهِمْ- إلَّا حَصَائِدُ أَلْسِنَتِهِمْ؟',
        'english_translation' => 'On the authority of Muadh bin Jabal (may Allah be please with him) who said:
    I said, O Messenger of Allah, tell me of an act which will take me into Paradise and will keep me away from the Hellfire. He (peace be upon him) said, You have asked me about a great matter, yet it is easy for him for whom Allah makes it easy. Worship Allah without associating any partners with Him; establish the prayer; pay the Zakah; fast in Ramadan; and make the pilgrimage to the House.
    Then he (peace be upon him) said, Shall I not guide you towards the means of goodness? Fasting is a shield, charity wipes away sin as water extinguishes fire, and the praying of a man in the depths of the night. Then he (peace be upon him) recited: [Those] who forsake their beds, to invoke their Lord in fear and hope, and they spend (charity in Allah cause) out of what We have bestowed on them. No person knows what is kept hidden for them of joy as a reward for what they used to do [as-Sajdah, 16-17].
    Then he (peace be upon him) said, Shall I not inform you of the head of the matter, its pillar and its peak? I said, Yes, O Messenger of Allah. He (peace be upon him) said, The head of the matter is Islam, its pillar is the prayer and its peak is jihad. Then he (peace be upon him) said, Shall I not tell you of the foundation of all of that? I said, Yes, O Messenger of Allah. So he took hold of his tongue and said, Restrain this. I said, O Prophet of Allah, will we be taken to account for what we say with it? He (peace be upon him) said, May your mother be bereaved of you, O Muadh Is there anything that throws people into the Hellfire upon their faces, or on their noses, except the harvests of their tongues?',
        'reference' => 'Tirmidhi'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي ثَعْلَبَةَ الْخُشَنِيِّ جُرْثُومِ بن نَاشِر رَضِيَ اللهُ عَنْهُ عَنْ رَسُولِ اللَّهِ صلى الله عليه و سلم قَال:
    إنَّ اللَّهَ تَعَالَى فَرَضَ فَرَائِضَ فَلَا تُضَيِّعُوهَا، وَحَدَّ حُدُودًا فَلَا تَعْتَدُوهَا، وَحَرَّمَ أَشْيَاءَ فَلَا تَنْتَهِكُوهَا، وَسَكَتَ عَنْ أَشْيَاءَ رَحْمَةً لَكُمْ غَيْرَ نِسْيَانٍ فَلَا تَبْحَثُوا عَنْهَا',
        'english_translation' => 'On the authority of Jurthum bin Nashir (may Allah be pleased with him) that the Messenger of Allah (peace be upon him) said:
    Verily Allah the Almighty has laid down religious obligations (faraid), so do not neglect them. He has set boundaries, so do not overstep them. He has prohibited some things, so do not violate them; about some things He was silent, out of compassion for you, not forgetfulness, so seek not after them.',
        'reference' => 'Daraqutni'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي الْعَبَّاسِ سَهْلِ بْنِ سَعْدٍ السَّاعِدِيّ رَضِيَ اللهُ عَنْهُ قَالَ:
    جَاءَ رَجُلٌ إلَى النَّبِيِّ صلى الله عليه و سلم فَقَالَ: يَا رَسُولَ اللهِ! دُلَّنِي عَلَى عَمَلٍ إذَا عَمِلْتُهُ أَحَبَّنِي اللهُ وَأَحَبَّنِي النَّاسُ؛ فَقَالَ: "ازْهَدْ فِي الدُّنْيَا يُحِبَّك اللهُ، وَازْهَدْ فِيمَا عِنْدَ النَّاسِ يُحِبَّك النَّاسُ',
        'english_translation' => 'On the authority of Sahl bin Saad al-Saidi (may Allah be pleased with him) who said:
    A man came to the Prophet (peace be upon him) and said: "O Messenger of Allah, direct me to an act which, if I do it, [will cause] Allah to love me and the people to love me." So he (peace be upon him) said, "Renounce the world and Allah will love you, and renounce what people possess and the people will love you.',
        'reference' => 'Ibn Majah'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي سَعِيدٍ سَعْدِ بْنِ مَالِكِ بْنِ سِنَانٍ الْخُدْرِيّ رَضِيَ اللهُ عَنْهُ أَنَّ رَسُولَ اللَّهِ صلى الله عليه و سلم قَالَ:
    لَا ضَرَرَ وَلَا ضِرَارَ',
        'english_translation' => 'It was related on the authority of Abu Said Sad bin Malik bin Sinan al-Khudri (ra) that the Messenger of Allah (sas) said:
    There should be neither harming nor reciprocating harm.',
        'reference' => 'Ibn Majah, Al-Daraqutni'
    ],
    [
        'arabic_text' => 'عَنْ ابْنِ عَبَّاسٍ رَضِيَ اللَّهُ عَنْهُمَا أَنَّ رَسُولَ اللَّهِ صلى الله عليه و سلم قَالَ:
    لَوْ يُعْطَى النَّاسُ بِدَعْوَاهُمْ لَادَّعَى رِجَالٌ أَمْوَالَ قَوْمٍ وَدِمَاءَهُمْ، لَكِنَّ الْبَيِّنَةَ عَلَى الْمُدَّعِي، وَالْيَمِينَ عَلَى مَنْ أَنْكَرَ',
        'english_translation' => 'On the authority of Ibn Abbas (may Allah be pleased with him), that the Messenger of Allah (peace be upon him) said:
    Were people to be given everything that they claimed, men would [unjustly] claim the wealth and lives of [other] people. But, the onus of proof is upon the claimant, and the taking of an oath is upon him who denies.',
        'reference' => 'Baihaqi'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي سَعِيدٍ الْخُدْرِيّ رَضِيَ اللهُ عَنْهُ قَالَ سَمِعْت رَسُولَ اللَّهِ صلى الله عليه و سلم يَقُولُ:
    مَنْ رَأَى مِنْكُمْ مُنْكَرًا فَلْيُغَيِّرْهُ بِيَدِهِ، فَإِنْ لَمْ يَسْتَطِعْ فَبِلِسَانِهِ، فَإِنْ لَمْ يَسْتَطِعْ فَبِقَلْبِهِ، وَذَلِكَ أَضْعَفُ الْإِيمَانِ',
        'english_translation' => 'On the authority of Abu Saeed al-Khudree (ra) who said: I heard the Messenger of Allah (saw) say,
    Whoso- ever of you sees an evil, let him change it with his hand; and if he is not able to do so, then [let him change it] with his tongue; and if he is not able to do so, then with his heart — and that is the weakest of faith.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَةَ رَضِيَ اللهُ عَنْهُ قَالَ:
    قَالَ رَسُولُ اللَّهِ صلى الله عليه و سلم " لَا تَحَاسَدُوا، وَلَا تَنَاجَشُوا، وَلَا تَبَاغَضُوا، وَلَا تَدَابَرُوا، وَلَا يَبِعْ بَعْضُكُمْ عَلَى بَيْعِ بَعْضٍ، وَكُونُوا عِبَادَ اللَّهِ إخْوَانًا، الْمُسْلِمُ أَخُو الْمُسْلِمِ، لَا يَظْلِمُهُ، وَلَا يَخْذُلُهُ، وَلَا يَكْذِبُهُ، وَلَا يَحْقِرُهُ، التَّقْوَى هَاهُنَا، وَيُشِيرُ إلَى صَدْرِهِ ثَلَاثَ مَرَّاتٍ، بِحَسْبِ امْرِئٍ مِنْ الشَّرِّ أَنْ يَحْقِرَ أَخَاهُ الْمُسْلِمَ، كُلُّ الْمُسْلِمِ عَلَى الْمُسْلِمِ حَرَامٌ: دَمُهُ وَمَالُهُ وَعِرْضُهُ',
        'english_translation' => 'On the authority of Abu Hurayrah (ra) who said:
    The Messenger of Allah (saw) said, “Do not envy one another, and do not inflate prices for one another, and do not hate one another, and do not turn away from one another, and do not undercut one another in trade, but [rather] be slaves of Allah and brothers [amongst yourselves]. A Muslim is the brother of a Muslim: he does not oppress him, nor does he fail him, nor does he lie to him, nor does he hold him in contempt. Taqwa (piety) is right here [and he pointed to his chest three times]. It is evil enough for a man to hold his brother Muslim in contempt. The whole of a Muslim is inviolable for another Muslim: his blood, his property, and his honour.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَةَ رَضِيَ اللهُ عَنْهُ عَنْ النَّبِيِّ صلى الله عليه و سلم قَالَ:
    مَنْ نَفَّسَ عَنْ مُؤْمِنٍ كُرْبَةً مِنْ كُرَبِ الدُّنْيَا نَفَّسَ اللَّهُ عَنْهُ كُرْبَةً مِنْ كُرَبِ يَوْمِ الْقِيَامَةِ، وَمَنْ يَسَّرَ عَلَى مُعْسِرٍ، يَسَّرَ اللَّهُ عَلَيْهِ فِي الدُّنْيَا وَالْآخِرَةِ، وَمَنْ سَتَرَ مُسْلِما سَتَرَهُ اللهُ فِي الدُّنْيَا وَالْآخِرَةِ ، وَاَللَّهُ فِي عَوْنِ الْعَبْدِ مَا كَانَ الْعَبْدُ فِي عَوْنِ أَخِيهِ، وَمَنْ سَلَكَ طَرِيقًا يَلْتَمِسُ فِيهِ عِلْمًا سَهَّلَ اللَّهُ لَهُ بِهِ طَرِيقًا إلَى الْجَنَّةِ، وَمَا اجْتَمَعَ قَوْمٌ فِي بَيْتٍ مِنْ بُيُوتِ اللَّهِ يَتْلُونَ كِتَابَ اللَّهِ، وَيَتَدَارَسُونَهُ فِيمَا بَيْنَهُمْ؛ إلَّا نَزَلَتْ عَلَيْهِمْ السَّكِينَةُ، وَغَشِيَتْهُمْ الرَّحْمَةُ، وَ حَفَّتهُمُ المَلاَئِكَة، وَذَكَرَهُمْ اللَّهُ فِيمَنْ عِنْدَهُ، وَمَنْ أَبَطْأَ بِهِ عَمَلُهُ لَمْ يُسْرِعْ بِهِ نَسَبُهُ',
        'english_translation' => 'On the authority of Abu Hurayrah (may Allah be pleased with him), that the Prophet (peace be upon him) said:
    Whoever removes a worldly grief from a believer, Allah will remove from him one of the griefs of the Day of Resurrection. And whoever alleviates the need of a needy person, Allah will alleviate his needs in this world and the Hereafter. Whoever shields [or hides the misdeeds of] a Muslim, Allah will shield him in this world and the Hereafter. And Allah will aid His slave so long as he aids his brother. And whoever follows a path to seek knowledge therein, Allah will make easy for him a path to Paradise. No people gather together in one of the Houses of Allah, reciting the Book of Allah and studying it among themselves, except that sakeenah (tranquility) descends upon them, and mercy envelops them, and the angels surround them, and Allah mentions them amongst those who are with Him. And whoever is slowed down by his actions, will not be hastened forward by his lineage.',
        'reference' => 'Muslim'
    ],
    [
        'arabic_text' => 'عَنْ ابْنِ عَبَّاسٍ رَضِيَ اللَّهُ عَنْهُمَا عَنْ رَسُولِ اللَّهِ صلى الله عليه و سلم فِيمَا يَرْوِيهِ عَنْ رَبِّهِ تَبَارَكَ وَتَعَالَى، قَالَ:
    إنَّ اللَّهَ كَتَبَ الْحَسَنَاتِ وَالسَّيِّئَاتِ، ثُمَّ بَيَّنَ ذَلِكَ، فَمَنْ هَمَّ بِحَسَنَةٍ فَلَمْ يَعْمَلْهَا كَتَبَهَا اللَّهُ عِنْدَهُ حَسَنَةً كَامِلَةً، وَإِنْ هَمَّ بِهَا فَعَمِلَهَا كَتَبَهَا اللَّهُ عِنْدَهُ عَشْرَ حَسَنَاتٍ إلَى سَبْعِمِائَةِ ضِعْفٍ إلَى أَضْعَافٍ كَثِيرَةٍ، وَإِنْ هَمَّ بِسَيِّئَةٍ فَلَمْ يَعْمَلْهَا كَتَبَهَا اللَّهُ عِنْدَهُ حَسَنَةً كَامِلَةً، وَإِنْ هَمَّ بِهَا فَعَمِلَهَا كَتَبَهَا اللَّهُ سَيِّئَةً وَاحِدَةً',
        'english_translation' => 'On the authority of Ibn Abbas (may Allah be pleased with him), from the Messenger of Allah (peace and blessings of Allah be upon him), from what he has related from his Lord:
    Verily Allah taala has written down the good deeds and the evil deeds, and then explained it [by saying]: “Whosoever intended to perform a good deed, but did not do it, then Allah writes it down with Himself as a complete good deed. And if he intended to perform it and then did perform it, then Allah writes it down with Himself as from ten good deeds up to seven hundred times, up to many times multiplied. And if he intended to perform an evil deed, but did not do it, then Allah writes it down with Himself as a complete good deed. And if he intended it [i.e., the evil deed] and then performed it, then Allah writes it down as one evil deed.',
        'reference' => 'Bukhari & Muslim'
    ],
    [
        'arabic_text' => 'عَنْ أَبِي هُرَيْرَة رَضِيَ اللهُ عَنْهُ قَالَ:
    قَالَ رَسُول اللَّهِ صلى الله عليه و سلم إنَّ اللَّهَ تَعَالَى قَالَ: "مَنْ عَادَى لِي وَلِيًّا فَقْد آذَنْتهُ بِالْحَرْبِ، وَمَا تَقَرَّبَ إلَيَّ عَبْدِي بِشَيْءٍ أَحَبَّ إلَيَّ مِمَّا افْتَرَضْتُهُ عَلَيْهِ، وَلَا يَزَالُ عَبْدِي يَتَقَرَّبُ إلَيَّ بِالنَّوَافِلِ حَتَّى أُحِبَّهُ، فَإِذَا أَحْبَبْتُهُ كُنْت سَمْعَهُ الَّذِي يَسْمَعُ بِهِ، وَبَصَرَهُ الَّذِي يُبْصِرُ بِهِ، وَيَدَهُ الَّتِي يَبْطِشُ بِهَا، وَرِجْلَهُ الَّتِي يَمْشِي بِهَا، وَلَئِنْ سَأَلَنِي لَأُعْطِيَنَّهُ، وَلَئِنْ اسْتَعَاذَنِي لَأُعِيذَنَّهُ',
        'english_translation' => 'On the authority of Abu Hurayrah (ra) who said:
    The Messenger of Allah (saw) said, “Verily Allah taala has said: Whosoever shows enmity to a wali (friend) of Mine, then I have declared war against him. And My servant does not draw near to Me with anything more loved to Me than the religious duties I have obligated upon him. And My servant continues to draw near to me with nafil (supererogatory) deeds until I Love him. When I Love him, I am his hearing with which he hears, and his sight with which he sees, and his hand with which he strikes, and his foot with which he walks. Were he to ask [something] of Me, I would surely give it to him; and were he to seek refuge with Me, I would surely grant him refuge.',
        'reference' => 'Bukhari'
    ],
    [
        'arabic_text' => 'عَنْ ابْنِ عَبَّاسٍ رَضِيَ اللَّهُ عَنْهُمَا أَنَّ رَسُولَ اللَّهِ صلى الله عليه و سلم قَالَ:
    إنَّ اللَّهَ تَجَاوَزَ لِي عَنْ أُمَّتِي الْخَطَأَ وَالنِّسْيَانَ وَمَا اسْتُكْرِهُوا عَلَيْهِ',
        'english_translation' => 'On the authority of Ibn Abbas (may Allah be pleased with him), the Messenger of Allah (peace be upon him) said:
    Verily Allah has pardoned for me my ummah: their mistakes, their forgetfulness, and that which they have been forced to do under duress',
        'reference' => 'Ibn Majah'
    ],
    [
        'arabic_text' => 'عَنْ ابْن عُمَرَ رَضِيَ اللَّهُ عَنْهُمَا قَالَ: أَخَذَ رَسُولُ اللَّهِ صلى الله عليه و سلم بِمَنْكِبِي، وَقَالَ
        :كُنْ فِي الدُّنْيَا كَأَنَّك غَرِيبٌ أَوْ عَابِرُ سَبِيلٍ".
وَكَانَ ابْنُ عُمَرَ رَضِيَ اللَّهُ عَنْهُمَا يَقُولُ:
    إذَا أَمْسَيْتَ فَلَا تَنْتَظِرْ الصَّبَاحَ، وَإِذَا أَصْبَحْتَ فَلَا تَنْتَظِرْ الْمَسَاءَ، وَخُذْ مِنْ صِحَّتِك لِمَرَضِك، وَمِنْ حَيَاتِك لِمَوْتِك',
        'english_translation' => 'On the authority of Abdullah ibn Umar (ra), who said:
The Messenger of Allah (saw) took me by the shoulder and said,
    Be in this world as though you were a stranger or a wayfarer.
And Ibn Umar (ra) used to say,
    In the evening do not expect [to live until] the morning, and in the morning do not expect [to live until] the evening. Take [advantage of] your health before times of sickness, and [take advantage of] your life before your death.',
        'reference' => 'Bukhari'
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
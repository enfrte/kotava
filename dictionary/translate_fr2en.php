<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

// This script iterates the dictionary, where no english translation exists, and queries google translate API to translate the french words to english
class Translate 
{	
	function googleTranslate(string $sourceLang = 'fr', string $targetLang = 'en') {
		die("<h1>Comment out this line if you want to run this script</h1>");
		
		// connect to sqlite kt_fr_dictionary_production.db
		$db = new PDO('sqlite:kt_fr_dictionary_production.db');

		$sql = 'SELECT * 
				FROM dictionary
				WHERE english IS NULL';

		$stmt = $db->query($sql);
		$empty_translations = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$counter = 0;

		// Use the Google Translate API to translate each empty translation
		foreach ($empty_translations as $empty_translation) {
			$text = $empty_translation['french'];
			$url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl={$sourceLang}&tl={$targetLang}&dt=t&q=" . urlencode($text);

			try {
				$responseBody = file_get_contents($url);
				
				if ($responseBody === false) {
					throw new \Exception("Failed to fetch translation for: " . $text);
				}

				// Google returns a complex JSON, we need to extract the translated text
				$translatedTextArray = json_decode($responseBody, true);
				$translatedEnglish = $translatedTextArray[0][0][0];

				// Update the database with the translated text
				$updateSql = 'UPDATE dictionary SET english = ? WHERE id = ?';
				$stmtUpdate = $db->prepare($updateSql);
				$stmtUpdate->execute([$translatedEnglish, $empty_translation['id']]);
				
				$counter++;
				
				sleep(1); // be respectful to google's servers

				// die('thats enough for now');
			} 
			catch (\Exception $e) {
				echo "<p>Error translating: " . $text . " - " . $e->getMessage() . "</p>";
				echo "<p>Done: " . $counter . "</p>";
			}
		}

		echo "<p>Done: " . $counter . "</p>";
	}

}

$gt = new Translate();
$gt->googleTranslate(); 

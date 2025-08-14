<?php 

// Task: Fix english dictionary entries

// Define the input and output files
$english_db = 'kotava_dictionary_production.db';
$french_db = 'kt_fr_dictionary_production.db';

// Connect to the database
$en_db = new SQLite3($english_db);
$fr_db = new SQLite3($french_db);

$en_db->exec("BEGIN TRANSACTION");
$fr_db->exec("BEGIN TRANSACTION");

// English

$en_result = $en_db->query("SELECT id, kotava FROM dictionary");
$kotavaWordsFromEn = array();

while ($row = $en_result->fetchArray(SQLITE3_ASSOC)) {
	$kotavaWordsFromEn[$row['id']] = $row['kotava'];
}

// French

$fr_result = $fr_db->query("SELECT * FROM dictionary");
$kotavaWordsFromFr = array();

while ($row = $fr_result->fetchArray(SQLITE3_ASSOC)) {
	$kotavaRowsFromFr[$row['id']] = ['kotava' => $row['kotava'], 'english' => $row['english'], 'grammar' => $row['grammar']];
	$kotavaWordsFromFr[$row['id']] = $row['kotava'];
}

// Find all items from the english en_result not found in fr_result

$mismatchedEntriesEn = array();

foreach ($kotavaWordsFromEn as $pk_id => $word) {
	if (!in_array($word, $kotavaWordsFromFr)) {
		$mismatchedEntriesEn[] = $pk_id;
	}
}

// Flag mismatched entries as likely legacy

foreach ($mismatchedEntriesEn as $pk_id) {
	$en_db->exec("UPDATE dictionary SET status_update = 'legacy' WHERE id = '$pk_id'");
}

// Find all items from the french fr_result not found in en_result

$mismatchedEntriesFr = array();

foreach ($kotavaWordsFromFr as $id => $kotava_word) {
	if (!in_array($kotava_word, $kotavaWordsFromEn)) {
		$mismatchedEntriesFr[] = $id;
	}
}

// Add French mismatched entries as new rows to English db

foreach ($mismatchedEntriesFr as $pk_id) {
	$kotava = $en_db->escapeString($kotavaRowsFromFr[$pk_id]['kotava']);
	$english = $en_db->escapeString($kotavaRowsFromFr[$pk_id]['english']);
	$grammar = $en_db->escapeString($kotavaRowsFromFr[$pk_id]['grammar']);
	$en_db->exec(
		"INSERT INTO dictionary (kotava, english, grammar, status_update) 
		VALUES ('$kotava', '$english', '$grammar', 'google translated')"
	);
}

$en_db->exec("COMMIT");
$fr_db->exec("COMMIT");

// Close the database connections
$en_db->close();
$fr_db->close();

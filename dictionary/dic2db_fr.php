<?php 

// This script converts a json dictionary file into a database format

// Define the input and output files
$inputFile = 'dic_kotava_french.json';
$outputFile = 'kt_fr_dictionary_'.date('YmdHis').'.db';

// Connect to the database
$db = new SQLite3($outputFile);

// Create the dictionary table
$db->exec("CREATE TABLE IF NOT EXISTS dictionary (id INTEGER PRIMARY KEY, kotava TEXT, english TEXT, french TEXT, grammar TEXT)");

// Read the dictionary json file and convert to an array
$json = file_get_contents($inputFile);
$data = json_decode($json, true);

$db->exec('BEGIN TRANSACTION');
$stmt = $db->prepare("INSERT INTO dictionary (kotava, french, grammar) VALUES (:kotava, :french, :grammar)");

foreach ($data as $entry) {
    $stmt->bindValue(':kotava', trim($entry['kt']), SQLITE3_TEXT);
    $stmt->bindValue(':french', trim($entry['fr']), SQLITE3_TEXT);
    $stmt->bindValue(':grammar', trim($entry['gr']), SQLITE3_TEXT);
    $stmt->execute();
    $stmt->clear();
}

$db->exec('COMMIT');

// Close the database connection
$db->close();
echo "Dictionary has been successfully converted to database format.\n";
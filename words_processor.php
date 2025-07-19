<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

function countVowels($word) {
    $vowels = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'];
    $count = 0;
    for ($i = 0; $i < strlen($word); $i++) {
        if (in_array($word[$i], $vowels)) {
            $count++;
        }
    }
    return $count;
}

function processWordsFile($filename) {
    if (!file_exists($filename)) {
        return ['error' => 'Words file not found'];
    }
    
    $content = file_get_contents($filename);
    $words = array_filter(array_map('trim', explode("\n", $content)));
    
    $wordsByVowels = [];
    $vowelCounts = [];
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $vowelCount = countVowels($word);
            
            if (!isset($wordsByVowels[$vowelCount])) {
                $wordsByVowels[$vowelCount] = [];
            }
            
            $wordsByVowels[$vowelCount][] = $word;
            $vowelCounts[] = $vowelCount;
        }
    }
    
    foreach ($wordsByVowels as $count => &$wordList) {
        usort($wordList, function($a, $b) {
            return strlen($a) - strlen($b);
        });
    }
    
    $vowelCounts = array_unique($vowelCounts);
    sort($vowelCounts);
    
    return [
        'vowelCounts' => $vowelCounts,
        'wordsByVowels' => $wordsByVowels
    ];
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = processWordsFile('words.txt');
    echo json_encode($result);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $vowelCount = isset($input['vowelCount']) ? (int)$input['vowelCount'] : 0;
    
    $result = processWordsFile('words.txt');
    
    if (isset($result['wordsByVowels'][$vowelCount])) {
        echo json_encode([
            'success' => true,
            'words' => $result['wordsByVowels'][$vowelCount],
            'count' => count($result['wordsByVowels'][$vowelCount])
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No words found with that vowel count'
        ]);
    }
}
?>

<?php
// Set the response content type to JSON
header('Content-Type:application/json');

// Set your OpenAI API key
$OPENAI_API_KEY = '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';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  // Get the topic from the POST parameters
  $topic = isset($_POST['i']) ? htmlspecialchars($_POST['i']) : '';
  
  // Check if the topic is not empty
  if ($topic != '') {
    
    // Initialize a cURL session to the OpenAI API
    $ch = curl_init();
    
    // Set the cURL options for the API request
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $OPENAI_API_KEY ?? '',
    ]);


    $data = array(
      'model' => 'text-davinci-003',
      'prompt' => 'list 10 content ideas related to "'.$topic.'"',
      'temperature' => 0.7,
      'max_tokens' => 256,
      'top_p' => 1,
      'frequency_penalty' => 0,
      'presence_penalty' => 0
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Execute the API request and get the response
    $response = curl_exec($ch);
    
    // Close the cURL session
    curl_close($ch);
    
    // Parse the JSON response from the OpenAI API
    $res = json_decode($response);
    
    // Get the ideas from the API response
    $ideas = [];
    if (isset($res->choices) && is_array($res->choices) && count($res->choices) > 0) {
      $ideas_text = str_replace("\n", "<!--DIVIDER-->", $res->choices[0]->text);
      $ideas = array_map(function($idea) {
        return preg_replace('/^\d+\.\s+/', '', $idea);
      }, explode("<!--DIVIDER-->", $ideas_text));

      if (empty($ideas)) {
        // Create an error JSON response if no ideas were found
        $json_arr = [
          'status' => 'error',
          'ideas' => [],
          'message' => 'No ideas found'
        ];
        
        // Return the JSON response to the client
        echo json_encode($json_arr, JSON_FORCE_OBJECT);
        return;
      }
    }
    else {
      // Create an error JSON response if the choices array is empty
      $json_arr = [
        'status' => 'error',
        'ideas' => [],
        'message' => 'No ideas found'
      ];
      
      // Return the JSON response to the client
      echo json_encode($json_arr, JSON_FORCE_OBJECT);
      return;
    }

    // Create the JSON response for the client
    $json_arr = [
      'status' => 'success',
      'ideas' => $ideas,
      'message' => 'Fetch success'
    ];

    // Return the JSON response to the client
    echo json_encode($json_arr, JSON_FORCE_OBJECT);
  }
}
?>

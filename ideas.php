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
    
    // Check if the API returned an error message
    if (isset($res->error)) {
        // Return an error message with a proper HTTP status code
        http_response_code(400);
        echo json_encode([
            'message' => $res->error->message
        ]);
        return;
    }
    
    // Get the ideas from the API response
    $ideas = [];
    if (isset($res->choices) && is_array($res->choices) && count($res->choices) > 0) {
        // Extract the ideas from the API response
        $ideas_text = str_replace("\n", "<!--DIVIDER-->", $res->choices[0]->text);
        $ideas = array_map(function($idea) {
            return preg_replace('/^\d+\.\s+/', '', $idea);
        }, explode("<!--DIVIDER-->", $ideas_text));
        
        // Check if any ideas were returned
        if (empty($ideas)) {
            // Create an error JSON response if no ideas were found
            $json_arr = [
                'status' => 'error',
                'ideas' => [],
                'message' => 'No ideas found'
            ];
        } else {
            // Create a success JSON response with the ideas
            $json_arr = [
                'status' => 'success',
                'ideas' => $ideas,
                'message' => 'Fetch success'
            ];
        }
    } else {
        // Create an error JSON response if the API response is not what we expect
        $json_arr = [
            'status' => 'error',
            'ideas' => [],
            'message' => 'Unexpected API response'
        ];
    }
  }
}

?>

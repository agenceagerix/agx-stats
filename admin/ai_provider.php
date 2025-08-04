<?php
/*-----------------------------------------------------------------------------------------------------/
	@version		1.2.0
	@build			31st July, 2025
	@created		31st July, 2025
	@package		JoomlaHits
	@subpackage		ai_provider.php
	@author			Hugo Dantas - Agence Agerix <https://www.agerix.fr>
	@copyright		Copyright (C) 2025. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
      __    ___  ____  __ _   ___  ____     __    ___  ____  ____  __  _  _
	 / _\  / __)(  __)(  ( \ / __)(  __)   / _\  / __)(  __)(  _ \(  )( \/ )
	/    \( (_ \ ) _) /    /( (__  ) _)   /    \( (_ \ ) _)  )   / )(  )  (
	\_/\_/ \___/(____)\_)__) \___)(____)  \_/\_/ \___/(____)(__\_)(__)(_/\_)
/------------------------------------------------------------------------------------------------------*/

class AIProvider 
{
    private $provider;
    private $apiKey;
    private $config;
    
    public function __construct($params) {
        $this->provider = $params['ai_provider'] ?? 'mistral';
        $this->config = $params;
        
        // Get API key based on provider
        if ($this->provider === 'openai') {
            $this->apiKey = getenv('OPENAI_API_KEY') ?: ($params['openai_api_key'] ?? '');
        } else {
            $this->apiKey = getenv('MISTRAL_API_KEY') ?: ($params['mistral_api_key'] ?? '');
        }
    }
    
    /**
     * Generate content using the configured AI provider
     */
    public function generateContent($prompt, $retries = 2) {
        if (empty($this->apiKey)) {
            throw new Exception("API key not configured for {$this->provider}");
        }
        
        $lastException = null;
        
        // Try with retries for rate limiting
        for ($i = 0; $i <= $retries; $i++) {
            try {
                switch ($this->provider) {
                    case 'openai':
                        return $this->callOpenAI($prompt);
                    case 'mistral':
                    default:
                        return $this->callMistral($prompt);
                }
            } catch (Exception $e) {
                $lastException = $e;
                
                // If it's a rate limit error and we have retries left, wait and retry
                if ($i < $retries && strpos($e->getMessage(), '429') !== false) {
                    $waitTime = ($i + 1) * 5; // Progressive backoff: 5s, 10s
                    error_log("OpenAI rate limit hit, waiting {$waitTime} seconds before retry " . ($i + 1));
                    sleep($waitTime);
                    continue;
                }
                
                // For other errors or no retries left, throw immediately
                throw $e;
            }
        }
        
        throw $lastException;
    }
    
    /**
     * Call Mistral AI API
     */
    private function callMistral($prompt) {
        $url = 'https://api.mistral.ai/v1/chat/completions';
        
        $payload = json_encode([
            'model' => 'mistral-small-latest',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        
        $fullResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('Mistral API connection error: ' . $curlError);
        }
        
        $response = substr($fullResponse, $headerSize);
        
        if ($httpCode !== 200) {
            $errorDetails = $this->getMistralErrorMessage($httpCode);
            throw new Exception("Mistral API error: HTTP {$httpCode}{$errorDetails}");
        }
        
        $response = trim($response);
        $response = preg_replace('/^\xEF\xBB\xBF/', '', $response);
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid Mistral API response');
        }
        
        if (isset($result['error'])) {
            throw new Exception('Mistral error: ' . $result['error']['message']);
        }
        
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Unexpected Mistral response structure');
        }
        
        return trim($result['choices'][0]['message']['content']);
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($prompt) {
        // Add a small delay to respect rate limits (especially for OpenAI)
        static $lastCallTime = 0;
        $currentTime = microtime(true);
        $timeDiff = $currentTime - $lastCallTime;
        
        // Ensure minimum 1.5 seconds between calls for OpenAI
        if ($timeDiff < 1.5) {
            usleep((1.5 - $timeDiff) * 1000000);
        }
        $lastCallTime = microtime(true);
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $model = $this->config['openai_model'] ?? 'gpt-3.5-turbo';
        
        $payload = json_encode([
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        
        $fullResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('OpenAI API connection error: ' . $curlError);
        }
        
        $response = substr($fullResponse, $headerSize);
        
        if ($httpCode !== 200) {
            $errorDetails = $this->getOpenAIErrorMessage($httpCode);
            throw new Exception("OpenAI API error: HTTP {$httpCode}{$errorDetails}");
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid OpenAI API response');
        }
        
        if (isset($result['error'])) {
            throw new Exception('OpenAI error: ' . $result['error']['message']);
        }
        
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Unexpected OpenAI response structure');
        }
        
        return trim($result['choices'][0]['message']['content']);
    }
    
    /**
     * Get Mistral error message based on HTTP code
     */
    private function getMistralErrorMessage($httpCode) {
        switch ($httpCode) {
            case 401:
                return ' - Invalid API key';
            case 422:
                return ' - Invalid request data';
            case 429:
                return ' - Rate limit exceeded. Please try again later';
            default:
                return '';
        }
    }
    
    /**
     * Get OpenAI error message based on HTTP code
     */
    private function getOpenAIErrorMessage($httpCode) {
        switch ($httpCode) {
            case 401:
                return ' - Invalid API key. Please verify your OpenAI API key is correct and active.';
            case 402:
                return ' - Insufficient credits. Please add credits to your OpenAI account or check your billing settings.';
            case 429:
                return ' - Rate limit or quota exceeded. Please wait a few minutes before trying again, or upgrade your OpenAI plan for higher limits.';
            case 503:
                return ' - OpenAI service temporarily unavailable. Please try again in a few minutes.';
            case 400:
                return ' - Bad request. The request format may be invalid.';
            case 500:
                return ' - OpenAI internal server error. Please try again later.';
            default:
                return ' - Unexpected error occurred.';
        }
    }
    
    /**
     * Get the current provider name
     */
    public function getProvider() {
        return $this->provider;
    }
}
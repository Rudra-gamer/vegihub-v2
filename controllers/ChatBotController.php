<?php

class ChatBotController extends Controller {

    private $apiKey;
    private $model = "gemini-3.1-flash-lite-preview";

    public function __construct() {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function handle() {
        // Validate CSRF
        $this->validateCsrf();

        // Get user input
        $message = trim($_POST['message'] ?? '');
        $history = json_decode($_POST['history'] ?? '[]', true);

        if (empty($message)) {
            $this->json(['success' => false, 'message' => 'Message is empty']);
        }

        if (!$this->apiKey) {
            $this->json(['success' => false, 'message' => 'AI Configuration missing. Please contact admin.']);
        }

        // Prepare context
        $systemInstruction = $this->getSystemPrompt();
        
        // Call Gemini
        $aiResponse = $this->callGemini($message, $history, $systemInstruction);

        if ($aiResponse) {
            $this->json([
                'success' => true,
                'response' => $aiResponse['text'],
                'suggestions' => $aiResponse['suggestions'] ?? []
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'VegiBot is thinking too hard... Please try again later.']);
        }
    }

    private function getSystemPrompt() {
        $user = current_user();
        $userName = $user ? $user['name'] : 'Guest';
        $userRole = $user ? $user['role'] : 'Visitor';

        return "You are VegiBot 🌿, a friendly and helpful assistant for 'Vegihub', an online marketplace for fresh vegetables, fruits, and herbs.
Your goal is to help users navigate the site, answer questions about products, shipping, and selling.

KEY INFORMATION:
- Store Name: Vegihub (Online Vegetable Marketplace).
- Mission: Connecting local farmers directly to customers.
- Current User: {$userName} ({$userRole}).
- Shipping: Deliver within 24-48 hours. Free for orders above ₹500.
- Payment: Secure online payment via Razorpay and Cash on Delivery (COD).
- Selling: Farmers can register as 'Sellers' to list their produce. Sell link: ".base_url('register?role=seller')."
- Orders: Users can track orders at ".base_url('orders')."
- Freshness Guarantee: If quality is bad at delivery, it can be returned immediately.

GUIDELINES:
- Be polite, friendly, and use emojis relevant to fresh food (🥬, 🥕, 🍎, 🌿).
- Keep responses concise but helpful.
- If you mention a feature, provide a link if possible using standard <a> tags.
- If unsure, ask the user to clarify or suggest contacting support (rudranahak1000@gmail.com).";
    }

    private function callGemini($message, $history, $systemInstruction) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Format contents for Gemini
        $contents = [];
        
        // Add history
        foreach ($history as $h) {
            $contents[] = [
                'role' => ($h['role'] === 'bot' ? 'model' : 'user'),
                'parts' => [['text' => $h['text']]]
            ];
        }

        // Add current message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        $body = [
            'contents' => $contents,
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev environments

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $httpCode !== 200) {
            error_log("Gemini API Error: " . ($err ?: $response));
            return null;
        }

        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Extract some simple suggestions if the bot asks questions (optional logic)
        $suggestions = [];
        if (strpos($text, '?') !== false) {
            // Very basic extraction of potential quick replies or just generic ones
            $suggestions = ['Tell me more', 'Track my order', 'Check fresh arrivals'];
        }

        return [
            'text' => $text,
            'suggestions' => $suggestions
        ];
    }
}

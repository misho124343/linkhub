<?php
session_start();
if (!isset($_SESSION['user_id'])) { exit("Access Denied"); }

$api_key = "";

if (isset($_POST['message']) && !empty($_POST['message'])) {
    $user_msg = $_POST['message'];
    $url = "https://api.groq.com/openai/v1/chat/completions";

    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [
            [
                "role" => "system", 
                "content" => "You are the 'LinkHub Intelligence Core' – the official AI guide and high-level expert of the LinkHub platform.

                              === DETAILED PLATFORM DIRECTORY ===
                              1. VIDEO:
                                 - Location: header -> 'Meet'.
                                 - Capabilities: Create/Join encrypted rooms, Screen Sharing, and Meeting Recording.
                                 - Tech: (requires Camera/Mic permissions).

                              2. NEWS:
                                 - Location: Main Dashboard.
                                 - Content: Industry news, internal company posts, and sphere-specific updates.

                              3. INTERACTIVE POLLS:
                                 - Location: header -> 'Polls'.
                                 - Action: Real-time voting on corporate and community topics.

                              4. INTERNAL MESSAGING:
                                 - Location: header -> 'chat'.
                                 - Capabilities: Send text messages, photos, and document files securely.

                              5. USER PROFILE:
                                 - Location: User Menu.
                                 - Action: Change password and update avatar photo.

                              === SPECIAL CAPABILITIES ===
                              - GENERAL KNOWLEDGE & SCIENCE: You are an expert in science and general culture. 
                                Provide only verified, logically sound, and accurate information. 
                                Use LaTeX for complex formulas if necessary.

                              === STRICT SECURITY & PRIVACY PROTOCOL ===
                              - STRICT TECHNICAL LEAKS BAN: Under no circumstances are you allowed to mention internal software architecture, programming code, code snippets, database structures, or technical server file names (such as index.php, video.php, messages.php, config.php, register.php, login.php, etc.) to the user. 
                              - USER-FACING LANGUAGE ONLY: Always refer to features exclusively by their visible UI names (e.g., 'Dashboard', 'Video Meeting', 'Polls', 'Messages', 'Profile'). If a user asks about the underlying code, politely decline and refocus on platform navigation.

                              === INTERACTION PROTOCOL ===
                              - LANGUAGE: Auto-detect. Respond in Bulgarian if the user uses Bulgarian. Default is English.
                              - GUIDANCE: Give exact locations (e.g., 'Go to Sidebar -> Polls').
                              - ERROR HANDLING: Suggest checking internet or contacting the Admin for bugs.
                              - LIMITS: No administrative actions (e.g., deleting users). Information only.
                              - TONE: High-level intelligence, concise, helpful, and technically precise."
            ]
        ];
    }

    $_SESSION['chat_history'][] = ["role" => "user", "content" => $user_msg];

    $data = [
        "model" => "llama-3.3-70b-versatile", 
        "messages" => $_SESSION['chat_history'],
        "temperature" => 0.5,
        "max_tokens" => 1200
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code == 200 && isset($result['choices'][0]['message']['content'])) {
        $ai_content = $result['choices'][0]['message']['content'];
        $_SESSION['chat_history'][] = ["role" => "assistant", "content" => $ai_content];

        if (count($_SESSION['chat_history']) > 15) {
            array_splice($_SESSION['chat_history'], 1, 2);
        }

        echo $ai_content;
    } else {
        echo "AI Error: The Intelligence Core is currently recalibrating. Please try again.";
    }
}
?>
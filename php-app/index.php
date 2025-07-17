<?php

// Set headers for JSON output and allow cross-origin requests (for testing purposes)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Mock data storage (in a real application, this would be a database)
// We'll use a global array for simplicity in this example.
$users = [
    [
        'id' => 1,
        'name' => 'Alice',
        'email' => 'alice@example.com'
    ],
    [
        'id' => 2,
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ],
    [
        'id' => 3,
        'name' => 'Charlie',
        'email' => 'charlie@example.com'
    ]
];

// Get the HTTP method and path
$method = $_SERVER['REQUEST_METHOD'];
$isDebug = isset($_GET['debug']) ?  filter_var($_GET['debug']) : false;
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = isset($_SERVER['REQUEST_URI']) ? explode('/', trim($url, '/')) : [];
$entity = filter_var($path[0]);
if ($isDebug) {
    http_response_code(200);
    print_r(json_encode(
        ['server' => $_SERVER, 'path' => $path, 'entity' => $entity]
    ));
    return;
}

if(empty($entity)){
    http_response_code(404);
    echo json_encode(['message' => 'Entity not found']);
    return;
}
$hasId = isset($path[1]);

// Route the request based on method and path
switch ($method) {
    case 'GET':
        if (!$hasId) {
            // GET /users - Get all users
            echo json_encode($users);
        } else {
            // GET /users/{id} - Get a single user by ID
            $userId = (int) filter_var($path[1]);
            $foundUser = null;
            foreach ($users as $user) {
                if ($user['id'] === $userId) {
                    $foundUser = $user;
                    break;
                }
            }
            if ($foundUser) {
                echo json_encode($foundUser);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'User not found']);
            }
        }
        break;

    case 'POST':
        // POST /users - Create a new user
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['name']) && isset($data['email'])) {
            // Generate a new ID (in a real app, this would be auto-incremented by DB)
            $newId = end($users)['id'] + 1;
            $newUser = [
                'id' => $newId,
                'name' => $data['name'],
                'email' => $data['email']
            ];
            $users[] = $newUser; // Add to our mock data
            http_response_code(201); // Created
            echo json_encode($newUser);
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Name and email are required']);
        }
        break;

    case 'PUT':
        // PUT /users/{id} - Update an existing user
        if ($hasId) {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = (int) filter_var($path[1]);
            $userUpdated = false;
            foreach ($users as $key => $user) {
                if ($user['id'] === $userId) {
                    if (isset($data['name'])) {
                        $users[$key]['name'] = $data['name'];
                    }
                    if (isset($data['email'])) {
                        $users[$key]['email'] = $data['email'];
                    }
                    $userUpdated = true;
                    echo json_encode($users[$key]);
                    break;
                }
            }
            if (!$userUpdated) {
                http_response_code(404);
                echo json_encode(['message' => 'User not found']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'User ID is required for update']);
        }
        break;

    case 'DELETE':
        // DELETE /users/{id} - Delete a user
        if ($hasId) {
            $userDeleted = false;
            $userId = (int) filter_var($path[1]);
            foreach ($users as $key => $user) {
                if ($user['id'] === $userId) {
                    array_splice($users, $key, 1); // Remove from mock data
                    $userDeleted = true;
                    http_response_code(204); // No Content
                    break;
                }
            }
            if (!$userDeleted) {
                http_response_code(404);
                echo json_encode(['message' => 'User not found']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'User ID is required for delete']);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}

?>

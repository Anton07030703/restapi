<?php

function getTasks($pdo){

    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; 
    $offset = ($page - 1) * $limit;
    $tasksQuery = "SELECT * FROM post WHERE 1=1"; 
    $params = [];

    if ($search) {
        $tasksQuery .= " AND title LIKE :search";
        $params[':search'] = "%" . $search . "%"; 
    }

    if ($sort) {
        if ($sort === 'due_date' || $sort === 'created_at') {
            $tasksQuery .= " ORDER BY " . $sort; 
        }
    }
    $tasksQuery .= " LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($tasksQuery);

    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val, PDO::PARAM_STR);
    }
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countQuery = "SELECT COUNT(*) FROM post WHERE 1=1";
    
    if ($search) {
        $countQuery .= " AND title LIKE :search";
    }
    $countStmt = $pdo->prepare($countQuery);

    if ($search) {
        $countStmt->bindValue(':search', "%" . $search . "%", PDO::PARAM_STR);
    }
    $countStmt->execute();
    
    echo json_encode($tasks, JSON_UNESCAPED_UNICODE);
}

function getTask($pdo,$id){
    $taskQuery = "SELECT * FROM post WHERE id = ?";
    $stmt = $pdo->prepare($taskQuery);
    $stmt->execute([$id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if($task){
        http_response_code(200);
        echo json_encode($task, JSON_UNESCAPED_UNICODE);
        
    }else{
        http_response_code(404);
        $res = [
            "status" => false,
            "message" => "Task not found"
        ];
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }
    
}

function addTask($pdo,$data){

    $title = $data['title'];
    $description = $data['description'];
    $dueDate = $data['due_date'];
    $createDate = $data['create_date'];
    $priority = $data['priority'];
    $category = $data['category'];
    $status = $data['status'];

   $stmt = $pdo->prepare("INSERT INTO `post` (`title`, `description`, `due_date`, `create_date`, `priority`, `category`, `status`) VALUES (:title, :description, :due_date, :create_date, :priority, :category, :status)");


   $stmt->execute([
       ':title' => $title,
       ':description' => $description,
       ':due_date' => $dueDate,
       ':create_date' => $createDate,
       ':priority' => $priority,
       ':category' => $category,
       ':status' => $status
   ]);

    http_response_code(201);

    $res = [
        'id' =>$pdo->lastInsertId(),
        'message' => 'Task created successfully'
    ];

    echo json_encode($res,JSON_UNESCAPED_UNICODE);
}

function updateTask($pdo,$id,$data){
    $title = $data['title'];
    $description = $data['description'];
    $dueDate = $data['due_date'];
    $createDate = $data['create_date'];
    $priority = $data['priority'];
    $category = $data['category'];
    $status = $data['status'];

 $stmt = $pdo->prepare("UPDATE post SET title = :title, description = :description, due_date = :due_date, create_date = :create_date, priority = :priority, category = :category, status = :status WHERE id = :id");
    
 $stmt->execute([
     ':title' => $title,
     ':description' => $description,
     ':due_date' => $dueDate,
     ':create_date' => $createDate,
     ':priority' => $priority,
     ':category' => $category,
     ':status' => $status,
     ':id' => $id 
 ]);

 http_response_code(200);
 $res = [
    'message' =>'Task updated successfully'
];

echo json_encode($res,JSON_UNESCAPED_UNICODE);
};

function deleteTask($pdo,$id){
    try {
        $stmt = $pdo->prepare("DELETE FROM post WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            $res = [
                'message' => 'Task deleted successfully'
            ];
        } else {
            http_response_code(404);
            $res = [
                'status' => false,
                'message' => 'NOT FOUND'
            ];
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $res = [
            'status' => false,
            'message' => 'ERROR: ' . $e->getMessage()
        ];
    }

    echo json_encode($res, JSON_UNESCAPED_UNICODE);
}
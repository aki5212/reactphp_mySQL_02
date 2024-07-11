<?php
// エラー報告を有効にする
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 許可するオリジンのリスト
$allowed_origins = ['http://localhost:3000'];

// オリジンを確認してCORSヘッダーを設定する
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// プリフライトリクエストを処理する
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database connection
$db_conn = mysqli_connect("localhost", "root", "", "reactphp");

// 接続を確認してください
if ($db_conn === false) {
    logError("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}

// エラーをログに記録する機能
function logError($message) {
    error_log($message, 3, 'errors.log');
}

// リクエストメソッドを取得する
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $path = explode('/', $_SERVER['REQUEST_URI']);

        if (isset($path[6]) && is_numeric($path[6])) {
            $pid = intval($path[6]);
            $stmt = $db_conn->prepare("SELECT * FROM productdata WHERE pid = ?");
            $stmt->bind_param("i", $pid);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if ($product) {
                echo json_encode($product);
            } else {
                echo json_encode(["error" => "Product not found."]);
            }
        } else {
            $result = $db_conn->query("SELECT * FROM productdata");
            $products = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($products);
        }
        break;

    case "POST":
        if (isset($_FILES['pfile']) && $_FILES['pfile']['error'] === UPLOAD_ERR_OK) {
            $pday = $_POST['pday'];
            $ptitle = $_POST['ptitle'];
            $pprice = $_POST['pprice'];
            $pstatus = isset($_POST['pstatus']) ? $_POST['pstatus'] : '';

            $pfile = time() . basename($_FILES['pfile']['name']);
            $pfile_temp = $_FILES['pfile']['tmp_name'];
            $pfile_destination = "uploads/" . $pfile;

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['pfile']['type'], $allowed_types) && $_FILES['pfile']['size'] <= 2 * 1024 * 1024) {
                $stmt = $db_conn->prepare("INSERT INTO productdata (pday, ptitle, pprice, pfile, pstatus) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiss", $pday, $ptitle, $pprice, $pfile, $pstatus);

                if ($stmt->execute()) {
                    move_uploaded_file($pfile_temp, $pfile_destination);
                    echo json_encode(["success" => "Product successfully added."]);
                } else {
                    logError("Failed to insert product: " . $stmt->error);
                    echo json_encode(["error" => "Failed to add product."]);
                }

                $stmt->close();
            } else {
                echo json_encode(["error" => "Invalid file type or size."]);
            }
        } else {
            echo json_encode(["error" => "File upload error or invalid input data."]);
        }
        break;

    case "DELETE":
        $path = explode('/', $_SERVER["REQUEST_URI"]);
        if (isset($path[6]) && is_numeric($path[6])) {
            $pid = intval($path[6]);
            $stmt = $db_conn->prepare("DELETE FROM productdata WHERE pid = ?");
            $stmt->bind_param("i", $pid);

            if ($stmt->execute()) {
                echo json_encode(["success" => "Product successfully deleted."]);
            } else {
                logError("Failed to delete product: " . $stmt->error);
                echo json_encode(["error" => "Failed to delete product."]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Invalid product ID."]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method."]);
        break;
}

?>
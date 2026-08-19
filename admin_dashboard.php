<?php
session_start();
require_once "connect.php";

/* Integrated login + logout for admin_dashboard.php */

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin_dashboard.php");
    exit();
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login_action'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $login_error = 'Please enter your username and password.';
    } else {
        $stmt = $conn->prepare("SELECT admin_id, full_name, username, password FROM admins WHERE username = ? LIMIT 1");

        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $admin = $result->fetch_assoc();

                if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $stmt->close();

                    header("Location: admin_dashboard.php");
                    exit();
                }
            }

            $stmt->close();
        }

        $login_error = 'Invalid username or password. Please try again.';
    }
}

if (!isset($_SESSION['admin_id'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Glow Care</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(rgba(47,33,27,.82),rgba(47,33,27,.82)),radial-gradient(circle at top right,#b88656 0%,#49362b 52%,#211915 100%);font-family:"Segoe UI",Arial,sans-serif;color:#3b2c25}
.login-shell{width:min(430px,100%);background:#fff;border:1px solid #e8ddd2;border-radius:16px;padding:38px;box-shadow:0 24px 70px rgba(0,0,0,.28)}
.brand{text-align:center;font-family:Georgia,serif;font-size:29px;color:#49362b;margin-bottom:6px}.brand span{color:#b88656}
.eyebrow{text-align:center;color:#b88656;font-size:11px;font-weight:700;letter-spacing:2.4px;text-transform:uppercase;margin-bottom:24px}
h1{text-align:center;font:400 27px Georgia,serif;color:#49362b;margin-bottom:8px}.sub{text-align:center;color:#75675d;font-size:13px;margin-bottom:26px}
.field{margin-bottom:17px}.field label{display:block;margin-bottom:7px;font-size:12px;font-weight:700;color:#5d4b41}
.field input{width:100%;padding:12px 13px;border:1px solid #d9cbc0;border-radius:7px;background:#fffdfb;color:#3b2c25;outline:none;font:inherit;font-size:13px}
.field input:focus{border-color:#b88656;box-shadow:0 0 0 3px rgba(184,134,86,.12)}
.login-btn{width:100%;border:0;border-radius:7px;padding:13px;background:#8c6239;color:#fff;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;transition:.25s}
.login-btn:hover{background:#6f4c2c;transform:translateY(-1px)}
.error{margin-bottom:18px;padding:11px 13px;border:1px solid #efcccc;border-radius:7px;background:#fff1f1;color:#a43d3d;font-size:12px;text-align:center}
.back{display:block;margin-top:18px;text-align:center;color:#8c6239;font-size:12px;text-decoration:none}.back:hover{text-decoration:underline}
.secure{margin-top:20px;padding-top:16px;border-top:1px solid #eee4da;text-align:center;color:#95857a;font-size:11px}
</style>
</head>
<body>
<div class="login-shell">
    <div class="brand">Glow <span>Care</span></div>
    <div class="eyebrow">Administration Portal</div>
    <h1>Admin Login</h1>
    <p class="sub">Sign in to access the Glow Care management dashboard.</p>

    <?php if ($login_error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>

    <form method="POST" action="admin_dashboard.php">
        <input type="hidden" name="admin_login_action" value="1">
        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="login-btn">Login to Dashboard</button>
    </form>

    <a class="back" href="admin.php">← Back to IT Support</a>
    <div class="secure">Authorized administrators only</div>
</div>
</body>
</html>
<?php
exit();
endif;

mysqli_report(MYSQLI_REPORT_OFF);

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function table_exists($conn, $table) {
    $table = $conn->real_escape_string($table);
    $r = $conn->query("SHOW TABLES LIKE '$table'");
    return $r && $r->num_rows > 0;
}
function columns_of($conn, $table) {
    $out = [];
    if (!table_exists($conn, $table)) return $out;
    $r = $conn->query("SHOW COLUMNS FROM `$table`");
    if ($r) while ($row = $r->fetch_assoc()) $out[$row['Field']] = $row;
    return $out;
}
function col_exists($cols, $name) { return isset($cols[$name]); }
function scalar($conn, $sql, $default = 0) {
    $r = $conn->query($sql);
    if ($r && ($row = $r->fetch_row())) return $row[0] ?? $default;
    return $default;
}
function money_num($value) {
    if (is_numeric($value)) return (float)$value;
    return (float)preg_replace('/[^0-9.]/', '', (string)$value);
}
function image_path($value, $type = 'product') {
    $value = trim((string)$value);
    if ($value === '') return '';
    if (preg_match('~^(https?://|data:)~i', $value)) return $value;
    if (preg_match('~^(image|images)/~i', $value)) return $value;
    return $type === 'deal' ? 'images/' . ltrim($value, '/') : 'image/' . ltrim($value, '/');
}
function redirect_tab($tab, $msg = '') {
    $url = 'admin_dashboard.php?tab=' . urlencode($tab);
    if ($msg !== '') $url .= '&msg=' . urlencode($msg);
    header('Location: ' . $url);
    exit();
}
function pdf_escape($text) {
    return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $text);
}
function simple_pdf_download($filename, $title, $lines) {
    $content = "BT\n/F1 15 Tf\n50 800 Td\n(" . pdf_escape($title) . ") Tj\n/F1 9 Tf\n0 -24 Td\n";
    $y = 0;
    foreach ($lines as $line) {
        $safe = pdf_escape(substr((string)$line, 0, 110));
        $content .= "(" . $safe . ") Tj\n0 -15 Td\n";
        $y++;
        if ($y > 45) break;
    }
    $content .= "ET";
    $objects = [];
    $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
    $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
    $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >> endobj\n";
    $objects[] = "4 0 obj << /Length " . strlen($content) . " >> stream\n$content\nendstream endobj\n";
    $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";
    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $obj) { $offsets[] = strlen($pdf); $pdf .= $obj; }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    for ($i=1;$i<=5;$i++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Content-Length: '.strlen($pdf));
    echo $pdf;
    exit();
}

// Optional support tables used only by this dashboard.
$conn->query("CREATE TABLE IF NOT EXISTS customer_skin_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    skin_issue VARCHAR(120) NOT NULL,
    notes TEXT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS customer_skin_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    photo_type ENUM('Before','After','Other') DEFAULT 'Other',
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS admin_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    channel ENUM('Email','SMS','App Push') NOT NULL,
    recipient VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NULL,
    message TEXT NOT NULL,
    schedule_at DATETIME NULL,
    status VARCHAR(40) DEFAULT 'Queued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure the existing complaints table can also store public IT-support requests.
if (table_exists($conn, 'complaints')) {
    $conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS customer_name_text VARCHAR(150) NULL");
    $conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS customer_email VARCHAR(190) NULL");
    $conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(50) NULL");
    $conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS subject VARCHAR(255) NULL");
    $conn->query("ALTER TABLE complaints ADD COLUMN IF NOT EXISTS admin_reply TEXT NULL");
}

$customerCols = columns_of($conn, 'customers');
$orderCols = columns_of($conn, 'orders');
$complaintCols = columns_of($conn, 'complaints');
$dealCols = columns_of($conn, 'deals');
$productCols = columns_of($conn, 'product');
$ordersExist = !empty($orderCols);
$complaintsExist = !empty($complaintCols);
$dealsExist = !empty($dealCols);
$productsExist = !empty($productCols);
$customersExist = !empty($customerCols);

if (empty($_SESSION['admin_csrf'])) $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
$csrf = $_SESSION['admin_csrf'];

// ---------------- EXPORTS ----------------
if (isset($_GET['export']) && in_array($_GET['export'], ['sales_csv','sales_pdf'], true)) {
    $period = $_GET['period'] ?? 'monthly';
    $dateExpr = "DATE(order_date)";
    if ($period === 'yearly') $dateExpr = "YEAR(order_date)";
    elseif ($period === 'monthly') $dateExpr = "DATE_FORMAT(order_date, '%Y-%m')";
    $rows = [];
    if ($ordersExist && col_exists($orderCols,'total_amount') && col_exists($orderCols,'order_date')) {
        $q = $conn->query("SELECT $dateExpr period_label, COUNT(*) order_count, SUM(total_amount) revenue FROM orders GROUP BY period_label ORDER BY period_label DESC LIMIT 60");
        if ($q) while ($r = $q->fetch_assoc()) $rows[] = $r;
    }
    if ($_GET['export'] === 'sales_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="glowcare_sales_'.$period.'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, ['Period','Orders','Revenue (Rs.)']);
        foreach ($rows as $r) fputcsv($out, [$r['period_label'],$r['order_count'],number_format((float)$r['revenue'],2,'.','')]);
        fclose($out); exit();
    }
    $lines = ['Period | Orders | Revenue (Rs.)'];
    foreach ($rows as $r) $lines[] = $r['period_label'].' | '.$r['order_count'].' | '.number_format((float)$r['revenue'],2);
    simple_pdf_download('glowcare_sales_'.$period.'.pdf','Glow Care Sales Report - '.ucfirst($period),$lines);
}

// ---------------- POST ACTIONS ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) die('Invalid request token. Please refresh and try again.');
    $action = $_POST['action'] ?? '';

    if ($action === 'complaint_update' && $complaintsExist) {
        $id = (int)($_POST['complaint_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'Pending');
        $reply = trim($_POST['admin_reply'] ?? '');
        if (col_exists($complaintCols,'admin_reply')) {
            $s=$conn->prepare("UPDATE complaints SET status=?, admin_reply=? WHERE complaint_id=?"); $s->bind_param('ssi',$status,$reply,$id); $s->execute(); $s->close();
        } else {
            $s=$conn->prepare("UPDATE complaints SET status=? WHERE complaint_id=?"); $s->bind_param('si',$status,$id); $s->execute(); $s->close();
        }
        redirect_tab($_POST['return_tab'] ?? 'order-complaints','Complaint updated');
    }

    if ($action === 'order_status' && $ordersExist) {
        $id=(int)$_POST['order_id']; $status=trim($_POST['status']);
        $s=$conn->prepare("UPDATE orders SET status=? WHERE order_id=?"); $s->bind_param('si',$status,$id); $s->execute(); $s->close();
        redirect_tab('orders','Order status updated');
    }

    if ($action === 'customer_update' && $customersExist) {
        $id=(int)$_POST['customer_id'];
        $allowed=['name','email','phone','address']; $sets=[]; $vals=[]; $types='';
        foreach ($allowed as $c) if (col_exists($customerCols,$c)) { $sets[]="`$c`=?"; $vals[]=trim($_POST[$c]??''); $types.='s'; }
        if ($sets) { $vals[]=$id; $types.='i'; $s=$conn->prepare("UPDATE customers SET ".implode(',',$sets)." WHERE id=?"); $s->bind_param($types,...$vals); $s->execute(); $s->close(); }
        redirect_tab('customers','Client profile updated');
    }

    if ($action === 'skin_history_add') {
        $cid=(int)$_POST['customer_id']; $issue=trim($_POST['skin_issue']); $notes=trim($_POST['notes']??'');
        if ($issue!=='') { $s=$conn->prepare("INSERT INTO customer_skin_history(customer_id,skin_issue,notes) VALUES(?,?,?)"); $s->bind_param('iss',$cid,$issue,$notes); $s->execute(); $s->close(); }
        redirect_tab('customers','Skin care history added');
    }

    if ($action === 'skin_photo_add') {
        $cid=(int)$_POST['customer_id']; $ptype=$_POST['photo_type']??'Other'; $caption=trim($_POST['caption']??'');
        if (isset($_FILES['skin_photo']) && $_FILES['skin_photo']['error']===UPLOAD_ERR_OK) {
            $ext=strtolower(pathinfo($_FILES['skin_photo']['name'],PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','webp'],true) && $_FILES['skin_photo']['size'] <= 5*1024*1024) {
                $dir='uploads/skin_history'; if (!is_dir($dir)) @mkdir($dir,0775,true);
                $name='skin_'.$cid.'_'.time().'_'.bin2hex(random_bytes(3)).'.'.$ext; $path=$dir.'/'.$name;
                if (move_uploaded_file($_FILES['skin_photo']['tmp_name'],$path)) { $s=$conn->prepare("INSERT INTO customer_skin_photos(customer_id,photo_type,image_path,caption) VALUES(?,?,?,?)"); $s->bind_param('isss',$cid,$ptype,$path,$caption); $s->execute(); $s->close(); }
            }
        }
        redirect_tab('customers','Client photo uploaded');
    }

    if ($action === 'deal_save' && $dealsExist) {
        $mode=$_POST['mode']??'add'; $name=trim($_POST['name']); $price=(float)$_POST['price']; $old=$_POST['old_price']===''?null:(float)$_POST['old_price'];
        $size=trim($_POST['size']); $img=trim($_POST['image_url']); $desc=trim($_POST['description']??''); $max=$_POST['max_qty']===''?null:(int)$_POST['max_qty']; $section=trim($_POST['section_type']);
        if ($mode==='edit') { $id=(int)$_POST['id']; $s=$conn->prepare("UPDATE deals SET name=?,price=?,old_price=?,size=?,image_url=?,description=?,max_qty=?,section_type=? WHERE id=?"); $s->bind_param('sddsssisi',$name,$price,$old,$size,$img,$desc,$max,$section,$id); }
        else { $id=(int)($_POST['id']??0); $s=$conn->prepare("INSERT INTO deals(id,name,price,old_price,size,image_url,description,max_qty,section_type) VALUES(?,?,?,?,?,?,?,?,?)"); $s->bind_param('isddsssis',$id,$name,$price,$old,$size,$img,$desc,$max,$section); }
        $s->execute(); $s->close(); redirect_tab('deals',$mode==='edit'?'Deal updated':'Deal added');
    }

    if ($action === 'deal_delete' && $dealsExist) { $id=(int)$_POST['id']; $s=$conn->prepare("DELETE FROM deals WHERE id=?"); $s->bind_param('i',$id); $s->execute(); $s->close(); redirect_tab('deals','Deal deleted'); }

    if ($action === 'product_save' && $productsExist) {
        $mode=$_POST['mode']??'add'; $name=trim($_POST['P_name']); $image=trim($_POST['image']); $cat=trim($_POST['category']); $sub=trim($_POST['sub_category']); $type=trim($_POST['Skin_Hair_type']); $price=trim($_POST['P_price']); $qty=trim($_POST['P_quantity']); $stock=trim($_POST['In_stock']); $guide=trim($_POST['guide']); $benefits=trim($_POST['benifits']);
        if ($mode==='edit') { $id=(int)$_POST['P_id']; $s=$conn->prepare("UPDATE product SET P_name=?,image=?,category=?,sub_category=?,`Skin/Hair_type`=?,P_price=?,P_quantity=?,In_stock=?,guide=?,benifits=? WHERE P_id=?"); $s->bind_param('ssssssssssi',$name,$image,$cat,$sub,$type,$price,$qty,$stock,$guide,$benefits,$id); }
        else { $s=$conn->prepare("INSERT INTO product(P_name,image,category,sub_category,`Skin/Hair_type`,P_price,P_quantity,In_stock,guide,benifits) VALUES(?,?,?,?,?,?,?,?,?,?)"); $s->bind_param('ssssssssss',$name,$image,$cat,$sub,$type,$price,$qty,$stock,$guide,$benefits); }
        $s->execute(); $s->close(); redirect_tab('products',$mode==='edit'?'Product updated':'Product added');
    }

    if ($action === 'product_delete' && $productsExist) { $id=(int)$_POST['P_id']; $s=$conn->prepare("DELETE FROM product WHERE P_id=?"); $s->bind_param('i',$id); $s->execute(); $s->close(); redirect_tab('products','Product deleted'); }

    if ($action === 'notification_add') {
        $channel=$_POST['channel']; $recipient=trim($_POST['recipient']); $subject=trim($_POST['subject']??''); $message=trim($_POST['message']); $schedule=trim($_POST['schedule_at']??''); $schedule=$schedule!==''?str_replace('T',' ',$schedule).':00':null;
        $s=$conn->prepare("INSERT INTO admin_notifications(channel,recipient,subject,message,schedule_at) VALUES(?,?,?,?,?)"); $s->bind_param('sssss',$channel,$recipient,$subject,$message,$schedule); $s->execute(); $s->close();
        redirect_tab('notifications','Notification queued');
    }
}

$tab = $_GET['tab'] ?? 'overview';
$allowedTabs=['overview','orders','order-complaints','public-complaints','deals','products','customers','analytics','reports','notifications'];
if (!in_array($tab,$allowedTabs,true)) $tab='overview';
$msg=$_GET['msg']??'';

// Admin profile
$adminId=(int)$_SESSION['admin_id'];
$adminInfo=['full_name'=>$_SESSION['admin_name']??'Admin','email'=>'','contact_no'=>''];
if (table_exists($conn,'admins')) { $s=$conn->prepare("SELECT full_name,email,contact_no FROM admins WHERE admin_id=? LIMIT 1"); if($s){$s->bind_param('i',$adminId);$s->execute();$r=$s->get_result();if($r&&$r->num_rows)$adminInfo=array_merge($adminInfo,$r->fetch_assoc());$s->close();} }

$totalCustomers=$customersExist?(int)scalar($conn,"SELECT COUNT(*) FROM customers",0):0;
$totalProducts=$productsExist?(int)scalar($conn,"SELECT COUNT(*) FROM product",0):0;
$totalDeals=$dealsExist?(int)scalar($conn,"SELECT COUNT(*) FROM deals",0):0;
$totalOrders=$ordersExist?(int)scalar($conn,"SELECT COUNT(*) FROM orders",0):0;
$totalRevenue=($ordersExist&&col_exists($orderCols,'total_amount'))?(float)scalar($conn,"SELECT COALESCE(SUM(total_amount),0) FROM orders",0):0;
$pendingComplaints=($complaintsExist&&col_exists($complaintCols,'status'))?(int)scalar($conn,"SELECT COUNT(*) FROM complaints WHERE status<>'Resolved'",0):0;

$orders=$ordersExist?$conn->query("SELECT o.*".($customersExist?", c.name customer_name, c.email customer_email":"")." FROM orders o ".($customersExist?"LEFT JOIN customers c ON o.customer_id=c.id ":"")."ORDER BY ".(col_exists($orderCols,'order_date')?'o.order_date':'o.order_id')." DESC"):null;
$complaintCols = columns_of($conn, 'complaints');
$complaintsExist = !empty($complaintCols);

if ($complaintsExist) {
    $selectExtras = "";
    $joinCustomers = "";

    if ($customersExist && col_exists($complaintCols, 'customer_id')) {
        $joinCustomers = " LEFT JOIN customers c ON cp.customer_id=c.id ";
        $nameExpr  = col_exists($complaintCols, 'customer_name_text') ? "COALESCE(c.name, cp.customer_name_text)" : "c.name";
        $emailExpr = col_exists($complaintCols, 'customer_email') ? "COALESCE(c.email, cp.customer_email)" : "c.email";
        $phoneExpr = col_exists($complaintCols, 'customer_phone') ? "COALESCE(c.phone, cp.customer_phone)" : "c.phone";
        $selectExtras = ", $nameExpr customer_name, $emailExpr customer_email, $phoneExpr customer_phone";
    } else {
        if (col_exists($complaintCols, 'customer_name_text')) $selectExtras .= ", cp.customer_name_text customer_name";
        if (col_exists($complaintCols, 'customer_email')) $selectExtras .= ", cp.customer_email customer_email";
        if (col_exists($complaintCols, 'customer_phone')) $selectExtras .= ", cp.customer_phone customer_phone";
    }

    $orderBy = col_exists($complaintCols, 'created_at') ? 'cp.created_at' : 'cp.complaint_id';
    $complaints = $conn->query("SELECT cp.* $selectExtras FROM complaints cp $joinCustomers ORDER BY $orderBy DESC");
} else {
    $complaints = null;
}
$deals=$dealsExist?$conn->query("SELECT * FROM deals ORDER BY id DESC"):null;
$products=$productsExist?$conn->query("SELECT * FROM product ORDER BY P_id DESC"):null;
$customers=$customersExist?$conn->query("SELECT * FROM customers ORDER BY id DESC"):null;
$historyRows=$conn->query("SELECT h.*, c.name customer_name FROM customer_skin_history h LEFT JOIN customers c ON h.customer_id=c.id ORDER BY h.recorded_at DESC LIMIT 100");
$photoRows=$conn->query("SELECT p.*, c.name customer_name FROM customer_skin_photos p LEFT JOIN customers c ON p.customer_id=c.id ORDER BY p.uploaded_at DESC LIMIT 100");
$notifications=$conn->query("SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 100");

// Editing data
$editDeal=null; if(isset($_GET['edit_deal'])&&$dealsExist){$id=(int)$_GET['edit_deal'];$r=$conn->query("SELECT * FROM deals WHERE id=$id");if($r&&$r->num_rows)$editDeal=$r->fetch_assoc();}
$editProduct=null; if(isset($_GET['edit_product'])&&$productsExist){$id=(int)$_GET['edit_product'];$r=$conn->query("SELECT * FROM product WHERE P_id=$id");if($r&&$r->num_rows)$editProduct=$r->fetch_assoc();}
$editCustomer=null; if(isset($_GET['edit_customer'])&&$customersExist){$id=(int)$_GET['edit_customer'];$r=$conn->query("SELECT * FROM customers WHERE id=$id");if($r&&$r->num_rows)$editCustomer=$r->fetch_assoc();}

// Analytics: skin issues
$skinLabels=[];$skinValues=[];$r=$conn->query("SELECT skin_issue,COUNT(*) total FROM customer_skin_history GROUP BY skin_issue ORDER BY total DESC LIMIT 8");if($r)while($x=$r->fetch_assoc()){$skinLabels[]=$x['skin_issue'];$skinValues[]=(int)$x['total'];}
// Top-selling products parsed from order products text such as "Product x2, Other x1"
$topMap=[]; if($ordersExist&&col_exists($orderCols,'products')){$r=$conn->query("SELECT products FROM orders");if($r)while($x=$r->fetch_assoc()){foreach(preg_split('/[,;\n]+/',$x['products']) as $piece){$piece=trim($piece);if($piece==='')continue;$qty=1;$name=$piece;if(preg_match('/^(.*?)\s+x\s*(\d+)$/i',$piece,$m)){ $name=trim($m[1]);$qty=(int)$m[2]; }elseif(preg_match('/^(.*?)\s*\(\s*(\d+)\s*\)$/',$piece,$m)){ $name=trim($m[1]);$qty=(int)$m[2]; }$topMap[$name]=($topMap[$name]??0)+$qty;}}} arsort($topMap);$topMap=array_slice($topMap,0,8,true);$topLabels=array_keys($topMap);$topValues=array_values($topMap);
// Revenue last 6 months
$revLabels=[];$revValues=[]; if($ordersExist&&col_exists($orderCols,'order_date')&&col_exists($orderCols,'total_amount')){$r=$conn->query("SELECT DATE_FORMAT(order_date,'%Y-%m') m,SUM(total_amount) revenue FROM orders WHERE order_date>=DATE_SUB(CURDATE(),INTERVAL 5 MONTH) GROUP BY m ORDER BY m");if($r)while($x=$r->fetch_assoc()){$revLabels[]=$x['m'];$revValues[]=(float)$x['revenue'];}}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Glow Care</title>
<style>
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:#f7f2ec;color:#3b2c25;font-family:"Segoe UI",Arial,sans-serif;line-height:1.5}a{text-decoration:none;color:inherit}button,input,select,textarea{font:inherit}.layout{display:grid;grid-template-columns:255px minmax(0,1fr);min-height:100vh}.sidebar{position:sticky;top:0;height:100vh;overflow:auto;background:#2f211b;color:#fff;padding:26px 18px}.brand{font-family:Georgia,serif;font-size:25px;margin:0 10px 28px}.brand span{color:#d6a779}.admin-mini{margin:0 7px 24px;padding:14px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);border-radius:10px}.admin-mini strong{display:block}.admin-mini small{color:#cbb8aa}.nav-label{margin:20px 10px 7px;color:#a99486;font-size:10px;letter-spacing:1.5px;text-transform:uppercase}.nav a{display:flex;align-items:center;gap:10px;padding:11px 12px;margin:4px 0;border-radius:7px;color:#d9cec7;font-size:13px;transition:.2s}.nav a:hover,.nav a.active{background:#8c6239;color:#fff}.nav .ico{width:19px;text-align:center}.side-bottom{margin-top:24px}.logout{display:block;padding:11px 12px;border:1px solid rgba(255,255,255,.13);border-radius:7px;color:#e7d9d0;font-size:13px}.main{min-width:0}.topbar{position:sticky;top:0;z-index:20;display:flex;justify-content:space-between;align-items:center;gap:20px;padding:18px 28px;background:rgba(255,255,255,.94);border-bottom:1px solid #e8ddd2;backdrop-filter:blur(10px)}.topbar h1{margin:0;font:400 27px Georgia,serif;color:#49362b}.topbar p{margin:2px 0 0;color:#85746a;font-size:12px}.top-actions{display:flex;gap:8px;align-items:center}.pill{padding:8px 11px;background:#f2e7db;border-radius:20px;color:#6f4c2c;font-size:12px}.content{padding:28px;max-width:1650px;margin:auto}.msg{margin-bottom:18px;padding:12px 15px;border:1px solid #cfe2d1;background:#eff8ef;color:#2f6c3a;border-radius:8px;font-size:13px}.hero{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:22px;padding:28px;border-radius:14px;color:#fff;background:linear-gradient(115deg,#49362b,#6f4c2c 58%,#b88656)}.hero h2{margin:0 0 7px;font:400 30px Georgia,serif}.hero p{max-width:720px;margin:0;color:#eee3da;font-size:13px}.hero-badge{min-width:140px;text-align:center;padding:16px;border:1px solid rgba(255,255,255,.18);border-radius:10px;background:rgba(255,255,255,.08)}.hero-badge strong{display:block;font-size:24px}.stats{display:grid;grid-template-columns:repeat(6,minmax(140px,1fr));gap:14px;margin-bottom:23px}.stat{padding:18px;background:#fff;border:1px solid #e8ddd2;border-radius:11px;box-shadow:0 6px 18px rgba(71,50,35,.04)}.stat .icon{font-size:20px}.stat .num{margin:8px 0 2px;font-size:25px;font-weight:700;color:#8c6239}.stat .lbl{font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:#85746a}.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.panel{margin-bottom:20px;background:#fff;border:1px solid #e8ddd2;border-radius:12px;box-shadow:0 7px 22px rgba(71,50,35,.04);overflow:hidden}.panel-head{display:flex;justify-content:space-between;align-items:center;gap:15px;padding:18px 20px;border-bottom:1px solid #eee4da}.panel-head h3{margin:0;font:400 20px Georgia,serif;color:#49362b}.panel-head p{margin:3px 0 0;color:#88786e;font-size:12px}.panel-body{padding:20px}.table-wrap{overflow:auto}.data-table{width:100%;border-collapse:collapse;min-width:760px}.data-table th,.data-table td{padding:12px 13px;border-bottom:1px solid #f0e7df;text-align:left;vertical-align:top;font-size:12px}.data-table th{position:sticky;top:0;background:#fcfaf8;color:#69584e;font-size:10px;text-transform:uppercase;letter-spacing:.6px}.data-table tr:hover td{background:#fffdfa}.thumb{width:54px;height:54px;object-fit:cover;border-radius:8px;border:1px solid #e8ddd2;background:#f7f2ec}.product-name{font-weight:700;color:#49362b}.muted{color:#8b7d74}.badge{display:inline-block;padding:5px 8px;border-radius:20px;font-size:10px;font-weight:700;background:#eee6df;color:#6d5b50}.badge.pending{background:#fff3cd;color:#856404}.badge.progress,.badge.shipped{background:#e3efff;color:#315f92}.badge.resolved,.badge.delivered,.badge.yes{background:#e5f4e8;color:#397046}.badge.cancelled,.badge.no{background:#fde8e8;color:#944}.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 13px;border:0;border-radius:6px;background:#8c6239;color:#fff;font-size:11px;font-weight:700;cursor:pointer;transition:.2s}.btn:hover{background:#6f4c2c;transform:translateY(-1px)}.btn.secondary{background:#f2e7db;color:#6f4c2c}.btn.danger{background:#a4554f}.btn.outline{background:#fff;color:#8c6239;border:1px solid #b88656}.btn.small{padding:6px 9px;font-size:10px}.form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}.form-grid.three{grid-template-columns:repeat(3,1fr)}.field{display:flex;flex-direction:column;gap:6px}.field.full{grid-column:1/-1}.field label{font-size:11px;font-weight:700;color:#5d4b41}.field input,.field select,.field textarea{width:100%;padding:10px 11px;border:1px solid #d9cbc0;border-radius:6px;background:#fffdfb;color:#3b2c25;outline:none;font-size:12px}.field input:focus,.field select:focus,.field textarea:focus{border-color:#b88656;box-shadow:0 0 0 3px rgba(184,134,86,.1)}.actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:15px}.reply-box{min-width:240px}.reply-box textarea{width:100%;min-height:70px;padding:8px;border:1px solid #ddd0c4;border-radius:6px;font-size:11px}.chart-card{padding:18px;background:#fff;border:1px solid #e8ddd2;border-radius:12px}.chart-card h4{margin:0 0 12px;font:400 18px Georgia,serif}.chart-card canvas{width:100%;height:280px}.kpi{padding:18px;border-left:3px solid #b88656;background:#fcfaf8}.kpi strong{font-size:22px;color:#8c6239}.photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px}.photo-card{border:1px solid #e8ddd2;border-radius:9px;overflow:hidden;background:#fff}.photo-card img{width:100%;height:120px;object-fit:cover}.photo-card div{padding:9px;font-size:11px}.empty{padding:30px;text-align:center;color:#8b7d74}.section-note{padding:12px 14px;background:#fcfaf8;border-left:3px solid #b88656;color:#75675d;font-size:12px;margin-bottom:15px}.report-actions{display:flex;flex-wrap:wrap;gap:10px}.mobile-menu{display:none}.modal-bg{display:none;position:fixed;inset:0;z-index:100;background:rgba(33,25,21,.55);padding:30px;overflow:auto}.modal-bg.open{display:block}.modal{max-width:720px;margin:40px auto;background:#fff;border-radius:12px;padding:22px}.modal-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:16px}.modal-head h3{margin:0;font:400 22px Georgia,serif}.close{border:0;background:none;font-size:25px;cursor:pointer}.search{min-width:230px;padding:8px 10px;border:1px solid #d9cbc0;border-radius:6px;background:#fff}.quick-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.quick{padding:16px;border:1px solid #e8ddd2;border-radius:9px;background:#fcfaf8}.quick strong{display:block;margin-bottom:4px;color:#49362b}.quick span{font-size:11px;color:#7d6f65}
@media(max-width:1250px){.stats{grid-template-columns:repeat(3,1fr)}.grid-3{grid-template-columns:1fr 1fr}.quick-row{grid-template-columns:1fr 1fr}}
@media(max-width:900px){.layout{grid-template-columns:1fr}.sidebar{position:fixed;left:-280px;z-index:80;width:255px;transition:.25s}.sidebar.open{left:0}.mobile-menu{display:inline-flex}.grid-2,.grid-3{grid-template-columns:1fr}.content{padding:18px}.topbar{padding:14px 18px}.stats{grid-template-columns:1fr 1fr}.hero{align-items:flex-start;flex-direction:column}.form-grid,.form-grid.three{grid-template-columns:1fr}.hero-badge{width:100%}}
@media(max-width:560px){.stats,.quick-row{grid-template-columns:1fr}.top-actions .pill{display:none}.content{padding:12px}.panel-body{padding:14px}.photo-grid{grid-template-columns:1fr 1fr}}
@media print{.sidebar,.topbar,.btn,.report-actions{display:none!important}.layout{display:block}.content{padding:0}.panel{box-shadow:none}}
</style>
</head>
<body>
<div class="layout">
<aside class="sidebar" id="sidebar">
    <div class="brand">Glow <span>Care</span></div>
    <div class="admin-mini"><strong><?=e($adminInfo['full_name'])?></strong><small>Administrator</small></div>
    <div class="nav">
        <div class="nav-label">Dashboard</div>
        <a class="<?=$tab==='overview'?'active':''?>" href="?tab=overview"><span class="ico">⌂</span>Overview</a>
        <div class="nav-label">Commerce</div>
        <a class="<?=$tab==='orders'?'active':''?>" href="?tab=orders"><span class="ico">🛒</span>Customer Orders</a>
        <a class="<?=$tab==='order-complaints'?'active':''?>" href="?tab=order-complaints"><span class="ico">↩</span>Order Complaints</a>
        <a class="<?=$tab==='public-complaints'?'active':''?>" href="?tab=public-complaints"><span class="ico">💬</span>Public Complaints</a>
        <a class="<?=$tab==='deals'?'active':''?>" href="?tab=deals"><span class="ico">🏷</span>Deals</a>
        <a class="<?=$tab==='products'?'active':''?>" href="?tab=products"><span class="ico">✦</span>Products</a>
        <div class="nav-label">Clients</div>
        <a class="<?=$tab==='customers'?'active':''?>" href="?tab=customers"><span class="ico">👥</span>User & Client Management</a>
        <div class="nav-label">Insights</div>
        <a class="<?=$tab==='analytics'?'active':''?>" href="?tab=analytics"><span class="ico">▥</span>Analytics</a>
        <a class="<?=$tab==='reports'?'active':''?>" href="?tab=reports"><span class="ico">📄</span>Reports</a>
        <a class="<?=$tab==='notifications'?'active':''?>" href="?tab=notifications"><span class="ico">🔔</span>Notifications</a>
    </div>
    <div class="side-bottom"><a class="logout" href="admin_dashboard.php?logout=1">↪ Logout</a></div>
</aside>
<main class="main">
<header class="topbar">
    <div><button class="btn secondary mobile-menu" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button> <h1 style="display:inline-block"><?=e(ucwords(str_replace('-',' ',$tab)))?></h1><p>Glow Care management console</p></div>
    <div class="top-actions"><span class="pill">Pending complaints: <?=$pendingComplaints?></span><a class="btn outline" href="home.php" target="_blank">View Website ↗</a></div>
</header>
<div class="content">
<?php if($msg!==''):?><div class="msg">✓ <?=e($msg)?></div><?php endif;?>

<?php if($tab==='overview'):?>
<section class="hero"><div><h2>Good to see you, <?=e($adminInfo['full_name'])?>.</h2><p>Manage Glow Care orders, support requests, products, clients, analytics and notifications from one dashboard.</p></div><div class="hero-badge"><small>Total Revenue</small><strong>Rs. <?=number_format($totalRevenue,0)?></strong></div></section>
<div class="stats">
<div class="stat"><div class="icon">👥</div><div class="num"><?=$totalCustomers?></div><div class="lbl">Customers</div></div>
<div class="stat"><div class="icon">🛒</div><div class="num"><?=$totalOrders?></div><div class="lbl">Orders</div></div>
<div class="stat"><div class="icon">✦</div><div class="num"><?=$totalProducts?></div><div class="lbl">Products</div></div>
<div class="stat"><div class="icon">🏷</div><div class="num"><?=$totalDeals?></div><div class="lbl">Deals</div></div>
<div class="stat"><div class="icon">💬</div><div class="num"><?=$pendingComplaints?></div><div class="lbl">Open Complaints</div></div>
<div class="stat"><div class="icon">Rs.</div><div class="num"><?=number_format($totalRevenue/1000,1)?>K</div><div class="lbl">Revenue</div></div>
</div>
<div class="quick-row">
<a class="quick" href="?tab=orders"><strong>Customer Orders</strong><span>Review and update delivery status.</span></a>
<a class="quick" href="?tab=order-complaints"><strong>Reply to Complaints</strong><span>Respond to order-related issues.</span></a>
<a class="quick" href="?tab=products"><strong>Manage Products</strong><span>Add, edit, view images and stock.</span></a>
<a class="quick" href="?tab=analytics"><strong>Analytics & Reports</strong><span>Track skin issues, sales and revenue.</span></a>
</div>
<div class="grid-2" style="margin-top:20px"><div class="chart-card"><h4>Revenue — Last 6 Months</h4><canvas id="revChart"></canvas></div><div class="chart-card"><h4>Top Selling Products</h4><canvas id="topChart"></canvas></div></div>

<?php elseif($tab==='orders'):?>
<div class="panel"><div class="panel-head"><div><h3>Customer Orders</h3><p>Track purchases, payments and delivery progress.</p></div><input class="search" placeholder="Search orders..." oninput="filterRows(this,'ordersTable')"></div><div class="table-wrap"><table class="data-table" id="ordersTable"><thead><tr><th>Order</th><th>Customer</th><th>Products</th><th>Total</th><th>Payment</th><th>Date</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php if($orders&&$orders->num_rows): while($r=$orders->fetch_assoc()):?><tr><td>#<?=e($r['order_id'])?></td><td><strong><?=e($r['customer_name']??('Customer #'.($r['customer_id']??'')))?></strong><br><span class="muted"><?=e($r['customer_email']??'')?></span></td><td style="max-width:300px"><?=e($r['products']??'')?></td><td>Rs. <?=number_format((float)($r['total_amount']??0),2)?></td><td><?=e($r['payment_status']??'-')?></td><td><?=e(isset($r['order_date'])?date('d M Y, h:i A',strtotime($r['order_date'])):'-')?></td><td><span class="badge <?=strtolower(str_replace(' ','',$r['status']??''))?>"><?=e($r['status']??'-')?></span></td><td><form method="post"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="order_status"><input type="hidden" name="order_id" value="<?=e($r['order_id'])?>"><select name="status" onchange="this.form.submit()"><option <?=($r['status']??'')==='Pending Delivery'?'selected':''?>>Pending Delivery</option><option <?=($r['status']??'')==='Shipped'?'selected':''?>>Shipped</option><option <?=($r['status']??'')==='Delivered'?'selected':''?>>Delivered</option><option <?=($r['status']??'')==='Cancelled'?'selected':''?>>Cancelled</option></select></form></td></tr><?php endwhile; else:?><tr><td colspan="8" class="empty">No orders found.</td></tr><?php endif;?>
</tbody></table></div></div>

<?php elseif($tab==='order-complaints'||$tab==='public-complaints'):?>
<?php $wantOrder=$tab==='order-complaints'; ?>
<div class="panel">
    <div class="panel-head">
        <div>
            <h3><?=$wantOrder?'Order Complaints':'Public Complaints'?></h3>
            <p><?=$wantOrder?'Reply to complaints connected to an order.':'View public support requests submitted through the IT Support page.'?></p>
        </div>
        <input class="search" placeholder="Search complaints..." oninput="filterRows(this,'complaintsTable')">
    </div>

    <div class="table-wrap">
        <table class="data-table" id="complaintsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Order</th>
                    <th>Subject / Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <?php if($wantOrder): ?><th>Admin Reply</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if($complaints&&$complaints->num_rows): ?>
                <?php $shown=0; while($r=$complaints->fetch_assoc()):
                    $hasOrder=!empty($r['order_id']);
                    if($hasOrder!==$wantOrder) continue;
                    $shown++;
                ?>
                <tr>
                    <td>#<?=e($r['complaint_id']??'')?></td>
                    <td>
                        <strong><?=e($r['customer_name']??$r['customer_name_text']??$r['name']??('Customer #'.($r['customer_id']??'')))?></strong><br>
                        <span class="muted"><?=e($r['customer_email']??$r['email']??'')?> <?=e($r['customer_phone']??$r['phone']??'')?></span>
                    </td>
                    <td><?=$hasOrder?'#'.e($r['order_id']):'<span class="muted">Public</span>'?></td>
                    <td style="max-width:330px">
                        <?php if(!empty($r['subject'])):?><strong><?=e($r['subject'])?></strong><br><?php endif;?>
                        <?=e($r['message']??'')?>
                    </td>
                    <td><?=e(isset($r['created_at'])?date('d M Y, h:i A',strtotime($r['created_at'])):'-')?></td>
                    <td><span class="badge <?=strtolower(str_replace(' ','',$r['status']??'pending'))?>"><?=e($r['status']??'Pending')?></span></td>

                    <?php if($wantOrder): ?>
                    <td>
                        <form method="post" class="reply-box">
                            <input type="hidden" name="csrf" value="<?=$csrf?>">
                            <input type="hidden" name="action" value="complaint_update">
                            <input type="hidden" name="complaint_id" value="<?=e($r['complaint_id'])?>">
                            <input type="hidden" name="return_tab" value="<?=e($tab)?>">
                            <textarea name="admin_reply" placeholder="Write a reply to the customer..."><?=e($r['admin_reply']??'')?></textarea>
                            <div class="actions">
                                <select name="status">
                                    <option <?=($r['status']??'')==='Pending'?'selected':''?>>Pending</option>
                                    <option <?=($r['status']??'')==='In Progress'?'selected':''?>>In Progress</option>
                                    <option <?=($r['status']??'')==='Resolved'?'selected':''?>>Resolved</option>
                                </select>
                                <button class="btn small">Save Reply</button>
                            </div>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>

                <?php if(!$shown): ?>
                    <tr><td colspan="<?=$wantOrder?7:6?>" class="empty">No <?=$wantOrder?'order':'public'?> complaints found.</td></tr>
                <?php endif; ?>
            <?php else: ?>
                <tr><td colspan="<?=$wantOrder?7:6?>" class="empty">Complaints table is empty or unavailable.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</tbody></table></div></div>

<?php elseif($tab==='deals'):?>
<div class="grid-2"><div class="panel"><div class="panel-head"><div><h3><?=$editDeal?'Edit Deal':'Add New Deal'?></h3><p>Use image names from the existing images folder.</p></div></div><div class="panel-body"><form method="post"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="deal_save"><input type="hidden" name="mode" value="<?=$editDeal?'edit':'add'?>"><div class="form-grid">
<div class="field"><label>Deal ID *</label><input type="number" name="id" required <?=$editDeal?'readonly':''?> value="<?=e($editDeal['id']??'')?>"></div><div class="field"><label>Name *</label><input name="name" required value="<?=e($editDeal['name']??'')?>"></div><div class="field"><label>Price *</label><input type="number" step="0.01" name="price" required value="<?=e($editDeal['price']??'')?>"></div><div class="field"><label>Old Price</label><input type="number" step="0.01" name="old_price" value="<?=e($editDeal['old_price']??'')?>"></div><div class="field"><label>Size *</label><input name="size" required value="<?=e($editDeal['size']??'')?>"></div><div class="field"><label>Section *</label><select name="section_type" required><?php foreach(['large_volume','heavy_weight','bundle'] as $s):?><option value="<?=$s?>" <?=($editDeal['section_type']??'')===$s?'selected':''?>><?=e($s)?></option><?php endforeach;?></select></div><div class="field full"><label>Image URL / filename *</label><input name="image_url" required value="<?=e($editDeal['image_url']??'')?>" placeholder="large-shampoo.jpg"></div><div class="field full"><label>Description</label><textarea name="description" rows="3"><?=e($editDeal['description']??'')?></textarea></div><div class="field"><label>Max Quantity</label><input type="number" name="max_qty" value="<?=e($editDeal['max_qty']??'')?>"></div></div><div class="actions"><button class="btn"><?=$editDeal?'Update Deal':'Add Deal'?></button><?php if($editDeal):?><a class="btn secondary" href="?tab=deals">Cancel</a><?php endif;?></div></form></div></div>
<div class="panel"><div class="panel-head"><div><h3>Deal Preview</h3><p>Images resolve from the project <b>images/</b> folder.</p></div></div><div class="panel-body"><div class="section-note">Existing deals are displayed below with image previews. If an image is missing, check the saved filename/path.</div></div></div></div>
<div class="panel"><div class="panel-head"><div><h3>View Deals</h3><p>Edit or remove current offers.</p></div><input class="search" placeholder="Search deals..." oninput="filterRows(this,'dealsTable')"></div><div class="table-wrap"><table class="data-table" id="dealsTable"><thead><tr><th>Image</th><th>ID</th><th>Name</th><th>Price</th><th>Old Price</th><th>Size</th><th>Section</th><th>Actions</th></tr></thead><tbody><?php if($deals&&$deals->num_rows):while($r=$deals->fetch_assoc()):?><tr><td><img class="thumb" src="<?=e(image_path($r['image_url'],'deal'))?>" onerror="this.style.opacity=.15"></td><td><?=e($r['id'])?></td><td class="product-name"><?=e($r['name'])?></td><td>Rs. <?=number_format((float)$r['price'],2)?></td><td><?=!empty($r['old_price'])?'Rs. '.number_format((float)$r['old_price'],2):'-'?></td><td><?=e($r['size'])?></td><td><span class="badge"><?=e($r['section_type'])?></span></td><td><div class="actions"><a class="btn small secondary" href="?tab=deals&edit_deal=<?=e($r['id'])?>">Edit</a><form method="post" onsubmit="return confirm('Delete this deal?')"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="deal_delete"><input type="hidden" name="id" value="<?=e($r['id'])?>"><button class="btn small danger">Delete</button></form></div></td></tr><?php endwhile;else:?><tr><td colspan="8" class="empty">No deals found.</td></tr><?php endif;?></tbody></table></div></div>

<?php elseif($tab==='products'):?>
<div class="panel"><div class="panel-head"><div><h3><?=$editProduct?'Edit Product':'Add New Product'?></h3><p>Create and update product catalogue items.</p></div></div><div class="panel-body"><form method="post"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="product_save"><input type="hidden" name="mode" value="<?=$editProduct?'edit':'add'?>"><?php if($editProduct):?><input type="hidden" name="P_id" value="<?=e($editProduct['P_id'])?>"><?php endif;?><div class="form-grid three"><div class="field"><label>Product Name *</label><input name="P_name" required value="<?=e($editProduct['P_name']??'')?>"></div><div class="field"><label>Image Path *</label><input name="image" required value="<?=e($editProduct['image']??'')?>" placeholder="image/foam cleanser.jpeg"></div><div class="field"><label>Category *</label><input name="category" required value="<?=e($editProduct['category']??'')?>"></div><div class="field"><label>Sub Category *</label><input name="sub_category" required value="<?=e($editProduct['sub_category']??'')?>"></div><div class="field"><label>Skin / Hair Type *</label><input name="Skin_Hair_type" required value="<?=e($editProduct['Skin/Hair_type']??'')?>"></div><div class="field"><label>Price *</label><input name="P_price" required value="<?=e($editProduct['P_price']??'')?>" placeholder="Rs. 4,800.00"></div><div class="field"><label>Quantity *</label><input name="P_quantity" required value="<?=e($editProduct['P_quantity']??'')?>" placeholder="150ml"></div><div class="field"><label>In Stock *</label><select name="In_stock"><option <?=($editProduct['In_stock']??'')==='Yes'?'selected':''?>>Yes</option><option <?=($editProduct['In_stock']??'')==='No'?'selected':''?>>No</option></select></div><div class="field full"><label>Usage Guide</label><textarea name="guide" rows="2"><?=e($editProduct['guide']??'')?></textarea></div><div class="field full"><label>Benefits</label><textarea name="benifits" rows="2"><?=e($editProduct['benifits']??'')?></textarea></div></div><div class="actions"><button class="btn"><?=$editProduct?'Update Product':'Add Product'?></button><?php if($editProduct):?><a class="btn secondary" href="?tab=products">Cancel</a><?php endif;?></div></form></div></div>
<div class="panel"><div class="panel-head"><div><h3>View Products</h3><p>Product images, details, stock and editing tools.</p></div><input class="search" placeholder="Search products..." oninput="filterRows(this,'productsTable')"></div><div class="table-wrap"><table class="data-table" id="productsTable"><thead><tr><th>Image</th><th>ID</th><th>Name</th><th>Category</th><th>Type</th><th>Price</th><th>Qty</th><th>Stock</th><th>Actions</th></tr></thead><tbody><?php if($products&&$products->num_rows):while($r=$products->fetch_assoc()):?><tr><td><img class="thumb" src="<?=e(image_path($r['image'],'product'))?>" onerror="this.style.opacity=.15"></td><td><?=e($r['P_id'])?></td><td><span class="product-name"><?=e($r['P_name'])?></span><br><span class="muted"><?=e($r['sub_category'])?></span></td><td><?=e($r['category'])?></td><td><?=e($r['Skin/Hair_type'])?></td><td><?=e($r['P_price'])?></td><td><?=e($r['P_quantity'])?></td><td><span class="badge <?=strtolower(e($r['In_stock']))?>"><?=e($r['In_stock'])?></span></td><td><div class="actions"><a class="btn small secondary" href="?tab=products&edit_product=<?=e($r['P_id'])?>">Edit</a><form method="post" onsubmit="return confirm('Delete this product?')"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="product_delete"><input type="hidden" name="P_id" value="<?=e($r['P_id'])?>"><button class="btn small danger">Delete</button></form></div></td></tr><?php endwhile;else:?><tr><td colspan="9" class="empty">No products found.</td></tr><?php endif;?></tbody></table></div></div>

<?php elseif($tab==='customers'):?>
<?php if($editCustomer):?><div class="panel"><div class="panel-head"><div><h3>Edit Client Profile</h3><p>Update customer contact information.</p></div></div><div class="panel-body"><form method="post"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="customer_update"><input type="hidden" name="customer_id" value="<?=e($editCustomer['id'])?>"><div class="form-grid"><?php foreach(['name'=>'Name','email'=>'Email','phone'=>'Phone','address'=>'Address'] as $c=>$label):if(col_exists($customerCols,$c)):?><div class="field"><label><?=$label?></label><input name="<?=$c?>" value="<?=e($editCustomer[$c]??'')?>"></div><?php endif;endforeach;?></div><div class="actions"><button class="btn">Save Client Profile</button><a class="btn secondary" href="?tab=customers">Cancel</a></div></form></div></div><?php endif;?>
<div class="panel"><div class="panel-head"><div><h3>User & Client Management</h3><p>View profiles, edit details and inspect skin-care records.</p></div><input class="search" placeholder="Search clients..." oninput="filterRows(this,'customersTable')"></div><div class="table-wrap"><table class="data-table" id="customersTable"><thead><tr><th>ID</th><th>Profile</th><th>Contact</th><th>Address</th><th>Registered</th><th>Skin History</th><th>Actions</th></tr></thead><tbody><?php if($customers&&$customers->num_rows):while($r=$customers->fetch_assoc()): $hc=(int)scalar($conn,"SELECT COUNT(*) FROM customer_skin_history WHERE customer_id=".(int)$r['id'],0);?><tr><td>#<?=e($r['id'])?></td><td><strong><?=e($r['name']??'')?></strong></td><td><?=e($r['email']??'')?><br><span class="muted"><?=e($r['phone']??'')?></span></td><td><?=e($r['address']??'-')?></td><td><?=e(isset($r['created_at'])?date('d M Y',strtotime($r['created_at'])):'-')?></td><td><span class="badge"><?=$hc?> records</span></td><td><div class="actions"><a class="btn small secondary" href="?tab=customers&edit_customer=<?=e($r['id'])?>">Edit</a><button class="btn small outline" type="button" onclick="openSkin(<?=e($r['id'])?>,'<?=e(addslashes($r['name']??'Client'))?>')">History / Photos</button></div></td></tr><?php endwhile;else:?><tr><td colspan="7" class="empty">No customers found.</td></tr><?php endif;?></tbody></table></div></div>
<div class="grid-2"><div class="panel"><div class="panel-head"><div><h3>Recent Skin Care History</h3></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Client</th><th>Issue</th><th>Notes</th><th>Date</th></tr></thead><tbody><?php if($historyRows&&$historyRows->num_rows):while($r=$historyRows->fetch_assoc()):?><tr><td><?=e($r['customer_name']??('#'.$r['customer_id']))?></td><td><span class="badge"><?=e($r['skin_issue'])?></span></td><td><?=e($r['notes'])?></td><td><?=e(date('d M Y',strtotime($r['recorded_at'])))?></td></tr><?php endwhile;else:?><tr><td colspan="4" class="empty">No skin history yet.</td></tr><?php endif;?></tbody></table></div></div><div class="panel"><div class="panel-head"><div><h3>Before / After Photos</h3></div></div><div class="panel-body"><div class="photo-grid"><?php if($photoRows&&$photoRows->num_rows):while($r=$photoRows->fetch_assoc()):?><div class="photo-card"><img src="<?=e($r['image_path'])?>"><div><strong><?=e($r['customer_name']??'Client')?></strong><br><?=e($r['photo_type'])?> · <?=e($r['caption'])?></div></div><?php endwhile;else:?><div class="empty">No photos uploaded yet.</div><?php endif;?></div></div></div></div>

<?php elseif($tab==='analytics'):?>
<div class="grid-3"><div class="chart-card"><h4>Skin Issues Trends</h4><canvas id="skinChart"></canvas></div><div class="chart-card"><h4>Top Selling Products</h4><canvas id="topChart"></canvas></div><div class="chart-card"><h4>Revenue Trend</h4><canvas id="revChart"></canvas></div></div>
<div class="grid-3" style="margin-top:20px"><div class="kpi"><span class="muted">Most common issue</span><br><strong><?=e($skinLabels[0]??'No data')?></strong></div><div class="kpi"><span class="muted">Top selling item</span><br><strong><?=e($topLabels[0]??'No data')?></strong></div><div class="kpi"><span class="muted">Total sales revenue</span><br><strong>Rs. <?=number_format($totalRevenue,2)?></strong></div></div>

<?php elseif($tab==='reports'):?>
<div class="panel"><div class="panel-head"><div><h3>Revenue & Sales Reports</h3><p>Export daily, monthly or yearly sales summaries.</p></div></div><div class="panel-body"><div class="section-note">CSV downloads open directly in Excel. PDF exports are generated by this single PHP file without an extra library.</div><div class="grid-3"><?php foreach(['daily','monthly','yearly'] as $p):?><div class="chart-card"><h4><?=ucfirst($p)?> Report</h4><p class="muted">Orders and revenue grouped by <?=$p?> period.</p><div class="report-actions"><a class="btn" href="?export=sales_csv&period=<?=$p?>">Export CSV</a><a class="btn secondary" href="?export=sales_pdf&period=<?=$p?>">Export PDF</a></div></div><?php endforeach;?></div></div></div>

<?php elseif($tab==='notifications'):?>
<div class="grid-2"><div class="panel"><div class="panel-head"><div><h3>Notification Center</h3><p>Prepare Email, SMS or App Push reminders and alerts.</p></div></div><div class="panel-body"><div class="section-note">Messages are queued in the dashboard database. Actual external SMS/email/push delivery needs the relevant service/API credentials.</div><form method="post"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="notification_add"><div class="form-grid"><div class="field"><label>Channel</label><select name="channel"><option>Email</option><option>SMS</option><option>App Push</option></select></div><div class="field"><label>Recipient</label><input name="recipient" required placeholder="email, phone or user ID"></div><div class="field full"><label>Subject</label><input name="subject" placeholder="Appointment Reminder / Routine Alert"></div><div class="field full"><label>Message</label><textarea name="message" rows="5" required></textarea></div><div class="field"><label>Schedule Time</label><input type="datetime-local" name="schedule_at"></div></div><div class="actions"><button class="btn">Queue Notification</button></div></form></div></div><div class="panel"><div class="panel-head"><div><h3>Notification Summary</h3></div></div><div class="panel-body"><div class="stats" style="grid-template-columns:repeat(2,1fr)"><div class="stat"><div class="num"><?=(int)scalar($conn,"SELECT COUNT(*) FROM admin_notifications",0)?></div><div class="lbl">Total Queued</div></div><div class="stat"><div class="num"><?=(int)scalar($conn,"SELECT COUNT(*) FROM admin_notifications WHERE schedule_at IS NOT NULL",0)?></div><div class="lbl">Scheduled</div></div></div></div></div></div>
<div class="panel"><div class="panel-head"><div><h3>Notification Queue</h3></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Channel</th><th>Recipient</th><th>Subject</th><th>Message</th><th>Schedule</th><th>Status</th></tr></thead><tbody><?php if($notifications&&$notifications->num_rows):while($r=$notifications->fetch_assoc()):?><tr><td><span class="badge"><?=e($r['channel'])?></span></td><td><?=e($r['recipient'])?></td><td><?=e($r['subject'])?></td><td><?=e($r['message'])?></td><td><?=e($r['schedule_at']??'-')?></td><td><?=e($r['status'])?></td></tr><?php endwhile;else:?><tr><td colspan="6" class="empty">No notifications queued.</td></tr><?php endif;?></tbody></table></div></div>
<?php endif;?>
</div>
</main>
</div>

<div class="modal-bg" id="skinModal"><div class="modal"><div class="modal-head"><h3 id="skinModalTitle">Client Skin Care</h3><button class="close" onclick="closeSkin()">×</button></div><div class="grid-2"><form method="post"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="skin_history_add"><input type="hidden" name="customer_id" class="skinCustomerId"><div class="field"><label>Skin Issue</label><select name="skin_issue" required><option>Acne</option><option>Hyperpigmentation</option><option>Dryness</option><option>Oily Skin</option><option>Sensitivity</option><option>Hair Fall</option><option>Dandruff</option><option>Other</option></select></div><div class="field" style="margin-top:10px"><label>History / Notes</label><textarea name="notes" rows="5" placeholder="Skin-care history, observations, routine notes..."></textarea></div><div class="actions"><button class="btn">Add History</button></div></form><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="action" value="skin_photo_add"><input type="hidden" name="customer_id" class="skinCustomerId"><div class="field"><label>Photo Type</label><select name="photo_type"><option>Before</option><option>After</option><option>Other</option></select></div><div class="field" style="margin-top:10px"><label>Photo (JPG/PNG/WEBP, max 5MB)</label><input type="file" name="skin_photo" accept="image/jpeg,image/png,image/webp" required></div><div class="field" style="margin-top:10px"><label>Caption</label><input name="caption"></div><div class="actions"><button class="btn">Upload Photo</button></div></form></div></div></div>
<script>
function filterRows(input,id){const q=input.value.toLowerCase();document.querySelectorAll('#'+id+' tbody tr').forEach(tr=>tr.style.display=tr.innerText.toLowerCase().includes(q)?'':'none')}
function openSkin(id,name){document.querySelectorAll('.skinCustomerId').forEach(x=>x.value=id);document.getElementById('skinModalTitle').textContent=name+' — Skin Care History & Photos';document.getElementById('skinModal').classList.add('open')}
function closeSkin(){document.getElementById('skinModal').classList.remove('open')}
document.getElementById('skinModal').addEventListener('click',e=>{if(e.target.id==='skinModal')closeSkin()});
function drawBars(id,labels,values){const c=document.getElementById(id);if(!c)return;const dpr=window.devicePixelRatio||1,w=c.clientWidth||500,h=280;c.width=w*dpr;c.height=h*dpr;const x=c.getContext('2d');x.scale(dpr,dpr);x.clearRect(0,0,w,h);const pad={l:42,r:15,t:20,b:58},cw=w-pad.l-pad.r,ch=h-pad.t-pad.b,max=Math.max(...values,1);x.font='11px Segoe UI';x.fillStyle='#7d6f65';x.strokeStyle='#e8ddd2';for(let i=0;i<=4;i++){let y=pad.t+ch*i/4;x.beginPath();x.moveTo(pad.l,y);x.lineTo(w-pad.r,y);x.stroke();let val=Math.round(max*(1-i/4));x.fillText(val,5,y+4)}const bw=cw/Math.max(labels.length,1)*.62;labels.forEach((lab,i)=>{let bh=(values[i]/max)*ch,bx=pad.l+(i+.19)*cw/labels.length,by=pad.t+ch-bh;x.fillStyle='#8c6239';x.fillRect(bx,by,bw,bh);x.save();x.translate(bx+bw/2,h-8);x.rotate(-.55);x.fillStyle='#66564c';x.textAlign='right';x.fillText(String(lab).slice(0,18),0,0);x.restore()})}
const skinLabels=<?=json_encode($skinLabels)?>,skinValues=<?=json_encode($skinValues)?>,topLabels=<?=json_encode($topLabels)?>,topValues=<?=json_encode($topValues)?>,revLabels=<?=json_encode($revLabels)?>,revValues=<?=json_encode($revValues)?>;
function drawAll(){drawBars('skinChart',skinLabels,skinValues);drawBars('topChart',topLabels,topValues);drawBars('revChart',revLabels,revValues)}drawAll();window.addEventListener('resize',()=>{clearTimeout(window._ct);window._ct=setTimeout(drawAll,150)});
</script>
</body>
</html>
<?php $conn->close(); ?>
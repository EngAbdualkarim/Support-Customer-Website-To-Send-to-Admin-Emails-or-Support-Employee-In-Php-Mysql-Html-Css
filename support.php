<?php

$conn = mysqli_connect('localhost:3307','root','', 'feedback');
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);

    $file_name = $_FILES['attachment']['name'];
    $file_path = "uploads/".basename($_FILES["attachment"]["name"]);

    if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $file_path)) {
        $sql = "INSERT INTO feedbacks (name, email, message, type, file_name) 
        VALUES ('$name', '$email', '$message', '$type', '$file_name')";

        if ($conn->query($sql) === TRUE) {
            // Send email
            $to = "abdu324432@gmail.com";
            $subject = "New Feedback Submitted";
            $boundary = md5(time());
            
            $headers = "From: $email\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
            
            $body = "--$boundary\r\n";
            $body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
            $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $body .= "Name: $name\r\n";
            $body .= "Email: $email\r\n";
            $body .= "Message: $message\r\n\r\n";
            
            $file_content = file_get_contents($file_path);
            $file_content = chunk_split(base64_encode($file_content));
            
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: application/octet-stream; name=\"$file_name\"\r\n";
            $body .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= $file_content."\r\n";
            $body .= "--$boundary--";
            
            if (mail($to, $subject, $body, $headers)) {
                echo '<script>alert("تم إرسال المعلومات بنجاح!")</script>';
            } else {
                echo '<script>alert("حدث خطأ أثناء إرسال المعلومات، يرجى المحاولة مرة أخرى!")</script>';
            }

            echo '<script>window.location.href = "support.html";</script>';
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "There was an error uploading your attachment.";
    }
}

$conn->close();
?>

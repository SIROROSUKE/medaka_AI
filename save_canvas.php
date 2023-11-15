<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] == 0) {
        $file = $_FILES['imageFile']['tmp_name'];
        $file_type = $_FILES['imageFile']['type'];
        $filename = "pic.jpeg";
        move_uploaded_file($file, $filename);
        $show_form = true;
        $input = 'pic.jpeg';
        $output = 'img.jpeg';
        $new_width = 244;
        $new_height = 244;
        list($width, $height) = getimagesize($input);
        $new_image = imagecreatetruecolor($new_width, $new_height);
        $image = imagecreatefromjpeg($input);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($new_image, $output, 100);
        imagedestroy($new_image);
        ?>
        <img id="image" src="./pic.jpeg" alt="選択した画像" width=224 height=224>
        <?php
        $command = "python C:/xampp/htdocs/use_AI.py";
        exec($command, $output);
        ?>
        <p><?php print "$output[3]\n"; ?></p>
        <p><?php print "$output[4]\n"; ?></p>
        <p><?php print "$output[5]\n"; ?></p>
        <p><?php print "$output[6]\n"; ?></p>
        <p><?php print "$output[7]\n"; ?></p>
    <?php
    } else {
        echo "ファイルがアップロードされませんでした。";
    }
}
?>
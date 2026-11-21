<?php
$postPhoto = $_FILES['imgUpload'];
$postDes = $_POST['imgDes'];
$total_count = count($postPhoto['name']);
for( $i=0 ; $i < $total_count ; $i++ ) {
   $tmpFilePath = $postPhoto['tmp_name'][$i];
   $file_ext = pathinfo($postPhoto['name'][$i], PATHINFO_EXTENSION);
   if ($tmpFilePath != ""){
      $newFilePath = "./uploads/" . "merchimg_1_".$i.".". $file_ext;
      if(move_uploaded_file($tmpFilePath, $newFilePath)) {






        echo $postDes[$i];
        echo "<br>";
      }
   }
}

?>
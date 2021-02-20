<?php 
function pixelintocm($pixel, $dpiAxes)
{
    return $pixel * 2.5/$dpiAxes;
}
function get_dpi($filename){
   $imgae_info = getimagesize($filename);
   $image = null;
   switch ($imgae_info['mime']) {
        case "image/gif":
            $image = imagecreatefromgif($filename);
            break;
        case "image/jpeg":
            $image = imagecreatefromjpeg($filename);
            break;
        case "image/png":
            $image = imagecreatefrompng($filename);
            break;
        case "image/bmp":
            $image = imagecreatefrombmp($filename);
            break; 
   }
   return imageresolution($image);
}
function checking_image_dem($filename){
    $minWidth = 4;
    $minHeight = 2;
    $maxWidth  = 29.7;
    $maxHeight = 21;
    $errorInImageSize  = array();

    $image = getimagesize($filename);
    $dpi = get_dpi($filename);
    $image_width = pixelintocm($image[0],$dpi[0]);
    $image_height = pixelintocm($image[1],$dpi[1]);
    if($image_width < $minWidth){
        $errorInImageSize['image_width'] = "Width of Image is less than ".$minWidth." cm as width of image is ".$image_width;
        $errorInImageSize['image_size'] = 0;
    }
    if ($image_width > $maxWidth) {
        $errorInImageSize['image_width'] = "Width of Image is greater than ".$maxWidth." cm as width of image is ".$image_width;
        $errorInImageSize['image_size'] = 0;
    }
    if($image_height < $minHeight){
        $errorInImageSize['image_height'] = "Height of Image is less than ".$minHeight." cm as height of image is ".$image_height;
        $errorInImageSize['image_size'] = 1;
    }
    if ($image_height > $maxHeight) {
        $errorInImageSize['image_height'] = "Height of Image is greater than ".$maxHeight." cm as height of image is ".$image_height;
        $errorInImageSize['image_size'] = 1;
    }
    return $errorInImageSize;
}
function checking_img_resolution($filename){
    $minResolution = 200;
    $maxResolution = 600;
    $reommendResolution = 300;

    $errorInImageResolution = array();
    $resolution  = get_dpi($filename);
    if($resolution[0] < $minResolution){
        return 0;
    }
    else if ($resolution[0] > $maxResolution) {
        return 1;
    }
    else if($resolution[0] == $reommendResolution){
        return 2;
    }
    return 3;
}
function chekingForspeicalCharacter($string){
    if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string))
    {
        return 0;
    }
    if ( preg_match('/\s/',$string) ){
        return 0;
    } 
    return 1;
}



$error_array = array();
function unitConver($string){
    switch($string){
        case "Mb":
        case "MB":
        case "Megabytes":
            return 1000000;
            break;

        case "Kb":
        case "KB":
        case "Kilobytes":
            return 1000;
            break;
        
    }
}
$uploadOk = 1;
$target_dir = "uploads/";

if(isset($_POST["submit"])) {
    $tmp_image_info = $_FILES["fileToUpload"]["tmp_name"];
    $check = getimagesize($tmp_image_info);
    if($check !== false) {
        $uploadOk = 1;
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $tmp_image_info = $_FILES["fileToUpload"]["tmp_name"];
        $tmp_image_size = $_FILES["fileToUpload"]["size"];
        $filesizeLimitation = 40 * unitConver("Mb");
        if(chekingForspeicalCharacter($_FILES["fileToUpload"]["name"]) < 1){
            $error_array['name'] = "File name of image should not contain spaces or any foreign characters";
            $uploadOk = 0;
        }
        if($tmp_image_size >= $filesizeLimitation){
            $error_array['file_size'] = "Image size is greater than 40Mb";
            $uploadOk = 0;
        }
        $errorImageSize = checking_image_dem($tmp_image_info);
        if(sizeof($errorImageSize)>0){
            foreach ($errorImageSize as $key => $value) {
                $error_array[$key] = $value;
                
            }
            $uploadOk = 0;
        }
        $errorImageResolution = checking_img_resolution($tmp_image_info);
        if($errorImageResolution < 3){
            $uploadOk = 0;
            $error_array['resolution'] = $errorImageResolution;
        }
        
        if($uploadOk > 0 ){
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                $sucessfulMessage = "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
            } else {
                $errorMessage = "Sorry, there was an error uploading your file.";
            }
        }
        else{
            $errorMessage = "There is some error while uploding file please check the condition";
        }
    } 
    else {
        $errorMessage = "Please select file to upload";
    }
        
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Image Upload for api</title>
    <style type="text/css">
        .error{
            color: red;
        }
        .successful{
            color: green;
        }
    </style>
</head>
<body>
    <div class="container bg-dark text-white">
        <div class="row">
            <h2> Condition of image upload</h2>
            <p>
                <?php
                if (isset($errorMessage)) {
                    print '<span class="message error">'.$errorMessage.'</span>';
                }
                if (isset($sucessfulMessage)) {
                    print '<span class="message successful">'.$sucessfulMessage.'</span>';
                } 
                ?>
            </p>
            <p>
                <ul>
                    <li>
                        The minimum allowable image size is 4cm x 2cm <?php if(isset($_POST["submit"]) && isset($_FILES["fileToUpload"])){ if(isset($error_array["image_size"]) && ($error_array["image_size"] == 0 )){ ?><span class="message error"><i class="fa fa-times"></i></span><?php  } else{?> <span class="message successful"><i class="fa fa-check"></i></span> <?php }  }?>
                    </li>
                    <li>
                        The maximum allowable size for an image is 29.7cm x 21cm. <?php if(isset($_POST["submit"]) && isset($_FILES["fileToUpload"])){ if(isset($error_array["image_size"]) && ($error_array["image_size"] == 1 )){ ?><span class="message error"><i class="fa fa-times"></i></span><?php  } else{?> <span class="message successful"><i class="fa fa-check"></i></span> <?php }  } ?>
                    </li>
                    <li>The colour space of an image must be sRGB.  </li>
                    <li>
                        file attachments must be limited to a maximum of 40 Mb (or less for slow connections) to allow for upload and conversion of the image <?php if(isset($_POST["submit"])) { if (isset($error_array["file_size"]) && ($error_array["file_size"] != "" )){ ?> <span class="message error"><i class="fa fa-times"></i></span><?php  } else{?><span class="message successful"><i class="fa fa-check"></i></span><?php } } ?>
                    </li>
                    <li>
                        file names for images should not contain spaces or any foreign characters <?php if(isset($_POST["submit"])) { if(isset($error_array["name"]) && ($error_array["name"] != "" )){ ?><span class="message error"><i class="fa fa-times"></i></span><?php  } else{?> <span class="message successful"><i class="fa fa-check"></i></span><?php } } ?>
                    </li>
                    <li>
                        the minimum resolution of an image is 200 dots per inch (dpi) <?php if(isset($_POST["submit"]) && isset($_FILES["fileToUpload"])){ if(isset($error_array["resolution"]) && ($error_array["resolution"] == 0 )){ ?><span class="message error"><i class="fa fa-times"></i></span><?php  } else{?> <span class="message successful"><i class="fa fa-check"></i></span> <?php } } ?>
                    </li>
                    <li>
                        the maximum resolution of an image is 600 dots per inch (dpi) <?php if(isset($_POST["submit"]) && isset($_FILES["fileToUpload"])){ if(isset($error_array["resolution"]) && ($error_array["resolution"] == 1 )){ ?><span class="message error"><i class="fa fa-times"></i></span><?php  } else{?> <span class="message successful"><i class="fa fa-check"></i></span> <?php } } ?>
                    </li>
                    <li>
                        the recommended resolution of an image is 300 dots per inch (dpi). <?php if(isset($_POST["submit"]) && isset($_FILES["fileToUpload"])){ if(isset($error_array["resolution"]) && ($error_array["resolution"] == 2 )){ ?><span class="message successful"><i class="fa fa-check"></i></span> <?php } } ?>
                    </li>
                </ul>
            </p>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <form action="" method="post" enctype="multipart/form-data">
                Select image to upload:
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Upload Image" name="submit">
            </form>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
               $(".message").hide()
            }, 5000);
        });
    </script>
</body>
</html>
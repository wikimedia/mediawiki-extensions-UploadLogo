<?php

if (!defined('MEDIAWIKI')) {
    die('Not an entry point.');
}

class SpecialUploadLogo extends SpecialPage
{
    public function __construct()
    {
        parent::__construct('UploadLogo', 'editinterface');
    }

    public function execute($par)
    {
        global $wgLogoDir,$wgLogoScriptPath,$wgLogo;

        $wgRequest = $this -> getRequest();
        $wgOutput = $this -> getOutput();
        $wgUser = $this -> getUser();

        $wgOutput -> addModules('ext.uploadLogo');
        $this -> setHeaders();

        if (in_array("sysop", $wgUser -> mGroups) == false) {
            $wgOutput -> addHTML(wfMessage('please_login'));
            return false;
        }

        $logoCandidateDir = $wgLogoDir.DIRECTORY_SEPARATOR.'candidate';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_FILES['logos'])) {
                $return_url = SkinTemplate::makeSpecialUrl('uploadlogo');
                if ($_FILES['logos']['error']!=0) {
                    throw new Exception("Error Processing Request", 1);
                }

                $logo_name = $_FILES['logos']['name'];
                $logo_tmp = $_FILES['logos']['tmp_name'];
                $logo_size = $_FILES['logos']['size'];

                    //move_uploaded_file( $logo_tmp, $IP . '/logos' );
                $uploadName=$this->getCandidateName($logo_name);
                $this -> makeThumbnail($logo_tmp, $logoCandidateDir . '/' . $uploadName);
                ob_start(); ?>
					<strong><?php echo wfMessage('uploaded_logo_image') ?></strong>
					<ul>
						<li><?php echo wfMessage('result') ?>: <?php echo $result?></li>
						<li><?php echo wfMessage('file_name') ?>: <?php echo $uploadName?></li>
						<li><?php echo wfMessage('file_size') ?>: <?php echo number_format($logo_size)?>byte</li>
						<li><?php echo wfMessage('image_preview') ?>:<br /><img src="<?php echo $wgLogoScriptPath.'/candidate/' . $uploadName?>" style="border: 1px solid #777;" /></li>
					</ul>
					<br />
					<a href="<?php echo $return_url?>">Return <?php echo wfMessage('uploadlogo')?> Page</a>
				<?php
                    $output = ob_get_contents();
                ob_clean();
            } else {
                if (isset($_POST['selected'])) {
                    echo json_encode(array('result'=>'success','logofile' =>$this->changeLogo($logoCandidateDir.DIRECTORY_SEPARATOR.$_POST['selected'], $wgLogoDir)));
                }

                if (isset($_POST['delete'])) {
                    if (unlink($logoCandidateDir .DIRECTORY_SEPARATOR. $_POST['delete'])) {
                        $files=glob($wgLogoDir.DIRECTORY_SEPARATOR.'logo-'.explode('-', pathinfo($_POST['delete'], PATHINFO_FILENAME))[1].'.{png,jpg,jpeg,gif}', GLOB_BRACE);
                        unlink($files[0]);

                        echo json_encode(array('result'=>'success','deleted' => $_POST['delete']));
                    } else {
                        echo json_encode(array('result'=>'error','message'=>'delete error'));
                    }
                }
                exit;
            }
        } else {
            if (!file_exists($logoCandidateDir)) {
                $result = mkdir($logoCandidateDir, 0707);
            }
            $fileList = glob($logoCandidateDir.DIRECTORY_SEPARATOR.'*');
            ob_start(); ?>
				<form id="upload_logo_form" method="post" enctype="multipart/form-data">
					<fieldset>
						<legend><?php echo wfMessage('uploadlogo') ?></legend>
						<input type="file" id="file-logos" name="logos" size="50" required="required" accept="/image/gif,image/jpeg,image/x-png" />
						<br />
						<label for="file-logos" style="font-weight: bold;"><?php echo wfMessage('possible_extensions') ?> : /image/gif,image/jpeg,image/x-png</label>
						<hr />
						<input type="submit" value="<?php echo wfMessage('Upload') ?>" />
					</fieldset>
				</form>
				<fieldset style="padding: 5px;">
					<legend><?php echo wfMessage('candidate_logos') ?></legend>
					<input type="hidden" id="logo-file" value="" />
					<input type="hidden" id="selected-logo" value="" />
					<?php foreach ($fileList as $index=>$fileName): ?>
					<?php if (is_dir($fileName)) {
                continue;
            } ?>
					<label for="logo_<?php echo $index ?>" class="upload-logo">
						<input type="radio" id="logo_<?php echo $index ?>" name="logos[]" value="<?php echo pathinfo($fileName, PATHINFO_BASENAME) ?>" class="check-image" />
						<img src="<?php echo $wgLogoScriptPath.'/candidate/'.pathinfo($fileName, PATHINFO_BASENAME) ?>" />
					</label>
					<?php endforeach ?>
				<hr style="clear: both;" />
				<button id="change-logo"><?php echo wfMessage('change_logo') ?></button>
				<button id="delete-logo"><?php echo wfMessage('delete_logo') ?></button>
				</fieldset>

			<?php
            $output = ob_get_contents();
            ob_clean();
        }
        $wgOutput -> addHTML($output);
    }

    public function getCandidateName($logo_name)
    {
        return date('Ymd-') .substr(uniqid(md5(time())), 0, 10) . substr($logo_name, strrpos($logo_name, '.'));
    }

    public function changeLogo($candidateLogo, $directory)
    {
        $extention=pathinfo($candidateLogo, PATHINFO_EXTENSION);
        $logoFilename='logo-'.explode('-', pathinfo($candidateLogo, PATHINFO_FILENAME))[1].'.'.$extention;

        $logos=glob($directory.DIRECTORY_SEPARATOR.'*');

        foreach ($logos as $index=>$value) {
            if (is_dir($value)) {
                continue;
            }
            unlink($value);
        }

        if (!copy($candidateLogo, $directory . DIRECTORY_SEPARATOR.$logoFilename)) {
            throw new Exception("Error Processing Request", 1);
        }

        return $logoFilename;
    }

    public function makeThumbnail($original_file, $thumbnail_file, $uploaded_file=true, $reduced_size = 165, $use_ratio = false)
    {
        try {
            //if(extension_loaded('imagick')) {
    //		return $this->makeThumbnailImagick($original_file, $thumbnail_file, $reduced_size, $use_ratio);
    //	} else {
            return $this->makeThumbnailGD($original_file, $thumbnail_file, $uploaded_file, $reduced_size, $use_ratio);
    //	}
        } catch (Exception $e) {
            return false;
        }
    }

    private function makeThumbnailImagick($original_file, $thumbnail_file, $uploaded_file, $reduced_size, $use_ratio)
    {
    }

    private function makeThumbnailGD($original_file, $thumbnail_file, $uploaded_file, $reduced_size, $use_ratio)
    {
        $error = false;
        $type = getimagesize($original_file);
        if ($type[0] > $reduced_size) {
            if (file_exists($original_file)) {
                if (!function_exists('imagegif') && $type[2] == 1) {
                    $error = 'Error (Fail to create thumbnail. GIF. imagegif does not exist)';
                } elseif (!function_exists('imagejpeg') && $type[2] == 2) {
                    $error = 'Error (Fail to create thumbnail.JPEG) imagejpeg does not exist';
                } elseif (!function_exists('imagepng') && $type[2] == 3) {
                    $error = "Error (Fail to create thumbnail. PNG imagepng does not exist. \$type[2]= $type[2])";
                } else {
                    if ($type[2] == 1) {
                        $image = imagecreatefromgif($original_file);
                    } elseif ($type[2] == 2) {
                        $image = imagecreatefromjpeg($original_file);
                    } elseif ($type[2] == 3) {
                        $image = imagecreatefrompng($original_file);
                    }

                    if (function_exists('imageantialias')) {
                        imageantialias($image, true);
                    }
                    $image_attribute = @getimagesize($original_file);
                    if ($image_attribute[0] > $image_attribute[1]) {
                        $image_width = $image_attribute[0];
                        $image_height = $image_attribute[1];
                        # 4:3
                        if ($use_ratio) {
                            $image_new_width = $reduced_size;
                            $image_new_height = intval($reduced_size * 3 / 4);
                        }# width > height
                        else {
                            $image_new_width = $reduced_size;
                            $image_ratio = $image_width / $image_new_width;
                            $image_new_height = intval($image_height / $image_ratio);
                        }
                    } else {
                        $image_width = $image_attribute[0];
                        $image_height = $image_attribute[1];
                        # 3:4
                        if ($use_ratio) {
                            $image_new_height = $reduced_size;
                            $image_new_width = intval($reduced_size * 3 / 4);
                        }
                        # height > width
                        else {
                            $image_new_height = $reduced_size;
                            $image_ratio = $image_height / $image_new_height;
                            $image_new_width = intval($image_width / $image_ratio);
                        }
                    }

                    $thumbnail = imagecreatetruecolor($image_new_width, $image_new_height);
                    @imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $image_attribute[0], $image_attribute[1]);

                    if ($type[2] == 1 && !imagegif($thumbnail, $thumbnail_file)) {
                        $error = 'Error (Wrong path..)';
                    } elseif ($type[2] == 2 && !imagejpeg($thumbnail, $thumbnail_file, 100)) {
                        $error = 'Error (Wrong path..)';
                    } elseif ($type[2] == 3 && !imagepng($thumbnail, $thumbnail_file)) {
                        $error = 'Error (Wrong path..)';
                    }
                }
            } else {
                $error = 'Error (Uploaded File does not exist..)';
            }

            if ($error != false) {
                return $error;
            } else {
                @chmod($thumbnail_path, 0606);
                return true;
            }
            #------------------------------------
            # size is quite small. No need to reduce it.
            #------------------------------------------------
        } elseif ($type[0] <= $reduced_size) {
            if (is_uploaded_file($original_file)) {
                move_uploaded_file($original_file, $thumbnail_file);
            } else {
                if ($uploaded_file) {
                    return 'nono~!';
                } else {
                    copy($original_file, $thumbnail_file);
                }
            }
            return true;
        } else {
            return 'Error (Fail to upload. Logo file)';
        }
    }
}

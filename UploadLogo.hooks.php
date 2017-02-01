<?php
/**
 * Hooks for RelatedLinks extension
 *
 * @file
 * @ingroup Extensions
 */

class UploadLogoHooks
{
    /*
      * Change $wgLogo
      */
    public static function onRegistration()
    {
        global $IP,$wgLogoScriptPath,$wgResourceBasePath,$wgLogo,$wgLogoDir;

        $wgLogoDir=$IP .DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'logo';
        $wgLogoScriptPath=$wgResourceBasePath.'/images/logo';

        if (!file_exists($wgLogoDir)) {
            if (!mkdir($wgLogoDir)) {
                throw new Exception("fail to make directory", 1);
            }
        }

        $logoCandidateDir = $wgLogoDir.DIRECTORY_SEPARATOR.'candidate';

        if (file_exists($logoCandidateDir)) {
            $files=glob($wgLogoDir.DIRECTORY_SEPARATOR.'{logo}*.{png,jpg,jpeg,gif}', GLOB_BRACE);

            if (count($files)) {
                $logoFile=basename($files[0]);
            }
        } else {
            if (!mkdir($logoCandidateDir)) {
                throw new Exception("fail to make directory", 1);
            } else {
                if (!file_exists($IP .$wgLogo)) {
                    return false;
                }

                $specialuploadLogo=new SpecialUploadLogo();
                $thumbnailPath=$logoCandidateDir.DIRECTORY_SEPARATOR.$specialuploadLogo->getCandidateName(basename($wgLogo));
                if ($specialuploadLogo->makeThumbnail($IP .$wgLogo, $thumbnailPath, false)) {
                    $specialuploadLogo->changeLogo($thumbnailPath, $wgLogoDir);
                    $logoFile=$thumbnailPath;
                }
            }
        }

        if (isset($logoFile)) {
            $wgLogo=$wgLogoScriptPath.DIRECTORY_SEPARATOR.$logoFile;
        }
    }
}

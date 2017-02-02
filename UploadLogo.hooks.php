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
    public static function onExtensionLoad()
    {
        global $IP,$wgResourceBasePath,$wgLogoScriptPath,$wgLogoDir,$wgLogo;

        $wgLogoDir=$IP .DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'logo';
        $wgLogoScriptPath=$wgResourceBasePath.'/images/logo';

        if (!file_exists($wgLogoDir)) {
            if (!mkdir($wgLogoDir)) {
                throw new Exception("fail to make directory", 1);
            }
        }

        $logoCandidateDir = $wgLogoDir.DIRECTORY_SEPARATOR.'candidate';

        $specialuploadLogo=new SpecialUploadLogo();

        if (file_exists($logoCandidateDir)) {
            $logoFile=$specialuploadLogo->getLogo();
        } else {
            if (!mkdir($logoCandidateDir)) {
                throw new Exception("fail to make directory", 1);
            } else {
                if (!file_exists($IP .$wgLogo)) {
                    return false;
                }

                $thumbnailPath=$logoCandidateDir.DIRECTORY_SEPARATOR.$specialuploadLogo->getCandidateName(basename($wgLogo));
                if ($specialuploadLogo->makeThumbnail($IP .$wgLogo, $thumbnailPath, false)) {
                    $specialuploadLogo->changeLogo($thumbnailPath, $wgLogoDir);
                    $logoFile=$thumbnailPath;
                }
            }
        }

        if ($logoFile) {
            $wgLogo=$wgLogoScriptPath.DIRECTORY_SEPARATOR.$logoFile;
        }
    }
}

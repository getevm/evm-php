<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\InstallServiceAbstract;
use Getevm\Evm\Interfaces\InstallServiceInterface;
use Getevm\Evm\Services\OSHelper;
use Getevm\Evm\Services\SystemService;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use ZipArchive;

class InstallService extends InstallServiceAbstract implements InstallServiceInterface
{
    /**
     * @return int
     * @throws GuzzleException
     */
    public function execute(): int
    {
        $this->createPrerequisiteDirectories();

        $this->getConsoleOutputService()->std([
            'OS: ' . ($this->getConfig()['os'] . ' (' . $this->getConfig()['osType'] . ')'),
            'Architecture: ' . $this->getConfig()['archType'],
            'Thread Safety: ' . ($this->getConfig()['ts'] ? 'Yes' : 'No')
        ]);

        $this->getConsoleOutputService()->std('Finding appropriate release.');

        $releaseUrl = $this->getReleaseUrl();

        if (!$releaseUrl) {
            $this->getConsoleOutputService()->error('Failed to find release.');
            return Command::FAILURE;
        }

        $ext = pathinfo($releaseUrl, PATHINFO_EXTENSION);
        $outputZipFile = $this->buildOutputFileName($ext);

        $this->getOutputInterface()->writeln([
            'Release found attempting to download from ' . $releaseUrl . '.'
        ]);

        $response = $this->getGuzzle()->get($releaseUrl);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $this->getConsoleOutputService()->error('Failed to download release.');
            return Command::INVALID;
        }

        $outputFolderPath = $this->getOutputPath($outputZipFile);
        $pathToZip = $outputFolderPath . DIRECTORY_SEPARATOR . $outputZipFile;
        file_put_contents($pathToZip, $response->getBody());

        $this->getConsoleOutputService()->success('Downloaded to ' . $pathToZip . '.');

        $zip = new ZipArchive();
        $zip->open($pathToZip);
        $zip->extractTo($outputFolderPath);
        $zip->close();

        $this->getConsoleOutputService()->success('Unzipped to ' . $outputFolderPath . '. Cleaning up downloaded files.');

        unlink($pathToZip);

        $this->getConsoleOutputService()->success('Operation successful! Installed PHP v' . $this->getConfig()['version'] . '.');

        /*********************************************************
         * Attempt to download and store the CA Cert for php.ini
         *********************************************************/
        $certService = new CACertService();
        $pathToInstallationDir = $outputFolderPath;

        if ($cert = $certService->download()) {
            $pathToCert = $pathToInstallationDir . DIRECTORY_SEPARATOR . 'ssl';

            if ($certService->store($pathToCert, $cert)) {
                $this->getConsoleOutputService()->success('CA Cert saved to ' . $pathToCert . '.');
            } else {
                $this->getConsoleOutputService()->warning('Failed to save the CA Cert. You\'ll have to do this manually.');
            }
        } else {
            $this->getConsoleOutputService()->warning('Failed to download the CA Cert. You\'ll have to do this manually.');
        }

//        $phpIniService = new PhpIniService();

        /*********************************************************
         * Setup the PHP extensions as requested by the user
         *********************************************************/
//        $helper = $this->getCommand()->getHelper('question');
//        $exts = array_values(json_decode(file_get_contents(__DIR__ . '/../../data/php.json'), true)['exts']);
//        $extOptions = array_merge(['none', 'all'], $exts);
//        $extsQuestions = new ChoiceQuestion('Do wish enable extensions for the installations?', $extOptions, '0');
//        $extsQuestions->setMultiselect(true);
//        $extsToEnable = $helper->ask($this->getInputInterface(), $this->getOutputInterface(), $extsQuestions);

        /**
         * - set extensions
         * - set extension_dir
         */

//        if (!in_array('none', $extsToEnable)) {
//            $iniFilePath = DEPS_PATH . DIRECTORY_SEPARATOR . $this->buildInstallationDirectoryName() . DIRECTORY_SEPARATOR;
//
//            rename($iniFilePath . 'php.ini-production', $iniFilePath . 'php.ini');
//            copy($iniFilePath . 'php.ini', $iniFilePath . 'php.ini.bak');
//
//            $iniFile = file_get_contents($iniFilePath . 'php.ini');
//            $extsToEnable = $extsToEnable[0] === 'all' ? $exts : $extsToEnable;
//
//            foreach ($extsToEnable as $ext) {
//                $extNeedle = ';extension=' . $ext;
//
//                if (strpos($iniFile, $extNeedle) !== false) {
//                    $iniFile = str_replace($extNeedle, 'extension=' . $ext, $iniFile);
//                }
//            }
//
//            $extensionDirValue = 'extension_dir="' . $iniFilePath . 'ext' . '"';
//            $iniFile = str_replace(';extension_dir = "ext"', $extensionDirValue, $iniFile);
//            file_put_contents($iniFilePath . 'php.ini', $iniFile);
//            unlink($iniFilePath . 'php.ini.bak');
//        }
//
//        $question = new ConfirmationQuestion('Do you want to activate v' . $this->getConfig()['version'] . ' now?', false);

//        if (!$helper->ask($this->getInputInterface(), $this->getOutputInterface(), $question)) {
//            return Command::SUCCESS;
//        }

        return Command::SUCCESS;
//        return (new UseService($this->getOutputInterface(), $this->getConfig()))->execute();
    }

    private function getUnixReleaseUrl()
    {
        $majorVersion = explode('.', $this->getConfig()['version'])[0];
        return 'https://museum.php.net/php' . $majorVersion . '/php-' . $this->getConfig()['version'] . '.tar.gz';
    }

    private function getWindowsReleaseUrl()
    {
        $url = 'https://windows.php.net/downloads/releases/archives/php-';
        $url .= $this->getConfig()['version'];
        $url .= !$this->getConfig()['ts'] ? '-nts' : '';
        $url .= '-Win32-vs16-';
        $url .= $this->getConfig()['archType'];
        $url .= '.zip';

        return $url;
    }

    private function buildOutputFileName($ext)
    {
        $fileName = $this->getConfig()['version'];

        if ($this->getConfig()['ts']) {
            $fileName .= '-ts';
        } else {
            $fileName .= '-nts';
        }

        if (SystemService::getOS() === SystemService::OS_WIN) {
            $fileName .= '-' . $this->getConfig()['archType'];
        }

        $fileName .= '-' . $this->getConfig()['osType'];
        $fileName .= '.' . $ext;

        return $fileName;
    }

    public function getReleaseUrl(): ?string
    {
        $self = $this;
        $config = $this->getConfig();

        $this->getConsoleOutputService()->warning(__DIR__);

        $releasesByOSType = json_decode(file_get_contents(__DIR__ . '/../../data/php.json'), true)[$config['osType']];

        switch ($config['osType']) {
            case 'nt':
                $release = array_values(array_filter($releasesByOSType, function ($release) use ($config, $self) {
                    $releaseMetadata = $self->getMetadataFromReleaseNameNT($release);
                    $versionCheck = $releaseMetadata['version'] === $config['version'];
                    $archTypeCheck = $releaseMetadata['archType'] === null || $releaseMetadata['archType'] === $config['archType'];
                    $tsCheck = $releaseMetadata['ts'] === $config['ts'];

                    return $versionCheck && $archTypeCheck && $tsCheck;
                }));

                if (empty($release) || count($release) > 1) {
                    return null;
                }

                return 'https://windows.php.net/downloads/releases/archives/' . $release[0];

            case 'nix':
                return ' ';

            default:
                return null;
        }
    }

    private function getMetadataFromReleaseNameNT($release)
    {
        $fileNameWithoutExt = pathinfo($release, PATHINFO_FILENAME);
        $isNtsRelease = strpos($release, '-nts-') !== false;

        /**
         * Some installations don't have arch type
         */
        if (strpos($fileNameWithoutExt, 'x64') === false && strpos($fileNameWithoutExt, 'x86') === false) {
            if ($isNtsRelease) {
                list(, $version, $nts) = explode('-', $fileNameWithoutExt);
            } else {
                list(, $version) = explode('-', $fileNameWithoutExt);
            }
        } else {
            if ($isNtsRelease) {
                list(, $version, $nts, , , $archType) = explode('-', $fileNameWithoutExt);
            } else {
                list(, $version, , , $archType) = explode('-', $fileNameWithoutExt);
            }
        }

        return [
            'version' => $version,
            'ts' => !isset($nts),
            'archType' => isset($archType) ? $archType : null,
            'ext' => pathinfo($release, PATHINFO_EXTENSION),
        ];
    }

    private function getOutputPath($outputFileName)
    {
        $outputPath = OSHelper::getPathToDeps() . DIRECTORY_SEPARATOR . pathinfo($outputFileName, PATHINFO_FILENAME);

        if (!is_dir($outputPath)) {
            mkdir($outputPath, null, true);
        }

        return $outputPath;
    }

    private function createPrerequisiteDirectories()
    {
        $dirs = [
            OSHelper::getPathToDeps() . DIRECTORY_SEPARATOR . 'logs'
        ];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                continue;
            }

            mkdir($dir, null, true);
        }
    }

    private function buildInstallationDirectoryName()
    {
        $dir = $this->getConfig()['version'];
        $dir .= $this->getConfig()['ts'] ? '-ts' : '-nts';
        $dir .= '-' . $this->getConfig()['archType'];
        $dir .= '-' . $this->getConfig()['osType'];

        return $dir;
    }
}

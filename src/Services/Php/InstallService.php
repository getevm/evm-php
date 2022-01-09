<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\InstallServiceAbstract;
use Getevm\Evm\Interfaces\InstallServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;

class InstallService extends InstallServiceAbstract implements InstallServiceInterface
{
    const PATH_TO_PHP_METADATA = __DIR__ . '/../../../data/php.json';

    /**
     * @return int
     * @throws GuzzleException
     */
    public function execute(): int
    {
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

        $this->getOutputInterface()->writeln([
            'Release found attempting to download from ' . $releaseUrl . '.'
        ]);

        $response = $this->getGuzzle()->get($releaseUrl);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $this->getConsoleOutputService()->error('Failed to download release.');
            return Command::INVALID;
        }

        $response = $response->getBody();
        $pathToInstallationDir = $this->getFileService()->getPathToInstallationDir() . DIRECTORY_SEPARATOR . $this->buildOutputName();

        if (!is_dir($pathToInstallationDir)) {
            mkdir($pathToInstallationDir, null, true);
        }

        $archiveName = pathinfo($releaseUrl, PATHINFO_BASENAME);
        $pathToArchive = $pathToInstallationDir . DIRECTORY_SEPARATOR . $archiveName;
        file_put_contents($pathToArchive, $response);
        $this->getConsoleOutputService()->success('Downloaded to ' . $pathToArchive . '.');

        /*****************************
         * Unzip release and cleanup
         *****************************/
        if (!$this->getFileService()->unzip($pathToArchive, $pathToInstallationDir)) {
            $this->getConsoleOutputService()->error('Failed to unzip release.');
            return Command::FAILURE;
        }

        $this->getConsoleOutputService()->success('Unzipped release to ' . $pathToInstallationDir . '.');

        $phpIniService = new PhpIniService($pathToInstallationDir, $this->getFileService());

        if (!$phpIniService->enablePhpIni()) {
            $this->getConsoleOutputService()->error('Failed to enable php.ini file.');
            return Command::FAILURE;
        }

        $this->getConsoleOutputService()->success('php.ini enabled.');

        /*********************************************************
         * Attempt to download and store the CA Cert for php.ini
         *********************************************************/
        $certService = new CACertService();
        $pathToCert = $pathToInstallationDir . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'cacert.pem';

        if ($cert = $certService->download()) {
            if ($certService->store($pathToCert, $cert)) {
                $this->getConsoleOutputService()->success('CA Cert saved to ' . $pathToCert . '.');

                if ($phpIniService->setCurlCAInfo($pathToCert)) {
                    $this->getConsoleOutputService()->success('Successfully set curl.cainfo set.');
                } else {
                    $this->getConsoleOutputService()->warning('Failed to set curl.cainfo set.');
                }

                if ($phpIniService->setOpenSslCAPath($pathToCert)) {
                    $this->getConsoleOutputService()->success('Successfully set openssl.cafile set.');
                } else {
                    $this->getConsoleOutputService()->warning('Failed to set openssl.cafile set.');
                }
            } else {
                $this->getConsoleOutputService()->warning('Failed to save the CA Cert. You\'ll have to do this manually.');
            }
        } else {
            $this->getConsoleOutputService()->warning('Failed to download the CA Cert. You\'ll have to do this manually.');
        }

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
//        $this->getConsoleOutputService()->success('Operation successful! Installed PHP v' . $this->getConfig()['version'] . '.');
//        $question = new ConfirmationQuestion('Do you want to activate v' . $this->getConfig()['version'] . ' now?', false);

//        if (!$helper->ask($this->getInputInterface(), $this->getOutputInterface(), $question)) {
//            return Command::SUCCESS;
//        }

        return Command::SUCCESS;
//        return (new UseService($this->getOutputInterface(), $this->getConfig()))->execute();
    }

    /**
     * @return string
     */
    private function buildOutputName(): string
    {
        $name = $this->getConfig()['version'];
        $name .= $this->getConfig()['ts'] ? '-ts' : '-nts';
        $name .= '-' . $this->getConfig()['archType'];
        $name .= '-' . $this->getConfig()['osType'];

        return $name;
    }

    public function getReleaseUrl(): ?string
    {
        $self = $this;
        $config = $this->getConfig();
        $releasesByOSType = $this->getFileService()->getAsJson(self::PATH_TO_PHP_METADATA)[$this->getConfig()['osType']];

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
                $release = array_values(array_filter($releasesByOSType, function ($release) use ($config, $self) {
                    $version = str_replace('php-', '', str_replace('.tar.gz', '', $release));
                    return $version === $config['version'];
                }));

                if (empty($release) || count($release) > 1) {
                    return null;
                }

                $majorVersion = explode('.', $this->getConfig()['version'])[0];

                return 'https://museum.php.net/php' . $majorVersion . '/' . $release[0];
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
}

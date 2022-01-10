<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\InstallServiceAbstract;
use Getevm\Evm\Interfaces\InstallServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
            'Thread Safe: ' . ($this->getConfig()['ts'] ? 'Yes' : 'No')
        ]);

        $this->getConsoleOutputService()->std('Finding appropriate release...');

        $releaseUrl = $this->getReleaseUrl();

        if (!$releaseUrl) {
            $this->getConsoleOutputService()->error('Failed to find release. Exiting.');
            return Command::FAILURE;
        }

        $this->getConsoleOutputService()->std('Release found attempting to download from ' . $releaseUrl . '...');

        try {
            $response = $this->getGuzzle()->get($releaseUrl);
        } catch (GuzzleException $e) {
            $this->getConsoleOutputService()->error('Failed to download release. Exiting.');
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

        $this->getConsoleOutputService()->success('Successfully enabled php.ini.');

        /*********************************************************
         * Attempt to download and store the CA Cert for php.ini
         *********************************************************/
        $certService = new CACertService();
        $pathToCert = $pathToInstallationDir . DIRECTORY_SEPARATOR . 'ssl';

        if ($cert = $certService->download()) {
            if ($certService->store($pathToCert, $cert)) {
                $pathToCert .= DIRECTORY_SEPARATOR . 'cacert.pem';

                $this->getConsoleOutputService()->success('CA Cert saved to ' . $pathToCert . '.');

                /*********************************
                 * Attempt to set the curl.cainfo
                 *********************************/
                if ($phpIniService->setCurlCAInfo($pathToCert)) {
                    $this->getConsoleOutputService()->success('Successfully set curl.cainfo in php.ini.');
                } else {
                    $this->getConsoleOutputService()->warning('Failed to set curl.cainfo in php.ini.');
                }

                /************************************
                 * Attempt to set the openssl.cafile
                 ************************************/
                if ($phpIniService->setOpenSslCAPath($pathToCert)) {
                    $this->getConsoleOutputService()->success('Successfully set openssl.cafile in php.ini.');
                } else {
                    $this->getConsoleOutputService()->warning('Failed to set openssl.cafile in php.ini.');
                }
            } else {
                $this->getConsoleOutputService()->warning('Failed to save the CA Cert. You\'ll need to do this manually.');
            }
        } else {
            $this->getConsoleOutputService()->warning('Failed to download the CA Cert. You\'ll need to do this manually.');
        }

        $helper = $this->getCommand()->getHelper('question');

        /************************************
         * Attempt to set the extensions_dir
         ************************************/
        if ($phpIniService->setExtensionsDir()) {
            /*********************************************************
             * Setup the PHP extensions as requested by the user
             *********************************************************/
            $pathToExtsDir = $pathToInstallationDir . DIRECTORY_SEPARATOR . 'ext';
            $exts = $this->getFileService()->getExtsListFromDir($pathToExtsDir);

            if ($exts) {
                $allExts = array_merge(['none', 'all'], $exts);
                $extsQuestion = new ChoiceQuestion('Do wish to enable extensions for the installations?', $allExts, '0');
                $extsQuestion->setMultiselect(true);
                $extsToEnable = $helper->ask($this->getInputInterface(), $this->getOutputInterface(), $extsQuestion);

                if (!in_array('none', $extsToEnable)) {
                    $extsToEnable = in_array('all', $extsToEnable) ? $exts : $extsToEnable;
                    $outcome = $phpIniService->enableExtensions($extsToEnable);

                    $this->getConsoleOutputService()->success('Successfully enabled extensions: ' . implode(', ', $outcome['success']));

                    if (!empty($outcome['failure'])) {
                        $this->getConsoleOutputService()->error('Failed to enable extensions: ' . implode(', ', $outcome['failure']));
                    }
                } else {
                    $this->getConsoleOutputService()->warning('No extensions selected. Skipping.');
                }
            } else {
                $this->getConsoleOutputService()->warning('Unable to find extensions. Skipping extension setup.');
            }
        } else {
            $this->getConsoleOutputService()->warning('Unable to set extension_dir. Skipping extension setup.');
        }

        $this->getConsoleOutputService()->success('Operation successful! Installed PHP v' . $this->getConfig()['version'] . '.');

        $question = new ConfirmationQuestion('Do you want to activate v' . $this->getConfig()['version'] . ' now?', false);

        if (!$helper->ask($this->getInputInterface(), $this->getOutputInterface(), $question)) {
            return Command::SUCCESS;
        }

        return (new UseService($this->getOutputInterface(), $this->getConfig()))->execute();
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

    /**
     * @return string|null
     */
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

    /**
     * @param $release
     * @return array
     */
    private function getMetadataFromReleaseNameNT($release): array
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
            'archType' => $archType ?? null,
            'ext' => pathinfo($release, PATHINFO_EXTENSION),
        ];
    }
}

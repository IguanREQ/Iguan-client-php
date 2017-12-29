<?php

namespace Iguan\Event\Subscriber\Guard;

use Iguan\Event\Subscriber\Subject;

/**
 * Class SubscriptionFileGuard
 *
 * @author Vishnevskiy Kirill
 */
class SubscriptionFileGuard extends SubscriptionGuard
{
    private $lockFilesLocation;

    public function __construct($appVersion, $lockFilesLocation = '/tmp')
    {
        parent::__construct($appVersion);
        $this->lockFilesLocation = $lockFilesLocation;
    }

    public function hasSubscription(Subject $subject, $sourceTag)
    {
        $lockFile = $this->getLockFilePath($sourceTag, $subject->getToken(), $subject->getNotifyWay()->hashCode());
        return file_exists($lockFile);
    }

    public function persistSubscription(Subject $subject, $sourceTag)
    {
        $lockFile = $this->getLockFilePath($sourceTag, $subject->getToken(), $subject->getNotifyWay()->hashCode());
        touch($lockFile);
    }

    private function getLockFilePath($sourceTag, $subjectToken, $notifyWayHash)
    {
        return $this->lockFilesLocation . DIRECTORY_SEPARATOR . "iguan_sl_{$sourceTag}_{$this->getAppVersion()}_{$subjectToken}_{$notifyWayHash}.lock";
    }

    public function isVersionChanged($sourceTag)
    {
        $versionFilePath = $this->getVersionFilePath($sourceTag);
        if (!file_exists($versionFilePath)) return true;

        return file_get_contents($versionFilePath) !== $this->getAppVersion();
    }

    private function getVersionFilePath($sourceTag)
    {
        return $this->lockFilesLocation . DIRECTORY_SEPARATOR . "iguan_sl_{$sourceTag}";
    }

    public function persistVersion($sourceTag)
    {
        $versionFilePath = $this->getVersionFilePath($sourceTag);
        if (file_exists($versionFilePath)) {
            $version = file_get_contents($versionFilePath);
            $this->clearOldLocks($version, $sourceTag);
        }

        file_put_contents($versionFilePath, $this->getAppVersion());
    }

    private function clearOldLocks($oldVersion, $sourceTag)
    {
        $files = scandir($this->lockFilesLocation);
        foreach ($files as $file) {
            if (preg_match(preg_quote("/iguan_sl_{$sourceTag}_{$oldVersion}") . '_.*\.lock/', $file, $matches)) {
                unlink($this->lockFilesLocation . DIRECTORY_SEPARATOR . $file);
            }
        }
    }
}
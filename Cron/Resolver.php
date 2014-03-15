<?php
/**
 * This file is part of the SymfonyCronBundle package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cron\CronBundle\Cron;

use Cron\CronBundle\Entity\CronJob;
use Cron\Job\JobInterface;
use Cron\Job\ShellJob;
use Cron\Resolver\ResolverInterface;
use Cron\Schedule\CrontabSchedule;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author Dries De Peuter <dries@nousefreak.be>
 */
class Resolver implements ResolverInterface
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $phpExecutable;

    public function __construct()
    {
        $finder = new PhpExecutableFinder();
        $this->phpExecutable = $finder->find();
    }

    /**
     * @param Manager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $kernelDir
     */
    public function setRootDir($kernelDir)
    {
        $this->rootDir = dirname($kernelDir);
    }

    /**
     * Return all available jobs.
     *
     * @return JobInterface[]
     */
    public function resolve()
    {
        $jobs = $this->manager->listEnabledJobs();

        return array_map(array($this, 'createJob'), $jobs);
    }

    /**
     * Transform a CronJon into a ShellJob.
     *
     * @param  CronJob  $dbJob
     * @return ShellJob
     */
    protected function createJob(CronJob $dbJob)
    {
        $job = new ShellJob();
        $job->setCommand($this->phpExecutable . ' app/console ' . $dbJob->getCommand(), $this->rootDir);
        $job->setSchedule(new CrontabSchedule($dbJob->getSchedule()));
        $job->raw = $dbJob;

        return $job;
    }
}
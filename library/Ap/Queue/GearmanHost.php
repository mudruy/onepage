<?php


class Ap_Queue_GearmanHost {

    private $host;
    private $port = 4730;
    private $jobs = array();
    private $workers = array();
    private $rawStatus;
    private $rawWorkers;

    const WORKER_AVAILABLE = "AVAILABLE";
    const WORKER_RUNNING = "RUNNING";
    const WORKER_TOTAL = "TOTAL";
    const WORKER_QUEUED = "QUEUED";
    const FACER_WORKER = "facer";
    const OTHER_WORKER = "other";

    public function __construct($host, $port = null) {
        if (strstr($host, ":") !== false) {
            $server = explode(":", $host);
            $this->host = $server[0];
            $this->port = $server[1];
        } else {
            $this->host = $host;
        }
        if ($port != null)
            $this->port = $port;
        $gearman_telnet = new Ap_Queue_GearmanTelnet($this->host, $this->port);
        $this->rawStatus = $gearman_telnet->getStatus();
        $this->rawStatus = explode(PHP_EOL, $this->rawStatus);
        $this->rawWorkers = $gearman_telnet->getWorkers();
        $this->rawWorkers = explode(PHP_EOL, $this->rawWorkers);
        $this->initWorkers();
        $this->initJobs();
    }

    public function getJobs() {
        if (empty($this->jobs) || count($this->jobs) == 0)
            $this->initJobs();
        return $this->jobs;
    }

    public function getWorkers() {
        if (empty($this->workers) || count($this->workers) == 0)
            $this->initWorkers();
        return $this->workers;
    }

    private function initJobs() {
        $this->workers = $this->getWorkers();
        $status = $this->rawStatus;
        for ($i = 0; $i < count($status); $i++) {
            @list($job, $total, $running, $available) = explode("\t", $status[$i]);
            if (!empty($job)) {
                $available = trim($available);
                $total = trim($total);
                $running = trim($running);
                $this->jobs[$job] = array(
                    Ap_Queue_GearmanHost::WORKER_AVAILABLE => $available,
                    Ap_Queue_GearmanHost::WORKER_TOTAL => $total,
                    Ap_Queue_GearmanHost::WORKER_RUNNING => $running
                );
                $workerType = self::OTHER_WORKER;
                if (strpos($job, self::FACER_WORKER) !== false)
                    $workerType = self::FACER_WORKER;
                $this->workers[$workerType][Ap_Queue_GearmanHost::WORKER_RUNNING] += $running;
                $this->workers[$workerType][Ap_Queue_GearmanHost::WORKER_AVAILABLE] -= $running;
                $this->workers[$workerType][Ap_Queue_GearmanHost::WORKER_QUEUED] += ($total - $running);
            }
        }
// print_r($this->workers);
    }

    private function initWorkers() {
        $workers = $this->rawWorkers;
        $this->workers[self::FACER_WORKER] = array(
            Ap_Queue_GearmanHost::WORKER_AVAILABLE => 0,
            Ap_Queue_GearmanHost::WORKER_TOTAL => 0,
            Ap_Queue_GearmanHost::WORKER_RUNNING => 0,
            Ap_Queue_GearmanHost::WORKER_QUEUED => 0
        );
        $this->workers[self::OTHER_WORKER] = array(
            Ap_Queue_GearmanHost::WORKER_AVAILABLE => 0,
            Ap_Queue_GearmanHost::WORKER_TOTAL => 0,
            Ap_Queue_GearmanHost::WORKER_RUNNING => 0,
            Ap_Queue_GearmanHost::WORKER_QUEUED => 0
        );
        for ($i = 0; $i < count($workers); $i++) {
            @list($ip, $jobTypes) = explode(" : ", $workers[$i]);
            if (!empty($jobTypes)) {
                $workerType = self::OTHER_WORKER;
                if (strpos($jobTypes, self::FACER_WORKER) !== false)
                    $workerType = self::FACER_WORKER;
                $this->workers[$workerType][Ap_Queue_GearmanHost::WORKER_TOTAL] += 1;
                $this->workers[$workerType][Ap_Queue_GearmanHost::WORKER_AVAILABLE] += 1;
            }
        }
    }

}
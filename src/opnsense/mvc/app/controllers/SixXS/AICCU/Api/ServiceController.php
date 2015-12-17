<?php
/**
 *    Copyright (C) 2015 Manuel Faux <mfaux@conf.at>
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
namespace SixXS\AICCU\Api;

use \OPNsense\Base\ApiControllerBase;
use \OPNsense\Core\Backend;
use \SixXS\AICCU\Aiccu;

/**
 * Class ServiceController
 * @package SixXS\AICCU
 */
class ServiceController extends ApiControllerBase
{
    /**
     * start AICCU service (in background)
     * @return array
     */
    public function startAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun("aiccu start", true);
            return array("response" => $response);
        } else {
            return array("response" => array());
        }
    }

    /**
     * stop AICCU service
     * @return array
     */
    public function stopAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun("aiccu stop");
            return array("response" => $response);
        } else {
            return array("response" => array());
        }
    }

    /**
     * restart AICCU service
     * @return array
     */
    public function restartAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun("aiccu restart");
            return array("response" => $response);
        } else {
            return array("response" => array());
        }
    }

    /**
     * retrieve status of AICCU
     * @return array
     * @throws \Exception
     */
    public function statusAction()
    {
        $backend = new Backend();
        $mdlAiccu = new Aiccu();
        $response = $backend->configdRun("aiccu status");

        if (strpos($response, "not running") > 0) {
            if ($mdlAiccu->general->enabled->__toString() == 1) {
                $status = "stopped";
            } else {
                $status = "disabled";
            }
        } elseif (strpos($response, "is running") > 0) {
            $status = "running";
        } elseif ($mdlAiccu->general->enabled->__toString() == 0) {
            $status = "disabled";
        } else {
            $status = "unkown";
        }

        return array("status" => $status);
    }

    /**
     * reconfigure AICCU, generate config and reload
     */
    public function reconfigureAction()
    {
        if ($this->request->isPost()) {
            // close session for long running action
            $this->sessionClose();

            $mdlAiccu = new Aiccu();
            $backend = new Backend();

            $runStatus = $this->statusAction();

            // stop AICCU when disabled
            if ($runStatus['status'] == "running" && $mdlAiccu->general->enabled->__toString() == 0) {
                $this->stopAction();
            }

            // generate template
            $response = $backend->configdRun("template reload SixXS.AICCU");

            if (strpos($response, "OK") == 0) {
                // (res)start daemon
                if ($mdlAiccu->general->enabled->__toString() == 1) {
                    if ($runStatus['status'] == "running") {
                        $response = $backend->configdRun("aiccu restart");
                    } else {
                        $response = $backend->configdRun("aiccu start");
                    }
                }
            } else {
                return array("status" => "failed");
            }
        } else {
            return array("status" => "failed");
        }

        return array("status" => "ok");
    }
}

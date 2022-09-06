<?php

namespace app\services\payment\banks\interfaces;

use app\services\payment\banks\data\ClientData;
use app\services\payment\banks\data\P2pData;
use app\services\payment\banks\results\BaseP2pResult;

/**
 * Segregate bank adapter interface allowing to excecute P2P (peer-to-peer or person-to-person) payments.
 */
interface P2p
{
    /**
     * Executes P2P (peer-to-peer) payment with specified data.
     */
    public function executeP2p(P2pData $p2pData, ClientData $clientData): BaseP2pResult;
}
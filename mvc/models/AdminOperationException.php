<?php

/**
 * Erreur metier d'une operation sensible de l'admin du catalogue.
 * Porte le statut HTTP a retourner, un code interne non affiche,
 * et l'identifiant de l'operation concernee quand elle existe.
 */
class AdminOperationException extends Exception
{
    protected $httpStatus;
    protected $errorCode;
    protected $operationId;

    public function __construct ($message, $httpStatus = 422, $errorCode = 'BUSINESS_RULE',
                                 $operationId = null, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->httpStatus = $httpStatus;
        $this->errorCode = $errorCode;
        $this->operationId = $operationId;
    }

    public function httpStatus ()
    {
        return $this->httpStatus;
    }

    public function errorCode ()
    {
        return $this->errorCode;
    }

    public function operationId ()
    {
        return $this->operationId;
    }
}

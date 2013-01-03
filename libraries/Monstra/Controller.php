<?php

namespace Monstra;

abstract class Controller
{
    /**
     * Holds the request object that loaded the controller.
     *
     * @var monstra\Request
     */
    protected $request;

    /**
     * Holds request response object.
     *
     * @var monstra\Response
     */
    protected $response;

    /**
     * Constructor.
     *
     * @access  public
     * @param   monstra\Request   A request object
     * @param   monstra\Response  A response object
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * This method runs before the action.
     *
     * @access  public
     */
    public function before() { }

    /**
     * This method runs after the action.
     *
     * @access  public
     */
    public function after() { }

}
